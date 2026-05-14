# Multi-User Role-Based Access System

## Overview
The admin dashboard now supports multiple users with different access levels, allowing you to grant appropriate permissions to different team members.

---

## Default User Accounts

### Admin Account
- **Username**: `admin`
- **Password**: `admin2025` (⚠️ **CHANGE BEFORE DEPLOYMENT**)
- **Access Level**: Full access to all features

### Portal Manager Account
- **Username**: `manager`
- **Password**: `manager2025` (⚠️ **CHANGE BEFORE DEPLOYMENT**)
- **Access Level**: Downloads management only

---

## User Roles Explained

### 🔴 Admin Role (Full Access)
**Who should have this role:** 
- Executives
- Sales team managers
- IT administrators
- Anyone who needs to manage customer leads

**What they can access:**
- ✅ **Contact Submissions Tab**: View, search, filter, and manage all customer inquiries
- ✅ **Downloads Tab**: Upload, edit, delete, and manage downloadable files
- ✅ **Settings Tab**: Change their own password
- ✅ Dashboard title shows: "Admin Dashboard"

**Typical use cases:**
- Review new leads from contact form
- Update lead status (New → Contacted → Converted → Closed)
- Add internal notes to leads
- Upload marketing materials
- Manage team resources

---

### 🟢 Portal Manager Role (Limited Access)
**Who should have this role:**
- Marketing team members
- Content managers
- Anyone who only needs to manage downloadable materials
- Staff who should NOT see customer contact information

**What they can access:**
- ❌ Contact Submissions Tab: **HIDDEN** (no access to customer leads)
- ✅ **Downloads Tab**: Upload, edit, delete, and manage downloadable files
- ✅ **Settings Tab**: Change their own password
- ✅ Dashboard title shows: "Portal Manager Dashboard"

**Typical use cases:**
- Upload new brochures, fact sheets, presentations
- Toggle file visibility (active/inactive)
- Update file descriptions
- Delete outdated materials
- Track download statistics

**Security features:**
- Cannot view customer contact data
- Cannot access lead management features
- If they try to access restricted areas via URL manipulation, they are redirected to Downloads tab

---

## Login Process

1. Navigate to: `https://pms.pluswealth.com/admin_dashboard.php`
2. Enter username and password
3. System authenticates user and loads appropriate dashboard
4. Header displays: "Logged in as: [Full Name] ([Role])"
5. Only authorized tabs are visible

---

## Security Features

### Password Security
- All passwords stored using `password_hash()` with `PASSWORD_DEFAULT` algorithm
- Passwords never stored in plain text
- Current password required before changing to new password
- Minimum 8 characters enforced

### Session Management
- Secure session-based authentication
- Session stores: `user_id`, `username`, `user_role`, `full_name`
- Session expires on logout
- No backdoor access

### Access Control
- Role checked on every page load
- Tabs hidden based on user role
- URL manipulation prevention (portal managers redirected from restricted views)
- Last login timestamp tracked for audit purposes

---

## How to Change Passwords

### For Admin Users
1. Log in with admin credentials
2. Click on **Settings** tab
3. Enter current password
4. Enter new password (minimum 8 characters)
5. Confirm new password
6. Click **Change Password**

### For Portal Manager Users
1. Log in with portal manager credentials
2. Click on **Settings** tab (only tab besides Downloads)
3. Enter current password
4. Enter new password (minimum 8 characters)
5. Confirm new password
6. Click **Change Password**

---

## Adding New Users (Manual Method)

Currently, new users must be added directly to the database. Here's how:

### Using phpMyAdmin or MySQL Console

```sql
-- Add a new admin user
INSERT INTO admin_users (username, password, role, full_name, email, is_active) 
VALUES (
    'john.doe',                              -- username
    PASSWORD_HASH_HERE,                      -- See note below
    'admin',                                 -- role (admin or portal_manager)
    'John Doe',                              -- full name
    'john.doe@pluswealth.net',               -- email
    1                                        -- is_active (1=active, 0=inactive)
);

-- Add a new portal manager
INSERT INTO admin_users (username, password, role, full_name, email, is_active) 
VALUES (
    'sarah.smith',
    PASSWORD_HASH_HERE,
    'portal_manager',
    'Sarah Smith',
    'sarah.smith@pluswealth.net',
    1
);
```

**To generate password hash:**
Use PHP's password_hash function. You can create a temporary PHP file:

```php
<?php
echo password_hash('your_password_here', PASSWORD_DEFAULT);
?>
```

Run this file in your browser, copy the hash, then delete the file.

---

## Deactivating Users

Instead of deleting users, you can deactivate them:

```sql
-- Deactivate a user (they cannot log in)
UPDATE admin_users 
SET is_active = 0 
WHERE username = 'username_here';

-- Reactivate a user
UPDATE admin_users 
SET is_active = 1 
WHERE username = 'username_here';
```

---

## Viewing All Users

```sql
-- See all users and their details
SELECT id, username, role, full_name, email, is_active, created_at, last_login 
FROM admin_users 
ORDER BY created_at DESC;
```

---

## Recommended User Setup

### Small Team (2-3 people)
- 1 Admin account for sales/executive
- 1 Portal Manager account for marketing

### Medium Team (4-8 people)
- 1-2 Admin accounts for sales managers
- 2-3 Portal Manager accounts for marketing/content team

### Large Team (9+ people)
- Multiple Admin accounts for sales team
- Multiple Portal Manager accounts for marketing department
- Consider implementing a user management interface (future enhancement)

---

## Troubleshooting

### "Access Denied" Message
**Problem**: User sees "Access Denied: You don't have permission to view this section."
**Solution**: This means a portal manager is trying to access Contact Submissions. This is expected behavior. They should only use the Downloads tab.

### Cannot Login
**Possible causes**:
1. Wrong username or password
2. Account is deactivated (is_active = 0)
3. Database connection issue

**Steps to verify**:
```sql
SELECT username, is_active FROM admin_users WHERE username = 'username_here';
```

### Tabs Not Showing Correctly
**Problem**: Admin sees only Downloads tab, or portal manager sees Contact Submissions
**Solution**: Check session data is correct. Log out and log in again. Verify role in database:
```sql
SELECT username, role FROM admin_users WHERE username = 'username_here';
```

---

## Future Enhancements (Optional)

Consider adding these features as your team grows:
1. **User Management Interface**: Admin tab to add/edit/delete users via UI
2. **Password Reset**: Email-based password reset functionality
3. **Activity Logging**: Track which user performed which actions
4. **Additional Roles**: Create more granular roles (e.g., "viewer" role with read-only access)
5. **Two-Factor Authentication**: Extra security layer for sensitive accounts

---

## Security Best Practices

1. **Change default passwords immediately** after deployment
2. **Use strong passwords**: Minimum 12 characters, mix of letters, numbers, symbols
3. **Don't share accounts**: Each team member should have their own login
4. **Review last_login timestamps** regularly to identify inactive accounts
5. **Deactivate users** when they leave the team (don't delete - maintains audit trail)
6. **Use HTTPS**: Ensure SSL certificate is installed on your domain
7. **Regular backups**: Backup the admin_users table regularly

---

## Database Table Structure

```sql
CREATE TABLE IF NOT EXISTS `admin_users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) UNIQUE NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('admin', 'portal_manager') DEFAULT 'portal_manager',
    `full_name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(255),
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `last_login` TIMESTAMP NULL,
    INDEX `idx_username` (`username`),
    INDEX `idx_role` (`role`),
    INDEX `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

---

## Support Contact

For technical issues with the multi-user system:
- Review FEATURE_DOCUMENTATION.md for detailed technical information
- Check database connection in includes/db_config.php
- Verify admin_users table was created correctly via setup_database.php
- Contact IT support: it.support@pluswealth.net

---

**Last Updated**: November 28, 2025
**Version**: 1.0
