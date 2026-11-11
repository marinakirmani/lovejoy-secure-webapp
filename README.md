# Lovejoy — Secure Web Application (University Coursework)

**PHP • MySQL • HTML • XAMPP • Web Security**

Lovejoy is a university coursework project demonstrating a database-backed web application for an evaluation-request service. Visitors can register, log in, submit an object-evaluation request (including an optional photo), and review their own signed-in details. Administrators can view submitted requests.

## Features

- Account registration, password hashing and login
- Login-attempt lockout (five failed attempts / 15 minutes)
- Password reset token workflow for **local demonstration**
- Session-based authentication and administrator-only access
- Evaluation request form with optional image uploads
- Database access through PDO prepared statements
- CSRF checks on forms, escaped HTML output and upload type checks

## Quick start on a Mac with XAMPP

1. Install/start **Apache** and **MySQL** in XAMPP.
2. Put the files from this repository in `/Applications/XAMPP/xamppfiles/htdocs/lovejoy/`.
3. Visit `http://localhost/phpmyadmin`, select **Import**, and import `schema.sql`. This creates `lovejoy_db` and the demo tables. The schema is reconstructed from the PHP source; no original user data is included.
4. If XAMPP uses its usual **local** MySQL configuration (`root`, blank password), the sample defaults in `config.php` may work. If not, supply `DB_HOST`, `DB_NAME`, `DB_USER`, and `DB_PASS` as environment variables. **Never commit real database credentials.**
5. Open **http://localhost/lovejoy/register.php**, register with a throwaway email, then log in.
6. To demo the admin view locally, use phpMyAdmin to change your **test account** role from `user` to `admin`, for example:

   ```sql
   UPDATE users SET role = 'admin' WHERE email = 'your-test-email@example.com';
   ```

7. Use the dashboard to submit an evaluation request and view requests as admin.

## Project layout

| File | Purpose |
| --- | --- |
| `register.php`, `login.php`, `logout.php` | Account and session workflows |
| `forgot.php`, `reset.php` | Local password-reset demonstration |
| `request_evaluation.php` | Evaluation submission and optional image upload |
| `dashboard.php`, `admin.php`, `admin_requests.php` | Dashboard and admin features |
| `auth.php` | Shared access-control helpers |
| `config.php` | Database configuration (environment-variable support) |
| `schema.sql` | Reconstructed empty MySQL demo schema |
| `uploads/` | Runtime uploads; excluded from version control |

## Changes made when preparing this public repository

This is based on my original Lovejoy coursework. To create a safer, easier-to-run public demonstration, the repository removes temporary/debug files and macOS metadata, replaces hard-coded local configuration with environment-variable support, adds a *reconstructed* empty database schema, repairs admin/dashboard and logout navigation, aligns evaluation table names, and adds basic safeguards around forms and image uploads. These are preparation changes rather than claims about the original submission.

## Important limitations

**Academic demonstration only — not audited or production ready.** It has not undergone comprehensive security review or integration testing. The reset workflow displays a link locally instead of emailing it; it should never be deployed this way for a real service. No real customers, personal records, live credentials, or public hosting should be used without a full security review.

No real user data, passwords, database dumps or uploaded photos are included.
