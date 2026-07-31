# TAYA — Detainee Rights & Overstay Alert System

Build a complete Laravel web application for tracking detainee rights compliance, phase tracking, overstay alerts, and legal action management for BJMP (Bureau of Jail Management and Penology) facilities in the Philippines.

## User Review Required

> [!IMPORTANT]
> **Framework Version**: The workspace has **Laravel 13** with **Tailwind CSS v4** (not Laravel 11 + Tailwind v3 as specified). The plan adapts accordingly — all patterns will follow Laravel 13's approach (no `Kernel.php`, middleware registered in `bootstrap/app.php`, etc.). The functionality remains identical.

> [!IMPORTANT]
> **Database**: The current `.env` uses **SQLite**. The prompt specifies MySQL/MariaDB. I will build the system to work with SQLite by default (what's configured) and add MySQL config as comments in `.env`. SQLite is simpler for development and all migration features used are compatible.

> [!WARNING]
> **Laravel Breeze**: Installing Breeze requires a composer install + `php artisan breeze:install blade`. This will scaffold auth views and controllers. I will install it first, then layer TAYA's customizations on top.

> [!IMPORTANT]
> **barryvdh/laravel-dompdf**: Requires composer install. Will be installed for PDF export functionality.

## Open Questions

> [!IMPORTANT]
> **Semaphore SMS**: The Semaphore PH API integration is listed, but no phone number field exists on the models. I will add the env vars but implement only the email notification channel. SMS can be added later when phone fields are defined.

## Proposed Changes

The system comprises **80+ files** organized into these component groups, built in dependency order.

---

### 1. Package Installation

Install required packages via composer/npm before writing code:
- `laravel/breeze` — Auth scaffolding
- `barryvdh/laravel-dompdf` — PDF generation

---

### 2. Database Migrations (10 migration files)

All run in sequence. The default users migration will be modified in-place.

#### [MODIFY] [0001_01_01_000000_create_users_table.php](file:///d:/laravel/database/migrations/0001_01_01_000000_create_users_table.php)
Add `role` enum and `facility_id` FK columns to the users table.

#### [NEW] `2026_06_29_000001_create_facilities_table.php`
Columns: id, name, region, address, capacity, timestamps.

#### [NEW] `2026_06_29_000002_create_penalty_references_table.php`
Columns: id, rpc_code, charge_name, max_penalty_years (decimal), max_penalty_months (int nullable), law_source (enum), last_validated (date), timestamps.

#### [NEW] `2026_06_29_000003_create_detainees_table.php`
Columns: id, full_name, charge_description, charge_rpc_code (FK → penalty_references), commitment_date, facility_id (FK), status (enum), created_by (FK → users), timestamps.

#### [NEW] `2026_06_29_000004_create_detainee_phases_table.php`
Columns: id, detainee_id (FK), phase_number, phase_name, due_date, day_count, completed, completed_at, completed_by (FK nullable), flagged, flag_reason, timestamps.

#### [NEW] `2026_06_29_000005_create_overstay_computations_table.php`
Columns: id, detainee_id (FK), days_detained, max_penalty_days, overstay_days, alert_level (enum), computed_at, timestamps.

#### [NEW] `2026_06_29_000006_create_alerts_table.php`
Columns: id, computation_id (FK), detainee_id (FK), alert_level, recommended_action, assigned_to (FK nullable), admin_override, override_note, resolved_at, timestamps.

#### [NEW] `2026_06_29_000007_create_documents_table.php`
Columns: id, detainee_id (FK), file_path, doc_type (enum), phase_number, uploaded_by (FK), uploaded_at, timestamps.

#### [NEW] `2026_06_29_000008_create_audit_logs_table.php`
Columns: id, user_id (FK nullable), detainee_id (FK nullable), action, description, ip_address, created_at.

#### [NEW] `2026_06_29_000009_create_legal_actions_table.php`
Columns: id, alert_id (FK), detainee_id (FK), action_type (enum), filed_by (FK), notes, filed_at, timestamps.

---

### 3. Models (10 models)

#### [MODIFY] [User.php](file:///d:/laravel/app/Models/User.php)
Add role, facility_id fillables. Add relationships: belongsTo Facility, hasMany alerts, legal actions, audit logs.

#### [NEW] `app/Models/Facility.php`
#### [NEW] `app/Models/PenaltyReference.php`
#### [NEW] `app/Models/Detainee.php`
#### [NEW] `app/Models/DetaineePhase.php`
#### [NEW] `app/Models/OverstayComputation.php`
#### [NEW] `app/Models/Alert.php`
#### [NEW] `app/Models/Document.php`
#### [NEW] `app/Models/AuditLog.php`
#### [NEW] `app/Models/LegalAction.php`

---

### 4. Service Layer

#### [NEW] `app/Services/PhaseComplianceService.php`
Methods: `initializePhases()`, `completePhase()`, `flagOverduePhases()`, `computeOverstay()`, `getRecommendedAction()`. Contains all business logic for phase compliance and overstay computation.

#### [NEW] `app/Services/AuditService.php`
Helper to write audit log entries consistently across the application.

---

### 5. Middleware

#### [NEW] `app/Http/Middleware/CheckRole.php`
Accepts comma-separated roles, aborts 403 if user's role not in list. Registered as `role` alias in `bootstrap/app.php`.

#### [MODIFY] [app.php](file:///d:/laravel/bootstrap/app.php)
Register the `role` middleware alias.

---

### 6. Form Requests (4 files)

#### [NEW] `app/Http/Requests/StoreDetaineeRequest.php`
#### [NEW] `app/Http/Requests/StoreDocumentRequest.php`
#### [NEW] `app/Http/Requests/StoreLegalActionRequest.php`
#### [NEW] `app/Http/Requests/UpdateAlertRequest.php`

---

### 7. Policies (5 files)

#### [NEW] `app/Policies/DetaineePolicy.php`
#### [NEW] `app/Policies/AlertPolicy.php`
#### [NEW] `app/Policies/PhasePolicy.php`
#### [NEW] `app/Policies/DocumentPolicy.php`
#### [NEW] `app/Policies/AdminPolicy.php`

---

### 8. Controllers (9 controllers)

#### [NEW] `app/Http/Controllers/DashboardController.php`
#### [NEW] `app/Http/Controllers/DetaineeController.php`
#### [NEW] `app/Http/Controllers/PhaseController.php`
#### [NEW] `app/Http/Controllers/AlertController.php`
#### [NEW] `app/Http/Controllers/DocumentController.php`
#### [NEW] `app/Http/Controllers/LegalActionController.php`
#### [NEW] `app/Http/Controllers/ReportController.php`
#### [NEW] `app/Http/Controllers/AdminController.php`
#### [NEW] `app/Http/Controllers/AuditLogController.php`

---

### 9. Routes

#### [MODIFY] [web.php](file:///d:/laravel/routes/web.php)
Complete route definitions with auth + role middleware groups as specified.

---

### 10. Artisan Commands (2 files)

#### [NEW] `app/Console/Commands/FlagOverduePhases.php`
Daily midnight: flags overdue phases for all active detainees.

#### [NEW] `app/Console/Commands/RecomputeOverstay.php`
Daily 1 AM: recomputes overstay for all active detainees.

#### [MODIFY] `routes/console.php`
Schedule both commands.

---

### 11. Notifications

#### [NEW] `app/Notifications/AlertNotification.php`
Email notification for critical/at-risk alerts to assigned lawyers.

---

### 12. Blade Views (~20 view files)

#### Layout
- [NEW] `resources/views/layouts/app.blade.php` — Dark navy sidebar, top bar, flash messages, role-filtered navigation

#### Dashboards
- [NEW] `resources/views/dashboard/bjmp.blade.php` — Summary cards, overdue phases table
- [NEW] `resources/views/dashboard/lawyer.blade.php` — Priority alert queue
- [NEW] `resources/views/dashboard/admin.blade.php` — System stats, audit log preview
- [NEW] `resources/views/dashboard/policy.blade.php` — Chart.js analytics

#### Detainee Views
- [NEW] `resources/views/detainees/index.blade.php` — Searchable, filterable, paginated table
- [NEW] `resources/views/detainees/create.blade.php` — New detainee form
- [NEW] `resources/views/detainees/show.blade.php` — Full detainee profile with phase tracker, overstay, docs, legal actions
- [NEW] `resources/views/detainees/edit.blade.php` — Edit detainee form

#### Alert Views
- [NEW] `resources/views/alerts/index.blade.php` — Color-coded alert queue
- [NEW] `resources/views/alerts/show.blade.php` — Alert detail with assign, legal action, resolve

#### Admin Views
- [NEW] `resources/views/admin/users.blade.php` — User management
- [NEW] `resources/views/admin/facilities.blade.php` — Facility CRUD
- [NEW] `resources/views/admin/penalties.blade.php` — Penalty reference CRUD
- [NEW] `resources/views/admin/audit.blade.php` — Audit log viewer

#### PDF Views
- [NEW] `resources/views/reports/facility.blade.php` — Facility report PDF template
- [NEW] `resources/views/reports/case-alert.blade.php` — Single case PDF template

#### Auth Views
- Provided by Laravel Breeze (login, register, etc.)

---

### 13. Seeders (4 files)

#### [NEW] `database/seeders/FacilitySeeder.php` — 5 BJMP facilities
#### [NEW] `database/seeders/PenaltyReferenceSeeder.php` — 20+ RPC articles
#### [NEW] `database/seeders/UserSeeder.php` — One user per role (password: "password")
#### [MODIFY] `database/seeders/DatabaseSeeder.php` — Call all seeders in order

---

### 14. Configuration

#### [MODIFY] [.env](file:///d:/laravel/.env)
Update APP_NAME=TAYA, add Semaphore keys, update mail settings.

#### [MODIFY] [.env.example](file:///d:/laravel/.env.example)
Add TAYA-specific env vars.

#### [MODIFY] [app.css](file:///d:/laravel/resources/css/app.css)
Add custom theme colors and component styles for the dark navy design system.

---

## Verification Plan

### Automated Tests
```bash
php artisan migrate:fresh --seed
php artisan serve
```

### Manual Verification
- Verify all migrations run cleanly with `php artisan migrate:fresh --seed`
- Verify login works for each seeded role
- Verify role-based dashboard routing
- Verify detainee CRUD with phase initialization
- Verify alert computation and color-coded display
- Verify PDF generation for facility reports
- Verify document upload/download
- Verify audit log entries are recorded
- Browse through all views to check layout and styling

---

## Execution Order

1. Install packages (Breeze, dompdf)
2. Migrations (in specified order)
3. Models (with all relationships)
4. Service classes
5. Middleware + bootstrap registration
6. Form Requests
7. Policies
8. Controllers
9. Routes
10. Commands + scheduling
11. Notification
12. Blade views (layout first, then pages)
13. Seeders
14. Configuration updates
15. Migrate + seed + verify
