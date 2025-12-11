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
php artisan test --filter=RoleMiddlewareTest
php artisan test --filter=PolicyTest
php artisan test --filter=RoleAccessTest
php artisan test --filter=RTLTest
php artisan test --filter=EvaluasiTest
```

## Role-Based Access Control (RBAC)

This system implements comprehensive RBAC using Laravel's middleware and policies.

### Roles Overview

| Role | Description | Prodi-Bound |
|------|-------------|-------------|
| `admin` | System administrator with full access | No |
| `dekan` | Faculty dean, approves evaluations, views reports | No |
| `GPM` | Quality assurance team, reviews evaluations, verifies RTL | No |
| `GKM` | Quality control per prodi, creates and manages RTL | Yes |
| `kaprodi` | Program head, creates evaluations for their prodi | Yes |
| `BPAP` | Planning bureau, manages Renstra master data | No |

### Using the Role Middleware

The `RoleMiddleware` is registered as `role` in `bootstrap/app.php`. Use it to protect routes:

```php
// Single role
Route::get('/users', [UserController::class, 'index'])
    ->middleware('role:admin');

// Multiple roles (OR logic - any of these roles can access)
Route::get('/evaluasi/create', [EvaluasiController::class, 'create'])
    ->middleware('role:admin,kaprodi,gpm,dekan');

// In route groups
Route::middleware(['auth', 'role:admin,bpap'])->group(function () {
    Route::resource('renstra', RenstraController::class)->except(['index', 'show']);
});
```

### Using Policies

Policies provide fine-grained authorization at the model level:

```php
// In controllers - using authorize()
public function update(Request $request, Evaluasi $evaluasi)
{
    $this->authorize('update', $evaluasi);
    // ... update logic
}

// In controllers - using Gate facade
use Illuminate\Support\Facades\Gate;

if (Gate::allows('update', $evaluasi)) {
    // User can update
}

// In Blade templates
@can('create', App\Models\Renstra::class)
    <a href="{{ route('renstra.create') }}">Create Renstra</a>
@endcan

@can('update', $evaluasi)
    <a href="{{ route('evaluasi.edit', $evaluasi) }}">Edit</a>
@endcan
```

### Policy Rules Summary

#### RenstraPolicy
| Action | admin | dekan | GPM | GKM | kaprodi | BPAP |
|--------|-------|-------|-----|-----|---------|------|
| viewAny | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| view | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| create | ✅ | ❌ | ❌ | ❌ | ❌ | ✅ |
| update | ✅ | ❌ | ❌ | ❌ | ❌ | ✅ |
| delete | ✅ | ❌ | ❌ | ❌ | ❌ | ✅ |

#### EvaluasiPolicy
| Action | admin | dekan | GPM | GKM | kaprodi | BPAP |
|--------|-------|-------|-----|-----|---------|------|
| viewAny | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| view | ✅ | ✅ | ✅ | Own Prodi | Own Prodi | ❌ |
| create | ✅ | ❌ | ❌ | ❌ | ✅ | ❌ |
| update | ✅ | ❌ | ❌ | ❌ | Own Prodi | ❌ |
| verify | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |

#### RTLPolicy
| Action | admin | dekan | GPM | GKM | kaprodi | BPAP |
|--------|-------|-------|-----|-----|---------|------|
| viewAny | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ |
| view | ✅ | ✅ | ✅ | Own Prodi | Own Prodi | ❌ |
| create | ✅ | ❌ | ❌ | ✅ | ❌ | ❌ |
| update | ✅ | ❌ | ❌ | Own Prodi | ❌ | ❌ |
| verify | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |

### Seeding Roles

Run the seeder to create sample users for each role:

```bash
# Seed only roles
php artisan db:seed --class=RoleSeeder

# Full database seed (includes roles)
php artisan migrate:fresh --seed
```

### Checking Roles in Code

```php
// Using User model methods
$user->isAdmin();     // true if role is 'admin'
$user->isDekan();     // true if role is 'dekan'
$user->isGPM();       // true if role is 'GPM'
$user->isGKM();       // true if role is 'GKM'
$user->isKaprodi();   // true if role is 'kaprodi'
$user->isBPAP();      // true if role is 'BPAP'

// Check single role
$user->hasRole('admin');

// Check multiple roles (OR logic)
$user->hasRole(['admin', 'GPM', 'dekan']);

// Using role constants
use App\Models\User;
$user->hasRole([User::ROLE_ADMIN, User::ROLE_GPM]);
```

### Defined Gates

Gates are registered in `AppServiceProvider` for quick authorization checks:

```php
// Check if user can manage users
Gate::allows('manage-users');

// Check if user can manage renstra
Gate::allows('manage-renstra');

// Check if user can verify evaluations
Gate::allows('verify-evaluasi');

// In Blade
@can('manage-users')
    <a href="/users">Manage Users</a>
@endcan
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
