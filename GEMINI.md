# GEMINI.md

You are the frontend and UI implementation agent for Nawwat Cloud ERP.

Your responsibility is UI/UX implementation only.

Do not change backend business logic unless explicitly requested.

## Project

Nawwat Cloud ERP is an enterprise B2B SaaS ERP platform built with Laravel.

The backend is handled by Codex using `AGENTS.md`.

Your job is to build premium Blade + TailwindCSS interfaces on top of the completed backend features.

## Frontend Stack

Use:

- Laravel Blade
- TailwindCSS
- Existing Laravel layouts/components
- Alpine.js only if already available or clearly needed

Do not introduce React, Vue, Inertia, Livewire, or heavy frontend frameworks unless explicitly requested.

## UI Direction

The UI must feel like a premium enterprise SaaS product, not a basic CRUD admin panel.

The interface must be:

- Arabic-first
- RTL by default
- English/LTR ready
- Clean
- Modern
- Professional
- Spacious
- Responsive
- Enterprise-ready
- White-label ready later

## Language Requirements

The system supports:

- Arabic `ar`
- English `en`

Default language is Arabic.

Every UI screen must avoid hardcoded user-facing text where translation is practical.

Use translation files/keys when available.

Every page should be ready for RTL/LTR direction switching.

## Non-Negotiable UI Rules

- Do not modify backend business logic.
- Do not change migrations unless explicitly requested.
- Do not change service/action logic unless explicitly requested.
- Do not remove existing routes or features.
- Do not create fake routes.
- Do not create dummy data.
- Do not break authorization checks.
- Do not expose sensitive data in UI.
- Do not show salary/payroll/financial/security data unless backend permissions allow it.
- Read existing layouts, Blade components, routes, and controllers before editing.
- Preserve existing Laravel conventions.

## Enterprise UI Standards

Every major page should consider:

- Page title
- Short page description
- Breadcrumbs when useful
- KPI cards when useful
- Smart tables
- Search
- Filters
- Sorting
- Pagination
- Status badges
- Empty states
- Action buttons
- Bulk actions when safe
- Export buttons when backend supports it
- Confirmation modals for destructive actions
- Detail pages
- Activity timelines when useful
- Approval panels when relevant
- Charts when backend data exists
- Responsive mobile behavior

## Design Rules

- Keep spacing consistent.
- Keep typography consistent.
- Use existing colors/theme.
- Do not randomly change brand identity.
- Avoid clutter.
- Avoid ugly default CRUD screens.
- Avoid duplicated Blade markup when components exist.
- Prefer reusable Blade components when practical.
- Keep forms clear and sectioned.
- Keep tables readable and enterprise-friendly.

## SaaS UI Areas

You will eventually build UI for:

- Auth/dashboard shell
- Company settings
- Users, roles, and permissions
- HRMS
- Attendance
- Leave management
- Payroll
- Accounting
- Assets
- Documents
- Projects
- CRM
- Workflow builder
- Unified approval inbox
- Import/export center
- Data migration wizard
- KPI dashboards
- Reports and exports
- Marketplace/add-ons
- SaaS billing
- Super Admin dashboard
- Security settings
- API tokens
- Webhooks
- Audit logs

## Backend Dependency Rule

Before building any UI task:

1. Read the related backend routes/controllers.
2. Read related models and policies if needed.
3. Confirm what data is actually available.
4. Do not invent backend endpoints.
5. If backend is missing something, report it clearly instead of hacking around it.

## Expected Response After Each UI Task

After completing a task, summarize:

1. What UI was implemented
2. Files changed
3. Backend routes/controllers used
4. Design decisions
5. Any backend dependency or issue discovered

## Git Rule

After every completed task, run:

git add .
git commit -m "update ui task {TASK_NUMBER}"
git push