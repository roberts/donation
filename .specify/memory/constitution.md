<!--
SYNC IMPACT REPORT
Version change: New -> 1.0.0
List of modified principles: N/A (Initial Creation)
Added sections: All Principles, Governance
Removed sections: N/A
Templates requiring updates: ✅ None (Templates are generic)
Follow-up TODOs: None
-->

# IBE Foundation Portal Constitution

## Core Principles

### I. The Laravel Way
Follow standard Laravel conventions (MVC, Eloquent, Artisan, Service Providers) strictly. Use built-in features (Mailables, Notifications, Queues, Cashier) over custom implementations or third-party packages unless absolutely necessary. Prefer "Convention over Configuration". If Laravel provides a way to do it, do it that way.

### II. Inertia-First Frontend
Build all user interfaces using Inertia.js with Vue 3. Avoid traditional Blade views for dynamic content; Blade should only be used for the root layout (`app.blade.php`) and emails. Use standard Vue Composition API with `<script setup>`.

### III. Testing Discipline (Pest)
All features MUST be tested using Pest v4. Prefer Feature tests over Unit tests for controllers and business logic to ensure real-world reliability. Critical user flows (e.g., Donation process) MUST have Browser tests (Pest Browser/Dusk). Red-Green-Refactor cycle is encouraged.

### IV. Modern Styling (Tailwind v4)
Use utility-first CSS with Tailwind CSS v4 for all styling. Avoid custom CSS files unless absolutely necessary for complex animations or legacy integrations. Use `@apply` sparingly; prefer utility classes directly in markup.

### V. Strict Typing & Code Quality
Use PHP 8.2+ features extensively, including typed properties, return types, and constructor promotion. Run `pint` for code formatting before committing. Ensure Vue components use strict Prop validation or TypeScript interfaces.

## Application Architecture

### Tech Stack
- **Framework**: Laravel 12
- **Frontend**: Inertia.js (Vue 3)
- **Styling**: Tailwind CSS v4
- **Testing**: Pest v4.1
- **Database**: MySQL

### Directory Structure
- **Domain Logic**: Keep logic in `app/Models`, `app/Services` (for complex business logic), and `app/Http/Controllers`.
- **Frontend**: `resources/js/Pages` for Inertia pages, `resources/js/Components` for reusable UI.
- **Tests**: `tests/Feature`, `tests/Unit`, `tests/Browser`.

## Development Workflow

### Migration & Database
- All database changes MUST be done via Migrations.
- Model factories MUST be maintained for all Eloquent models to support testing.

### API & Webhooks
- API endpoints (if separate from Inertia) MUST use API Resources.
- Webhook handlers (e.g., Stripe) MUST be robust, idempotent, and logged.

## Governance

This constitution supersedes all other practices. Amendments require documentation, approval, and a migration plan.

**Rules**:
1. All Pull Requests must verify compliance with these principles.
2. Deviations must be explicitly justified in the PR description.
3. Complexity must be justified; keep solutions simple.

**Version**: 1.0.0 | **Ratified**: 2025-12-10 | **Last Amended**: 2025-12-10
