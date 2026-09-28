# Clusterfiy

> Clusterfiy is a multi-company team-management SaaS. A user has one login, belongs to one or more companies, and switches between active company contexts. All tasks, departments, members, and reports are strictly scoped to the active company. Companies subscribe to a plan (free or team) that enforces user/company limits. Key events generate notifications (in-app + email).

## 0.4 Plan limits (config/plans.php)

| Plan | Companies per user | Members per company | Excel exports |
|------|--------------------|---------------------|---------------|
| Free | 1 | 5 | No (redirect to /pricing) |
| Team | unlimited | unlimited | Yes |

## 0.3 Role matrix

| Capability | Owner (company_admin) | Manager (manager) | Member (employee) | Super-admin (platform) |
|---|---|---|---|---|
| Manage company, billing, delete company | Yes | No | No | Yes (all companies) |
| Manage members, invite, cancel invites | Yes | Yes (cannot invite owners) | No | Yes |
| Create tasks and departments, assign anyone, view reports | Yes | Yes | No | Yes |
| Work on assigned tasks, comment, log time, self-assign only | Yes | Yes | Yes | N/A |
| Platform routes (all companies, switch/reset) | No | No | No | Yes |

Super-admin is platform-level and separate from company roles. Company roles are stored in `company_user.role` (owner/manager/member) and mirrored to Spatie roles (company_admin/manager/employee).

## 0.2 Current-state inventory (fresh clone)

Exists: Company, Department, Task, Comment (polymorphic, stands in for TaskComment), TimeEntry (stands in for TaskTimeEntry), TaskStatusChange, ActivityLog, LoginHistory, User (+CompanyUser pivot), Project, TaskAssignee, Attachment models; SetCurrentCompany + IsSuperAdmin middleware; TaskService, DashboardService, ReportService, NotififcationService (typo, class NotificationService); TaskPolicy, CompanyPolicy, DepartmentPolicy, UserPolicy; spatie permission config; RolesAndPermissionsSeeder; Exports/TaskExports.

Missing vs plan: routes/ and resources/views/ were absent (recreated in Phase 1); App\Models\Scopes\TenantScope referenced but file missing (added CompanyScope + TenantScope alias); company_id missing on comments, time_entries, task_status_changes, activity_logs (added via migration 2026_09_28_000001); CompanyContext singleton missing (added); company_invitations table + flow missing (added); NotificationService rename + TaskCommented/CompanyInviteSent/MemberAdded wiring + notifications view missing (added); TaskService::transition() machine + audit + assignment rules missing (added); Dashboard DTOs for manager/member missing (added); tests/ missing (added TenancyTest, RolesTest, InvitationsTest, TaskLifecycleTest).

Ticket adjustments: TaskComment = Comment model with company_id added; TaskTimeEntry = TimeEntry model with company_id added; User model unscoped (multi-company login) instead of tenant-scoped.

## Phase 1 notes

- 1.1 Tenant isolation: `App\Support\CompanyContext` + `App\Models\Scopes\CompanyScope` applied to Task, Department, Comment, TimeEntry, TaskStatusChange, ActivityLog. Controllers/services no longer use manual `where('company_id', ...)`. Exceptions use `withoutGlobalScope(CompanyScope::class)` (super-admin views, company switch). Route-model binding resolves within scope.
- 1.2 Invitations: `company_invitations` table, token 40 chars, 72h expiry, single-use, role validation (manager cannot invite owners), membership uniqueness, existing-member rejection. Files: CompanyInvitation model + policy, InvitationController, SendCompanyInvite notification, StoreInvitationRequest, company-scoped routes.
- 1.3 Notifications: renamed `NotififcationService.php` to `NotificationService.php`. Database channel for all events, mail (queued) for TaskAssigned + invites. Events: TaskAssigned, TaskCommented, CompanyInviteSent (SendCompanyInvite), MemberAddedToCompany. View: /notifications + unread count in nav. Run `php artisan queue:work` for mail.
- 1.4 Task lifecycle: `todo/backlog to in_progress to in_review/review to done`, `done to in_progress` reopen, `cancelled` from any except done. Enforced in `TaskService::transition()` with 422 on illegal. Assignee must belong to current company; members self-assign only. Time entries: started_at required, ended_at >= started_at, own entries only.
- 1.5 Dashboards: `DashboardService::managerDashboard()` (by status, overdue, workload, 30d trend, activity) and `memberDashboard()` (my open/overdue/due-week/comments). Controller thin, Blade + Chart.js (lazy chunk) rendering, eager-loaded.
- 1.6 Tests: `tests/Feature/TenancyTest.php`, `RolesTest.php`, `InvitationsTest.php`, `TaskLifecycleTest.php` (RefreshDatabase). Run `composer install` then `php artisan test`.

## Site and SEO

- Every page sets a distinct `<title>` and meta description; layout includes canonical, Open Graph + Twitter image (`/images/og-default.png`), JSON-LD on home.
- `lang="en"`, all images have alt text, `public/sitemap.xml` + `/sitemap.xml` route + `robots.txt`.
- `vite.config.js`: `sourcemap: false`, chart.js split to separate chunk to avoid massive bundles. `resources/js/app.js` has no console output.
- Design: pure white backgrounds, Lucide icons, system font stack (no Inter/Geist/Space Grotesk), square corners, no shadows, no gradients, no emojis, horizontal scroll strip instead of 3 cards in a row, skeleton loaders, TOS at /terms and Privacy at /privacy.

## Manual walkthrough

Register, create company, invite second user (`/invitations`), accept via `/invitations/accept/{token}`, assign task, comment, transition (todo to in_progress to in_review to done), log time, view `/dashboard` and `/reports`.

## Phase 2: Billing (Stripe)

Two plans, enforced in code (not decoration): Free = 1 company per user, 5 members per company, no Excel exports. Team = unlimited + exports, 12 USD/month via Stripe Checkout.

Setup:

```bash
composer install  # pulls stripe/stripe-php
# .env:
STRIPE_KEY=pk_test_...
STRIPE_SECRET=sk_test_...
STRIPE_TEAM_PRICE_ID=price_...
STRIPE_WEBHOOK_SECRET=whsec_...
php artisan migrate
stripe listen --forward-to localhost:8000/stripe/webhook  # local webhook testing
```

How it works:

- Checkout: owner opens `/billing`, posts to `/billing/checkout` (policy `manageBilling`). `BillingGateway` creates a Stripe Checkout session (subscription mode, `metadata.company_id`); card form is Stripe-hosted so PCI surface stays with Stripe. No keys configured → `FakeGateway` serves local dev. Success/cancel pages at `/billing/success`, `/billing/cancel`.
- Portal: `/billing/portal` creates a Stripe customer-portal session so companies manage payment method, invoices and cancellation themselves.
- Enforcement lives in `App\Billing\PlanLimits` and is called from `InvitationController@store`, `CompanyController@store`, the `within-limits` middleware (member adds), the `plan:team` middleware (exports) and `Company::teamFeaturesActive()`. Without this layer billing would be decoration.
- Webhooks: `POST /stripe/webhook` (CSRF-exempt, HMAC signature verified, 5-minute replay guard) handles `checkout.session.completed`, `customer.subscription.created/updated/deleted`, `invoice.payment_failed`. Handled by `SubscriptionService` with upserts keyed on Stripe IDs.
- Idempotency: every Stripe `event_id` is stored in `webhook_events` on first sight (`firstOrCreate` under `lockForUpdate`) and marked `processed_at` after handling. Retries return 200 with `duplicate: true` and never double-apply. Same thinking as an idempotent outbox.
- Graceful downgrade: `payment_failed` sets `past_due` + 7-day `grace_until` (team features keep working, banner warns). `subscription.deleted` flips plan to free but keeps all data; member/company adds stay blocked until the count is compliant or the company re-upgrades (`PlanLimits::isOverLimit`).
- Money-path tests: `tests/Feature/BillingTest.php` covers checkout creation (fake + real gateway payload via `Http::fake`), portal redirect, webhook activation, duplicate delivery, bad signature, past-due grace, downgrade with data retention, per-plan member/company/export limits, and over-limit read-but-not-add. Run `php artisan test --filter=BillingTest`.
