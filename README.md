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
- `vendor/` — Composer dependencies
- `test/` — testing and validation work

Additional root files include configuration, routing, bootstrap, and Composer files.

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

## Requirements

Before running the application locally, make sure you have:

- PHP 8.x
- MySQL 8.x or a compatible MariaDB version
- Composer
- A local web server environment such as Apache, Nginx, XAMPP, Laragon, or similar

## Local Installation

### 1. Clone the repository

    git clone https://github.com/cbrewt/LedgerCore.git
    cd LedgerCore

### 2. Install Composer dependencies

    composer install

### 3. Create the database

Create a MySQL database for LedgerCore.

Example:

    CREATE DATABASE ledgercore;

### 4. Import the schema

Import your database schema and any required seed data.

If you maintain SQL setup files separately, import them into the new database before running the application.

### 5. Configure database settings

Update the application configuration so it points to your local database credentials.

Typical items to verify:

- database host
- database name
- database username
- database password

Review files such as:

- `config.php`
- `bootstrap.php`

and any other environment-specific configuration files used by the application.

### 6. Point your web server to the public directory

Your document root should point to:

    /public

This helps keep non-public application files outside the web root.

### 7. Start the application

Launch the site through your local development server and verify that routing, database connectivity, and page rendering are working correctly.

## Database Notes

LedgerCore uses a relational design centered around financial records and supporting reference tables. Based on the current codebase, the application includes tables such as:

- `rpaccounts`
- `transactions`
- `payees`
- `categories`
- `credit_cards`
- `transaction_types`
- `account_types`
- `account_balances`

These tables work together to support account tracking, transaction classification, payee relationships, and balance-related workflows.

## Development Notes

This project is being improved in careful stages rather than through large disruptive rewrites. Current development priorities include:

- controller cleanup
- archive workflow refinement
- account navigation cleanup
- reconciliation stability
- payee and transaction workflow reliability
- long-term architecture improvements

## Roadmap

Planned or ongoing improvements include:

- improved repository organization
- clearer installation and database setup documentation
- continued controller refactoring
- stronger reconciliation support
- improved transaction filtering and reporting
- broader test coverage
- release tagging and project milestones
- screenshots and usage documentation

## Repository To-Do

The following repository improvements are still recommended:

- add a project description in GitHub settings
- add repository topics
- add a license
- review whether IDE-specific files should remain tracked
- review whether dependency directories should remain tracked
- add screenshots to this README
- document database setup in more detail
- add release tags as milestones are reached

## Contributing

This repository is currently being developed in a controlled incremental manner. If contribution guidelines are added later, they will be documented here.

## License

No license has been added yet.

If you intend for others to use, modify, or distribute this code, add an appropriate license file to the repository.
