# RBAC Implementation Guide

## Quick Start Guide for Role-Based Access Control

This guide will help you implement and use the RBAC system that has been set up for your Laravel application.

---

## ✅ What's Already Implemented

### 1. **User Model with Role Methods**
Location: `app/Models/User.php`

The User model already includes:
- Role constants (ADMIN, BPAP, GKM, GPM, Dekan, Kaprodi)
- Helper methods: `isAdmin()`, `isBPAP()`, `isGKM()`, `isGPM()`, `isDekan()`, `isKaprodi()`
- `hasRole()` method for checking multiple roles
- `canVerify()` and `canApprove()` permission methods

### 2. **Policy Classes**
Location: `app/Policies/`

Three policy classes control access:
- `RenstraPolicy.php` - Controls RENSTRA master data access (BPAP, Admin)
- `EvaluasiPolicy.php` - Controls Evaluasi access (Kaprodi, GPM, Dekan, Admin)
- `RTLPolicy.php` - Controls RTL access (GKM, GPM, Dekan, Admin)

### 3. **Validation Request Classes** ✨ NEW
Location: `app/Http/Requests/`

- `StoreEvaluasiRequest.php` - Validates new evaluasi with conditional logic
- `UpdateEvaluasiRequest.php` - Validates evaluasi updates with conditional logic

**Conditional Logic:**
- If `ketercapaian >= 100%` → `faktor_pendukung` is REQUIRED
- If `ketercapaian < 100%` → `akar_masalah` and `faktor_penghambat` are REQUIRED

### 4. **Role Middleware**
Location: `app/Http/Middleware/RoleMiddleware.php`

Middleware for protecting routes based on roles.

### 5. **Updated Controller**
Location: `app/Http/Controllers/EvaluasiController.php`

Now uses the new request classes for automatic conditional validation.

---

## 📋 Implementation Checklist

### Step 1: Register Middleware (If Not Already Done)

**File:** `bootstrap/app.php` (Laravel 11) or `app/Http/Kernel.php` (Laravel 10)

```php
// Laravel 11 (bootstrap/app.php)
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'role' => \App\Http\Middleware\RoleMiddleware::class,
    ]);
})

// OR Laravel 10 (app/Http/Kernel.php)
protected $middlewareAliases = [
    // ... other middleware
    'role' => \App\Http\Middleware\RoleMiddleware::class,
];
```

### Step 2: Protect Routes with Middleware

**File:** `routes/web.php`

```php
use App\Http\Controllers\EvaluasiController;
use App\Http\Controllers\RenstraController;
use App\Http\Controllers\RTLController;

Route::middleware(['auth'])->group(function () {
    
    // RENSTRA Routes - BPAP and Admin only
    Route::middleware(['role:admin,BPAP'])->group(function () {
        Route::get('renstra/create', [RenstraController::class, 'create'])->name('renstra.create');
        Route::post('renstra', [RenstraController::class, 'store'])->name('renstra.store');
        Route::get('renstra/{renstra}/edit', [RenstraController::class, 'edit'])->name('renstra.edit');
        Route::put('renstra/{renstra}', [RenstraController::class, 'update'])->name('renstra.update');
        Route::delete('renstra/{renstra}', [RenstraController::class, 'destroy'])->name('renstra.destroy');
        Route::post('renstra/import', [RenstraController::class, 'import'])->name('renstra.import');
    });
    
    // RENSTRA View - All authenticated users
    Route::get('renstra', [RenstraController::class, 'index'])->name('renstra.index');
    Route::get('renstra/{renstra}', [RenstraController::class, 'show'])->name('renstra.show');
    
    // EVALUASI Routes - Kaprodi and Admin only
    Route::middleware(['role:admin,kaprodi'])->group(function () {
        Route::get('evaluasi/create', [EvaluasiController::class, 'create'])->name('evaluasi.create');
        Route::post('evaluasi', [EvaluasiController::class, 'store'])->name('evaluasi.store');
        Route::get('evaluasi/{evaluasi}/edit', [EvaluasiController::class, 'edit'])->name('evaluasi.edit');
        Route::put('evaluasi/{evaluasi}', [EvaluasiController::class, 'update'])->name('evaluasi.update');
        Route::post('evaluasi/{evaluasi}/submit', [EvaluasiController::class, 'submit'])->name('evaluasi.submit');
    });
    
    // EVALUASI Verification - GPM and Admin
    Route::middleware(['role:admin,GPM'])->group(function () {
        Route::post('evaluasi/{evaluasi}/verify', [EvaluasiController::class, 'verify'])->name('evaluasi.verify');
        Route::post('evaluasi/{evaluasi}/reject', [EvaluasiController::class, 'reject'])->name('evaluasi.reject');
    });
    
    // EVALUASI Approval - Dekan and Admin
    Route::middleware(['role:admin,dekan'])->group(function () {
        Route::post('evaluasi/{evaluasi}/approve', [EvaluasiController::class, 'approve'])->name('evaluasi.approve');
    });
    
    // EVALUASI View - All authenticated users (with prodi filtering)
    Route::get('evaluasi', [EvaluasiController::class, 'index'])->name('evaluasi.index');
    Route::get('evaluasi/{evaluasi}', [EvaluasiController::class, 'show'])->name('evaluasi.show');
    
    // RTL Routes - GKM and Admin only
    Route::middleware(['role:admin,GKM'])->group(function () {
        Route::get('rtl/create', [RTLController::class, 'create'])->name('rtl.create');
        Route::post('rtl', [RTLController::class, 'store'])->name('rtl.store');
        Route::get('rtl/{rtl}/edit', [RTLController::class, 'edit'])->name('rtl.edit');
        Route::put('rtl/{rtl}', [RTLController::class, 'update'])->name('rtl.update');
        Route::post('rtl/{rtl}/complete', [RTLController::class, 'complete'])->name('rtl.complete');
    });
    
    // RTL Verification - GPM, Dekan, and Admin
    Route::middleware(['role:admin,GPM,dekan'])->group(function () {
        Route::post('rtl/{rtl}/verify', [RTLController::class, 'verify'])->name('rtl.verify');
    });
    
    // RTL View - All authenticated users (with prodi filtering)
    Route::get('rtl', [RTLController::class, 'index'])->name('rtl.index');
    Route::get('rtl/{rtl}', [RTLController::class, 'show'])->name('rtl.show');
    
    // Admin-only routes
    Route::middleware(['role:admin'])->group(function () {
        Route::resource('users', UserController::class);
        Route::resource('prodi', ProdiController::class);
        Route::resource('jabatan', JabatanController::class);
    });
});
```

### Step 3: Update Evaluasi Views

**Replace form content in:**
- `resources/views/evaluasi/create.blade.php`
- `resources/views/evaluasi/edit.blade.php`

**Include the conditional fields snippet:**

```blade
@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-6">{{ isset($evaluasi) ? 'Edit' : 'Tambah' }} Evaluasi</h1>
    
    <form action="{{ isset($evaluasi) ? route('evaluasi.update', $evaluasi) : route('evaluasi.store') }}" 
          method="POST" 
          enctype="multipart/form-data">
        @csrf
        @if(isset($evaluasi))
            @method('PUT')
        @endif
        
        <div class="bg-white shadow-md rounded-lg p-6">
            
            {{-- Basic Fields (RENSTRA, Prodi, Semester, Tahun) --}}
            {{-- Add your existing basic fields here --}}
            
            {{-- Include the conditional fields snippet --}}
            @include('evaluasi._conditional_fields_snippet')
            
            {{-- Bukti Upload --}}
            <div class="mb-4">
                <label for="bukti_file" class="block text-sm font-medium text-gray-700">
                    Upload Bukti
                </label>
                <input type="file" 
                       name="bukti_file" 
                       id="bukti_file"
                       accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.zip"
                       class="mt-1 block w-full">
                @error('bukti_file')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            
            {{-- Action Buttons --}}
            <div class="flex justify-end space-x-3">
                <a href="{{ route('evaluasi.index') }}" 
                   class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                    Batal
                </a>
                <button type="submit" 
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    {{ isset($evaluasi) ? 'Update' : 'Simpan' }}
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
```

### Step 4: Add Role-Based UI Elements

**In Blade Templates:**

```blade
{{-- Show create button only for authorized roles --}}
@can('create', App\Models\Renstra::class)
    <a href="{{ route('renstra.create') }}" class="btn btn-primary">
        Tambah RENSTRA
    </a>
@endcan

{{-- Show edit button only if user can update --}}
@can('update', $evaluasi)
    <a href="{{ route('evaluasi.edit', $evaluasi) }}" class="btn btn-warning">
        Edit
    </a>
@endcan

{{-- Show verify button for GPM --}}
@can('verify', $evaluasi)
    <form action="{{ route('evaluasi.verify', $evaluasi) }}" method="POST">
        @csrf
        <button type="submit" class="btn btn-success">
            Verifikasi
        </button>
    </form>
@endcan

{{-- Show approve button for Dekan --}}
@can('approve', $evaluasi)
    <form action="{{ route('evaluasi.approve', $evaluasi) }}" method="POST">
        @csrf
        <button type="submit" class="btn btn-success">
            Setujui
        </button>
    </form>
@endcan

{{-- Role-specific navigation --}}
@if(auth()->user()->isBPAP())
    <li><a href="{{ route('renstra.index') }}">Kelola RENSTRA</a></li>
@endif

@if(auth()->user()->isKaprodi())
    <li><a href="{{ route('evaluasi.index') }}">Kelola Evaluasi</a></li>
@endif

@if(auth()->user()->isGKM())
    <li><a href="{{ route('rtl.index') }}">Kelola RTL</a></li>
@endif

@if(auth()->user()->isGPM())
    <li><a href="{{ route('evaluasi.index') }}?status=submitted">Verifikasi Evaluasi</a></li>
    <li><a href="{{ route('rtl.index') }}?status=completed">Verifikasi RTL</a></li>
@endif

@if(auth()->user()->isDekan())
    <li><a href="{{ route('evaluasi.index') }}?status=verified">Approval Evaluasi</a></li>
    <li><a href="{{ route('rtl.index') }}?status=verified">Approval RTL</a></li>
@endif

@if(auth()->user()->isAdmin())
    <li><a href="{{ route('users.index') }}">Kelola User</a></li>
@endif
```

---

## 🧪 Testing the Implementation

### Test 1: Kaprodi Creating Evaluasi

1. Login as Kaprodi
2. Navigate to Evaluasi → Create
3. Fill in realisasi value
4. **If ketercapaian < 100%:**
   - Form should show "Akar Masalah" and "Faktor Penghambat" as required
   - Faktor Pendukung should be hidden/optional
5. **If ketercapaian >= 100%:**
   - Form should show "Faktor Pendukung" as required
   - Akar Masalah and Faktor Penghambat should be optional
6. Submit form and verify validation works

### Test 2: Role-Based Access

**As BPAP:**
```
✓ Can access /renstra/create
✓ Can access /renstra/{id}/edit
✗ Should NOT access /evaluasi/create
✗ Should NOT access /rtl/create
```

**As GKM:**
```
✓ Can access /rtl/create
✓ Can access /rtl/{id}/edit (for own prodi)
✗ Should NOT access /renstra/create
✗ Should NOT access /evaluasi/create
✗ Should NOT access /rtl/{id}/edit (other prodi)
```

**As GPM:**
```
✓ Can verify submitted Evaluasi
✓ Can verify completed RTL
✓ Can view all prodi data
✗ Should NOT create Evaluasi or RTL
```

**As Dekan:**
```
✓ Can approve verified Evaluasi
✓ Can approve verified RTL
✓ Can view all data
✗ Should NOT create or edit data
```

**As Kaprodi:**
```
✓ Can create Evaluasi for own prodi
✓ Can edit Evaluasi (if status = draft/rejected)
✓ Can view own prodi Evaluasi
✗ Should NOT access other prodi data
✗ Should NOT create RTL or RENSTRA
```

### Test 3: Validation Logic

**Test Case: Target NOT Achieved**
```php
POST /evaluasi
{
    "ketercapaian": 75,
    "akar_masalah": null,  // Should FAIL validation
    "faktor_penghambat": null,  // Should FAIL validation
    "faktor_pendukung": "Some text"  // Optional
}

Expected: Validation error - "Akar masalah wajib diisi karena target belum tercapai"
```

**Test Case: Target Achieved**
```php
POST /evaluasi
{
    "ketercapaian": 120,
    "faktor_pendukung": null,  // Should FAIL validation
    "akar_masalah": "Some text",  // Optional
    "faktor_penghambat": "Some text"  // Optional
}

Expected: Validation error - "Faktor pendukung wajib diisi karena target sudah tercapai"
```

---

## 🐛 Troubleshooting

### Issue: 403 Forbidden Error

**Symptom:** User gets "Unauthorized" when accessing a page

**Solutions:**
1. Check user's role in database: `SELECT role FROM users WHERE id = ?`
2. Verify middleware is applied to route
3. Check policy authorization in controller
4. Ensure user is active: `is_active = 1`

**Debug Code:**
```php
// In your controller or view
dd([
    'user_role' => auth()->user()->role,
    'is_admin' => auth()->user()->isAdmin(),
    'is_kaprodi' => auth()->user()->isKaprodi(),
    'prodi_id' => auth()->user()->prodi_id,
]);
```

### Issue: Validation Always Fails

**Symptom:** Form always shows validation errors even when fields are filled

**Solutions:**
1. Check if `ketercapaian` value is being submitted
2. Verify JavaScript is calculating ketercapaian correctly
3. Check browser console for JavaScript errors
4. Ensure field names match validation rules

**Debug Code:**
```php
// In StoreEvaluasiRequest::rules()
dd([
    'ketercapaian' => $this->input('ketercapaian'),
    'all_inputs' => $this->all(),
]);
```

### Issue: Wrong Fields Showing/Required

**Symptom:** Form shows wrong conditional fields

**Solutions:**
1. Check JavaScript `toggleConditionalFields()` function
2. Verify `ketercapaian` input has `oninput` event
3. Check if DOMContentLoaded event fires
4. Ensure no JavaScript errors in console

**Debug Code:**
```javascript
// In browser console
console.log('Ketercapaian:', document.getElementById('ketercapaian').value);
console.log('Achieved section:', document.getElementById('achieved-section').classList);
console.log('Not achieved section:', document.getElementById('not-achieved-section').classList);
```

---

## 📚 Additional Resources

### Policy Documentation
- [Laravel Authorization](https://laravel.com/docs/11.x/authorization)
- [Writing Policies](https://laravel.com/docs/11.x/authorization#writing-policies)

### Middleware Documentation
- [Laravel Middleware](https://laravel.com/docs/11.x/middleware)
- [Route Middleware](https://laravel.com/docs/11.x/middleware#assigning-middleware-to-routes)

### Form Request Validation
- [Form Request Validation](https://laravel.com/docs/11.x/validation#form-request-validation)
- [Conditional Validation](https://laravel.com/docs/11.x/validation#conditionally-adding-rules)

---

## 🔒 Security Best Practices

1. **Always use policies:** Don't rely solely on middleware
2. **Validate prodi_id:** Ensure users can't access other prodi data
3. **Check status before actions:** Verify status allows the action
4. **Audit all changes:** Use AuditLog for tracking
5. **Sanitize file uploads:** Validate file types and sizes
6. **Use HTTPS:** Ensure secure connections in production
7. **Rate limiting:** Add rate limiting to prevent abuse

---

## 📝 Quick Reference

### User Role Check Methods
```php
auth()->user()->isAdmin()      // Admin
auth()->user()->isBPAP()       // BPAP
auth()->user()->isGKM()        // GKM
auth()->user()->isGPM()        // GPM
auth()->user()->isDekan()      // Dekan
auth()->user()->isKaprodi()    // Kaprodi
auth()->user()->hasRole(['GPM', 'dekan'])  // Multiple roles
```

### Policy Check Methods
```php
// In controllers
$this->authorize('create', Renstra::class);
$this->authorize('update', $evaluasi);
$this->authorize('verify', $rtl);

// In views
@can('create', App\Models\Renstra::class)
@can('update', $evaluasi)
@can('verify', $rtl)
```

### Validation Rules
```php
// Required if condition
'akar_masalah' => 'required_if:ketercapaian,<,100'

// Required if field equals value
'faktor_pendukung' => 'required_if:is_achieved,true'

// Required unless
'field' => 'required_unless:other_field,value'
```

---

## ✅ Final Checklist

Before deploying to production:

- [ ] All middleware registered in bootstrap/app.php or Kernel.php
- [ ] Routes protected with appropriate middleware
- [ ] Policies registered in AuthServiceProvider
- [ ] Form requests created and used in controllers
- [ ] Views updated with conditional fields
- [ ] Blade directives added for role-based UI
- [ ] JavaScript for conditional display working
- [ ] All role-based tests passing
- [ ] Validation logic tested for all scenarios
- [ ] Error messages in Bahasa Indonesia
- [ ] Audit logging implemented
- [ ] File upload security verified
- [ ] Documentation updated
- [ ] Deployment guide prepared

---

**For support or questions, refer to the main `RBAC_DOCUMENTATION.md` file.**

**Last Updated:** December 14, 2025
