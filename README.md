# KYNOS Solar Systems

PHP 8.1+, Tailwind CSS, and MySQL website prepared for Namecheap cPanel shared hosting. The public site includes the solar load assessment; completed assessments are stored in MySQL and available in the password-protected admin dashboard.

## Namecheap cPanel deployment

1. In cPanel, create a MySQL database and database user, then grant the user **All Privileges**.
2. Open phpMyAdmin, select the new database, and import `database.sql`.
3. Copy `config.php.example` to `config.php` and enter the cPanel database values. Generate the admin hash locally with:
   `php -r "echo password_hash('YOUR-STRONG-PASSWORD', PASSWORD_DEFAULT), PHP_EOL;"`
4. Upload the project contents to the domain's `public_html` folder. Do not upload the old `index.html`; `index.php` is the entry point.
5. In cPanel **MultiPHP Manager**, choose PHP 8.1 or newer. Ensure `pdo_mysql` and `mbstring` are enabled.
6. Visit the home page, complete a test assessment, then sign in at `/admin.php` and confirm it appears.

`config.php` is ignored by Git and blocked by `.htaccess`. Never commit production credentials.

## Migrating from Vercel

Vercel cannot run this traditional PHP/MySQL application. Point the domain DNS to Namecheap hosting after the files and database are verified in cPanel. Preserve the existing domain and paths so the current canonical and social-preview URLs continue to work.
