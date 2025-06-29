# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

TripQuota is a multi-traveler trip management web application built with Laravel and Vue.js. It helps groups plan, share, and manage travel information including flights, accommodations, itineraries, and expense splitting.

### Project specs

- @AI-docs/000-project.md
- @AI-docs/001-base.md
- @AI-docs/002-class-model.md
- @AI-docs/database.md
- @AI-docs/directory-pattern.md
- @AI-docs/laravel.md
- @AI-docs/git-commit.md
- @AI-docs/php-composer.md
- @AI-docs/techinfo.md
- @AI-docs/ui-info.md
- AI-docs/zzz.md -- 読んでるか怪しい時に有効にしている。


## Development Commands

### Backend (Laravel)
```bash
# Run all tests
php artisan test

# Run specific test class
php artisan test --filter=ExpenseTest

# Run specific test method
php artisan test --filter=testCanCreateExpense

# Database migrations
php artisan migrate

# Generate factory for model
php artisan generate:factory ModelName

# Update model properties after schema changes
php artisan ide-helper:model ModelName

# Code formatting
./vendor/bin/pint

# Start development server (if composer dev script exists)
php artisan serve
```

### Frontend (Vue.js/Vite)
```bash
# Development mode
npm run dev

# Build for production  
npm run build

# Note: Frontend testing setup needs to be configured
# Vue component tests would use Vitest when properly set up
```

## Architecture Overview

### Domain-Driven Design
- Each feature is implemented as a domain under `TripQuota\` namespace
- Domains are accessed through `<DomainName>Service` classes
- Domains are self-contained and don't directly use other domain classes (except through Services)
- Repository pattern abstracts Eloquent models for testing
- Service classes use Repository interfaces for data access
- Mapping classes in `App\Mapping\<Domain>\` convert between Eloquent models and Repository interfaces

### Technology Stack
- **Backend**: PHP 8.3, Laravel 11
- **Frontend**: Vue.js 3, Vite, Tailwind CSS
- **Database**: SQLite (testing), with support for multiple currencies
- **Testing**: PHPUnit for backend, Vitest planned for frontend
- **Code Quality**: Laravel Pint for formatting, IDE Helper for model properties

### Key Directories
- `app/` - Laravel application (Controllers, Models, Enums)
- `TripQuota/` - Domain services and business logic
- `resources/js/components/` - Vue.js components
- `resources/views/` - Blade templates
- `tests/Feature/` - Integration tests
- `tests/Unit/TripQuota/` - Domain service unit tests

### Database Structure
- **Core entities**: Users, Accounts, TravelPlans, Groups, Members
- **Trip content**: Itineraries, Accommodations, Expenses
- **Group types**: CORE (all members) and BRANCH (subgroups)
- **Expense management**: ExpenseMembers, ExpenseSettlements with multi-currency support

## Testing Patterns

### Backend Tests
- Use `RefreshDatabase` trait for database tests
- Factory pattern for model creation
- Feature tests for HTTP endpoints and views
- Unit tests for domain services with repository mocking

### Frontend Tests
- Frontend testing framework needs to be configured
- When set up, should use Vitest with Vue Test Utils
- Mock child components when testing parent components
- Test user interactions and component state changes
- Test files should be located in `resources/js/**/*.{test,spec}.{js,mjs,cjs,ts,mts,cts,jsx,tsx}`

## Development Guidelines

### Transaction Handling
- Use `DB::transaction(function(){ /* operations */ })` for database transactions
- Avoid `DB::beginTransaction(); ... DB::commit();` pattern

### Code Style
- Follow existing Laravel and Vue.js conventions
- Use existing libraries and utilities
- Mimic established patterns in the codebase
- Never expose or commit secrets/keys

### Routing Guidelines
- **NEVER use `Route::resource()`** in `routes/web.php`
- Always define routes explicitly using `Route::get()`, `Route::post()`, etc.
- Use clear, descriptive route definitions for better maintainability

### Domain Guidelines
- Create new domain services in `TripQuota/` namespace
- Implement Repository pattern for data access
- Write unit tests with dummy repository implementations
- Ensure domain isolation (domains don't directly access other domains)

### Model Guidelines
- When creating new models, generate factories with `php artisan generate:factory`
- After table schema changes, update model properties with `php artisan ide-helper:model`
- Always format code with `php artisan pint` before committing
- Keep model relationships and properties properly documented

### Template Testing
- Always create display tests for new Blade templates
- Ensure tests pass before considering implementation complete

### Exception Handling
- All custom exceptions must extend `App\Exception\Exception`
- Never use `\Exception` directly
- Exception handling is centralized in `bootstrap/app.php` withExceptions
- Controllers should not contain try-catch blocks; exceptions flow to global handler

### JSON Data Handling
- Never use JSON column type in database
- Use `text` column with base64-encoded JSON data
- Create readonly classes in `app/Models/JSON/` for structured data
- Implement Cast classes to handle encoding/decoding
- Required methods: `toBase64JSON(): string` and `static fromBase64JSON(string): self`

## Recent Implementation Status

### Completed Features (2025-06-17)

#### UI Standardization & UX Improvements ✅
Comprehensive user interface standardization and improvements:

**Updated Views:**
- `dashboard.blade.php` - Added member management quick action icon
- `accommodations/index.blade.php` - Standardized bottom navigation
- `expenses/index.blade.php` - Standardized bottom navigation  
- `expenses/show.blade.php` - Standardized bottom navigation
- `settlements/index.blade.php` - Standardized bottom navigation
- `itineraries/create.blade.php` - Added train option, updated time labels
- `itineraries/edit.blade.php` - Added train option, updated time labels
- `members/create.blade.php` - Enhanced form field clearing
- All `travel-plans/*.blade.php` - Unified master.blade.php template usage

**Controller Updates:**
- `MemberController.php` - Fixed validation rules for member invitation

**Key Improvements:**
- Unified navigation pattern across all pages (bottom placement with consistent styling)
- Standardized template structure using master.blade.php layout
- Enhanced itinerary management (added train transportation, updated time labels)
- Fixed member invitation validation errors
- Improved dashboard with member management quick action
- Consistent design patterns and component usage

**Standardized Design Patterns:**
- Navigation placement: Bottom of page with `mt-8 flex justify-center`
- Link styling: `text-blue-600 hover:text-blue-800`
- Layout: `@extends('layouts.master')`
- Components: `@component('components.page-header')`

### Completed Features (2025-06-16)

#### Settlement Management System ✅
Comprehensive expense settlement system with debt optimization:

**Domain Services:**
- `TripQuota\Settlement\SettlementRepositoryInterface` - Data access contract
- `TripQuota\Settlement\SettlementRepository` - Eloquent implementation
- `TripQuota\Settlement\SettlementService` - Business logic for settlement calculations

**Controller & Routes:**
- `SettlementController` - Settlement management operations
- Settlement calculation, completion, and reset functionality

**Views:**
- `settlements/index.blade.php` - Settlement overview with statistics
- `settlements/show.blade.php` - Individual settlement details

**Key Features:**
- Multi-currency settlement calculations (JPY, USD, EUR, KRW, CNY)
- Debt optimization algorithms
- Settlement proposal generation and tracking
- Settlement completion workflow
- Statistical reporting and currency breakdown

**Testing:**
- 9 unit tests for SettlementService
- 11 feature tests for SettlementController
- Database optimization (removed redundant is_settled column)

#### Expense Management & Settlement System ✅
Comprehensive expense management with integrated settlement functionality:

**Domain Services:**
- `TripQuota\Expense\ExpenseRepositoryInterface` - Data access contract
- `TripQuota\Expense\ExpenseRepository` - Eloquent implementation with pivot table handling
- `TripQuota\Expense\ExpenseService` - Business logic for expense validation, calculation, and workflows
- `TripQuota\Settlement\SettlementService` - Integrated settlement calculations and debt optimization

**Controller & Routes:**
- `ExpenseController` - Full CRUD operations with automatic participation and settlement integration
- `SettlementController` - Settlement management integrated with expense workflow
- Explicit route definitions for all expense operations
- Special routes for split management and settlement confirmation

**Views:**
- `expenses/index.blade.php` - List with settlement status indicators (settled/unsettled)
- `expenses/create.blade.php` - Creation form with automatic member participation
- `expenses/show.blade.php` - Detailed view with integrated split management forms
- `expenses/edit.blade.php` - Edit form with existing data population
- `settlements/index.blade.php` - Settlement overview with expense integration
- `settlements/show.blade.php` - Individual settlement details

**Key Features:**
- **Integrated Workflow**: Expenses automatically flow into settlement calculations
- **Auto-participation**: Members automatically participate without manual confirmation
- **Settlement Status**: Real-time settlement status display (settled/unsettled)
- **Split Management**: Dedicated forms for modifying expense splits
- **Multi-currency**: Support for JPY, USD, EUR, KRW, CNY with automatic conversion
- **Debt Optimization**: Automatic calculation of optimal settlement paths
- **Unconfirmed Handling**: Unconfirmed expenses treated as unsettled
- **Streamlined UX**: Continuous expense entry workflow

**Testing:**
- 14 unit tests for ExpenseService
- 9 unit tests for SettlementService  
- 15 feature tests for ExpenseController
- 11 feature tests for SettlementController
- 11 view tests for template rendering
- Error handling and edge case coverage

### Next Priority Features

**High Priority:**
- Itinerary management functionality (create, edit, display)
- Responsive design improvements for mobile and tablet
- Enhanced user experience features

**Medium Priority:**
- Notification system for invitations and expense sharing
- Account management with multiple accounts per user
- Profile management with thumbnails and account switching

**See TODO.md for complete feature roadmap and implementation details.**

## Important Restrictions

- **NEVER read the `.env` file**
- Always ask user before making file changes
- Follow established naming conventions and patterns
- Maintain backward compatibility when possible