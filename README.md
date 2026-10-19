# KYNOS Solar Systems

PHP 8.1+, Tailwind CSS, and MySQL website prepared for Namecheap cPanel shared hosting. The public site includes the solar load assessment; completed assessments are stored in MySQL and available in the password-protected admin dashboard.

The public site includes dedicated About, Solutions, Projects and Contact pages. Solutions is the parent page for Solar Panels, Hybrid Inverters, Lithium Batteries and Installation Services.

## Namecheap cPanel deployment

1. In cPanel, create a MySQL database and database user, then grant the user **All Privileges**.
2. Open phpMyAdmin, select the database, and import `database.sql` for a fresh install, or `database-migration-v1.sql` when upgrading an existing site. **Run the migration before the new dashboard goes live.**
3. Copy `config.php.example` to `config.php` and fill in the cPanel database values plus a long random `APP_KEY` (used to hash IP addresses in audit records). Generate one with:
   `php -r "echo bin2hex(random_bytes(32)), PHP_EOL;"`
4. Upload the project contents to the domain's `public_html` folder. Do not upload the old `index.html`; `index.php` is the entry point.
5. In cPanel **MultiPHP Manager**, choose PHP 8.1 or newer. Ensure `pdo_mysql`, `openssl` and `mbstring` are enabled.
6. Visit `/admin.php`. While no administrator exists you will see a one-time setup screen; create the first **super administrator** there. There is deliberately no default or hard-coded password.
7. Sign in, then complete a test assessment on the home page and confirm it appears under **Customers**.

`config.php` is ignored by Git and blocked by `.htaccess`. Never commit production credentials or administrator passwords.

## Administration

Access is role-based and always enforced on the server, never only by hiding menus:

- **super_admin** — customers, site content, contact/social settings, team management and the audit log.
- **admin** — customers, site content and contact/social settings.
- **staff** — view customers, update enquiry status.

The final active super administrator cannot be demoted or disabled. Users are enabled or disabled, not deleted. See `SECURITY.md` (controls), `ARCHITECTURE.md` (data flow) and `GUI.md` (interface contract); the approved baseline is `security-baseline.yaml`.

## Tests

```
php tests/run-tests.php                  # unit tests, no database required
TEST_DB_HOST=127.0.0.1 TEST_DB_NAME=kynos_test TEST_DB_USER=root php tests/integration.php
```

`tests/integration.php` mirrors the migration schema in a scratch database that it wipes and recreates, then exercises bootstrap, login, throttling, CSRF, role denials, settings, the final-super-admin guard and the append-only audit log. It is skipped when the `TEST_DB_*` variables are absent. Never point it at production.


## Automatic GitHub deployment

Every push to `main` runs `.github/workflows/deploy.yml` and uploads the public release to Namecheap over FTPS. The workflow deliberately excludes `config.php`, database setup files, documentation, and repository metadata.

In GitHub, open **Settings → Environments → New environment** and create `production`. Add these environment secrets:

- `FTP_SERVER` — the Namecheap FTP hostname, such as `server123.web-hosting.com`
- `FTP_USERNAME` — the cPanel or FTP account username
- `FTP_PASSWORD` — the FTP account password

Add these optional environment variables:

- `FTP_SERVER_DIR` — deployment folder; defaults to `/` because the FTP account is rooted at the `kynossolutions.com` document directory
- `LIVE_URL` — public URL; defaults to `https://kynossolutions.com`

Keep the production `config.php` directly on Namecheap. The deployment workflow does not upload or delete it. The MySQL database and `database.sql` migration remain manual cPanel/phpMyAdmin operations.

## Migrating from Vercel

Vercel cannot run this traditional PHP/MySQL application. Point the domain DNS to Namecheap hosting after the files and database are verified in cPanel. Preserve the existing domain and paths so the current canonical and social-preview URLs continue to work.
