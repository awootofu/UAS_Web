# Role-Based Access Control (RBAC) Documentation

## Overview
This document outlines the complete role-based access control system for the Evaluation Web Application. The system manages permissions for six distinct user roles, each with specific capabilities and restrictions.

---

## User Roles

### 1. Admin
**Full System Access**
- ✅ Full access to all data across the system
- ✅ Manage users, roles, and master data
- ✅ View all Renstra, RTL, Evaluasi, and Bukti
- ✅ Create, Read, Update, Delete operations on all entities
- ✅ Override any permission restriction
- ✅ Access all prodi data regardless of assignment

**Implementation:**
- Role constant: `User::ROLE_ADMIN` ('admin')
- Helper method: `$user->isAdmin()`
- Has highest level permissions in all policies

---

### 2. BPAP (Badan Penjaminan Akademik dan Perencanaan)
**Master RENSTRA Data Management**

#### Permissions:
- ✅ **Create** master RENSTRA data:
  - Kebijakan (Policy)
  - Standar (Standards)
  - Indikator (Indicators)
  - Kegiatan (Activities)
  - Target
  - Kategori
- ✅ **Upload** via Excel OR manual form entry
- ✅ **View** all RENSTRA data
- ✅ **Update** RENSTRA data they created
- ✅ **Delete** RENSTRA data
- ❌ **Cannot** edit Evaluasi data
- ❌ **Cannot** create/edit RTL
- ❌ **Cannot** verify or approve submissions

**Implementation:**
- Role constant: `User::ROLE_BPAP` ('BPAP')
- Helper method: `$user->isBPAP()`
- Policy: `RenstraPolicy` - grants create, update, delete permissions
- Restricted from: `EvaluasiPolicy::create()`, `RTLPolicy::create()`

**Workflow:**
1. Login as BPAP
2. Navigate to RENSTRA management
3. Choose input method:
   - Upload Excel template with bulk data
   - Manual form entry for single records
4. Create Kebijakan → Standar → Indikator → Kegiatan → Target
5. System validates data structure
6. Save to database

---

### 3. GKM (Gugus Kendali Mutu)
**RTL (Rencana Tindak Lanjut) Management**

#### Permissions:
- ✅ **Create** RTL (Follow-up Action Plans)
- ✅ **Set** RTL deadline dates
- ✅ **Assign** PIC (Person in Charge) - select from user list
- ✅ **Upload** bukti (evidence) files for RTL
- ✅ **View** RTL status (pending, in_progress, completed, overdue)
- ✅ **Update** RTL before verification
- ✅ **Mark** RTL as completed
- ✅ View RTL within their prodi only
- ❌ **Cannot** verify or approve RTL (only GPM/Dekan)
- ❌ **Cannot** edit after GPM verification
- ❌ **Cannot** create RENSTRA or Evaluasi

**Implementation:**
- Role constant: `User::ROLE_GKM` ('GKM')
- Helper method: `$user->isGKM()`
- Policy: `RTLPolicy` - grants create, update, complete permissions
- Scoped to: `$user->prodi_id`

**Workflow:**
1. Login as GKM
2. View Evaluasi results that need follow-up
3. Create RTL with:
   - Description of action plan
   - Deadline date
   - Assigned PIC (user)
   - Upload supporting documents
4. Track RTL status
5. Update progress and upload evidence
6. Mark as completed when finished
7. Submit for GPM verification

**RTL Status Flow:**
```
pending → in_progress → completed (verified by GPM) → approved (by Dekan)
                     ↓
                  overdue (if deadline passed)
```

---

### 4. GPM (Gugus Penjaminan Mutu)
**RTL & Evaluasi Verification**

#### Permissions:
- ✅ **Monitor** all RTL and Evaluasi submissions
- ✅ **Verify** RTL from GKM
- ✅ **Verify** Evaluasi from Kaprodi
- ✅ **Approve** verified submissions
- ✅ **Reject** submissions with notes
- ✅ **Add** verification notes/comments
- ✅ **View** all data across prodi
- ✅ Track verification history
- ❌ **Cannot** create RTL or Evaluasi
- ❌ **Cannot** edit RENSTRA master data
- ❌ Final approval authority (that's Dekan)

**Implementation:**
- Role constant: `User::ROLE_GPM` ('GPM')
- Helper method: `$user->isGPM()`
- Policy: `RTLPolicy::verify()`, `EvaluasiPolicy::verify()`
- Method: `$user->canVerify()` returns true

**Workflow:**
1. Login as GPM
2. View pending submissions:
   - RTL from GKM (status: completed)
   - Evaluasi from Kaprodi (status: submitted)
3. Review submission details and evidence
4. Choose action:
   - **Verify**: Move to verified status, add notes
   - **Reject**: Return to creator with feedback
5. Verified items move to Dekan for final approval
6. Track verification metrics and timelines

**Verification Status Flow:**
```
RTL:      completed → verified (by GPM) → approved (by Dekan)
                   ↓
                rejected (back to GKM)

Evaluasi: submitted → verified (by GPM) → approved (by Dekan)
                   ↓
                rejected (back to Kaprodi)
```

---

### 5. Dekan (Dean)
**Final Approval & Monitoring Authority**

#### Permissions:
- ✅ **Monitor** all verified RTL and Evaluasi
- ✅ **Verify** what GPM has approved
- ✅ **Final approval** authority
- ✅ **Read-only** access to all data
- ✅ **View** comprehensive reports
- ✅ **Add** approval notes
- ✅ Dashboard with faculty-wide metrics
- ❌ **Cannot** create or edit submissions
- ❌ **Cannot** modify master data
- ❌ **Cannot** approve unverified items (must be verified by GPM first)

**Implementation:**
- Role constant: `User::ROLE_DEKAN` ('dekan')
- Helper method: `$user->isDekan()`
- Policy: `EvaluasiPolicy::approve()`, `RTLPolicy::verify()`
- Method: `$user->canApprove()` returns true

**Workflow:**
1. Login as Dekan
2. View GPM-verified submissions
3. Review:
   - Submission content
   - GPM verification notes
   - Supporting evidence
4. Final decision:
   - **Approve**: Complete the workflow
   - **Reject**: Return with notes
5. Monitor faculty-wide performance
6. Generate executive reports

**Approval Flow:**
```
verified (by GPM) → approved (by Dekan) → FINAL
                 ↓
              rejected (back to GPM for re-review)
```

---

### 6. Kaprodi (Program Study Head)
**Realization & Evaluation Input**

#### Permissions:
- ✅ **Input** REALISASI per semester (Ganjil / Genap)
- ✅ **Create** Evaluasi entries for their prodi
- ✅ **Input** achievement data:
  - Ketercapaian (Achievement percentage)
  - Realisasi (Actual value)
- ✅ **Input** Evaluasi Analisa Capaian:
  - Akar Masalah (Root cause analysis)
  - Faktor Pendukung (Supporting factors)
  - Faktor Penghambat (Hindering factors)
- ✅ **Upload** bukti evaluasi (evidence files)
- ✅ **Update** evaluasi in draft or rejected status
- ✅ **Submit** for GPM verification
- ✅ View evaluasi for their prodi only
- ❌ **Cannot** edit after submission
- ❌ **Cannot** verify or approve
- ❌ **Cannot** access other prodi data
- ❌ **Cannot** create RENSTRA or RTL

**Implementation:**
- Role constant: `User::ROLE_KAPRODI` ('kaprodi')
- Helper method: `$user->isKaprodi()`
- Policy: `EvaluasiPolicy` - grants create, update permissions
- Scoped to: `$user->prodi_id`

**Conditional Input Logic:**

#### ✅ If Indicator IS ACHIEVED (ketercapaian ≥ 100%)
**Required Fields:**
- ✅ Ketercapaian (Achievement percentage)
- ✅ Realisasi (Actual value)
- ✅ Faktor Pendukung (Supporting factors)
- ✅ Bukti (Evidence files)

**Optional/Not Required:**
- ⚪ Akar Masalah (can be empty)
- ⚪ Faktor Penghambat (can be empty)

#### ❌ If Indicator NOT ACHIEVED (ketercapaian < 100%)
**Required Fields:**
- ✅ Ketercapaian (Achievement percentage)
- ✅ Realisasi (Actual value)
- ✅ Akar Masalah (Root cause analysis) - **MANDATORY**
- ✅ Faktor Penghambat (Hindering factors) - **MANDATORY**
- ✅ Bukti (Evidence files)

**Optional:**
- ⚪ Faktor Pendukung (can be empty)

**Validation Rules:**
```php
// In EvaluasiRequest or Controller validation
'akar_masalah' => ['required_if:ketercapaian,<,100', 'nullable', 'string'],
'faktor_penghambat' => ['required_if:ketercapaian,<,100', 'nullable', 'string'],
'faktor_pendukung' => ['required_if:ketercapaian,>=,100', 'nullable', 'string'],
```

**Workflow:**
1. Login as Kaprodi
2. Select semester (Ganjil/Genap) and year
3. Choose RENSTRA indicator to evaluate
4. Input realization data
5. Calculate ketercapaian percentage
6. **System checks achievement:**
   - If ≥ 100%: Show fields for faktor_pendukung
   - If < 100%: Show fields for akar_masalah & faktor_penghambat
7. Upload supporting evidence files
8. Save as draft or submit for verification
9. Track verification status

**Semester Cycle:**
```
Semester Ganjil (Odd): August - December
Semester Genap (Even): February - June

Each semester requires separate evaluation input.
```

---

## Permission Matrix

| Action | Admin | BPAP | GKM | GPM | Dekan | Kaprodi |
|--------|-------|------|-----|-----|-------|---------|
| **RENSTRA (Master Data)** |
| View | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Create | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| Update | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| Delete | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| Upload Excel | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| **RTL (Rencana Tindak Lanjut)** |
| View All | ✅ | ❌ | Prodi | ✅ | ✅ | Prodi |
| Create | ✅ | ❌ | ✅ | ❌ | ❌ | ❌ |
| Update | ✅ | ❌ | ✅* | ❌ | ❌ | ❌ |
| Set Deadline | ✅ | ❌ | ✅ | ❌ | ❌ | ❌ |
| Assign PIC | ✅ | ❌ | ✅ | ❌ | ❌ | ❌ |
| Upload Bukti | ✅ | ❌ | ✅ | ❌ | ❌ | ❌ |
| Mark Complete | ✅ | ❌ | ✅ | ❌ | ❌ | ❌ |
| Verify | ✅ | ❌ | ❌ | ✅ | ✅ | ❌ |
| Approve | ✅ | ❌ | ❌ | ❌ | ✅ | ❌ |
| Delete | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **EVALUASI** |
| View All | ✅ | ❌ | Prodi | ✅ | ✅ | Prodi |
| Create | ✅ | ❌ | ❌ | ❌ | ❌ | ✅ |
| Update | ✅ | ❌ | ❌ | ❌ | ❌ | ✅* |
| Input Realisasi | ✅ | ❌ | ❌ | ❌ | ❌ | ✅ |
| Input Analisa | ✅ | ❌ | ❌ | ❌ | ❌ | ✅ |
| Upload Bukti | ✅ | ❌ | ❌ | ❌ | ❌ | ✅ |
| Submit | ✅ | ❌ | ❌ | ❌ | ❌ | ✅ |
| Verify | ✅ | ❌ | ❌ | ✅ | ❌ | ❌ |
| Approve | ✅ | ❌ | ❌ | ❌ | ✅ | ❌ |
| Reject | ✅ | ❌ | ❌ | ✅ | ✅ | ❌ |
| Delete | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **USER MANAGEMENT** |
| View Users | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| Create Users | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| Update Users | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| Delete Users | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| Assign Roles | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |

*Note: Update only allowed in specific statuses (draft, rejected, pending, in_progress)

---

## Data Scope & Filtering

### Admin
- **Scope:** All data across all prodi
- **Filter:** None (unrestricted access)

### BPAP
- **Scope:** All RENSTRA master data
- **Filter:** Can only edit records they created

### GKM
- **Scope:** RTL within assigned prodi
- **Filter:** `where('prodi_id', $user->prodi_id)`

### GPM
- **Scope:** All RTL and Evaluasi across prodi
- **Filter:** None for monitoring, can verify all

### Dekan
- **Scope:** All data (read-only + approval rights)
- **Filter:** None, faculty-wide view

### Kaprodi
- **Scope:** Evaluasi within assigned prodi
- **Filter:** `where('prodi_id', $user->prodi_id)`

---

## Status Workflows

### Evaluasi Status Flow
```
┌─────────┐
│  DRAFT  │ (Kaprodi creates/edits)
└────┬────┘
     │ Kaprodi submits
     ↓
┌──────────┐
│SUBMITTED │ (Waiting for GPM)
└────┬─────┘
     │ GPM verifies
     ↓
┌──────────┐
│ VERIFIED │ (Waiting for Dekan)
└────┬─────┘
     │ Dekan approves
     ↓
┌──────────┐
│ APPROVED │ (Final state)
└──────────┘

Alternative paths:
SUBMITTED → REJECTED (by GPM) → back to DRAFT
VERIFIED → REJECTED (by Dekan) → back to SUBMITTED
```

### RTL Status Flow
```
┌─────────┐
│ PENDING │ (GKM creates)
└────┬────┘
     │ GKM starts work
     ↓
┌─────────────┐
│ IN_PROGRESS │ (GKM working)
└────┬────────┘
     │ GKM marks complete
     ↓
┌───────────┐
│ COMPLETED │ (Waiting for GPM verification)
└────┬──────┘
     │ GPM/Dekan verifies
     ↓
┌──────────┐
│ APPROVED │ (Final state)
└──────────┘

Alternative paths:
Any status + deadline passed → OVERDUE
Any status → CANCELLED (by Admin)
```

---

## Implementation Details

### User Model Methods
```php
// Role checking
$user->isAdmin()      // Returns true if role is 'admin'
$user->isBPAP()       // Returns true if role is 'BPAP'
$user->isGKM()        // Returns true if role is 'GKM'
$user->isGPM()        // Returns true if role is 'GPM'
$user->isDekan()      // Returns true if role is 'dekan'
$user->isKaprodi()    // Returns true if role is 'kaprodi'

// Multi-role checking
$user->hasRole('admin')                    // Single role
$user->hasRole(['GPM', 'dekan'])          // Any of these roles

// Permission checks
$user->canVerify()    // GPM, Dekan, Admin can verify
$user->canApprove()   // Dekan, Admin can approve
```

### Policy Usage in Controllers
```php
// Authorization checks
$this->authorize('create', Renstra::class);
$this->authorize('update', $evaluasi);
$this->authorize('verify', $rtl);
$this->authorize('approve', $evaluasi);

// In views with Blade directives
@can('create', App\Models\Renstra::class)
    <a href="{{ route('renstra.create') }}">Create New</a>
@endcan

@can('update', $evaluasi)
    <a href="{{ route('evaluasi.edit', $evaluasi) }}">Edit</a>
@endcan
```

### Middleware Usage
```php
// In routes/web.php
Route::middleware(['auth'])->group(function () {
    // Admin only routes
    Route::middleware(['role:admin'])->group(function () {
        Route::resource('users', UserController::class);
    });
    
    // BPAP routes
    Route::middleware(['role:admin,BPAP'])->group(function () {
        Route::post('renstra/import', [RenstraController::class, 'import']);
    });
    
    // Kaprodi routes
    Route::middleware(['role:admin,kaprodi'])->group(function () {
        Route::resource('evaluasi', EvaluasiController::class);
    });
});
```

---

## Validation Rules

### Kaprodi Evaluasi Input
```php
// In EvaluasiRequest or Controller
public function rules()
{
    $rules = [
        'renstra_id' => 'required|exists:renstra,id',
        'semester' => 'required|in:ganjil,genap',
        'tahun_evaluasi' => 'required|integer|min:2020',
        'realisasi' => 'required|numeric|min:0',
        'ketercapaian' => 'required|numeric|min:0|max:200',
        'bukti_id' => 'nullable|exists:evaluasi_bukti,id',
    ];

    // Conditional validation based on achievement
    if ($this->ketercapaian < 100) {
        // NOT ACHIEVED - require root cause analysis
        $rules['akar_masalah'] = 'required|string|min:10';
        $rules['faktor_penghambat'] = 'required|string|min:10';
        $rules['faktor_pendukung'] = 'nullable|string';
    } else {
        // ACHIEVED - require supporting factors
        $rules['faktor_pendukung'] = 'required|string|min:10';
        $rules['akar_masalah'] = 'nullable|string';
        $rules['faktor_penghambat'] = 'nullable|string';
    }

    return $rules;
}

public function messages()
{
    return [
        'akar_masalah.required' => 'Akar masalah wajib diisi karena target belum tercapai',
        'faktor_penghambat.required' => 'Faktor penghambat wajib diisi karena target belum tercapai',
        'faktor_pendukung.required' => 'Faktor pendukung wajib diisi karena target sudah tercapai',
    ];
}
```

---

## Security Considerations

1. **Prodi Isolation**
   - GKM, Kaprodi can only access data from their assigned prodi
   - Enforced at query level: `->where('prodi_id', auth()->user()->prodi_id)`
   - Policy checks verify prodi_id matches

2. **Status-Based Permissions**
   - Users can only edit records in specific statuses
   - Verified/Approved records are locked
   - Enforced in `canEdit()` methods

3. **Audit Logging**
   - All create, update, delete actions are logged
   - Verification and approval actions tracked
   - Status changes recorded with user_id and timestamp

4. **File Upload Security**
   - Validate file types (PDF, images, documents)
   - Limit file sizes
   - Store in protected storage directory
   - Verify user owns the record before upload

---

## Testing Checklist

### Admin Tests
- [ ] Can view all prodi data
- [ ] Can create/edit/delete all records
- [ ] Can approve any submission
- [ ] Can manage users and roles

### BPAP Tests
- [ ] Can create RENSTRA via form
- [ ] Can import RENSTRA via Excel
- [ ] Cannot edit Evaluasi
- [ ] Cannot create RTL

### GKM Tests
- [ ] Can create RTL for own prodi
- [ ] Can set deadline and assign PIC
- [ ] Can upload bukti RTL
- [ ] Cannot access other prodi RTL
- [ ] Cannot verify RTL

### GPM Tests
- [ ] Can view all submitted Evaluasi and completed RTL
- [ ] Can verify submissions
- [ ] Can reject with notes
- [ ] Cannot create Evaluasi or RTL

### Dekan Tests
- [ ] Can view all verified submissions
- [ ] Can approve verified items
- [ ] Cannot edit any records
- [ ] Has read-only access to all data

### Kaprodi Tests
- [ ] Can create Evaluasi for own prodi
- [ ] Can input realisasi per semester
- [ ] Required fields change based on achievement
- [ ] Cannot edit after submission
- [ ] Cannot access other prodi Evaluasi

---

## Common Scenarios

### Scenario 1: New Semester Evaluation
1. **BPAP** ensures RENSTRA targets are set for the semester
2. **Kaprodi** logs in and selects semester (Ganjil/Genap) + year
3. **Kaprodi** inputs realization data for each indicator
4. System calculates ketercapaian percentage
5. Based on achievement:
   - If achieved: Input faktor_pendukung
   - If not achieved: Input akar_masalah + faktor_penghambat
6. **Kaprodi** uploads supporting evidence
7. **Kaprodi** submits for verification
8. **GPM** reviews and verifies
9. **Dekan** provides final approval

### Scenario 2: RTL Creation from Unachieved Target
1. **Kaprodi** submits Evaluasi with ketercapaian < 100%
2. **GPM** verifies and identifies need for follow-up
3. **GKM** creates RTL:
   - Links to the Evaluasi
   - Describes action plan
   - Sets realistic deadline
   - Assigns PIC (responsible person)
4. **GKM** monitors progress
5. **GKM** uploads evidence as work progresses
6. **GKM** marks as completed when finished
7. **GPM** verifies completion
8. **Dekan** approves final RTL

### Scenario 3: Excel Import of RENSTRA Data
1. **BPAP** downloads Excel template
2. Fills in:
   - Kebijakan (policies)
   - Standar (standards)
   - Indikator (indicators)
   - Target values
3. Uploads Excel file
4. System validates format and data
5. Displays preview of records to be imported
6. **BPAP** confirms import
7. System creates all RENSTRA records
8. Shows import summary (success/errors)

---

## Database Schema Notes

### Users Table
- `role`: enum('admin', 'dekan', 'GPM', 'GKM', 'kaprodi', 'BPAP')
- `prodi_id`: foreign key to prodi table (nullable for admin/BPAP/GPM/Dekan)
- `jabatan_id`: foreign key to jabatan table

### Evaluasi Table
- `status`: enum('draft', 'submitted', 'verified', 'rejected', 'approved')
- `semester`: enum('ganjil', 'genap')
- `created_by`: foreign key to users (Kaprodi)
- `verified_by`: foreign key to users (GPM)
- `approved_by`: foreign key to users (Dekan)
- `prodi_id`: foreign key (data isolation)

### RTL Table
- `status`: enum('pending', 'in_progress', 'completed', 'overdue', 'cancelled')
- `users_id`: foreign key to users (GKM creator)
- `pic_rtl`: string (assigned person name)
- `verified_by`: foreign key to users (GPM/Dekan)
- `prodi_id`: foreign key (data isolation)

---

## API Endpoints (if applicable)

### RENSTRA Endpoints
- `GET /api/renstra` - List all (BPAP, Admin)
- `POST /api/renstra` - Create (BPAP, Admin)
- `PUT /api/renstra/{id}` - Update (BPAP, Admin)
- `DELETE /api/renstra/{id}` - Delete (BPAP, Admin)
- `POST /api/renstra/import` - Excel import (BPAP, Admin)

### Evaluasi Endpoints
- `GET /api/evaluasi` - List (filtered by prodi for Kaprodi)
- `POST /api/evaluasi` - Create (Kaprodi, Admin)
- `PUT /api/evaluasi/{id}` - Update (Kaprodi, Admin)
- `POST /api/evaluasi/{id}/submit` - Submit (Kaprodi)
- `POST /api/evaluasi/{id}/verify` - Verify (GPM, Admin)
- `POST /api/evaluasi/{id}/approve` - Approve (Dekan, Admin)
- `POST /api/evaluasi/{id}/reject` - Reject (GPM, Dekan, Admin)

### RTL Endpoints
- `GET /api/rtl` - List (filtered by prodi for GKM)
- `POST /api/rtl` - Create (GKM, Admin)
- `PUT /api/rtl/{id}` - Update (GKM, Admin)
- `POST /api/rtl/{id}/complete` - Mark complete (GKM)
- `POST /api/rtl/{id}/verify` - Verify (GPM, Dekan, Admin)

---

## File Storage Structure

```
storage/app/
├── public/
│   ├── bukti_evaluasi/
│   │   ├── {prodi_id}/
│   │   │   ├── {year}/
│   │   │   │   ├── {semester}/
│   │   │   │   │   └── {filename}
│   ├── bukti_rtl/
│   │   ├── {prodi_id}/
│   │   │   ├── {year}/
│   │   │   │   └── {filename}
│   └── imports/
│       └── renstra/
│           └── {timestamp}_{filename}
```

---

## Environment Configuration

```env
# Application
APP_NAME="Evaluation Web System"

# Role-based features
ENABLE_ROLE_MIDDLEWARE=true
ENABLE_AUDIT_LOG=true

# File uploads
MAX_UPLOAD_SIZE=10240  # 10MB in KB
ALLOWED_EXTENSIONS=pdf,doc,docx,xls,xlsx,jpg,jpeg,png

# Excel import
ENABLE_EXCEL_IMPORT=true
MAX_IMPORT_ROWS=1000
```

---

## Maintenance & Updates

### Adding New Role
1. Add role constant to `User` model
2. Update `ROLES` array
3. Update database migration enum
4. Create helper method (e.g., `isNewRole()`)
5. Update all policies
6. Add middleware rules
7. Update documentation
8. Run tests

### Modifying Permissions
1. Update relevant Policy file
2. Update permission matrix in documentation
3. Update tests
4. Communicate changes to users
5. Deploy with changelog

---

## Support & Troubleshooting

### Common Issues

**Issue**: Kaprodi cannot submit Evaluasi
- Check status is 'draft' or 'rejected'
- Verify all required fields based on achievement
- Check prodi_id matches user's prodi

**Issue**: GPM cannot verify submission
- Verify status is 'submitted' for Evaluasi
- Verify status is 'completed' for RTL
- Check user has GPM role

**Issue**: File upload fails
- Check file size within limit
- Verify file extension is allowed
- Ensure storage directory is writable
- Check disk space

**Issue**: Access denied error
- Verify user has correct role
- Check prodi_id matching for scoped roles
- Verify policy authorization
- Check middleware configuration

---

## Conclusion

This RBAC system provides comprehensive access control for a complex multi-role evaluation system. Each role has clearly defined responsibilities and permissions, ensuring data security and workflow integrity. The conditional logic for Kaprodi evaluations ensures quality control based on achievement levels.

For questions or modifications, consult the development team or refer to the Laravel policy documentation.

---

**Document Version:** 1.0  
**Last Updated:** December 14, 2025  
**Maintained By:** Development Team
