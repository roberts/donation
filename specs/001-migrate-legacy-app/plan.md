# Implementation Plan: Migrate Legacy App Logic

**Branch**: `001-migrate-legacy-app` | **Date**: 2025-12-10 | **Spec**: [spec.md](./spec.md)
**Input**: Feature specification from `/specs/001-migrate-legacy-app/spec.md`

**Note**: This template is filled in by the `/speckit.plan` command. See `.specify/templates/commands/plan.md` for the execution workflow.

## Summary

Migrate the legacy Symfony/ArrestDB/PHP application to a modern Laravel 12 monolith. Key components include a public donation flow using Inertia.js/Vue 3, an Admin Panel using Filament v4.3, and Stripe integration via Laravel Cashier. The project emphasizes strict adherence to "The Laravel Way," utilizing Spatie packages for permissions, data transfer objects, and PDF generation. Legacy directories (`admin`, `api`, `stripesecret`, `webhooks`, `assets`) will be removed upon completion.

## Technical Context

**Language/Version**: PHP 8.4.15
**Primary Dependencies**: 
- Laravel 12
- Inertia.js v2 (Vue 3)
- Tailwind CSS v4
- Filament v4.3
- Laravel Cashier (Stripe)
- Spatie Packages (Permission, Data, PDF, Activitylog, Backup, CSP, Health)
- Resend (Email)
**Storage**: MySQL
**Testing**: Pest v4.1 (Feature & Browser tests)
**Target Platform**: Linux server
**Project Type**: Web application (Laravel Monolith)
**Performance Goals**: Immediate UI feedback for donations (async processing), <200ms response times for standard pages.
**Constraints**: Strict adherence to Laravel conventions, Inertia-First frontend, Pest testing, removal of legacy code.
**Scale/Scope**: Full replacement of legacy functionality, new Admin Panel, robust testing suite.

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

- [x] **The Laravel Way**: Plan uses standard Laravel features (Cashier, Queues, Mailables) and ecosystem packages (Filament, Spatie).
- [x] **Inertia-First Frontend**: Public donation flow is Inertia/Vue. Admin is Filament (Livewire-based, but acceptable as standard Laravel admin solution).
- [x] **Testing Discipline**: Pest v4 specified for all testing.
- [x] **Modern Styling**: Tailwind v4 specified.
- [x] **Strict Typing**: PHP 8.2+ features required by Constitution and Spec.

## Project Structure

### Documentation (this feature)

```text
specs/001-migrate-legacy-app/
├── plan.md              # This file
├── research.md          # Phase 0 output
├── data-model.md        # Phase 1 output
├── quickstart.md        # Phase 1 output
├── contracts/           # Phase 1 output
└── tasks.md             # Phase 2 output
```

### Source Code (repository root)

```text
app/
├── Actions/             # Domain actions (if needed)
├── Filament/            # Admin Panel Resources & Widgets
├── Http/
│   ├── Controllers/     # Standard Controllers
│   ├── Middleware/
│   └── Requests/        # Form Requests
├── Models/              # Eloquent Models
├── Mail/                # Mailables
├── Jobs/                # Queued Jobs
└── Policies/            # Authorization Policies

resources/
├── js/
│   ├── Pages/           # Inertia Pages
│   └── Components/      # Vue Components
└── views/               # Blade templates (PDFs, Emails)

tests/
├── Feature/             # Pest Feature Tests
├── Unit/                # Pest Unit Tests
└── Browser/             # Pest Browser Tests
```

**Structure Decision**: Standard Laravel 12 application structure with Filament for the admin panel and Inertia for the frontend.

## Complexity Tracking

> **Fill ONLY if Constitution Check has violations that must be justified**

N/A - Compliant with Constitution.

| Violation | Why Needed | Simpler Alternative Rejected Because |
|-----------|------------|-------------------------------------|
| [e.g., 4th project] | [current need] | [why 3 projects insufficient] |
| [e.g., Repository pattern] | [specific problem] | [why direct DB access insufficient] |
