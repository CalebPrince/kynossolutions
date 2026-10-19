# KYNOS security baseline

Canonical record: `security-baseline.yaml`, version 1.0.0.

Risk is MEDIUM because the change adds internet-facing authentication, customer contact data, privileged administration and public publishing. The approved design uses individual accounts, password hashing, throttling, secure sessions, CSRF protection, default-deny role checks, allowlisted settings, prepared SQL, encoded output and attributable audit events.

## Implemented controls (baseline v1.0.0)

- **Authentication** — email/password with `password_hash()` (bcrypt cost 12); minimum 10-character password that must not contain the email; generic failure messages that do not reveal whether an account exists; successful login regenerates the session ID.
- **Throttling** — 5 failed attempts per account **or** per hashed client IP within a 15-minute window; blocked attempts are audited and refused even when the password is correct.
- **Sessions** — cookie `KYNOSSESS`, `HttpOnly`, `SameSite=Lax`, `Secure` under HTTPS, strict mode; 1-hour idle timeout and 12-hour absolute timeout; the `admin_users` row is authoritative so disabling an account ends access immediately.
- **CSRF** — synchroniser token verified on every state-changing POST; a missing or forged token is rejected with 419.
- **Authorization** — default-deny role matrix asserted on the server for every action; hidden navigation is never the control.
- **Super-admin safety** — the final active super administrator can neither be demoted nor disabled; accounts are disabled, never deleted.
- **Settings/content** — allowlist (`SETTING_RULES`) of plain-text and HTTPS-URL fields; angle brackets and over-length values rejected; no uploads or HTML; packages validated as structured JSON.
- **Audit** — append-only log (DB triggers reject `UPDATE`/`DELETE`) recording logins, failures, blocked attempts, user/role changes, customer status changes and settings edits; never records passwords or tokens; client IPs are stored as `sha256(IP + APP_KEY)` hashes.
- **Transport/hardening** — `X-Frame-Options`, `nosniff`, `Referrer-Policy`, HSTS and no-store headers; `.htaccess` denies direct access to `config.php`, `app/`, `tests/`, SQL, YAML and Markdown files.
- **Output/SQL** — all dynamic output is escaped via `e()`; all database access uses prepared statements.

## Verification

`php tests/run-tests.php` (unit) and `php tests/integration.php` (env-gated MySQL) exercise the controls above, including direct attempts to perform forbidden actions as a lower-privileged role.

## Monitoring and response

Repeated login failures and privileged changes are recorded in the admin audit log. For suspected compromise, disable the affected account, rotate relevant credentials, preserve audit records and restore a known-good database/code release.

This baseline is a design and control record, not certification or a claim that implementation is vulnerability-free.
