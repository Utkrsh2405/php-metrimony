# Admin Member Management System

## Overview
The admin member management system provides comprehensive tools for administrators to manage, monitor, and moderate user accounts on the matrimony platform.

## Components

### 1. Member List Interface (`admin/members.php`)
**Location:** `/admin/members.php`

**Features:**
- Paginated member listing with 20 members per page
- Real-time search by name, email, or username
- Filter by account status (active, suspended, deleted)
- Filter by subscription plan
- Export members to CSV
- Quick action buttons (View, Edit, Suspend/Activate, Delete)

**Search & Filters:**
```javascript
// Search across multiple fields
- Username
- Email
- First Name
- Last Name

// Status Filter
- Active: Normal functioning accounts
- Suspended: Temporarily disabled accounts
- Deleted: Soft-deleted accounts (marked as deleted)

// Plan Filter
- Filter by subscription plan ID
```

**Member Information Displayed:**
- Member ID
- Full Name with verification badge
- Email address
- Age and Gender
- Location (State)
- Current subscription plan
- Account status (with color-coded badges)
- Profile completion percentage (visual progress bar)
- Last login date
- Action buttons

### 2. Member Management API (`admin/api/members.php`)
**Location:** `/admin/api/members.php`

**Endpoints:**

#### GET Request - List Members
```
URL: /admin/api/members.php
Method: GET
Parameters:
  - page: Page number (default: 1)
  - limit: Results per page (default: 20)
  - search: Search term (optional)
  - status: Account status filter (optional)
  - plan_id: Plan ID filter (optional)

Response:
{
  "success": true,
  "data": [...members array...],
  "pagination": {
    "total": 150,
    "page": 1,
    "limit": 20,
    "pages": 8
  }
}
```

#### POST Request - Update Member Status
```
URL: /admin/api/members.php
Method: POST
Content-Type: application/json
Body:
{
  "action": "suspend|activate|verify|delete",
  "member_id": 123
}

Actions:
- suspend: Set account_status to 'suspended'
- activate: Set account_status to 'active'
- verify: Set is_verified to 1 in customer table
- delete: Set account_status to 'deleted' (soft delete)

Response:
{
  "success": true,
  "message": "Member suspended"
}
```

#### DELETE Request - Permanent Delete
```
URL: /admin/api/members.php?id=123
Method: DELETE

Note: This permanently removes the member from the database.
Use with extreme caution!

Response:
{
  "success": true,
  "message": "Member permanently deleted"
}
```

### 3. Member Edit Interface (`admin/member-edit.php`)
**Location:** `/admin/member-edit.php?id=123`

**Features:**
- Edit user account details (username, email)
- Edit customer profile information
- Update account status
- Toggle verification status
- Adjust profile completion percentage
- Activity logging for all changes

**Editable Fields:**
- **Account Information:**
  - Username
  - Email
  - Account Status (active/suspended/deleted)
  - Profile Completeness (0-100%)
  
- **Personal Information:**
  - First Name, Last Name
  - Gender, Age
  - Mobile Number
  - State/Location
  
- **Profile Details:**
  - Marital Status
  - Religion, Caste
  - Education, Occupation
  - Income Range
  - Height, Weight, Complexion
  - About Me text
  
- **Verification:**
  - Verified Profile Badge (checkbox)

### 4. Export Members to CSV (`admin/api/export-members.php`)
**Location:** `/admin/api/export-members.php`

**Features:**
- Export filtered member data to CSV
- Applies same filters as member list
- Comprehensive data export including:
  - All account information
  - Personal details
  - Profile completeness
  - Subscription information
  - Activity timestamps

**CSV Columns:**
```
ID, Username, Email, First Name, Last Name, Gender, Age, 
Date of Birth, Mobile, State, Marital Status, Religion, 
Caste, Education, Occupation, Income, Height, Weight, 
Account Status, Verified, Profile Completeness, 
Current Plan, Plan Start, Plan End, Last Login, 
Registration Date
```

## Security Features

### 1. Authentication & Authorization
```php
// All admin pages check:
1. Session exists: if (!isset($_SESSION['id']))
2. User is admin: userlevel = 1
3. User ID sanitized: $user_id = intval($_SESSION['id'])
```

### 2. Input Sanitization
```php
// All user inputs are sanitized:
- Integer IDs: intval($id)
- String inputs: mysqli_real_escape_string($conn, $input)
- Status values: validated against allowed values
```

### 3. Activity Logging
All member management actions are logged:
```php
Actions Logged:
- suspend: Member account suspended
- activate: Member account activated
- verify: Member profile verified
- delete: Member marked as deleted
- permanent_delete: Member permanently deleted
- update: Member details updated

Log includes:
- Admin ID who performed action
- Action type
- Member ID affected
- Description
- Old data (for updates)
- New data (for updates)
- IP address
- User agent
- Timestamp
```

### 4. SQL Injection Prevention
```php
// All queries use:
1. intval() for integer parameters
2. mysqli_real_escape_string() for string parameters
3. Prepared statements where applicable
```

## Member Status Workflow

### Status Types
1. **Active** (Default)
   - Normal functioning account
   - Can login and use all features
   - Visible in searches
   
2. **Suspended**
   - Temporarily disabled
   - Cannot login
   - Not visible in searches
   - Can be reactivated by admin
   
3. **Deleted**
   - Soft delete (data retained)
   - Cannot login
   - Not visible in searches
   - Can be reactivated if needed

### Status Transitions
```
Active <---> Suspended
  |              |
  v              v
Deleted -----> Permanent Delete
(soft)         (hard delete)
```

## Usage Guide

### Suspending a Member
1. Navigate to Admin > Members
2. Find the member (use search if needed)
3. Click the Ban icon (🚫) in the Actions column
4. Confirm the action in the modal
5. Member status changes to "Suspended"
6. Action is logged with admin details

### Activating a Suspended Member
1. Filter by Status: "Suspended"
2. Find the member
3. Click the Check icon (✓) in the Actions column
4. Confirm the action
5. Member status changes to "Active"

### Deleting a Member (Soft Delete)
1. Find the member
2. Click the Trash icon (🗑️)
3. Confirm deletion
4. Member status changes to "Deleted"
5. Member no longer appears in searches
6. Data is retained for potential recovery

### Editing Member Details
1. Click the Edit icon (✏️) next to member
2. Modify any fields as needed
3. Click "Update Member"
4. Changes are saved and logged

### Verifying a Member
1. Click the Eye icon to view member
2. Review profile details
3. From member list, use verify action
4. OR edit member and check "Verified" checkbox
5. Verified badge appears next to member name

### Exporting Member Data
1. Apply desired filters (status, plan, search)
2. Click "Export CSV" button
3. CSV file downloads automatically
4. File includes all filtered members with complete data

## Database Schema

### Tables Used
```sql
-- User accounts
users (
  id, username, email, account_status, 
  profile_completeness, last_login, userlevel
)

-- Customer profiles
customer (
  cust_id (FK to users.id), firstname, lastname, 
  sex, age, state, mobile, is_verified, ...
)

-- Subscriptions
user_subscriptions (
  user_id (FK to users.id), plan_id, 
  status, start_date, end_date
)

-- Activity logs
admin_activity_logs (
  admin_id, action, entity_type, entity_id,
  description, old_data, new_data, ip_address,
  user_agent, created_at
)
```

### Key Relationships
```
users.id = customer.cust_id (1:1)
users.id = user_subscriptions.user_id (1:many)
plans.id = user_subscriptions.plan_id (1:many)
```

## API Integration

### JavaScript Integration
```javascript
// Load members with filters
function loadMembers() {
    $.ajax({
        url: '/admin/api/members.php',
        method: 'GET',
        data: {
            search: $('#search').val(),
            status: $('#status-filter').val(),
            plan_id: $('#plan-filter').val(),
            page: currentPage
        },
        success: function(response) {
            renderMembers(response.data);
            renderPagination(response.pagination);
        }
    });
}

// Execute member action
function executeAction(action, memberId) {
    $.ajax({
        url: '/admin/api/members.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({
            action: action,
            member_id: memberId
        }),
        success: function(response) {
            if (response.success) {
                loadMembers(); // Reload table
            }
        }
    });
}
```

## Error Handling

### Common Error Responses
```json
// Unauthorized access
{"error": "Unauthorized"}

// Invalid member ID
{"error": "Invalid member ID"}

// Invalid action
{"error": "Invalid action"}

// Database error
{"error": "Failed to suspend member"}
```

### Client-Side Validation
- Member ID must be positive integer
- Action must be one of: suspend, activate, verify, delete
- Confirmation required for destructive actions

## Performance Considerations

### Pagination
- Default: 20 members per page
- Reduces database load
- Improves page load time

### Indexing
Recommended indexes:
```sql
CREATE INDEX idx_account_status ON users(account_status);
CREATE INDEX idx_userlevel ON users(userlevel);
CREATE INDEX idx_cust_id ON customer(cust_id);
CREATE INDEX idx_user_sub ON user_subscriptions(user_id, status);
```

### Query Optimization
- Uses LEFT JOIN for optional relationships
- Filters applied in WHERE clause
- LIMIT and OFFSET for pagination
- COUNT query separate from data query

## Maintenance

### Regular Tasks
1. Review activity logs weekly
2. Monitor suspended accounts
3. Clean up old "deleted" accounts (optional)
4. Export member data for backup monthly

### Troubleshooting

**Issue:** Members not loading
- Check database connection
- Verify admin authentication
- Check browser console for AJAX errors

**Issue:** Actions not working
- Verify member ID is valid
- Check activity logs for errors
- Ensure proper permissions

**Issue:** CSV export fails
- Check file permissions
- Verify sufficient memory
- Test with smaller filter set

## Best Practices

1. **Always use filters** before exporting large datasets
2. **Suspend before delete** - give members chance to appeal
3. **Document reasons** for suspensions/deletions
4. **Review activity logs** regularly
5. **Verify members** only after thorough profile review
6. **Use soft delete** (status='deleted') instead of permanent deletion
7. **Regular backups** before bulk operations
8. **Test on staging** before production changes

## Future Enhancements

### Planned Features
- Bulk actions (suspend/activate multiple members)
- Email notifications to members on status changes
- Advanced filtering (registration date, login activity)
- Member notes/comments for admins
- Automated suspension rules (inactivity, violations)
- Member activity timeline
- Profile quality scoring

## Security Checklist

✅ SQL injection prevention (intval, mysqli_real_escape_string)
✅ Admin authentication verification
✅ Authorization checks (userlevel = 1)
✅ Activity logging for audit trail
✅ Input validation on all parameters
✅ CSRF protection via session validation
✅ Secure file uploads (not applicable here)
✅ Error messages don't expose sensitive data

## Support & Maintenance

**Developer Contact:** System Administrator
**Documentation Version:** 1.0
**Last Updated:** November 2024

For issues or questions:
1. Check activity logs: `/admin/activity-logs.php`
2. Review error logs in server
3. Contact technical support
