# Lead Management Software (PHP + MySQL)

Simple, professional, and beginner-friendly lead management system for small startup teams.

## Purpose
This project helps teams manage:
- Business leads (primary focus)
- Student leads for online frontend classes
- Selective student project leads with approval flag

It supports daily outreach flows for social media services, frontend websites, and portfolio/company profile services.

## Technologies Used
- PHP 8+ (plain PHP, PDO)
- MySQL / MariaDB
- HTML/CSS
- Vanilla JavaScript
- Bootstrap 5 CDN (UI only)

## Folder Structure
- `config/` database/config setup
- `includes/` auth, csrf, helpers, layout files
- `assets/css`, `assets/js`, `assets/uploads`
- `auth/` login/logout
- `dashboard/` dashboard, daily work, activity log
- `leads/` lead CRUD, CSV import/export, lead detail timeline
- `users/` user management (admin)
- `tasks/` tasks/reminders
- `meetings/` meeting management
- `proposals/` proposal tracking
- `templates/` outreach templates
- `reports/` report filters + print layouts
- `settings/` system settings
- `database/` schema + seed SQL

## Main Modules
1. Authentication + Roles (Admin/Staff)
2. Dashboard with key business cards and daily pipeline indicators
3. Lead CRUD with assignment, stages, follow-up, tags, duplicate warning
4. Lead detail page with notes, interactions, stage history, attachments
5. Tasks/reminders module
6. Meetings module
7. Proposals module
8. Notification center (manual + reminder-style alerts)
9. Outreach template module
10. Daily work summary page
11. Reports module with filter + CSV export + print pages
12. User management + status controls (soft delete/deactivate)
13. Settings module (logo + favicon upload support)
14. Activity/audit logs

## Setup (XAMPP / Localhost / Shared Hosting)
1. Copy project folder to your web root (`htdocs` for XAMPP).
2. Create a MySQL database named `lead_management` (or use script auto-create).
3. Import `database/database.sql` in phpMyAdmin.
4. Copy `config/config.sample.php` to `config/config.php` and set DB credentials.
5. Ensure `assets/uploads` is writable.
6. Open browser: `http://localhost/leadmanagment` (or your exact project folder name).

## Default Login
- Admin: `admin@example.com` / `Admin@123`
- Staff: `staff@example.com` / `Staff@123`

## Deployment on Shared Hosting
1. Upload all files to `public_html` (or subfolder).
2. Import `database/database.sql` into hosting MySQL database.
3. Update `config/config.php` database values and `base_url`.
4. Confirm PHP version is 8+ and PDO MySQL extension enabled.
5. Set write permissions for `assets/uploads`.

## Troubleshooting
- **"Missing config/config.php"**: copy from sample file.
- **DB connection failed**: verify host, DB name, user/password.
- **Login fails**: confirm seed SQL imported successfully.
- **Upload issues**: check folder permissions and PHP upload limits.
- **404 Not Found on `/auth/login.php`**: you are likely opening from domain root. Use your project subfolder URL (example: `http://localhost/leadmanagment/auth/login.php`) and set `base_url` in `config/config.php` to the same folder.

## Security Notes
- Session-based route checks
- Password hashing (`password_hash`, `password_verify`)
- CSRF token checks on forms
- PDO prepared statements
- Output escaping helper `e()`

## First-Time Admin Checklist
1. Login as admin.
2. Change admin password.
3. Add real staff users.
4. Update settings (company name/logo/timezone).
5. Start adding/importing leads.
