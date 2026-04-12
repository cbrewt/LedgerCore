# LedgerCore

LedgerCore is a PHP and MySQL personal finance application for managing accounts, transactions, payees, categories, credit cards, balances, and reconciliation workflows in a structured ledger-style system.

This repository reflects an active refactor and cleanup effort. The application is functional in parts, and the codebase is being improved incrementally with an emphasis on maintainability, clearer controller structure, and a gradual move toward a cleaner architecture.

## Current Status

LedgerCore is currently:

- under active development
- being refactored in small, controlled steps
- focused on improving accounts, transactions, reconciliation, and related workflows
- moving toward a cleaner and more maintainable application structure

## Core Features

- Account management
- Transaction entry and review
- Payee management
- Category management
- Credit card tracking
- Account balance support
- Reconciliation workflow
- Filtered transaction views
- MVC-style project structure
- Composer-based dependency management

## Technology Stack

- PHP
- MySQL
- Composer
- HTML / CSS
- Traditional MVC-style application structure

## Project Structure

The top-level structure currently includes folders such as:

- `Core/` — core classes and foundational application logic
- `controllers/` — controller actions by module
- `views/` — application views
- `public/` — public entry point and assets
- `test/` — testing and validation work

Additional root files include configuration, routing, bootstrap, Composer files, and repository metadata.

## Modules

LedgerCore currently includes work around the following business areas:

- Accounts
- Credit Cards
- Categories
- Payees
- Transactions
- Transaction Types
- Account Types
- Account Balances
- Reconciliation

## Requirements

Before running the application locally, make sure you have:

- PHP 8.x
- MySQL 8.x or a compatible MariaDB version
- Composer
- A local web server environment such as Apache, Nginx, XAMPP, Laragon, or similar

## Local Installation

### 1. Clone the repository

```bash
git clone https://github.com/cbrewt/LedgerCore.git
cd LedgerCore
