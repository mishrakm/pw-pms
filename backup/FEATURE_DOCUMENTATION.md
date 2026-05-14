# PlusWealth Capital Management LLP - Website Feature Documentation

**Project Handover Document**  
**Date:** November 28, 2025  
**Version:** 1.0

---

## 🎯 Executive Summary

A comprehensive, professional website for PlusWealth Capital Management LLP featuring portfolio management strategy information, team profiles, regulatory compliance data, contact management system, and downloadable resources. The site is built with PHP, MySQL, and includes a secure admin dashboard for content management.

---

## 🌐 Website Structure

### Public Pages

#### 1. **Home Page** (`index.php`)
- **Hero Section**: Eye-catching banner with call-to-action button linking to contact page
- **PlusWealth PMS Overview**: 
  - Investment Philosophy section with visual imagery
  - Risk Management approach
  - Rule-Based Investing methodology
  - Target Audience ("Who this PMS is suitable for") with visual support
- **FAQ Section**: Common questions about the investment approach
- **Responsive Design**: Mobile-friendly layout with optimized image sizes

#### 2. **Fusion Strategy Page** (`fusion.php`)
- **Strategy Overview**: Detailed explanation of the Pluswealth Fusion multi-asset strategy
- **Investment Attributes**: 4 key features
  - Multi-Asset Allocation
  - Factor Based Equity Exposure
  - Risk Monitoring & Control
  - Medium to Long-term Horizon
- **Call-to-Action Section**: Strategic placement with gradient background encouraging prospect engagement
- **Performance Section**: 
  - Performance chart visualization (`PlusWealth Fusion_Oct-2025_return.jpeg`)
  - Historical returns table with benchmark comparison
  - AUM display (₹2.78 Crores)
  - Performance data across multiple time periods (1M, 3M, 6M, 1Y, 2Y, 3Y, 4Y, 5Y, Since Inception)
  - Benchmark: NSE Multi Asset Index 2 (50% NIFTY 500, 20% NIFTY Medium Duration, 20% NIFTY Arbitrage, 10% INVIT/REIT)
  - Since inception return: 4.29% vs 1.52% benchmark

#### 3. **Team Page** (`team.php`)
- **Professional Profiles**: Three team members with photos, designations, and detailed biographies
  - **Gaurav Chhabra** - Designated Partner
    - 20+ years experience in multi-asset investing
    - NISM certifications in research, derivatives, and portfolio management
  - **Parmeet Singh Chadha** - Designated Partner
    - Quantitative model development expertise
    - FORE School of Management MBA
  - **Pavit Singh** - Strategy & Relations
    - Investment research background
    - CFA Level 3 candidate
    - Experience with Groww
- **Photo Styling**: Rounded rectangle format (180px × 220px) with professional shadows
- **Centered Layout**: Designations centered and italicized for visual hierarchy

#### 4. **Downloads Page** (`downloads.php`)
- **Hero Section**: Professional banner with background image
- **File Listings**: Dynamic display of downloadable materials
- **File Type Icons**: Visual indicators with color-coded gradients
  - PDF files: Red gradient
  - Word documents: Blue gradient
  - Excel files: Green gradient
  - PowerPoint: Orange gradient
  - Images: Teal gradient
  - ZIP/Text: Gray gradient
- **File Information Display**:
  - Title and description
  - Upload date
  - File size
  - Download count
- **Download Button**: Secure download through handler script
- **Empty State**: Friendly message when no downloads available

#### 5. **Contact Page** (`contact.php`)
- **Contact Form**: 5-field lead capture system
  - Name (minimum 2 characters)
  - Email (validated format)
  - Phone (minimum 7 characters)
  - City
  - Ticket Size (investment amount)
- **Form Validation**: Client-side and server-side validation
- **Beautiful Success Messages**: Custom modal with animations instead of basic alerts
- **Dual Email System**: 
  - Admin notification to 3 recipients
  - User confirmation email
- **Database Storage**: All submissions stored in MySQL for tracking

#### 6. **Compliance Page** (`compliance.php`)
- **SEBI Complaint Data**: Three professional tables
  - October 2025 monthly data
  - 10-month disposal trend (Jan-Oct 2025)
  - 7-year annual trend (2019-2026)
- **Responsive Tables**: Mobile-friendly scrolling
- **Professional Styling**: Bootstrap-based table design with dark headers

---

## 🔐 Admin Dashboard (`admin_dashboard.php`)

### Security Features
- **Multi-User Authentication System**: Username and password-based login
- **Role-Based Access Control**: Two user roles with different permissions
  - **Admin**: Full access to all features (contact submissions, downloads, settings)
  - **Portal Manager**: Limited access to downloads management only
- **Default User Accounts**:
  - Admin: Username: `admin`, Password: `admin2025` (**CHANGE BEFORE DEPLOYMENT**)
  - Portal Manager: Username: `manager`, Password: `manager2025` (**CHANGE BEFORE DEPLOYMENT**)
- **User-Specific Password Changes**: Each user can change their own password independently
- **Session-based Authentication**: Secure session management with user_id, username, role, and full_name tracking
- **Last Login Tracking**: Automatic timestamp recording for audit purposes
- **Access Control Enforcement**: URL manipulation prevention - portal managers cannot access restricted sections

### Three Main Sections

#### A. Contact Submissions Management (Admin Only)
**Statistics Dashboard**:
- Total submissions count
- New leads today
- Status breakdown (New, Contacted, Converted, Closed)

**Filtering & Search**:
- Filter by status (All, New, Contacted, Converted, Closed)
- Search by name, email, phone, or city
- Clear filters option

**Submissions Table**:
- View all contact form submissions
- Display fields: ID, Date, Name, Email, Phone, City, Ticket Size, Status
- Color-coded status badges
- Action buttons: View details, Update status

**Lead Management**:
- View detailed information modal
- Update status with notes
- Track IP address and user agent
- Add internal notes for each lead
- **Access Restricted**: Only visible to users with Admin role

#### B. Downloads Management (Admin & Portal Manager)
**File Upload**:
- Title field (required)
- Description field (optional)
- File upload input
- Automatic file size and type detection
- Unique filename generation to prevent conflicts

**File Management Table**:
- List all uploaded files
- Display: Title, Description, Upload date, File size, Download count, Status
- Actions available:
  - **Toggle Active/Inactive** (👁️/🚫): Control public visibility without deletion
  - **Download** (⬇️): Test file downloads
  - **Delete** (🗑️): Permanently remove file and database entry
- Confirmation dialogs for destructive actions
- **Access**: Available to both Admin and Portal Manager roles

#### C. Settings (All Users)
**Password Management**:
- Change own password functionality
- Current password verification required
- Minimum 8 characters enforcement
- Password confirmation field
- Individual user password updates (not global)

---

## 🗄️ Database Structure

### Database Configuration
- **Database Name**: `pluswoir_pms`
- **Username**: `pluswoir_pms`
- **Password**: `Pm$plusW3alth`
- **Host**: `localhost`

### Tables

#### 1. `contact_submissions`
```
- id (INT, AUTO_INCREMENT, PRIMARY KEY)
- name (VARCHAR 255, NOT NULL)
- email (VARCHAR 255, NOT NULL, INDEXED)
- phone (VARCHAR 50, NOT NULL)
- city (VARCHAR 255, NOT NULL)
- ticket_size (VARCHAR 100, NOT NULL)
- submitted_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP, INDEXED)
- ip_address (VARCHAR 45)
- user_agent (TEXT)
- status (ENUM: 'new', 'contacted', 'converted', 'closed', INDEXED)
- notes (TEXT)
```

#### 2. `downloads`
```
- id (INT, AUTO_INCREMENT, PRIMARY KEY)
- title (VARCHAR 255, NOT NULL)
- description (TEXT)
- filename (VARCHAR 255, NOT NULL)
- filepath (VARCHAR 500, NOT NULL)
- file_size (INT)
- file_type (VARCHAR 100)
- upload_date (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP, INDEXED)
- downloads_count (INT, DEFAULT 0)
- is_active (TINYINT 1, DEFAULT 1, INDEXED)
```

#### 3. `admin_settings`
```
- id (INT, AUTO_INCREMENT, PRIMARY KEY)
- setting_name (VARCHAR 100, UNIQUE, NOT NULL)
- setting_value (TEXT, NOT NULL)
- updated_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP ON UPDATE)
```
**Purpose**: General settings storage (legacy password storage - now superseded by admin_users table)

#### 4. `admin_users`
```
- id (INT, AUTO_INCREMENT, PRIMARY KEY)
- username (VARCHAR 50, UNIQUE, NOT NULL, INDEXED)
- password (VARCHAR 255, NOT NULL) [password_hash() with PASSWORD_DEFAULT]
- role (ENUM: 'admin', 'portal_manager', DEFAULT 'portal_manager', INDEXED)
- full_name (VARCHAR 100, NOT NULL)
- email (VARCHAR 255)
- is_active (TINYINT 1, DEFAULT 1, INDEXED)
- created_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)
- last_login (TIMESTAMP NULL)
```
**Purpose**: Multi-user authentication system with role-based access control
**Default Users**:
- Admin: username=`admin`, password=`admin2025`, role=`admin`, full_name=`Administrator`
- Portal Manager: username=`manager`, password=`manager2025`, role=`portal_manager`, full_name=`Portal Manager`

---

## 📧 Email Configuration

### PHPMailer with Office365 SMTP
- **SMTP Server**: `smtp.office365.com`
- **Port**: 587
- **Encryption**: TLS
- **Authentication**: Required
- **Sender Account**: `noreply@egitpro.com`
- **Password**: `Jad04108`

### Email Recipients (Contact Form)
1. `amit.mishra@pluswealth.net`
2. `yogesh.dixit@pluswealth.net`
3. `it.support@pluswealth.net`

### Email Features
- **Admin Notification**: Includes all form data with reply-to set to user's email
- **User Confirmation**: Automated thank you email with submission details
- **Professional Templates**: Includes SEBI and PM registration numbers
- **Error Handling**: Detailed error logging and user-friendly messages

---

## 🎨 Design System

### Color Palette (CSS Variables)
```css
--pw-blue: #1029a6       /* Primary brand color */
--pw-blue-2: #365ae1     /* Secondary blue */
--pw-blue-3: #244ae2     /* Accent blue */
--pw-accent: #84E4A4     /* Success/accent green */
--pw-text: #65677E       /* Body text */
--pw-heading: #3D4270    /* Headings */
--pw-surface: #e9ecef    /* Surface backgrounds */
--pw-muted: #666666      /* Muted text */
--pw-bg: #F4F7FC         /* Page background */
```

### Typography
- **Font Family**: System fonts (-apple-system, BlinkMacSystemFont, 'Segoe UI', Arial)
- **Body Text**: Justified alignment for readability
- **Headings**: Bold weight with brand color
- **Designations**: Centered, italic, muted color

### Layout Features
- **Responsive Grid**: Bootstrap 5 alpha framework
- **Card Components**: White background, rounded corners (12px), subtle shadows
- **Hover Effects**: Smooth transitions (0.3s) with elevation changes
- **Animations**: WOW.js for scroll-triggered animations with staggered delays

---

## 🔒 Security Measures

### File Upload Security
- **Uploads Directory**: Protected with `.htaccess` to prevent direct access
- **Download Handler**: All files served through `download_file.php` script
- **File Validation**: Type checking on upload
- **Unique Filenames**: Timestamp-based naming prevents overwrites
- **Index Protection**: `index.php` file blocks directory listing

### Form Security
- **Input Sanitization**: All user inputs sanitized with `htmlspecialchars()`
- **SQL Injection Protection**: Prepared statements with parameter binding
- **Email Validation**: Server-side validation with `filter_var()`
- **CSRF Protection**: Session-based form submission
- **XSS Prevention**: Output escaping on all user data display

### Database Security
- **Prepared Statements**: All queries use parameterized binding
- **Error Logging**: Errors logged to file, not displayed to users
- **Connection Pooling**: Single connection instance per request

---

## 📂 File Structure

```
pms/
├── index.php                      # Homepage
├── fusion.php                     # Fusion strategy page
├── team.php                       # Team profiles page
├── contact.php                    # Contact form page
├── compliance.php                 # Compliance data page
├── downloads.php                  # Downloads listing page
├── download_file.php              # Secure file download handler
├── admin_dashboard.php            # Admin control panel
├── setup_database.php             # Database setup script (DELETE AFTER USE)
│
├── includes/
│   ├── header.php                 # Site header with navigation
│   ├── footer.php                 # Site footer
│   └── db_config.php              # Database configuration
│
├── assets/
│   ├── mail.php                   # Contact form processor with PHPMailer
│   ├── css/
│   │   ├── main.css               # Custom styles with CSS variables
│   │   ├── bootstrap-5.0.0-alpha-1.min.css
│   │   ├── LineIcons.2.0.css
│   │   └── animate.css
│   ├── js/
│   │   ├── main.js                # Site interactions & custom modal
│   │   ├── bootstrap.bundle-5.0.0.alpha-1-min.js
│   │   └── wow.min.js
│   └── img/
│       ├── logo.png
│       ├── PlusWealth Fusion_Oct-2025_return.jpeg
│       ├── pms_for.jpeg
│       ├── team_gaurav.png
│       ├── team_parmeet.png
│       └── team_pavit_1.png
│
├── uploads/
│   ├── .htaccess                  # Directory access protection
│   └── index.php                  # Prevents directory listing
│
└── PHPMailer/                     # Email library
    └── src/
        ├── PHPMailer.php
        ├── SMTP.php
        └── Exception.php
```

---

## 🚀 Deployment Checklist

### Pre-Deployment Tasks
- [ ] Change admin dashboard password in `admin_dashboard.php` (line 12)
- [ ] Upload all files to web server
- [ ] Set proper file permissions (755 for directories, 644 for files)
- [ ] Ensure `uploads/` directory is writable (755)
- [ ] Verify PHPMailer library is properly uploaded
- [ ] Test email sending functionality

### Database Setup
1. Access `setup_database.php` in browser
2. Verify both tables (`contact_submissions` and `downloads`) are created
3. **IMMEDIATELY DELETE** `setup_database.php` after successful setup

### Configuration Verification
- [ ] Test database connection
- [ ] Verify SMTP email sending
- [ ] Test contact form submission
- [ ] Test file upload in admin
- [ ] Test file download on public page
- [ ] Check all navigation links
- [ ] Test on mobile devices
- [ ] Verify SSL certificate if using HTTPS

### Post-Deployment
- [ ] Test admin dashboard login
- [ ] Upload initial download materials
- [ ] Monitor error logs for first few days
- [ ] Set up regular database backups
- [ ] Configure server-side email sending limits

---

## 🔧 Maintenance & Updates

### Regular Tasks
1. **Weekly**: Check contact submissions and respond to new leads
2. **Monthly**: Review download statistics and update materials
3. **Quarterly**: Update performance data on fusion.php
4. **Annually**: Renew SSL certificate, update compliance tables

### Content Updates

#### Update Performance Data (fusion.php)
- Edit the Historical Returns table (lines 160-200)
- Update the AUM display (line 159)
- Update the performance chart image (`PlusWealth Fusion_Oct-2025_return.jpeg`)
- Update "Last updated" date in footer note (line 211)

#### Add Team Members (team.php)
- Upload team member photo to `assets/img/`
- Add new team card following existing structure
- Use class `.team-photo` for consistent styling

#### Update Compliance Data (compliance.php)
- Edit table data directly in HTML
- Add new rows for monthly/annual data
- Maintain consistent Bootstrap table classes

### Upload New Downloads
1. Login to admin dashboard
2. Click "Downloads" tab
3. Fill in title and description
4. Select file (max size depends on server configuration)
5. Click "Upload File"
6. Use toggle button to control visibility

---

## 🐛 Troubleshooting

### Email Not Sending
- Check SMTP credentials in `assets/mail.php`
- Verify Office365 account is active
- Check server firewall allows port 587
- Review PHP error logs for PHPMailer errors

### File Upload Failing
- Verify `uploads/` directory permissions (755)
- Check PHP `upload_max_filesize` and `post_max_size` settings
- Ensure disk space available
- Review file type restrictions

### Database Connection Errors
- Verify credentials in `includes/db_config.php`
- Check MySQL service is running
- Ensure database user has proper permissions
- Test connection with MySQL client

### Contact Form Validation Issues
- Check JavaScript console for errors
- Verify `assets/js/main.js` is loaded
- Test with browser developer tools
- Review PHP error logs

---

## 📊 Analytics & Tracking

### Built-in Metrics
- **Contact Submissions**: Count, status breakdown, date filtering
- **Downloads**: Individual file download counts, upload dates
- **Lead Status**: Track conversion funnel (new → contacted → converted)

### Recommended External Tools
- **Google Analytics**: Track page views, user flow, bounce rates
- **Hotjar/Microsoft Clarity**: Heatmaps and session recordings
- **Google Search Console**: Monitor search performance and indexing

---

## 🎓 User Guide for Stakeholders

### For Marketing Team
- **Update Content**: Edit page content directly in PHP files
- **Upload Resources**: Use admin dashboard Downloads section
- **Monitor Leads**: Check Contact Submissions daily
- **Track Performance**: Review download counts and submission statistics

### For Sales Team
- **Lead Access**: Login to admin dashboard → Contact Submissions
- **Lead Management**: Update status (new → contacted → converted)
- **Add Notes**: Track follow-up actions and conversation history
- **Export Data**: Use database export or build custom reports

### For Compliance Team
- **Update Tables**: Edit `compliance.php` with latest SEBI data
- **Monthly Process**: Add new row to monthly table
- **Annual Process**: Update annual trend table
- **Document Uploads**: Add regulatory documents to Downloads section

---

## 📞 Support & Contacts

### Technical Support
- **Database Issues**: Contact hosting provider for MySQL support
- **Email Issues**: Contact Office365 administrator
- **Server Issues**: Contact web hosting support

### Development Team
For feature requests or bug reports, document:
1. Page/feature affected
2. Expected behavior
3. Actual behavior
4. Steps to reproduce
5. Browser and device information
6. Screenshots if applicable

---

## 📝 Version History

| Version | Date | Changes |
|---------|------|---------|
| 1.0 | Nov 28, 2025 | Initial launch with all features |

---

## ⚖️ Legal & Compliance

### SEBI Registration
- Registration No: INZ000163752
- Portfolio Manager Registration No: INP000009144

### Data Protection
- User data stored securely in MySQL database
- Email communications logged
- IP addresses captured for security
- Comply with applicable data protection regulations

### Disclaimers
- Performance data not verified by SEBI (noted on fusion.php)
- Returns shown net of fees and transaction costs
- Past performance does not guarantee future results

---

## 🎯 Future Enhancement Opportunities

### Phase 2 Potential Features
1. **Blog/Insights Section**: Market commentary and investment insights
2. **Client Portal**: Secure login for existing clients
3. **Performance Dashboard**: Real-time portfolio updates
4. **Newsletter Signup**: Email marketing integration
5. **Multi-language Support**: Hindi/regional language options
6. **Video Content**: Embedded explainer videos
7. **Chatbot Integration**: Automated initial inquiries
8. **Advanced Analytics**: Google Analytics 4 integration
9. **A/B Testing**: Optimize conversion rates
10. **Mobile App**: Companion mobile application

---

**Document Prepared By:** Development Team  
**Last Updated:** November 28, 2025  
**Status:** Ready for Production Deployment

---

*This document should be kept confidential and used for internal stakeholder reference only.*
