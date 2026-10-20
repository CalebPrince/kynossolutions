# KYNOS administration interface

## Design direction

The admin uses KYNOS navy, white and orange with Montserrat headings and Mulish body text. A persistent left navigation opens with Dashboard, then separates Customers, Site content, Contact and social links, Team and Audit log. Dense operational tables are used only where comparison matters.

## Capability matrix

| Surface | Roles | State | Contract |
|---|---|---|---|
| Sign in | All named users | BUILT | Email/password, throttling, session regeneration, safe errors |
| First-run setup | (none, bootstrap only) | BUILT | Creates the first super admin only while `admin_users` is empty |
| Dashboard | All roles (same access as Customers) | BUILT | Landing page after sign-in; enquiry stat cards, latest enquiries, recent audit activity and section shortcuts, each section permission-gated |
| Customers | super_admin, admin, staff | BUILT | Enquiry list with status; staff may update status only |
| Site content | super_admin, admin | BUILT | Allowlisted plain-text fields, server validation, audit |
| Contact/social settings | super_admin, admin | BUILT | Validated phone, WhatsApp, email and HTTPS-only URLs |
| Team | super_admin | BUILT | Add/disable users, assign fixed roles, protect final super admin |
| Audit log | super_admin | BUILT | Read-only attributable security and publishing events |

Statuses: BUILT, PARTIAL, NOT BUILT. "State" reflects implementation after baseline v1.0.0; see `SECURITY.md` and `ARCHITECTURE.md` for the enforced contract. Migration must be applied before the dashboard is reachable; until then the dashboard shows a migration-required screen.

Every screen covers loading, empty, validation, success, forbidden and expired-session states. Hidden navigation never replaces backend authorization. Interface work must not change permissions, validation, retention, audit or deployment controls without a revised approved baseline.
