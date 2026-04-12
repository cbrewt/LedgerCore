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
```

### 2. Install Composer dependencies

```bash
composer install
```

### 3. Create your environment file

Create a local `.env` file based on `.env.example`.

### 4. Create the database

This public repository is configured for a demo/public showcase database.

Example:

```sql
CREATE DATABASE ledgercore_demo;
```

You may also choose a different local database name and update your environment settings accordingly.

### 5. Configure database settings

Set your local database credentials in your environment configuration.

Typical items to verify:

- database host
- database name
- database username
- database password
- database charset

### 6. Import the schema

Import your database schema and any demo or seed data you want to use.

### 7. Point your web server to the public directory

Your document root should point to:

```text
/public
```

### 8. Start the application

Launch the site through your local development server and verify that routing, database connectivity, and page rendering are working correctly.

## Demo Database Notes

This public repository is intended to use a demo/public showcase database configuration.

- Public/demo database name: `ledgercore_demo`
- Private/local personal database names and credentials should remain outside the repository
- Personal financial data is not included in this repository
- Any SQL exports or demo data included in the public project should be sanitized

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

## Repository Notes

The repository currently includes:

- an MIT license
- a `.gitignore` that excludes `.env`, `.idea`, and `vendor/`
- a demo-safe `.env.example`
- ongoing incremental cleanup and refactoring

## Contributing

This repository is currently being developed in a controlled incremental manner. If contribution guidelines are added later, they will be documented here.

## License

This project is licensed under the MIT License. See the `LICENSE` file for details.