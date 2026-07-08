# EAMO Backend System Integration and Configuration Report

This repository contains the backend API server and OAuth 2.0 PKCE authentication server for the EAMO (Equipment Asset Management Solution) platform. The application is built using Laravel 13, PostgreSQL, and Laravel Passport.

---

## 1. Prerequisites

The following software must be installed and configured on the host machine prior to deployment:
- PHP: Version 8.3 or higher (with pdo_pgsql, openssl, and mbstring extensions enabled)
- Composer: Dependency manager for PHP
- Node.js & npm/pnpm: For asset compilation
- PostgreSQL: Database management system

---

## 2. Installation and Initial Configuration

### 2.1. Dependency Installation
Install the necessary PHP composer packages:
```bash
composer install
```

### 2.2. Environment Configuration
Duplicate the configuration template file and set up database credentials:
```bash
cp .env.example .env
```
Open the `.env` file and configure the PostgreSQL connection parameters:
```ini
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=eam
DB_USERNAME=postgres
DB_PASSWORD=your_postgres_password
```

### 2.3. Encryption Key Generation
Generate the unique application key:
```bash
php artisan key:generate
```

### 2.4. Database Migration and Seeding
Execute migrations to create the database schema (including Passport token tables and cache structures) and seed the initial dataset:
```bash
php artisan migrate:fresh --seed
```

### 2.5. OAuth Key Generation
Generate the encryption keys required by Laravel Passport to sign the JSON Web Tokens (JWT):
```bash
php artisan passport:keys --force
```

### 2.6. Public OAuth Client Creation
Register a public client for Proof Key for Code Exchange (PKCE) pointing to the frontend application:
```bash
php artisan passport:client --public --name="Eamo Frontend" --redirect_uri="http://localhost:5173/auth/callback" --no-interaction
```
Note: Note the generated Client ID and copy it into the frontend application configuration.

### 2.7. Public Storage Symlink
Create a symbolic link from the public folder to the storage directory to make uploaded assets (like equipment images) accessible from the web:
```bash
php artisan storage:link
```

### 2.8. Asset Compilation and Execution
Compile the frontend assets for the single-page Laravel login interface and run the local development server:
```bash
npm install
npm run build
php artisan serve
```
The server will run on: http://localhost:8000

---

## 3. Seeded Accounts

The seeding process registers a default administrative user account:
- Username: admin
- Password: 12345678

---

## 4. Documentation References

For in-depth analysis of the system architecture, authentication flow, and directory layouts, consult the following reports:
- [OAuth 2.0 PKCE Authentication Flow Report](docs/auth.md)
- [Backend Directory Structure and Architecture Report](docs/backend_structure.md)
