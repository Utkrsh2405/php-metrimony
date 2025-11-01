# Homepage Customization Guide

## Overview
The admin dashboard now includes powerful homepage customization features that allow you to personalize your matrimony website without coding knowledge.

## Features

### 1. Banner Image Upload
Upload and change the homepage banner image directly from the admin dashboard.

**Location:** Admin Dashboard → Homepage Configuration

**Features:**
- Upload new banner images (JPG, PNG, GIF, WebP)
- Maximum file size: 5MB
- Recommended dimensions: 1920x600px
- Live preview of current banner
- Automatic old banner cleanup

**How to Use:**
1. Go to **Admin Dashboard** → **Homepage Configuration**
2. Find the "Homepage Banner Image" section at the top
3. Click **Choose File** and select your image
4. Click **Upload Banner**
5. View the preview to confirm the upload

**API Endpoint:** `/admin/api/upload-banner.php`

---

### 2. Custom HTML/CSS Sections
Create completely custom sections on your homepage with your own HTML and CSS code.

**Location:** Admin Dashboard → Homepage Configuration → Custom HTML/CSS Block

**Features:**
- Add custom HTML content
- Write custom CSS styling
- Live preview in a new tab
- Full Bootstrap 3 support
- Font Awesome icons available
- Toggle section on/off
- Reorder sections with drag-and-drop

**How to Use:**

#### Step 1: Access Custom HTML Section
1. Go to **Admin Dashboard** → **Homepage Configuration**
2. Scroll to the **Custom HTML/CSS Block** section
3. Click to expand it

#### Step 2: Add Your HTML
In the **HTML Content** textarea, add your custom HTML:

```html
<div class="my-custom-section">
  <div class="container">
    <h2>Welcome to Our Service</h2>
    <p>Find your perfect match with our advanced matching algorithm.</p>
    <div class="row">
      <div class="col-md-4">
        <i class="fa fa-heart fa-3x"></i>
        <h3>Find Love</h3>
        <p>Connect with compatible matches</p>
      </div>
      <div class="col-md-4">
        <i class="fa fa-shield fa-3x"></i>
        <h3>Stay Safe</h3>
        <p>Verified profiles and secure messaging</p>
      </div>
      <div class="col-md-4">
        <i class="fa fa-users fa-3x"></i>
        <h3>Join Community</h3>
        <p>Thousands of success stories</p>
      </div>
    </div>
  </div>
</div>
```

#### Step 3: Add Custom CSS (Optional)
In the **Custom CSS** textarea, add your styling:

```css
.my-custom-section {
  padding: 60px 0;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  text-align: center;
}

.my-custom-section h2 {
  font-size: 36px;
  margin-bottom: 20px;
  font-weight: bold;
}

.my-custom-section .col-md-4 {
  margin-bottom: 30px;
}

.my-custom-section .fa {
  color: #ffd700;
  margin-bottom: 15px;
}
```

#### Step 4: Preview Your Section
1. Click the **Preview in New Tab** button
2. Review how your section will look
3. Make adjustments as needed

#### Step 5: Activate and Save
1. Toggle the switch to **Active** (green)
2. Click **Save All Changes** at the top
3. Visit your homepage to see the new section

---

## Available Bootstrap Classes

You can use any Bootstrap 3 classes in your custom HTML:

### Layout
- `.container` - Fixed width container
- `.container-fluid` - Full width container
- `.row` - Row for grid system
- `.col-md-*` - Grid columns (1-12)

### Typography
- `.text-center` - Center align text
- `.text-left` - Left align text
- `.text-right` - Right align text
- `.lead` - Make text stand out

### Buttons
- `.btn .btn-primary` - Primary button
- `.btn .btn-success` - Success button
- `.btn .btn-info` - Info button
- `.btn .btn-lg` - Large button

### Components
- `.panel .panel-default` - Panel container
- `.alert .alert-info` - Alert box
- `.well` - Inset well container
- `.jumbotron` - Large showcase component

### Icons (Font Awesome)
- `.fa .fa-heart` - Heart icon
- `.fa .fa-users` - Users icon
- `.fa .fa-check` - Check icon
- `.fa .fa-star` - Star icon
- Use `.fa-2x`, `.fa-3x`, `.fa-4x` for sizing

---

## Example Sections

### Example 1: Call-to-Action Section
```html
<div class="cta-section">
  <div class="container">
    <div class="row">
      <div class="col-md-8">
        <h2>Ready to Find Your Perfect Match?</h2>
        <p class="lead">Join thousands of happy couples who found love through our platform.</p>
      </div>
      <div class="col-md-4 text-center">
        <a href="/register.php" class="btn btn-success btn-lg">
          <i class="fa fa-user-plus"></i> Register Now
        </a>
      </div>
    </div>
  </div>
</div>
```

```css
.cta-section {
  padding: 50px 0;
  background: #f8f9fa;
  border-top: 3px solid #28a745;
  border-bottom: 3px solid #28a745;
}

.cta-section h2 {
  margin-top: 10px;
  color: #333;
}
```

### Example 2: Feature Highlights
```html
<div class="features">
  <div class="container">
    <h2 class="text-center">Why Choose Us?</h2>
    <hr style="width: 100px; border-color: #e74c3c; border-width: 2px;">
    <div class="row">
      <div class="col-md-3 text-center">
        <div class="feature-box">
          <i class="fa fa-check-circle fa-4x"></i>
          <h4>Verified Profiles</h4>
          <p>All profiles are verified for authenticity</p>
        </div>
      </div>
      <div class="col-md-3 text-center">
        <div class="feature-box">
          <i class="fa fa-lock fa-4x"></i>
          <h4>100% Privacy</h4>
          <p>Your data is completely secure</p>
        </div>
      </div>
      <div class="col-md-3 text-center">
        <div class="feature-box">
          <i class="fa fa-search fa-4x"></i>
          <h4>Smart Matching</h4>
          <p>AI-powered compatibility matching</p>
        </div>
      </div>
      <div class="col-md-3 text-center">
        <div class="feature-box">
          <i class="fa fa-headphones fa-4x"></i>
          <h4>24/7 Support</h4>
          <p>We're always here to help</p>
        </div>
      </div>
    </div>
  </div>
</div>
```

```css
.features {
  padding: 60px 0;
  background: white;
}

.features h2 {
  font-size: 32px;
  margin-bottom: 10px;
  color: #2c3e50;
}

.feature-box {
  padding: 30px 15px;
  transition: transform 0.3s;
}

.feature-box:hover {
  transform: translateY(-5px);
}

.feature-box .fa {
  color: #e74c3c;
  margin-bottom: 20px;
}

.feature-box h4 {
  font-size: 20px;
  font-weight: bold;
  color: #34495e;
  margin: 15px 0;
}
```

### Example 3: Video Background Section
```html
<div class="video-section">
  <div class="overlay">
    <div class="container">
      <h1>Find Your Soulmate Today</h1>
      <p class="lead">India's #1 Trusted Matrimony Service</p>
      <a href="/register.php" class="btn btn-primary btn-lg">Get Started Free</a>
    </div>
  </div>
</div>
```

```css
.video-section {
  background: url('/images/couple-bg.jpg') center center no-repeat;
  background-size: cover;
  min-height: 400px;
  display: flex;
  align-items: center;
  position: relative;
}

.video-section .overlay {
  background: rgba(0, 0, 0, 0.5);
  width: 100%;
  padding: 80px 0;
  text-align: center;
}

.video-section h1 {
  color: white;
  font-size: 48px;
  font-weight: bold;
  margin-bottom: 20px;
  text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
}

.video-section .lead {
  color: white;
  font-size: 24px;
  margin-bottom: 30px;
}
```

---

## Section Management

### Reordering Sections
1. Click **Reorder Sections** button
2. Drag sections using the handle (≡ icon)
3. Drop sections in desired order
4. Click **Save Order** button (green checkmark)

### Activating/Deactivating Sections
- Use the toggle switch next to each section
- Green = Active (visible on homepage)
- Gray = Inactive (hidden from homepage)
- Click **Save All Changes** to apply

### Display Order
- Sections are displayed from top to bottom based on their order
- Lower display order numbers appear first
- Use drag-and-drop to easily change order

---

## Database Tables

### `site_settings`
Stores global site settings including the banner image path.

```sql
CREATE TABLE site_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    setting_type VARCHAR(50) DEFAULT 'text',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    updated_by INT,
    FOREIGN KEY (updated_by) REFERENCES users(id)
);
```

### `homepage_config`
Stores all homepage sections including custom HTML/CSS blocks.

```sql
CREATE TABLE homepage_config (
    id INT PRIMARY KEY AUTO_INCREMENT,
    section_key VARCHAR(50) NOT NULL UNIQUE,
    section_title VARCHAR(100) NOT NULL,
    section_content TEXT,
    is_active TINYINT(1) DEFAULT 1,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

---

## API Endpoints

### Upload Banner
**POST** `/admin/api/upload-banner.php`

**Parameters:** 
- `banner` (file) - Image file to upload

**Response:**
```json
{
  "success": true,
  "message": "Banner uploaded successfully",
  "path": "/uploads/banners/banner_1234567890_abc123.jpg",
  "filename": "banner_1234567890_abc123.jpg"
}
```

### Get/Update Site Settings
**GET** `/admin/api/site-settings.php?key=homepage_banner`
**POST** `/admin/api/site-settings.php`

**POST Body:**
```json
{
  "setting_key": "homepage_banner",
  "setting_value": "/uploads/banners/new-banner.jpg",
  "setting_type": "image"
}
```

### Get/Update Homepage Sections
**GET** `/admin/api/frontpage.php`
**POST** `/admin/api/frontpage.php`

**POST Body:**
```json
{
  "id": 5,
  "is_active": 1,
  "content": {
    "html": "<div>Custom HTML</div>",
    "css": ".custom { color: red; }"
  }
}
```

**PUT** `/admin/api/frontpage.php` (Reorder)

**PUT Body:**
```json
{
  "order": [1, 3, 2, 5, 4]
}
```

---

## Security Considerations

### File Upload Security
- Only image files allowed (JPG, PNG, GIF, WebP)
- Maximum file size: 5MB
- Unique filename generation prevents conflicts
- Old banners automatically deleted
- Files stored outside web root when possible

### HTML/CSS Security
- Admin-only access (requires userlevel = 1)
- Content is escaped when stored
- Preview opens in isolated window
- Consider implementing CSP headers

### Best Practices
1. Always preview custom HTML before activating
2. Test responsiveness on mobile devices
3. Keep file sizes small for faster loading
4. Use CDN URLs for external resources
5. Backup before making major changes

---

## Troubleshooting

### Banner Upload Issues
**Problem:** Upload fails with "Failed to move uploaded file"
**Solution:** Check permissions on `/uploads/banners/` directory (should be 755)

**Problem:** Image doesn't appear after upload
**Solution:** Clear browser cache or hard refresh (Ctrl+F5)

### Custom HTML Issues
**Problem:** Section doesn't appear on homepage
**Solution:** 
1. Ensure section is toggled to **Active** (green)
2. Check that you clicked **Save All Changes**
3. Verify HTML doesn't have syntax errors

**Problem:** CSS not applying
**Solution:**
1. Check for CSS syntax errors
2. Use more specific selectors
3. Use `!important` if needed (sparingly)

### Permission Errors
**Problem:** "Unauthorized" error
**Solution:** Ensure you're logged in as admin (userlevel = 1)

---

## Migration

Applied migration: `015_add_banner_and_custom_sections.sql`

This migration:
- Creates `site_settings` table
- Adds default banner setting
- Adds custom HTML/CSS section to `homepage_config`
- Creates `/uploads/banners/` directory structure

---

## Support

For additional help:
1. Check admin activity logs
2. Review browser console for JavaScript errors
3. Check PHP error logs
4. Ensure all migrations are applied
5. Verify database connections

---

**Last Updated:** January 2025
**Version:** 1.0
