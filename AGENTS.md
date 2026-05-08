<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.5
- laravel/framework (LARAVEL) - v13
- laravel/prompts (PROMPTS) - v0
- laravel/boost (BOOST) - v2
- laravel/mcp (MCP) - v0
- laravel/pail (PAIL) - v1
- laravel/pint (PINT) - v1
- pestphp/pest (PEST) - v4
- phpunit/phpunit (PHPUNIT) - v12
- tailwindcss (TAILWINDCSS) - v4

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Always use `search-docs` before making code changes. Do not skip this step. It returns version-specific docs based on installed packages automatically.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.
- To check environment variables, read the `.env` file directly.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== pest/core rules ===

## Pest

- This project uses Pest for testing. Create tests: `php artisan make:test --pest {name}`.
- The `{name}` argument should not include the test suite directory. Use `php artisan make:test --pest SomeFeatureTest` instead of `php artisan make:test --pest Feature/SomeFeatureTest`.
- Run tests: `php artisan test --compact` or filter: `php artisan test --compact --filter=testName`.
- Do NOT delete tests without approval.

</laravel-boost-guidelines>

# Nawwat Cloud ERP Backend Instructions

You are the backend implementation agent for Nawwat Cloud ERP.

Your responsibility is backend logic only. Do not redesign UI unless explicitly requested.

## Product Context

Nawwat Cloud ERP is an enterprise B2B SaaS ERP platform built with Laravel.

The product is expected to support:

- Multi-tenancy
- Company settings
- Users, roles, and permissions
- HRMS
- Attendance
- Leave management
- Payroll
- Accounting
- Assets
- Documents
- Workflow approvals
- Unified approval inbox
- Import/export
- Data migration
- Analytics/KPI
- Marketplace add-ons
- SaaS billing
- Audit logs
- Notifications

## Backend Architecture

Use clean Laravel-native architecture. Follow Laravel conventions before introducing new patterns.

Keep the application structure simple and familiar:

- `app/Models`
- `app/Http/Controllers`
- `app/Http/Requests`
- `app/Policies`
- `app/Actions`
- `app/Services`
- `app/Enums`
- `app/DTOs`
- `app/Jobs`
- `app/Events`
- `app/Listeners`
- `app/Notifications`
- `app/Support`

Do not create custom framework or DDD-heavy folders such as:

- `app/Modules`
- `app/Shared`
- `Domain`
- `Infrastructure`
- complex custom module layers

Do not install packages unless they are truly necessary and approved. Prefer Laravel's built-in features, first-party patterns, and simple application code.

## Controller Rules

Keep controllers thin.

Controllers should only coordinate the request lifecycle:

- authorize the action
- accept validated input
- call an Action or Service
- return a response, redirect, resource, or view

Do not put business rules, tenant resolution, accounting logic, payroll logic, workflow decisions, or complex queries directly in controllers.

## Actions And Services

Put business logic in Actions or Services.

Use Actions for single business operations, especially commands that change state, such as:

- creating a company
- inviting a user
- approving a workflow request
- posting an accounting entry
- running payroll
- exporting sensitive data

Use Services for reusable domain behavior that is shared by multiple Actions, controllers, jobs, or listeners.

Do not create abstract service layers without a concrete need.

## Validation

Use Form Requests for validation for non-trivial endpoints.

Controllers should use validated data from `$request->validated()` or typed accessors. Do not pass `$request->all()` into models or services.

Keep authorization that depends on the request payload either in the Form Request `authorize()` method or in a Policy/Gate, depending on the resource being protected.

## Authorization

Use Policies or Gates for protected actions.

Every protected backend action must check authorization before changing or exposing tenant data.

Sensitive actions must be explicitly authorized, including:

- creating or updating users
- inviting users to a company
- changing roles or permissions
- updating employee salary
- running payroll
- approving workflows
- posting accounting entries
- exporting sensitive data
- changing company settings

Do not rely on hidden UI controls as authorization.

## Tenant Scoping

Every tenant-owned record must be scoped by tenant, company, or workspace.

Never allow data leakage between companies.

Every query for tenant-owned data must include the tenant scope directly, through a clear relationship, or through a deliberate tenant scoping mechanism.

When creating tenant-owned data, automatically attach the current tenant/company. Do not trust request input for `company_id` unless the value is authorized against the current user.

Users are platform identities. Company membership, roles, permissions, employee profiles, payroll records, documents, approvals, and accounting data are tenant-owned.

Do not build HRMS, payroll, accounting, documents, approvals, analytics, or billing features before the tenant boundary is clear.

## Audit Logging

Every critical action must be auditable.

Audit logs should capture enough context to investigate who did what, when, and within which tenant/company.

Audit sensitive operations, including:

- user and membership changes
- role and permission changes
- employee compensation changes
- payroll runs
- workflow approvals or rejections
- accounting postings
- sensitive exports
- company settings changes

Do not silently skip audit logging for sensitive operations.

## Transactions

Use database transactions for sensitive or multi-write operations.

Wrap operations in transactions when they:

- create or update multiple related records
- change financial, payroll, approval, or permission data
- depend on audit logs being written together with the business change
- dispatch events that should only happen after commit

Prefer after-commit behavior for queued jobs, events, notifications, and external side effects related to transactional changes.

## Error Handling

Never use broad `catch(Throwable)`.

Do not silently swallow errors.

Do not hide real errors behind generic messages.

Only catch exceptions that can be handled meaningfully. Let unexpected exceptions bubble to Laravel's exception handler.

## Database And Models

Use migrations for schema changes and Eloquent models for domain records.

Every model must define mass-assignment protection through fillable/guarded conventions already used by the project.

Use explicit relationships with return types. Prefer relationship queries over manual foreign key handling when possible.

Add indexes for tenant keys, foreign keys, lookup fields, and columns used in filtering or ordering.

## Testing

Use Pest for tests.

Prefer feature tests for backend workflows and tenant isolation.

Tests for tenant-owned features must prove that users cannot access, update, delete, approve, or export another company's data.

Do not delete existing tests without approval.

## Frontend Boundaries

Backend tasks should not redesign UI.

Only inspect or adjust Blade/frontend files when they affect routes, form submissions, authorization visibility, or backend behavior requested by the task.

Never put business logic in Blade.

## Change Discipline

Before changing code, read the existing files and follow the local conventions.

Do not rewrite unrelated code.

Do not add unused imports, unused request parameters, placeholder classes, or speculative abstractions.

Do not create documentation files unless explicitly requested.

## Expected Response After Each Task

After completing a task, summarize:

1. What was implemented
2. Files changed
3. Important backend decisions
4. Any migrations/commands to run
5. Any risks or next required task
