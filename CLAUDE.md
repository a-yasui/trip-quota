# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

TripQuota is a multi-traveler trip management web application built with Laravel and Vue.js. It helps groups plan, share, and manage travel information including flights, accommodations, itineraries, and expense splitting.

### Project specs

- `@AI-docs/000-project.md`
- `@AI-docs/001-base.md`
- `@AI-docs/002-class-model.md`
- `@AI-docs/database.md`
- `@AI-docs/directory-pattern.md`
- `@AI-docs/git-commit.md`
- `@AI-docs/php-composer.md`
- `@AI-docs/techinfo.md`
- `@AI-docs/ui-info.md`
- `@AI-docs/zzz.md`


## Development Commands

### Backend (Laravel)
```bash
# Run all tests
php artisan test

# Run specific test
php artisan test --filter=ExpenseTest

# Start development server with queue and logs
composer dev

# Database migrations
php artisan migrate

# Code formatting
php artisan pint
```

### Frontend (Vue.js/Vite)
```bash
# Development mode
npm run dev

# Build for production  
npm run build

# Run frontend tests
npm run test

# Watch mode for tests
npm run test:watch
```

## Architecture Overview

### Domain-Driven Design
- Each feature is implemented as a domain under `TripQuota\` namespace
- Domains are accessed through `<DomainName>Service` classes
- Domains are self-contained and don't directly use other domain classes (except through Services)
- Repository pattern abstracts Eloquent models for testing

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
- Vitest with Vue Test Utils
- Mock child components when testing parent components
- Test user interactions and component state changes
- Located in `resources/js/**/*.{test,spec}.{js,mjs,cjs,ts,mts,cts,jsx,tsx}`

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

### Template Testing
- Always create display tests for new Blade templates
- Ensure tests pass before considering implementation complete

## Important Restrictions

- **NEVER read the `.env` file**
- Always ask user before making file changes
- Follow established naming conventions and patterns
- Maintain backward compatibility when possible