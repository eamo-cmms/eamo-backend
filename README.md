# EAMO Backend — OAuth & API Server

EAMO Backend acts as the API server and OAuth 2.0 PKCE authentication server for the EAMO client SPA. It utilizes Laravel 13, PostgreSQL, and Laravel Passport.

---

## 🛠️ Prerequisites

Make sure you have the following installed on your machine:
- **PHP** ^8.3 (with `pdo_pgsql`, `openssl`, `mbstring` extensions enabled)
- **Composer**
- **NodeJS** & **npm** / **pnpm**
- **PostgreSQL** Database server

---

## 🚀 Quick Start & Installation

Follow these steps to set up the backend server locally:

### 1. Install PHP Dependencies
```bash
composer install
```

### 2. Configure Environment Variables
Copy `.env.example` to `.env` and set up your PostgreSQL database credentials:
```bash
cp .env.example .env
```
Open `.env` and configure the database block:
```ini
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=eam
DB_USERNAME=postgres
DB_PASSWORD=your_postgres_password
```

### 3. Generate App Key
```bash
php artisan key:generate
```

### 4. Run Migrations & Database Seeds
This creates all tables (including Passport and cache structures) and registers a default Admin account:
```bash
php artisan migrate:fresh --seed
```

### 5. Generate Passport JWT Keys
Generate the encryption keys required by Passport to sign JWT tokens:
```bash
php artisan passport:keys --force
```

### 6. Create OAuth Public Client (PKCE)
Create a client configuration pointing to the local frontend instance:
```bash
php artisan passport:client --public --name="Eamo Frontend" --redirect_uri="http://localhost:5173/auth/callback" --no-interaction
```
*Note: Make sure to copy the outputted **Client ID** and paste it into the frontend configuration (`src/services/auth.ts`).*

### 7. Compile Assets & Start the Server
Compile the single-page login screen assets and boot up the server:
```bash
npm install
npm run build

# Start the Laravel application server
php artisan serve
```
The server will start at `http://localhost:8000`.

---

## 🔑 Seeding Credentials

After running `migrate:fresh --seed`, the database is populated with a default admin account:
- **Email / Username**: `admin`
- **Password**: `12345678`

---

## 📖 Architecture & Authentication Documentation

To learn more about the OAuth 2.0 PKCE implementation details, custom client models, and API endpoints, check the documentation file:
- [OAuth PKCE Setup Documentation](docs/auth.md)
