# Clusterfiy Platform Specification

> Clusterfiy is a multi-tenant, role-based project and task management platform built on Laravel, Blade, and Alpine.js. Below is an exhaustive walkthrough of its technical features.

## How to read this document

Each section carries one status tag so this spec never becomes a false-claims file:

- **[Shipped]** — implemented, tested, and documented in `README.md`.
- **[Partial]** — core exists; noted gaps remain.
- **[Planned]** — target architecture; no code in the repo yet. Do not cite these in CV/portfolio bullets until built.

## 1. Architecture & Stack [Partial]

- Backend: PHP 8.5, Laravel 13 (monolithic). **Delta:** `composer.json` currently requires PHP `^8.3`; CI pins 8.3. Bumping to 8.5 is a one-line change once the server runs it.
- Frontend: Blade templates + Alpine.js, Tailwind CSS, Vite. **[Shipped]**
- Database: PostgreSQL preferred, MySQL acceptable. **Delta:** dev runs SQLite, prod template targets MySQL (`deploy/.env.production.example`). PostgreSQL is unconfigured — **[Planned]**.
- Cache/Queue: Redis for queues, cache, and session storage. **Delta:** currently `database` driver for all three — **[Planned]**.
- Real-time: Laravel Echo + Soketi (self-hosted) or Pusher — **[Planned]**.
- Search: Laravel Scout with Meilisearch — **[Planned]** (basic query filters exist).
- Storage: S3-compatible object storage for attachments and avatars. **Delta:** local disk now — **[Planned]**.
- Email: SMTP, configurable by Master Admin. **Delta:** env-based + Postmark failover now; in-app `smtp_settings` management — **[Planned]**.
- OAuth: Laravel Socialite for calendar, Slack, Drive, etc. — **[Planned]**.
- PDF/CSV export: DomPDF for PDF, native CSV streaming. **Delta:** DomPDF installed (`barryvdh/laravel-dompdf`); `TaskExports` exists; Team-gated Excel export shipped — **[Partial]**.
- Charts: Chart.js for dashboards. **[Shipped]** (lazy-loaded chunk).
- Gantt: Frappe Gantt for timeline view — **[Planned]**.
- Drag-and-drop: SortableJS for Kanban and widget reordering — **[Planned]**.
- Deployment: Nginx + PHP-FPM **[Shipped]** (`deploy/`, `DEPLOY.md`); Redis, Soketi, Meilisearch, S3/MinIO **[Planned]**; CI/CD via GitHub Actions **[Shipped]** (test workflow).

## 2. Multi-Tenancy & Data Isolation [Shipped]

- Every tenant-specific table carries a `company_id` foreign key (added to comments, time entries, status changes, activity logs in the Phase 1 migration).
- A global Eloquent scope (`App\Models\Scopes\CompanyScope`) filters queries by the active company from the `CompanyContext` singleton populated by `SetCurrentCompany` middleware. (Spec name `HasCompanyScope` is equivalent; ours is `CompanyScope`.)
- Master Admin (role `super_admin`) bypasses the scope via `withoutGlobalScope` for platform views and the company switch endpoint.
- Repository pattern (`app/Repositories`) plus global scopes prevent cross-tenant leakage; covered by `TenancyTest`.
- Personal data is scoped by `assigned_to`/`user_id`.

## 3. Authentication & Account Provisioning [Partial]

- **Delta:** self-registration EXISTS (`register` routes + demo flow). The spec's "no self-registration" is a policy choice for locked-down deployments — enforce by disabling the route, not assumed.
- Login: email + password with `throttle.login` alias; remember-me via default session config.
- Password recovery: Laravel default reset flow (hashed tokens). 30-minute expiry / single-use / 5-per-hour-per-email tightening is **[Planned]** — current expiry is the framework default.
- 2FA: optional via Laravel Fortify (config + columns present).
- API tokens: Laravel Sanctum. **[Shipped]**
- Session security: CSRF protection (Stripe webhook explicitly exempted), secure cookies via `SESSION_SECURE_COOKIE` in prod env, HTTPS enforced at Nginx.

## 4. Role Hierarchy & Permissions [Partial]

- Global roles map 1:1 to the spec: Master Admin → `super_admin` (platform: companies, billing oversight, full data access; impersonation/broadcast are **[Planned]**), Company Admin → `company_admin` (one company: departments, managers, audit logs, billing), Manager → `manager` (members, projects, task assignment), Employee → `employee` (assigned tasks, status, comments, time). Matrix in `README.md`, enforced in policies, tested in `RolesTest`.
- Project-level roles (Viewer, Member, Project Admin in `project_members`) — **[Planned]**. `task_assignees` pivot exists for task-level assignment.
- Policies enforce every shipped resource (Task, Company, Department, User, CompanyInvitation). SavedSearch/Integration/Broadcast/PersonalToDo policies arrive with those features — **[Planned]**.
- Middleware: `is_super_admin`, `SetCurrentCompany`, `plan`, `within-limits`. (Spec names `role`, `user.role`, `company.set` are equivalents.)

## 5. Company Structure Management [Partial]

- Departments: name, description, manager — **[Shipped]** (`departments`).
- Branches (name, address, contact) — **[Planned]**; users carry `company_id`, no `branch_id` yet.
- Company Admin console manages departments now; branches arrive with the table.

## 6. Module Control [Planned]

- No `company_modules` table yet; no per-company module toggles or route gating. All shipped modules are visible to every company.

## 7. Billing [Shipped]

- `subscriptions` table stores plan, status, Stripe customer/subscription IDs, period end, cancel flag. **Delta:** billing details live in Stripe (Checkout + Customer Portal), not locally — nothing sensitive is stored at rest here, which is stronger than "encrypted billing info" for our PCI surface. `billing_email` on companies is currently unencrypted (flagged gap if card data ever touches us — it doesn't).
- Master Admin (`super_admin`) can view/manage any company's billing via `manageBilling` policy; Company Admin manages their own company. Plan changes flow through Stripe webhooks (`SubscriptionService`).
- Company Admin billing input = upgrade button → Checkout → portal link on `/billing`.

## 8. Project & Task Hierarchy [Partial]

- Projects with soft deletes **[Shipped]**; sub-projects/folders via `parent_id` — **[Planned]** (verify `Project` model before adding).
- Tasks: `company_id`, `department_id`, `project_id`, assignee(s) — **[Shipped]**. Subtasks (`parent_task_id`), dependencies (`blocked_by_task_id`) — **[Planned]** (the old June `parent_task_id` was dropped with the superseded migration; re-add deliberately).
- Priorities (low/medium/high/urgent) **[Shipped]**; fixed status enum **[Shipped]**; custom statuses per project (`custom_task_statuses`) — **[Planned]**.
- Tags/labels JSON, manual/auto progress, cached project progress, `task_versions`, `recurrence_rule`, `task_reminders`, `saved_searches` — **[Planned]**.
- Time tracking: `time_entries` (started/ended/duration/note), totals per task — **[Shipped]**.
- Attachments: metadata in `attachments` (polymorphic), local disk — **[Shipped]**; S3/MinIO backend **[Planned]**.
- Comments & mentions: threaded (`parent_id`) **[Shipped]**; `@username` parsing → notifications **[Planned]**.

## 9. Views & Visualisation [Partial]

- List view with status/priority/assignee filters **[Shipped]** (`tasks.index`).
- Kanban (SortableJS + AJAX), Calendar (FullCalendar + `/calendar/events`), Timeline/Gantt (Frappe + per-project data endpoint), smart lists (`saved_searches`) — **[Planned]**.
- Role-based dashboards **[Shipped]** (manager vs member DTOs, Chart.js trend).
- Customisable widget dashboards, per-project custom statuses with colours/order — **[Planned]**.

## 10. Personal To-Do List [Planned]

- No `personal_to_dos` table, routes, or reminder scheduler entries. Scheduler slots exist (`routes/console.php` pattern + cron) — add `todos:send-reminders` alongside the current nightly jobs.

## 11. Collaboration & Messaging [Partial]

- Task comments (threaded) + task file attachments **[Shipped]**.
- Direct messages (`messages`, `read_at`), private `user.{id}` channels, file sharing beyond tasks — **[Planned]**.

## 12. Notifications [Partial]

- In-app (database channel, bell with unread count at `/notifications`) + email (queued mail for assignment/invites) **[Shipped]**.
- `notification_preferences` opt-outs, `broadcast_notifications` (admin → all/by-role), `smtp_settings` management — **[Planned]**.

## 13. Automation [Planned]

- No `automation_rules` engine, no trigger/action runner. When built: triggers (`task.created/updated/due_soon/completed`), JSON conditions/actions, queued execution < 1 min, admin-only sensitive actions, `AutomationTriggered` event.

## 14. Reporting & Analytics [Partial]

- Standard reports (completion rate, by-priority, overdue) + Chart.js dashboards **[Shipped]** (`ReportService`, Team-gated export).
- `report_schedules` (weekly/monthly + `reports:send-scheduled`), streamed CSV, DomPDF output wiring — **[Planned]** (DomPDF package present).

## 15. Search & Filters [Partial]

- Query filters by status/priority/assignee/department **[Shipped]**.
- Scout + Meilisearch `toSearchableArray` on Task, branch/department facets, `saved_searches` — **[Planned]**.

## 16. Real-Time Architecture [Planned]

- No Echo/Soketi/Pusher, no private channels (`project.*`, `user.*`, `company.*`), none of the listed broadcast events. Mail + database notifications are the current async story.

## 17. Integrations [Partial]

- Sanctum REST API for auth/tasks/companies/dashboard **[Shipped]** (`app/Http/Controllers/Api`); Stripe incoming webhook **[Shipped]** (`/stripe/webhook`).
- Socialite (Calendar/Slack/Drive/Outlook/CRM), encrypted `integrations` credentials, outgoing `webhooks` + incoming `/webhooks/{webhook}`, calendar sync, S3 — **[Planned]**.

## 18. Security [Partial]

- HTTPS/HSTS/security headers (Nginx) **[Shipped]**; bcrypt hashing **[Shipped]**; optional 2FA **[Shipped]**; auth throttling **[Shipped]** (`throttle.login`; per-email reset tightening **[Planned]**); global-scope isolation + `TenancyTest` **[Shipped]**; `ActivityLog` audit trail **[Shipped]** (admin-action coverage grows with features); encrypted storage for SMTP/billing/integration secrets **[Planned]** (nothing of that class is stored locally today except `billing_email`); reset tokens per framework default (see §3); Master Admin access logging **[Partial]** (IsSuperAdmin logs access; extend per action as needed).

## 19. Non-Functional Requirements

- Performance targets (< 500 ms API/search/real-time): real-time N/A until §16; API/search unmeasured — add a `php artisan test --profile`-style timing or APM before claiming.
- Scalability: stateless app + database queue now; Redis/horizontal scaling with §1.
- Reliability: DB backup in `deploy.sh` **[Shipped]**; 99.9% uptime needs UptimeRobot (Phase 3.4) + automated backup restores tested.
- Compatibility (320px–1920px, modern browsers), WCAG 2.1 AA, keyboard shortcuts — **[Planned]** (responsive Tailwind base exists).

## 20. Deployment Architecture [Partial]

- Nginx + PHP-FPM + MySQL + Supervisor queue worker + cron scheduler + GHA CI **[Shipped]** (`deploy/`, `DEPLOY.md`, `.github/workflows/ci.yml`).
- Redis, Soketi, Meilisearch, S3/MinIO, staging environment — **[Planned]** (add to `DEPLOY.md` when adopted).

## 21. Data Model (key entities) — existence map

- Exist: `companies`, `departments`, `users`, `company_user`, `projects`, `tasks`, `task_assignees`, `attachments`, `comments`, `time_entries`, `task_status_changes`, `activity_logs`, `login_history`, `company_invitations`, `subscriptions`, `webhook_events`, permission tables.
- Planned: `branches`, `project_members`, `company_modules`, `custom_task_statuses`, `task_versions`, `task_reminders`, `saved_searches`, `personal_to_dos`, `messages`, `broadcast_notifications`, `notification_preferences` (or JSON), `smtp_settings`, `automation_rules`, `report_schedules`, `integrations`, `webhooks`, `activities` (covered by `activity_logs` — merge, don't duplicate).

## 22. Progress Calculation [Planned]

- Manual task progress, subtask-derived progress, cached `projects.progress` via events. Needs §8 subtasks first.

## 23. UI/UX Features [Partial]

- Responsive layout **[Shipped]**; collapsible sidebar / mobile bottom nav **[Planned]**; indigo primary + system font, pure-white surfaces **[Shipped]**; dark-mode toggle **[Planned]** (Tailwind `darkMode: 'class'` is configured, toggle + audit pending); Blade components (layouts, skeleton loaders) **[Shipped]**; Kanban/widget drag-and-drop, notification dropdown badge (page exists, dropdown pending), styled recovery screens, keyboard shortcuts, a11y pass — **[Planned]**.

## 24. Commands & Scheduling [Partial]

- Nightly `invitations:purge-expired` + `billing:enforce-grace` **[Shipped]** via cron-driven `schedule:run`.
- `tasks:generate-recurring`, `tasks:send-reminders` (every minute), `reports:send-scheduled` — **[Planned]** with their tables.

## 25. API Design [Partial]

- Shipped: login, tasks, companies, dashboard (+ Stripe webhook outside `/api`). Sanctum throughout.
- Planned: branches, departments, users, messages, broadcast, modules, comments, to-dos, versions, time entries, automation rules, reports, custom statuses, generic incoming webhooks.

---

*Rule: promoting any [Planned] item to [Shipped] requires code + test + README section, per the Phase 4 exit criteria.*
