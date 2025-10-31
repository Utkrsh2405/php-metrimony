# Admin Dashboard Specification

## Overview
This document outlines the architecture, features, and implementation plan for the comprehensive admin dashboard and member features for the Online Matrimonial Project.

## Architecture

### Directory Structure
```
php-metrimony/
├── admin/
│   ├── index.php                 # Dashboard home
│   ├── members.php               # Member management
│   ├── member-edit.php           # Edit member profile
│   ├── plans.php                 # Subscription plans
│   ├── payments.php              # Payment management
│   ├── sms-templates.php         # SMS template management
│   ├── sms-send.php              # Send SMS
│   ├── pages.php                 # CMS page management
│   ├── translations.php          # i18n management
│   ├── frontpage.php             # Homepage element config
│   ├── messages.php              # Message moderation
│   ├── interest-logs.php         # Interest/shortlist logs
│   ├── api/
│   │   ├── metrics.php           # Dashboard metrics API
│   │   ├── members.php           # Member CRUD API
│   │   └── ...
│   └── _layout.php               # Admin layout template
├── actions/
│   ├── interest.php              # Express interest
│   ├── shortlist.php             # Shortlist profile
│   └── remove-interest.php
├── messages/
│   ├── inbox.php
│   ├── outbox.php
│   ├── compose.php
│   └── chat.php
├── api/
│   ├── search.php                # Advanced search API
│   ├── messages/
│   │   └── send.php
│   ├── payments/
│   │   └── stripe_webhook.php
│   └── sms/
│       └── send.php
├── includes/
│   ├── admin-header.php          # Admin navigation
│   ├── permissions.php           # Permission checks
│   ├── plans.php                 # Plan logic
│   └── quotas.php                # Usage quotas
├── profile/
│   ├── progress.php              # Profile completion
│   └── handlers.php              # Profile update handlers
├── i18n/
│   ├── en.php                    # English translations
│   └── es.php                    # Spanish translations (example)
├── pages/
│   └── view.php                  # CMS page viewer
├── css/
│   └── admin.css                 # Admin-specific styles
└── db/
    └── migrations/
        ├── 001_add_plans.sql
        ├── 002_add_payments.sql
        ├── 003_add_messages.sql
        ├── 004_add_interests.sql
        ├── 005_add_pages.sql
        ├── 006_add_sms.sql
        ├── 007_add_translations.sql
        └── 008_add_usage_logs.sql
```

## Database Schema Changes

### New Tables

#### 1. `plans` - Subscription Plans
```sql
CREATE TABLE plans (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    duration_days INT NOT NULL,
    max_contacts_view INT DEFAULT 0,
    max_messages_send INT DEFAULT 0,
    max_interests_express INT DEFAULT 0,
    can_chat BOOLEAN DEFAULT 0,
    is_active BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### 2. `user_subscriptions` - User Plan Assignments
```sql
CREATE TABLE user_subscriptions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    plan_id INT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status ENUM('active', 'expired', 'cancelled') DEFAULT 'active',
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (plan_id) REFERENCES plans(id)
);
```

#### 3. `payments` - Payment Records
```sql
CREATE TABLE payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    subscription_id INT,
    amount DECIMAL(10,2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'USD',
    payment_method VARCHAR(50),
    transaction_id VARCHAR(255) UNIQUE,
    status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
    gateway VARCHAR(50),
    metadata JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (subscription_id) REFERENCES user_subscriptions(id)
);
```

#### 4. `messages` - Internal Messaging
```sql
CREATE TABLE messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    from_user_id INT NOT NULL,
    to_user_id INT NOT NULL,
    subject VARCHAR(255),
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT 0,
    is_deleted_by_sender BOOLEAN DEFAULT 0,
    is_deleted_by_receiver BOOLEAN DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (from_user_id) REFERENCES users(id),
    FOREIGN KEY (to_user_id) REFERENCES users(id),
    INDEX idx_receiver (to_user_id, is_read),
    INDEX idx_sender (from_user_id)
);
```

#### 5. `interests` - Express Interest
```sql
CREATE TABLE interests (
    id INT PRIMARY KEY AUTO_INCREMENT,
    from_user_id INT NOT NULL,
    to_user_id INT NOT NULL,
    status ENUM('pending', 'accepted', 'declined') DEFAULT 'pending',
    message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (from_user_id) REFERENCES users(id),
    FOREIGN KEY (to_user_id) REFERENCES users(id),
    UNIQUE KEY unique_interest (from_user_id, to_user_id)
);
```

#### 6. `shortlists` - Shortlisted Profiles
```sql
CREATE TABLE shortlists (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    profile_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (profile_id) REFERENCES users(id),
    UNIQUE KEY unique_shortlist (user_id, profile_id)
);
```

#### 7. `usage_logs` - Track Usage Quotas
```sql
CREATE TABLE usage_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    action_type ENUM('contact_view', 'message_send', 'interest_express') NOT NULL,
    target_user_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_user_action (user_id, action_type, created_at)
);
```

#### 8. `pages` - CMS Pages
```sql
CREATE TABLE pages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    slug VARCHAR(255) UNIQUE NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT,
    meta_description TEXT,
    is_published BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

#### 9. `sms_templates` - SMS Templates
```sql
CREATE TABLE sms_templates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    template_text TEXT NOT NULL,
    variables JSON,
    is_active BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### 10. `sms_logs` - SMS Sending Logs
```sql
CREATE TABLE sms_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    phone_number VARCHAR(20),
    message TEXT,
    status ENUM('queued', 'sent', 'failed') DEFAULT 'queued',
    provider_response JSON,
    sent_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

#### 11. `translations` - i18n Strings
```sql
CREATE TABLE translations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    lang_code VARCHAR(5) NOT NULL,
    translation_key VARCHAR(255) NOT NULL,
    translation_value TEXT NOT NULL,
    UNIQUE KEY unique_translation (lang_code, translation_key)
);
```

#### 12. `frontpage_config` - Homepage Elements
```sql
CREATE TABLE frontpage_config (
    id INT PRIMARY KEY AUTO_INCREMENT,
    element_type ENUM('banner', 'featured_profile', 'testimonial', 'block') NOT NULL,
    title VARCHAR(255),
    content TEXT,
    image_url VARCHAR(255),
    link_url VARCHAR(255),
    position INT DEFAULT 0,
    is_active BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Modified Tables

#### Update `users` table
```sql
ALTER TABLE users ADD COLUMN account_status ENUM('active', 'suspended', 'deleted') DEFAULT 'active';
ALTER TABLE users ADD COLUMN profile_completeness INT DEFAULT 0;
ALTER TABLE users ADD COLUMN last_login TIMESTAMP NULL;
```

#### Update `customer` table
```sql
ALTER TABLE customer ADD COLUMN is_verified BOOLEAN DEFAULT 0;
ALTER TABLE customer ADD COLUMN admin_notes TEXT;
```

## API Endpoints

### Admin APIs

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/admin/api/metrics.php` | GET | Dashboard metrics (counts, charts) |
| `/admin/api/members.php` | GET | List all members with pagination |
| `/admin/api/members.php` | POST | Create/Update member |
| `/admin/api/members.php?id=X` | DELETE | Delete/suspend member |
| `/admin/api/members.php?export=csv` | GET | Export members to CSV |
| `/admin/api/plans.php` | GET/POST | Manage plans |
| `/admin/api/payments.php` | GET | Payment history |
| `/admin/api/sms.php` | POST | Send SMS |
| `/admin/api/pages.php` | GET/POST/DELETE | Manage CMS pages |
| `/admin/api/translations.php` | GET/POST | Manage translations |
| `/admin/api/frontpage.php` | GET/POST/DELETE | Manage frontpage elements |

### Member APIs

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/api/search.php` | GET | Advanced search with filters |
| `/api/messages/send.php` | POST | Send internal message |
| `/api/messages/list.php` | GET | Get inbox/outbox |
| `/api/interest.php` | POST | Express interest |
| `/api/shortlist.php` | POST | Add/remove shortlist |
| `/api/profile/progress.php` | GET | Get profile completeness |
| `/api/payments/stripe_webhook.php` | POST | Stripe webhook handler |

## UI/UX Design

### Admin Dashboard Components

#### 1. Admin Sidebar Navigation
```
┌─────────────────────┐
│ Admin Panel         │
├─────────────────────┤
│ 📊 Dashboard        │
│ 👥 Members          │
│ 💳 Plans            │
│ 💰 Payments         │
│ 📱 SMS Templates    │
│ 📄 Pages (CMS)      │
│ 🌐 Translations     │
│ 🏠 Homepage Config  │
│ 💬 Messages         │
│ ❤️  Interests/Logs  │
│ ⚙️  Settings        │
└─────────────────────┘
```

#### 2. Dashboard Metrics Cards
```
┌──────────────┬──────────────┬──────────────┬──────────────┐
│ Total Members│ Active Plans │ Payments     │ Messages     │
│    1,234     │      89      │  $12,456     │     456      │
│  ↑ 12%      │   ↑ 5%      │  ↑ 23%      │   ↓ 3%      │
└──────────────┴──────────────┴──────────────┴──────────────┘
```

#### 3. Member Management Table
```
┌────┬─────────────┬──────────────┬────────────┬──────────┬─────────┐
│ ID │ Name        │ Email        │ Plan       │ Status   │ Actions │
├────┼─────────────┼──────────────┼────────────┼──────────┼─────────┤
│ 12 │ John Doe    │ john@ex.com  │ Premium    │ Active   │ [Edit]  │
│ 13 │ Jane Smith  │ jane@ex.com  │ Free       │ Suspended│ [Edit]  │
└────┴─────────────┴──────────────┴────────────┴──────────┴─────────┘
```

### Member Features UI

#### 1. Profile Progress Bar
```
Profile Completion: 65%
████████████████░░░░░░░░░ 65%

Missing:
☐ Upload photos (20%)
☐ Add partner preferences (10%)
☐ Verify phone number (5%)
```

#### 2. Advanced Search Filters
```
┌─────────────────────────┐
│ Advanced Search         │
├─────────────────────────┤
│ Age: [18] to [35]      │
│ Religion: [All ▼]      │
│ Caste: [All ▼]         │
│ State: [All ▼]         │
│ Education: [All ▼]     │
│ Occupation: [All ▼]    │
│ Income: [Min] - [Max]  │
│ [Search] [Reset]       │
└─────────────────────────┘
```

#### 3. Messaging Interface
```
┌─────────────────────────────────────┐
│ Inbox (12)  |  Sent (45)            │
├─────────────────────────────────────┤
│ From: Sarah | Subject: Hi!          │
│ Received: 2 hours ago               │
│ Message preview text here...        │
│                          [Reply]    │
└─────────────────────────────────────┘
```

## Implementation Phases

### Phase 1: Foundation (Todos 1-3)
- ✓ Planning & specification (current)
- Database migrations
- Admin layout and navigation
- Basic dashboard UI

### Phase 2: Core Admin Features (Todos 4-7)
- Member management
- Profile management
- Plans & subscriptions
- Payment gateway integration

### Phase 3: Communications (Todos 8-10)
- Contact view permissions
- SMS templates & sending
- i18n/translations

### Phase 4: Content Management (Todos 11-12)
- CMS for pages
- Homepage element management

### Phase 5: Member Features (Todos 13-17)
- Advanced search
- Express interest & shortlist
- Internal messaging & chat
- Quotas enforcement
- Profile progress bar

### Phase 6: Polish & Deploy (Todos 18-20)
- Security hardening
- Testing
- Documentation
- Deployment

## Authentication & Authorization

### Admin Access
- Admin users identified by `userlevel = 1` in `users` table
- All `/admin/*` routes check authentication via session
- Admin-specific permissions stored in new `admin_permissions` table (future)

### Member Permissions
- Plan-based permissions checked before:
  - Viewing contact details
  - Sending messages
  - Expressing interest
  - Using chat feature
- Quotas tracked in `usage_logs` table

## Security Considerations

1. **SQL Injection Prevention**: Use prepared statements everywhere
2. **Password Hashing**: Implement `password_hash()` for all passwords
3. **CSRF Protection**: Add tokens to all forms
4. **XSS Prevention**: Escape all output with `htmlspecialchars()`
5. **File Upload Security**: Validate file types and sizes
6. **Session Security**: Use `session_regenerate_id()` after login
7. **Payment Security**: Never store full card details; use tokenization

## Technology Choices

### Frontend
- **UI Framework**: Bootstrap 4 (already in use)
- **Icons**: Font Awesome (already in use)
- **Charts**: Chart.js for dashboard metrics
- **WYSIWYG**: TinyMCE for CMS editor
- **DataTables**: For admin tables with sorting/filtering

### Backend
- **PHP Version**: 8.3
- **Database**: MySQL 5.7+
- **Payment Gateway**: Stripe (recommended) or PayPal
- **SMS Provider**: Twilio (recommended) or alternatives
- **Session Storage**: File-based (upgrade to Redis for scale)

### Optional Enhancements
- **Caching**: Redis for session and data caching
- **Queue**: Simple DB-based queue for SMS/emails
- **Real-time**: Socket.io or Pusher for chat
- **CDN**: For static assets in production

## Testing Strategy

1. **Unit Tests**: Core functions (permissions, quotas, plan logic)
2. **Integration Tests**: Payment flows, messaging, search
3. **Security Tests**: SQL injection, XSS, CSRF
4. **Manual Testing**: Admin workflows, member workflows
5. **Performance Tests**: Search with large datasets

## Deployment Checklist

- [ ] Run all database migrations
- [ ] Configure payment gateway credentials
- [ ] Configure SMS provider credentials
- [ ] Set up SSL certificate
- [ ] Configure email settings
- [ ] Set up backups
- [ ] Configure logging
- [ ] Security audit
- [ ] Performance optimization
- [ ] User acceptance testing

## Future Enhancements (Backlog)

1. Mobile app APIs (REST/GraphQL)
2. Advanced matching algorithm (ML-based)
3. Video call integration
4. Blog/content marketing tools
5. Referral program
6. Analytics dashboard
7. A/B testing framework
8. Multi-tenant support
9. White-label capabilities
10. Advanced reporting

---

**Status**: Phase 1 - Planning Complete ✓  
**Next**: Begin database migrations and admin layout implementation
