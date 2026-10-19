# KYNOS architecture

The public PHP pages and `/admin.php` run on Namecheap shared hosting and use one private MySQL database. GitHub Actions deploys reviewed source over FTPS while `config.php` remains server-only.

The administration change adds database-backed users, fixed roles, settings/content records and append-only audit events. Public pages read allowlisted published settings with hard-coded fallbacks so a database outage does not erase essential contact information.

Authorization is server-side and default-deny. Super administrators manage accounts and roles; administrators manage customers and public content; staff view enquiries and update enquiry status only. The final super administrator cannot be removed or demoted.

## Modules

- `app/bootstrap.php` — environment loading, PDO connection (`db()`), request IP hashing (`request_ip_hash()`), HTTPS detection and the global `e()` output escaper.
- `app/auth.php` — role matrix (`role_permissions()`, `user_can()`, `assert_permission()`), sessions, login throttling, CSRF, and password policy/hashing.
- `app/operations.php` — business rules for customers, team, bootstrap and audit queries; every function re-checks permission so it can be tested without HTTP.
- `app/settings.php` — `SETTING_RULES` allowlist, `settings_all()` with per-request cache and DB-failure fallback, `validate_setting()` and `settings_save()` (transactional, one audit event per changed key).
- `app/audit.php` — append-only event writers.
- `app/admin/layout.php`, `app/admin/views.php` — dashboard shell and views.
- `admin.php` — single front controller: CSRF verification, POST action dispatch, GET view routing with per-view permission gates, migration-required screen and security headers.
- `app/site-data.php`, `app/site-layout.php` — settings-driven public helpers and shared page layout.

## Request flow (authenticated)

1. `admin.php` boots, verifies the CSRF token on every POST and resolves the current user from the session plus the authoritative `admin_users` row.
2. Actions call an `operations`/`settings` function that asserts the required permission before doing anything.
3. Mutations run inside a transaction together with their audit event.
4. Public pages call settings helpers that fall back to seeded defaults when the database is unavailable.

## Data model (baseline v1.0.0)

`admin_users`, `login_attempts`, `settings`, `audit_log` are created by `database-migration-v1.sql`. Enquiries move from `solar_assessments` to `customer_enquiries`; a guarded `RENAME` plus a compatibility `solar_assessments` view keeps the old page working until the new code is live (drop the view afterwards). `audit_log` rejects `UPDATE` and `DELETE` via triggers.

## Deployment order

1. Apply `database-migration-v1.sql` in phpMyAdmin.
2. Deploy the reviewed code over FTPS (the workflow ships only PHP, `.htaccess`, `app/`, `assets/` and `images/`; `config.php` stays on the server).
3. Create the first super administrator through the `/admin.php` setup screen.
4. Confirm a test enquiry appears under Customers, then drop the `solar_assessments` compatibility view.

## Testing

`php tests/run-tests.php` covers the role matrix, final-super-admin guard, password policy and settings validators with no database. `tests/integration.php` runs the full flow against a scratch MySQL (env-gated by `TEST_DB_*`). Because there is no local MySQL in development, DB-backed verification happens in the integration environment.
