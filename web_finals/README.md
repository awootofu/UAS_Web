# Renstra Evaluation System

Sistem Evaluasi Rencana Strategis (Renstra) berbasis Laravel untuk pengelolaan, monitoring, dan evaluasi capaian renstra perguruan tinggi.

## Features

- **Authentication & Authorization**: Multi-role authentication menggunakan Laravel Breeze
- **Role-Based Access Control**: 6 roles (Admin, Dekan, GPM, GKM, Kaprodi, BPAP)
- **Renstra Management**: CRUD operasi untuk data renstra
- **Evaluasi System**: Submit dan approval evaluasi capaian
- **RTL (Rencana Tindak Lanjut)**: Pengelolaan tindak lanjut untuk indikator yang tidak tercapai
- **PDF Export**: Generate laporan dalam format PDF
- **Audit Trail**: Logging aktivitas penting

## Tech Stack

- **Framework**: Laravel 12.x
- **PHP**: 8.2+
- **Database**: MySQL
- **Authentication**: Laravel Breeze (Blade)
- **PDF Generation**: barryvdh/laravel-dompdf
- **CSS**: Tailwind CSS

## Roles & Permissions

| Role | Permissions |
|------|-------------|
| Admin | Full access to all features |
| Dekan | View reports, approve evaluasi |
| GPM | Review evaluasi, verify RTL |
| GKM | Manage RTL, view evaluasi |
| Kaprodi | Submit evaluasi, view renstra |
| BPAP | Manage renstra data |

## Installation

### Prerequisites

- PHP 8.2 or higher
- Composer
- MySQL 8.0 or higher
- Node.js & NPM

### Setup Steps

1. **Clone the repository**
   ```bash
   git clone https://github.com/awootofu/UAS_Web.git
   cd web_finals
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install NPM dependencies**
   ```bash
   npm install
   ```

4. **Environment Configuration**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Configure Database**
   
   Edit `.env` file with your database credentials:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=renstra
   DB_USERNAME=root
   DB_PASSWORD=
   ```

6. **Run Migrations & Seeders**
   ```bash
   php artisan migrate --seed
   ```

7. **Build Assets**
   ```bash
   npm run build
   ```

8. **Create Storage Link**
   ```bash
   php artisan storage:link
   ```

9. **Start Development Server**
   ```bash
   php artisan serve
   ```

## Default Users (Seeded)

| Email | Password | Role |
|-------|----------|------|
| admin@renstra.test | password | Admin |
| dekan@renstra.test | password | Dekan |
| gpm@renstra.test | password | GPM |
| gkm@renstra.test | password | GKM |
| kaprodi@renstra.test | password | Kaprodi |
| bpap@renstra.test | password | BPAP |

## Database Schema

### Main Tables

- `users` - User accounts with role assignment
- `prodi` - Program Studi data
- `jabatan` - Jabatan/Position data
- `renstra_kategori` - Renstra categories
- `renstra_kegiatan` - Renstra activities
- `renstra_indikator` - Renstra indicators
- `renstra_target` - Yearly targets
- `renstra` - Main renstra records
- `evaluasi` - Evaluation submissions
- `evaluasi_bukti` - Evaluation evidence files
- `rtl` - Follow-up action plans
- `audit_logs` - Activity audit trail

## Testing

Run the test suite:

```bash
php artisan test
```

Run specific test files:

```bash
php artisan test --filter=RoleAccessTest
php artisan test --filter=RTLTest
php artisan test --filter=EvaluasiTest
```

## Project Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── DashboardController.php
│   │   ├── RenstraController.php
│   │   ├── EvaluasiController.php
│   │   ├── RTLController.php
│   │   ├── UserController.php
│   │   └── ReportController.php
│   └── Middleware/
│       └── RoleMiddleware.php
├── Models/
│   ├── User.php
│   ├── Prodi.php
│   ├── Jabatan.php
│   ├── Renstra*.php
│   ├── Evaluasi.php
│   ├── RTL.php
│   └── AuditLog.php
└── Policies/
    ├── RenstraPolicy.php
    ├── EvaluasiPolicy.php
    └── RTLPolicy.php

resources/views/
├── dashboard.blade.php
├── renstra/
├── evaluasi/
├── rtl/
├── users/
└── reports/
```

## API Routes

### Renstra
- `GET /renstra` - List all renstra
- `GET /renstra/{id}` - View renstra detail
- `POST /renstra` - Create renstra (admin, bpap)
- `PUT /renstra/{id}` - Update renstra (admin, bpap)
- `DELETE /renstra/{id}` - Delete renstra (admin, bpap)

### Evaluasi
- `GET /evaluasi` - List evaluasi
- `POST /evaluasi` - Submit evaluasi (admin, kaprodi, gpm, dekan)
- `POST /evaluasi/{id}/approve` - Approve evaluasi
- `POST /evaluasi/{id}/reject` - Reject evaluasi

### RTL
- `GET /rtl` - List RTL
- `POST /rtl` - Create RTL (admin, gkm)
- `POST /rtl/{id}/complete` - Mark RTL complete
- `POST /rtl/{id}/verify` - Verify RTL (admin, gpm, dekan)

### Reports
- `GET /reports/renstra` - View report page
- `GET /reports/renstra/pdf` - Download PDF report

## License

This project is created for educational purposes (UAS Web Development).

## Contributors

- Student Name / NIM
