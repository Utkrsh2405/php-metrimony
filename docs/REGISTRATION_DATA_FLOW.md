# Registration Data Flow - Complete Guide

## ✅ How Registration Data Works

This document explains how data flows from registration through to profile display and search.

---

## Database Structure

### 1. `users` Table (Authentication)
Stores basic login credentials and user identity.

```sql
id              INT (PK, Auto-increment)    -- User's unique ID
username        VARCHAR(20)                  -- Login username
password        VARCHAR(40)                  -- Hashed password (MD5/bcrypt)
email           VARCHAR(40)                  -- User's email
dateofbirth     DATE                         -- Birth date (YYYY-MM-DD)
gender          VARCHAR(5)                   -- Male/Female
profilestat     INT                          -- Profile completion status (0=incomplete)
userlevel       INT                          -- 0=user, 1=admin
```

### 2. `customer` Table (Detailed Profile)
Stores complete matrimonial profile information.

```sql
id              INT (PK, Auto-increment)    -- Customer record ID
cust_id         INT (FK → users.id)         -- Links to users table **CRITICAL**
email           VARCHAR(60)                 -- User's email (duplicate for queries)
firstname       TEXT                        -- First name (from username)
lastname        TEXT                        -- Last name
age             VARCHAR(10)                 -- Age (calculated from birth year)
sex             VARCHAR(6)                  -- Gender (Male/Female)
height          INT                         -- Height in cm
dateofbirth     DATE                        -- Birth date
religion        VARCHAR(20)                 -- Religion
caste           VARCHAR(20)                 -- Caste
subcaste        VARCHAR(20)                 -- Sub-caste
mothertounge    TEXT                        -- Mother tongue
maritalstatus   VARCHAR(20)                 -- Never Married/Divorced/Widowed/Separated
country         VARCHAR(10)                 -- Country (India/USA/etc)
state           VARCHAR(20)                 -- State
district        VARCHAR(20)                 -- City (stored in district field)
profilecreatedby VARCHAR(20)                -- Self/Parent/Sibling
education       TEXT                        -- Education level
occupation      TEXT                        -- Occupation
annual_income   VARCHAR(20)                 -- Annual income
... (35+ more fields for complete profile)
```

**🔑 KEY RELATIONSHIP**: `customer.cust_id` = `users.id` (Foreign Key)

---

## Registration Process

### Step 1: User Fills Form (`register.php`)

User enters:
- ✅ Email
- ✅ Password (+ confirmation)
- ✅ Name (becomes username)
- ✅ Gender (Male/Female)
- ✅ Date of Birth (day/month/year)
- ✅ Height
- ✅ Mother Tongue
- ✅ Religion
- ✅ Caste
- ✅ Sub-caste (optional)
- ✅ Marital Status
- ✅ Country
- ✅ State
- ✅ City
- ✅ Contact Address
- ✅ Mobile Number

### Step 2: Backend Validation (`functions.php → register()`)

System checks:
1. ✅ All required fields filled
2. ✅ Password confirmation matches
3. ✅ Password minimum 6 characters
4. ✅ Valid email format
5. ✅ Complete date of birth
6. ✅ Username not already taken
7. ✅ Email not already registered

### Step 3: Data Saved to Database

**Transaction 1: Create User Account**
```sql
INSERT INTO users (
    profilestat, username, password, email, 
    dateofbirth, gender, userlevel
) VALUES (
    0,                      -- Profile incomplete
    'user_name',            -- Username
    'md5_hash',             -- Password (MD5)
    'user@email.com',       -- Email
    '1995-06-15',          -- DOB
    'Male',                 -- Gender
    0                       -- Regular user (not admin)
)
```

**Transaction 2: Create Profile**
```sql
INSERT INTO customer (
    cust_id,                -- ← users.id from above
    email,
    age,                    -- Calculated from birth year
    height,
    sex,
    religion,
    caste,
    subcaste,
    district,               -- City
    state,
    country,
    maritalstatus,
    profilecreatedby,       -- 'Self'
    mothertounge,
    firstname,              -- Username
    dateofbirth,
    profilecreationdate     -- CURDATE()
) VALUES (...)
```

**🎯 CRITICAL**: `cust_id` is set to the `users.id` from the first INSERT

### Step 4: Auto-Rollback on Error

If customer INSERT fails:
```sql
DELETE FROM users WHERE id = $user_id
```
Ensures data integrity - no orphaned user records.

---

## Data Retrieval

### 1. Search Profiles (`api/search.php`)

**Query Structure:**
```sql
SELECT 
    c.cust_id as id,           -- ← Use cust_id (links to users.id)
    c.firstname,
    c.lastname,
    c.sex as gender,
    c.age,
    c.height,
    c.religion,
    c.maritalstatus,
    c.mothertounge,
    c.caste,
    c.state,
    c.district,
    u.username,
    u.profilestat
FROM customer c
LEFT JOIN users u ON c.cust_id = u.id    -- ← JOIN on cust_id
WHERE c.cust_id != $current_user_id      -- Exclude self
AND (u.userlevel = 0 OR u.userlevel IS NULL)  -- Only regular users
-- Filter conditions applied here
```

**Available Search Filters:**
- Gender (auto-filters to opposite gender)
- Age range (min/max)
- Marital status
- Religion
- Caste
- Height range
- State
- City
- Mother tongue
- Education
- Occupation
- Keyword search (across multiple fields)

### 2. View Profile (`view_profile.php?id=123`)

**URL Parameter**: `id` = User's ID (from users table) = `customer.cust_id`

**Query:**
```sql
SELECT * FROM customer WHERE cust_id = $id
```

**Data Retrieved:**
- All 35+ profile fields
- Personal info (name, age, height, gender)
- Religious/social background (religion, caste, sub-caste)
- Location (country, state, city)
- Family (brothers, sisters, parents' occupation)
- Education & Career (education, occupation, income)
- Lifestyle (diet, drink, smoke)
- Physical attributes (body type, complexion, weight, blood group)
- About me (free text)

### 3. Featured Profiles (`index.php`)

**Query:**
```sql
SELECT * FROM customer 
ORDER BY cust_id DESC    -- Latest registrations first
LIMIT 12
```

**Profile Links:**
```php
<a href="view_profile.php?id=<?php echo $row['cust_id']; ?>">
```

### 4. User's Own Profile (`userhome.php?id=123`)

User is redirected with their own `id` from session:
```php
$_SESSION['id']  // This is users.id
```

Access own profile data:
```sql
SELECT * FROM customer WHERE cust_id = $id
```

---

## Data Flow Diagram

```
┌─────────────────┐
│  Registration   │
│  Form Filled    │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│   Validation    │
│  (Backend PHP)  │
└────────┬────────┘
         │
         ▼
┌─────────────────┐     ┌──────────────────┐
│  INSERT users   │────→│  Get inserted    │
│  (Basic Auth)   │     │  users.id        │
└─────────────────┘     └────────┬─────────┘
                                 │
                                 ▼
                        ┌─────────────────┐
                        │ INSERT customer │
                        │ cust_id = ^ID   │
                        └────────┬────────┘
                                 │
                                 ▼
                        ┌─────────────────┐
                        │  Registration   │
                        │   Complete!     │
                        └─────────────────┘

RETRIEVAL:

┌─────────────────┐
│  Search Query   │
└────────┬────────┘
         │
         ▼
┌──────────────────────────────────┐
│  SELECT FROM customer c          │
│  JOIN users u                    │
│  ON c.cust_id = u.id             │
└────────┬─────────────────────────┘
         │
         ▼
┌─────────────────┐
│  Results List   │
│  (with IDs)     │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  Click Profile  │
│  view_profile   │
│  .php?id=cust_id│
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ SELECT * FROM   │
│ customer WHERE  │
│ cust_id = $id   │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ Display Full    │
│ Profile Details │
└─────────────────┘
```

---

## Fields Saved During Registration

### ✅ Automatically Populated

| Field | Source | Example |
|-------|--------|---------|
| `cust_id` | `users.id` (auto) | 123 |
| `age` | Calculated from birth year | 28 |
| `firstname` | Username field | "John" |
| `profilecreatedby` | Hardcoded | "Self" |
| `profilecreationdate` | `CURDATE()` | 2025-12-06 |

### ✅ From Registration Form

| Field | Form Field | Example |
|-------|------------|---------|
| `email` | Email input | user@example.com |
| `sex` | Gender radio | Male/Female |
| `height` | Height dropdown | 5.8 (feet) |
| `religion` | Religion select | Hindu |
| `caste` | Caste select | Brahmin |
| `subcaste` | Sub-caste input | (optional) |
| `mothertounge` | Mother tongue select | Hindi |
| `maritalstatus` | Marital status radio | Never Married |
| `country` | Country select | India |
| `state` | State select | Maharashtra |
| `district` | City select | Mumbai |
| `dateofbirth` | Day/Month/Year | 1995-06-15 |

### ⚠️ Set to Default (Empty/Zero)

These fields are initialized but can be filled later via "Edit Profile":

- `lastname` - Empty (can add later)
- `education` - Empty
- `occupation` - Empty
- `annual_income` - Empty
- `weight` - 0
- `colour` (complexion) - Empty
- `blood_group` - Empty
- `diet` - Empty
- `drink` - Empty
- `smoke` - Empty
- `body_type` - Empty
- `physical_status` - Empty
- `fathers_occupation` - Empty
- `mothers_occupation` - Empty
- `no_bro` - 0
- `no_sis` - 0
- `aboutme` - Empty

---

## Profile Visibility in Search

### ✅ Profile is Searchable if:

1. ✅ `customer.sex` is not empty (Male/Female)
2. ✅ `customer.age` > 0
3. ✅ `users.userlevel` = 0 (not admin)
4. ✅ Matches search criteria

### Search Query Logic

```sql
WHERE c.cust_id != $current_user_id          -- Not yourself
AND (u.userlevel = 0 OR u.userlevel IS NULL) -- Regular users only
AND c.sex = 'Female'                         -- Gender filter
AND c.age >= 21 AND c.age <= 35              -- Age range
AND c.religion = 'Hindu'                     -- Religion filter
AND c.state = 'Maharashtra'                  -- Location filter
-- etc...
```

---

## How to Use Registration Data

### 1. **Search for Profiles**
```php
// User searches for: Female, 25-30, Hindu, Mumbai
$query = "SELECT c.cust_id, c.firstname, c.age, c.height, c.religion
          FROM customer c
          WHERE c.sex = 'Female'
          AND c.age BETWEEN 25 AND 30
          AND c.religion = 'Hindu'
          AND c.district = 'Mumbai'";
```

### 2. **View Profile Details**
```php
// User clicks on Profile ID 456
$id = 456; // This is cust_id
$query = "SELECT * FROM customer WHERE cust_id = $id";
// Displays all profile fields
```

### 3. **Send Interest**
```php
// User wants to send interest to Profile ID 456
INSERT INTO interests (from_user, to_user, status)
VALUES ($my_id, 456, 'pending');
```

### 4. **Edit Own Profile**
```php
// User edits their profile (ID from session)
$my_id = $_SESSION['id']; // This is users.id
UPDATE customer 
SET education = 'MBA', occupation = 'Engineer', annual_income = '500000'
WHERE cust_id = $my_id;
```

---

## Testing Checklist

Use `test-registration-flow.php` to verify:

- [x] Users and customer tables exist with correct columns
- [x] `customer.cust_id` correctly links to `users.id`
- [x] Registration data saves to both tables
- [x] Search queries return results
- [x] Profile view displays complete data
- [x] All required fields populated during registration

---

## Common Issues & Solutions

### Issue 1: "Profile not found"
**Cause**: Query uses `customer.id` instead of `customer.cust_id`  
**Fix**: Always use `customer.cust_id` to link to `users.id`

```sql
-- ❌ WRONG
SELECT * FROM customer WHERE id = $user_id

-- ✅ CORRECT
SELECT * FROM customer WHERE cust_id = $user_id
```

### Issue 2: "No search results"
**Cause**: Missing gender or age in profile  
**Fix**: Ensure registration saves `sex` and `age`

### Issue 3: "Profile shows empty fields"
**Cause**: Fields not filled during registration  
**Fix**: User should complete profile via "Edit Profile"

### Issue 4: "Wrong profile displayed"
**Cause**: JOIN uses wrong key  
**Fix**: Use `ON c.cust_id = u.id` not `ON c.id = u.id`

```sql
-- ❌ WRONG
LEFT JOIN users u ON c.id = u.id

-- ✅ CORRECT
LEFT JOIN users u ON c.cust_id = u.id
```

---

## Summary

✅ **Registration saves data to BOTH tables**
- `users` table: Authentication (username, password, email, DOB, gender)
- `customer` table: Profile (35+ fields linked via `cust_id`)

✅ **Search uses customer table with JOIN**
- Query: `SELECT FROM customer c JOIN users u ON c.cust_id = u.id`
- Filters by gender, age, religion, location, etc.

✅ **Profile view uses cust_id**
- URL: `view_profile.php?id=123` where 123 = `customer.cust_id` = `users.id`
- Query: `SELECT * FROM customer WHERE cust_id = $id`

✅ **All registration data is immediately searchable**
- Name, age, gender, height, religion, caste, location
- Additional fields can be filled via "Edit Profile"

---

**Delete test-registration-flow.php after verification!**
