# AGENTS.md

# Purok Development Guide

## Purpose

This repository is developed with AI-assisted workflows.

Before implementing any feature:

1. Read `docs/context.md`
2. Read any relevant file inside `docs/features/`
3. Analyze the existing implementation
4. Explain the implementation plan
5. Wait for approval before making large architectural changes

---

# Technology Stack

* Laravel 13
* PHP 8.3+
* Livewire 4
* Filament 4
* TailwindCSS
* MySQL

---

# Development Philosophy

Prefer maintainability over cleverness.

Write code as if it will be maintained for many years.

Keep files focused and responsibilities clear.

Avoid unnecessary abstractions.

---

# Architecture

Business logic belongs in:

* Actions
* Services
* Repositories
* Support classes

Livewire components should:

* Validate input
* Call Actions or Services
* Return data to the view

Filament Resources should:

* Define forms
* Define tables
* Delegate business logic to Actions or Services

Repositories should contain reusable query logic.

DTOs should be immutable whenever possible.

Enums should replace hardcoded strings.

---

# Coding Standards

Use:

* strict_types=1
* Constructor property promotion
* Return types
* Typed properties
* Dependency injection

Prefer:

```php
public function execute(ProductData $data): Product
```

instead of

```php
public function execute(array $data)
```

---

# Project Rules

Do not:

* Duplicate business logic
* Put SQL inside Livewire components
* Put business logic inside Blade templates
* Use static helper classes unless appropriate
* Introduce unnecessary dependencies

Always:

* Reuse existing services
* Search the project before creating new classes
* Keep methods small
* Keep classes focused

---

# Database Rules

Prefer normalized tables.

Do not store product specifications as JSON unless explicitly required.

Use relationships instead of duplicated data.

Do not remove columns or tables without instruction.

---

# File Creation Rules

Before creating a new file:

1. Check whether an existing class can be extended.
2. Check whether similar functionality already exists.
3. Avoid duplicate repositories or services.

---

# Livewire Rules

Livewire components should:

* Load data
* Validate forms
* Dispatch Actions

They should not contain:

* Complex business logic
* Product import logic
* Tracking generation logic

---

# Filament Rules

Resources should remain thin.

Complex operations belong in Actions or Services.

Use RelationManagers instead of custom CRUD pages when possible.

---

# Imports and Exports

Able to export/import members and dependents
Able to export/import expenses
Able to export/import incomes
Able to export/import rentals

---

# Testing

For new business logic:

* Create Feature or Unit tests when appropriate.
* Keep services independently testable.
* Avoid tightly coupling business logic to Livewire.

---

# Documentation

If architecture changes:

Update the appropriate file inside `docs/`.

Do not leave documentation outdated.

---

# Git

Keep changes focused.

One feature per commit.

Avoid modifying unrelated files.

---

# Output Style

Before coding:

1. Explain the implementation plan.
2. List files that will be modified.
3. Explain assumptions.

After coding:

1. Summarize changes.
2. List created files.
3. List modified files.
4. Mention any follow-up tasks.

---

# Priority Order

When making decisions:

1. Existing architecture
2. Existing documentation
3. Maintainability
4. Performance
5. Convenience

Never sacrifice maintainability for short-term speed.
