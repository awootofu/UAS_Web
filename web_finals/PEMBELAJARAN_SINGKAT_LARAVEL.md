# 📚 Pembelajaran Laravel - Konsep Lengkap dengan Contoh Nyata

**Aplikasi:** Sistem Evaluasi Renstra  
**Framework:** Laravel 11  
**Bahasa:** Indonesia - Mudah Dipahami  

---

## 1️⃣ ROUTING (Jalur Aplikasi)

### **Apa itu Routing?**
Routing adalah **peta jalan aplikasi web** yang menentukan:
- URL apa yang bisa diakses user
- Controller & method mana yang dipanggil
- Siapa saja yang boleh akses (middleware)

### **Contoh Nyata dari Aplikasi:**

**File: `routes/web.php` (Baris 16-18)**
```php
// Route Dashboard - Halaman utama setelah login
Route::get('/', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified', 'role'])
    ->name('dashboard');
```
**Penjelasan:**
- `Route::get('/')` → URL: http://localhost/
- `[DashboardController::class, 'index']` → Panggil method `index()` di `DashboardController`
- `->middleware(['auth', 'verified', 'role'])` → Cek: sudah login? email sudah verified? punya role?
- `->name('dashboard')` → Kasih nama route biar gampang dipanggil

---

**File: `routes/web.php` (Baris 28-31)**
```php
// Route untuk Admin & BPAP saja - buat/edit/hapus Renstra
Route::middleware(['auth', 'role:admin,BPAP'])->group(function () {
    Route::resource('renstra', RenstraController::class)->except(['index', 'show']);
});
```
**Penjelasan:**
- `->middleware(['auth', 'role:admin,BPAP'])` → Hanya user dengan role **admin** atau **BPAP** yang bisa akses
- `Route::resource()` → Otomatis bikin 7 route CRUD:
  - `renstra.create` → GET /renstra/create
  - `renstra.store` → POST /renstra
  - `renstra.edit` → GET /renstra/{id}/edit
  - `renstra.update` → PUT/PATCH /renstra/{id}
  - `renstra.destroy` → DELETE /renstra/{id}
- `->except(['index', 'show'])` → Kecuali index & show (karena dibuat terpisah dengan akses berbeda)

---

**File: `routes/web.php` (Baris 43-46)**
```php
// Route untuk lihat data Renstra - semua role bisa akses
Route::middleware(['auth', 'role:admin,dekan,GPM,GKM,kaprodi,BPAP'])->group(function () {
    Route::get('/renstra', [RenstraController::class, 'index'])->name('renstra.index');
    Route::get('/renstra/{renstra}', [RenstraController::class, 'show'])->name('renstra.show');
});
```
**Penjelasan:**
- Lebih banyak role yang bisa akses (lihat doang, ga bisa edit)
- `{renstra}` → Parameter dinamis (ID renstra), Laravel otomatis inject model

---

### **Jenis-jenis Route:**
```php
Route::get('/url', [Controller::class, 'method']);      // Tampilkan halaman/data
Route::post('/url', [Controller::class, 'method']);     // Kirim data (submit form)
Route::put('/url', [Controller::class, 'method']);      // Update data (full update)
Route::patch('/url', [Controller::class, 'method']);    // Update data (partial)
Route::delete('/url', [Controller::class, 'method']);   // Hapus data
```

---

### **Grouping Routes (Kelompokan Route):**
```php
// File: routes/web.php (Baris 49-51)
Route::middleware(['auth', 'role:admin,kaprodi'])->group(function () {
    Route::resource('evaluasi', EvaluasiController::class)->except(['index', 'show']);
});
```
**Keuntungan:** Semua route di dalam group punya middleware yang sama

---

## 2️⃣ CONTROLLER (Pengatur Logic Aplikasi)

### **Apa itu Controller?**
Controller adalah **otak aplikasi** yang:
- Menerima request dari user
- Memproses data (ambil dari database, validasi, dll)
- Mengembalikan response (view/halaman atau redirect)

### **7 Method Standar Resource Controller:**
1. **index()** → Tampilkan semua data (daftar)
2. **create()** → Tampilkan form untuk tambah data
3. **store()** → Proses & simpan data baru
4. **show($id)** → Tampilkan 1 data secara detail
5. **edit($id)** → Tampilkan form untuk edit data
6. **update($id)** → Proses & update data
7. **destroy($id)** → Hapus data

---

### **Contoh 1: Method INDEX - Tampilkan Semua Data**

**File: `app/Http/Controllers/EvaluasiController.php` (Baris 34-75)**
```php
public function index(Request $request): View
{
    $user = $request->user(); // Ambil data user yang login
    
    // Buat query dengan eager loading (load relasi sekaligus)
    $query = Evaluasi::with(['renstra', 'prodi', 'target', 'creator', 'bukti']);

    // Filter berdasarkan role user
    $accessibleProdiIds = $user->getAccessibleProdiIds();
    if (!$user->isAdmin()) {
        // Kalau bukan admin, hanya tampilkan data prodi yang bisa diakses
        $query->whereIn('prodi_id', $accessibleProdiIds);
    }

    // Filter berdasarkan status (jika ada di URL)
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    // Filter berdasarkan prodi (jika ada di URL)
    if ($request->filled('prodi')) {
        if (in_array($request->prodi, $accessibleProdiIds) || $user->isAdmin()) {
            $query->where('prodi_id', $request->prodi);
        }
    }

    // Ambil data dengan pagination (15 data per halaman)
    $evaluasis = $query->latest()->paginate(15);
    
    // Ambil data prodi untuk dropdown filter
    $prodis = Prodi::whereIn('id', $accessibleProdiIds)->orderBy('nama_prodi')->get();

    // Return view dengan data
    return view('evaluasi.index', compact('evaluasis', 'prodis'));
}
```

**Penjelasan Detail:**
- `$request->user()` → Ambil data user yang sedang login
- `Evaluasi::with([...])` → **Eager Loading** - load relasi sekaligus biar efisien (hindari N+1 query problem)
- `$query->whereIn()` → Filter data berdasarkan role user
- `$request->filled('status')` → Cek apakah parameter 'status' ada di URL
- `->latest()` → Urutkan dari data terbaru
- `->paginate(15)` → Bagi data jadi halaman-halaman (15 per halaman)
- `compact('evaluasis', 'prodis')` → Kirim variabel ke view

---

### **Contoh 2: Method CREATE - Tampilkan Form Tambah**

**File: `app/Http/Controllers/EvaluasiController.php` (Baris 81-99)**
```php
public function create(Request $request): View
{
    $user = $request->user();
    $accessibleProdiIds = $user->getAccessibleProdiIds();
    
    // Ambil data renstra aktif untuk dropdown
    $renstras = Renstra::active()
        ->where(function($q) use ($accessibleProdiIds) {
            $q->whereIn('prodi_id', $accessibleProdiIds)
              ->orWhereNull('prodi_id'); // Include renstra universitas
        })
        ->with(['kategori', 'kegiatan', 'indikatorRelation'])
        ->get();

    // Ambil data target untuk dropdown
    $targets = RenstraTarget::with('indikator')->get();
    
    // Ambil data prodi untuk dropdown
    $prodis = Prodi::whereIn('id', $accessibleProdiIds)->orderBy('nama_prodi')->get();

    return view('evaluasi.create', compact('renstras', 'targets', 'prodis'));
}
```

**Penjelasan:**
- Siapkan data yang dibutuhkan untuk form (dropdown renstra, target, prodi)
- `Renstra::active()` → Scope query untuk ambil renstra yang aktif saja
- `->orWhereNull('prodi_id')` → Termasuk renstra tingkat universitas (bukan per prodi)

---

### **Contoh 3: Method STORE - Simpan Data Baru**

**File: `app/Http/Controllers/EvaluasiController.php` (Baris 105-150)**
```php
public function store(StoreEvaluasiRequest $request): RedirectResponse
{
    $user = $request->user();
    
    // Data sudah otomatis divalidasi oleh StoreEvaluasiRequest
    $validated = $request->validated();

    // Handle upload file bukti
    $buktiId = null;
    if ($request->hasFile('bukti_file')) {
        $file = $request->file('bukti_file');
        $path = $file->store('evaluasi-bukti', 'public'); // Simpan ke storage/app/public/evaluasi-bukti
        
        // Simpan info file ke tabel evaluasi_bukti
        $bukti = EvaluasiBukti::create([
            'nama_file' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_type' => $file->getClientOriginalExtension(),
            'file_size' => $file->getSize(),
            'uploaded_by' => $user->id,
        ]);
        
        $buktiId = $bukti->id;
    }

    // Buat data evaluasi baru
    $evaluasi = Evaluasi::create([
        'renstra_id' => $validated['renstra_id'],
        'prodi_id' => $validated['prodi_id'],
        'target_id' => $validated['target_id'] ?? null,
        'bukti_id' => $buktiId,
        'created_by' => $user->id,
        'semester' => $validated['semester'],
        'tahun_evaluasi' => $validated['tahun_evaluasi'],
        'realisasi' => $validated['realisasi'],
        'ketercapaian' => $validated['ketercapaian'],
        'akar_masalah' => $validated['akar_masalah'] ?? null,
        'faktor_pendukung' => $validated['faktor_pendukung'] ?? null,
        'faktor_penghambat' => $validated['faktor_penghambat'] ?? null,
        'status' => 'draft',
    ]);

    // Catat ke audit log
    AuditLog::log('created', Evaluasi::class, $evaluasi->id, null, $evaluasi->toArray());

    // Redirect ke halaman index dengan pesan sukses
    return redirect()->route('evaluasi.index')
        ->with('success', 'Evaluasi berhasil dibuat.');
}
```

**Penjelasan:**
- `StoreEvaluasiRequest` → Request khusus yang sudah ada validasi rules
- `$request->hasFile('bukti_file')` → Cek apakah user upload file
- `$file->store('evaluasi-bukti', 'public')` → Simpan file ke storage
- `Evaluasi::create([...])` → **Mass Assignment** - insert data ke database sekaligus
- `AuditLog::log()` → Catat aktivitas untuk audit trail
- `->with('success', ...)` → Session flash message (muncul sekali saja)

---

### **Contoh 4: Method UPDATE - Update Data**

**File: `app/Http/Controllers/EvaluasiController.php` (Baris 188-224)**
```php
public function update(UpdateEvaluasiRequest $request, Evaluasi $evaluasi): RedirectResponse
{
    $validated = $request->validated();

    // Handle upload file baru (jika ada)
    if ($request->hasFile('bukti_file')) {
        $file = $request->file('bukti_file');
        $path = $file->store('evaluasi-bukti', 'public');
        
        // Hapus file lama jika ada
        if ($evaluasi->bukti) {
            Storage::disk('public')->delete($evaluasi->bukti->file_path);
            $evaluasi->bukti->delete();
        }
        
        // Simpan file baru
        $bukti = EvaluasiBukti::create([
            'nama_file' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_type' => $file->getClientOriginalExtension(),
            'file_size' => $file->getSize(),
            'uploaded_by' => auth()->id(),
        ]);
        
        $validated['bukti_id'] = $bukti->id;
    }

    // Simpan data lama untuk audit log
    $oldValues = $evaluasi->toArray();
    
    // Update data evaluasi
    $evaluasi->update($validated);

    // Catat perubahan ke audit log
    AuditLog::log('updated', Evaluasi::class, $evaluasi->id, $oldValues, $evaluasi->fresh()->toArray());

    return redirect()->route('evaluasi.index')
        ->with('success', 'Evaluasi berhasil diperbarui.');
}
```

**Penjelasan:**
- `Evaluasi $evaluasi` → **Route Model Binding** - Laravel otomatis ambil data dari database berdasarkan ID di URL
- `Storage::disk('public')->delete()` → Hapus file lama dari storage
- `$evaluasi->update($validated)` → Update data yang sudah ada
- `$evaluasi->fresh()` → Refresh data dari database (ambil data terbaru)

---

### **Contoh 5: Method SHOW - Tampilkan Detail 1 Data**

**File: `app/Http/Controllers/EvaluasiController.php` (Baris 160-168)**
```php
public function show(Evaluasi $evaluasi): View
{
    // Cek izin akses menggunakan Policy
    $this->authorize('view', $evaluasi);
    
    // Load semua relasi yang dibutuhkan
    $evaluasi->load([
        'renstra.kategori', 
        'renstra.kegiatan', 
        'prodi', 
        'target', 
        'creator', 
        'bukti', 
        'verifier', 
        'approver', 
        'rtls'
    ]);

    return view('evaluasi.show', compact('evaluasi'));
}
```

**Penjelasan:**
- `$this->authorize('view', $evaluasi)` → Cek izin menggunakan **Policy** (EvaluasiPolicy)
- `$evaluasi->load([...])` → Lazy eager loading (load relasi setelah data utama diambil)
- `'renstra.kategori'` → Nested relation (relasi di dalam relasi)

---

### **Method Custom (Bukan 7 Standar):**

**File: `app/Http/Controllers/EvaluasiController.php` (Baris 256-270)**
```php
public function approve(Evaluasi $evaluasi): RedirectResponse
{
    $this->authorize('approve', $evaluasi);
    
    if ($evaluasi->status !== 'verified') {
        return back()->with('error', 'Hanya evaluasi yang sudah verified yang bisa di-approve.');
    }

    $oldValues = $evaluasi->toArray();
    
    $evaluasi->update([
        'status' => 'approved',
        'approved_by' => auth()->id(),
        'approved_at' => now(),
        'approval_notes' => request('notes'),
    ]);

    AuditLog::log('approved', Evaluasi::class, $evaluasi->id, $oldValues, ['status' => 'approved']);

    return back()->with('success', 'Evaluasi berhasil di-approve.');
}
```

**Penjelasan:**
- Method custom untuk workflow approval
- `now()` → Helper Laravel untuk ambil waktu sekarang
- `auth()->id()` → Ambil ID user yang login
- `back()` → Redirect ke halaman sebelumnya

---


## 3️⃣ CRUD OPERATIONS (Create, Read, Update, Delete)

### **Apa itu CRUD?**
CRUD adalah **4 operasi dasar** untuk manipulasi data:
- **C**reate → Tambah data baru
- **R**ead → Baca/tampilkan data
- **U**pdate → Edit/perbarui data
- **D**elete → Hapus data

---

### **C - CREATE (Tambah Data)**

**Route:**
```php
// File: routes/web.php
Route::post('/evaluasi', [EvaluasiController::class, 'store'])
    ->name('evaluasi.store');
```

**Controller:**
```php
// File: app/Http/Controllers/EvaluasiController.php
public function store(StoreEvaluasiRequest $request): RedirectResponse
{
    $validated = $request->validated();
    
    $evaluasi = Evaluasi::create([
        'renstra_id' => $validated['renstra_id'],
        'prodi_id' => $validated['prodi_id'],
        'created_by' => auth()->id(),
        'semester' => $validated['semester'],
        'status' => 'draft',
    ]);
    
    return redirect()->route('evaluasi.index');
}
```

**Cara Kerja:**
1. Form di view kirim data via POST
2. Route tangkap dan arahkan ke `EvaluasiController@store`
3. Data divalidasi oleh `StoreEvaluasiRequest`
4. `Evaluasi::create()` insert data ke database
5. Redirect ke halaman index

---

### **R - READ (Baca Data)**

#### **R1. Baca Semua Data**
```php
// Ambil semua data evaluasi
$evaluasis = Evaluasi::all();

// Ambil dengan kondisi
$evaluasis = Evaluasi::where('status', 'approved')->get();

// Ambil dengan pagination
$evaluasis = Evaluasi::paginate(15);

// Ambil data terbaru
$evaluasis = Evaluasi::latest()->get();
```

#### **R2. Baca 1 Data Berdasarkan ID**
```php
// Cara 1: find() - return null jika tidak ada
$evaluasi = Evaluasi::find($id);

// Cara 2: findOrFail() - throw 404 jika tidak ada
$evaluasi = Evaluasi::findOrFail($id);

// Cara 3: where + first
$evaluasi = Evaluasi::where('id', $id)->first();
```

#### **R3. Baca dengan Relasi (Eager Loading)**
```php
// File: app/Http/Controllers/RenstraController.php (Baris 30)
$query = Renstra::with([
    'kategori',      // Load relasi kategori
    'kegiatan',      // Load relasi kegiatan  
    'indikatorRelation',  // Load relasi indikator
    'target',        // Load relasi target
    'prodi',         // Load relasi prodi
    'user'           // Load relasi user yang buat
]);
```

**Keuntungan Eager Loading:**
- Hindari **N+1 Query Problem** (query berulang)
- Lebih efisien (1 query untuk semua relasi)

**Contoh N+1 Problem (JANGAN!):**
```php
$evaluasis = Evaluasi::all(); // 1 query

foreach ($evaluasis as $evaluasi) {
    echo $evaluasi->prodi->nama_prodi; // N query (1 query per loop!)
}
// Total: 1 + N query → LAMBAT!
```

**Solusi dengan Eager Loading (BENAR!):**
```php
$evaluasis = Evaluasi::with('prodi')->get(); // 2 query saja

foreach ($evaluasis as $evaluasi) {
    echo $evaluasi->prodi->nama_prodi; // Tidak ada query tambahan
}
// Total: 2 query → CEPAT!
```

#### **R4. Baca dengan Filter**
```php
// File: app/Http/Controllers/RenstraController.php (Baris 43-67)

// Filter by kategori
if ($request->filled('kategori')) {
    $query->where('kategori_id', $request->kategori);
}

// Filter by prodi
if ($request->filled('prodi')) {
    $query->where('prodi_id', $request->prodi);
}

// Filter by status
if ($request->filled('status')) {
    $query->where('status', $request->status);
}

// Search (pencarian)
if ($request->filled('search')) {
    $search = $request->search;
    $query->where(function ($q) use ($search) {
        $q->where('kode_renstra', 'like', "%{$search}%")
          ->orWhere('indikator', 'like', "%{$search}%");
    });
}
```

---

### **U - UPDATE (Edit Data)**

**Route:**
```php
// File: routes/web.php
Route::put('/evaluasi/{evaluasi}', [EvaluasiController::class, 'update'])
    ->name('evaluasi.update');
```

**Controller:**
```php
// File: app/Http/Controllers/EvaluasiController.php (Baris 188-224)
public function update(UpdateEvaluasiRequest $request, Evaluasi $evaluasi): RedirectResponse
{
    $validated = $request->validated();
    
    // Cara 1: update() - mass assignment
    $evaluasi->update($validated);
    
    // Cara 2: ubah property satu-satu lalu save()
    // $evaluasi->semester = $validated['semester'];
    // $evaluasi->realisasi = $validated['realisasi'];
    // $evaluasi->save();
    
    return redirect()->route('evaluasi.index');
}
```

**Perbedaan `update()` vs `save()`:**
- `update()` → Terima array, langsung update semua field sekaligus
- `save()` → Harus ubah property dulu satu-satu, baru `save()`

---

### **D - DELETE (Hapus Data)**

**Route:**
```php
// File: routes/web.php
Route::delete('/evaluasi/{evaluasi}', [EvaluasiController::class, 'destroy'])
    ->name('evaluasi.destroy');
```

**Controller:**
```php
public function destroy(Evaluasi $evaluasi): RedirectResponse
{
    // Cek izin delete
    $this->authorize('delete', $evaluasi);
    
    // Hapus file bukti jika ada
    if ($evaluasi->bukti) {
        Storage::disk('public')->delete($evaluasi->bukti->file_path);
        $evaluasi->bukti->delete();
    }
    
    // Hapus data evaluasi (soft delete)
    $evaluasi->delete();
    
    return redirect()->route('evaluasi.index')
        ->with('success', 'Evaluasi berhasil dihapus.');
}
```

**Jenis Delete:**
1. **Soft Delete** (yang dipakai di aplikasi)
   - Data tidak benar-benar dihapus
   - Hanya diberi tanda `deleted_at`
   - Bisa di-restore lagi
   ```php
   $evaluasi->delete();  // Soft delete
   $evaluasi->restore(); // Restore data
   ```

2. **Hard Delete** (hapus permanen)
   ```php
   $evaluasi->forceDelete(); // Hapus permanen dari database
   ```

---


## 4️⃣ QUERY BUILDER vs ELOQUENT ORM

### **Apa Bedanya?**

| Aspek | Query Builder | Eloquent ORM |
|-------|--------------|--------------|
| **Gaya** | Mirip SQL, lebih manual | Object-Oriented |
| **Relasi** | Manual join | Otomatis via method |
| **Model** | Tidak perlu | Butuh Model class |
| **Return** | stdClass / Array | Model instance |
| **Fitur** | Dasar | Banyak (events, soft delete, dll) |

---

### **Query Builder (DB Facade)**

**Contoh:**
```php
use Illuminate\Support\Facades\DB;

// Ambil semua data
$evaluasis = DB::table('evaluasi')
    ->where('status', 'approved')
    ->get();

// Dengan join manual
$evaluasis = DB::table('evaluasi')
    ->join('prodi', 'evaluasi.prodi_id', '=', 'prodi.id')
    ->join('renstra', 'evaluasi.renstra_id', '=', 'renstra.id')
    ->select('evaluasi.*', 'prodi.nama_prodi', 'renstra.indikator')
    ->where('evaluasi.status', 'approved')
    ->get();

// Insert data
DB::table('evaluasi')->insert([
    'renstra_id' => 1,
    'prodi_id' => 2,
    'status' => 'draft',
    'created_at' => now(),
    'updated_at' => now(),
]);

// Update data
DB::table('evaluasi')
    ->where('id', 5)
    ->update(['status' => 'approved']);

// Delete data
DB::table('evaluasi')
    ->where('id', 5)
    ->delete();
```

**Kapan Pakai Query Builder?**
- Query kompleks dengan banyak join
- Performa kritis (lebih ringan sedikit)
- Tidak butuh fitur Model (events, casting, dll)

---

### **Eloquent ORM (yang dipakai di aplikasi)**

**Model: `app/Models/Evaluasi.php`**
```php
class Evaluasi extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'evaluasi';

    protected $fillable = [
        'renstra_id',
        'prodi_id',
        'semester',
        'status',
        // ... field lainnya
    ];

    protected $casts = [
        'realisasi' => 'decimal:2',
        'ketercapaian' => 'decimal:2',
        'verified_at' => 'datetime',
    ];

    // Relasi BelongsTo (Evaluasi punya 1 Renstra)
    public function renstra(): BelongsTo
    {
        return $this->belongsTo(Renstra::class);
    }

    // Relasi BelongsTo (Evaluasi punya 1 Prodi)
    public function prodi(): BelongsTo
    {
        return $this->belongsTo(Prodi::class);
    }

    // Relasi BelongsTo (Evaluasi dibuat oleh 1 User)
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Relasi HasMany (Evaluasi punya banyak RTL)
    public function rtls(): HasMany
    {
        return $this->hasMany(RTL::class, 'evaluasi_id');
    }

    // Scope - Query yang bisa dipakai ulang
    public function scopePending($query)
    {
        return $query->whereIn('status', ['draft', 'submitted']);
    }
}
```

**Penggunaan di Controller:**
```php
// Ambil semua data dengan relasi
$evaluasis = Evaluasi::with(['renstra', 'prodi', 'creator'])->get();

// Akses relasi langsung
foreach ($evaluasis as $evaluasi) {
    echo $evaluasi->renstra->indikator;  // Otomatis!
    echo $evaluasi->prodi->nama_prodi;   // Otomatis!
    echo $evaluasi->creator->name;       // Otomatis!
}

// Pakai scope
$pendingEvaluasis = Evaluasi::pending()->get();

// Insert dengan mass assignment
Evaluasi::create([
    'renstra_id' => 1,
    'prodi_id' => 2,
    'status' => 'draft',
]); 
// created_at & updated_at otomatis terisi!

// Update
$evaluasi = Evaluasi::find(5);
$evaluasi->update(['status' => 'approved']);

// Soft Delete
$evaluasi->delete(); // Data tidak hilang, hanya marked deleted_at
```

---

### **Jenis-jenis Relasi Eloquent (Ada di Aplikasi)**

#### **1. BelongsTo (Punya 1)**
```php
// File: app/Models/Evaluasi.php (Baris 55-63)
public function renstra(): BelongsTo
{
    return $this->belongsTo(Renstra::class);
}

public function prodi(): BelongsTo
{
    return $this->belongsTo(Prodi::class);
}
```
**Maksudnya:** 1 Evaluasi punya 1 Renstra, 1 Evaluasi punya 1 Prodi

**Cara Pakai:**
```php
$evaluasi = Evaluasi::find(1);
echo $evaluasi->renstra->indikator;
echo $evaluasi->prodi->nama_prodi;
```

---

#### **2. HasMany (Punya Banyak)**
```php
// File: app/Models/User.php (Baris 90-93)
public function evaluasis(): HasMany
{
    return $this->hasMany(Evaluasi::class, 'created_by');
}
```
**Maksudnya:** 1 User bisa buat banyak Evaluasi

**Cara Pakai:**
```php
$user = User::find(1);
foreach ($user->evaluasis as $evaluasi) {
    echo $evaluasi->semester;
}
```

---

#### **3. BelongsTo dengan Custom Foreign Key**
```php
// File: app/Models/Evaluasi.php (Baris 70-73)
public function creator(): BelongsTo
{
    return $this->belongsTo(User::class, 'created_by');
}
```
**Maksudnya:** Foreign key nya bukan `user_id`, tapi `created_by`

---

### **Keuntungan Eloquent:**

1. **Relasi Otomatis**
   ```php
   // Query Builder: harus join manual
   $data = DB::table('evaluasi')
       ->join('prodi', 'evaluasi.prodi_id', '=', 'prodi.id')
       ->select('evaluasi.*', 'prodi.nama_prodi')
       ->get();
   
   // Eloquent: relasi otomatis
   $evaluasi = Evaluasi::with('prodi')->first();
   echo $evaluasi->prodi->nama_prodi; // Mudah!
   ```

2. **Timestamps Otomatis**
   - `created_at` & `updated_at` terisi otomatis
   
3. **Soft Delete**
   - Data tidak benar-benar dihapus
   
4. **Casting Otomatis**
   ```php
   protected $casts = [
       'verified_at' => 'datetime', // Otomatis jadi Carbon instance
       'realisasi' => 'decimal:2',  // Otomatis format desimal
   ];
   ```

5. **Mass Assignment Protection**
   ```php
   protected $fillable = ['nama', 'email']; // Hanya field ini boleh di-create/update
   ```

6. **Scope (Query yang bisa dipakai ulang)**
   ```php
   // File: app/Models/Evaluasi.php (Baris 94-97)
   public function scopePending($query)
   {
       return $query->whereIn('status', ['draft', 'submitted']);
   }
   
   // Cara pakai:
   $pending = Evaluasi::pending()->get();
   ```

---


## 5️⃣ MIGRATIONS (Versioning Database)

### **Apa itu Migration?**
Migration adalah **version control untuk database**. Seperti Git untuk kode, migration untuk struktur tabel.

**Keuntungan:**
- Tim bisa sync struktur database dengan mudah
- History perubahan database tercatat
- Bisa rollback kalau ada kesalahan
- Portable ke environment berbeda (dev, staging, production)
- Tidak perlu export/import SQL manual

---

### **Struktur Migration**

**File: `database/migrations/2024_01_01_000010_create_evaluasi_table.php`**
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations (jalankan saat migrate)
     */
    public function up(): void
    {
        Schema::create('evaluasi', function (Blueprint $table) {
            // Primary Key
            $table->id(); // bigint unsigned auto increment

            // Foreign Keys
            $table->foreignId('renstra_id')
                ->constrained('renstra')      // Relasi ke tabel renstra
                ->cascadeOnDelete();          // Jika renstra dihapus, evaluasi ikut terhapus

            $table->foreignId('prodi_id')
                ->constrained('prodi')
                ->cascadeOnDelete();

            $table->foreignId('target_id')
                ->constrained('renstra_target')
                ->cascadeOnDelete();

            $table->foreignId('bukti_id')
                ->nullable()                  // Boleh kosong
                ->constrained('evaluasi_bukti')
                ->nullOnDelete();             // Jika bukti dihapus, set NULL

            $table->foreignId('created_by')
                ->constrained('users')
                ->cascadeOnDelete();

            // Field Data
            $table->enum('semester', ['ganjil', 'genap']);
            $table->year('tahun_evaluasi');
            $table->decimal('realisasi', 10, 2)->nullable();
            $table->decimal('ketercapaian', 5, 2)->nullable(); // Max 999.99
            $table->text('akar_masalah')->nullable();
            $table->text('faktor_pendukung')->nullable();
            $table->text('faktor_penghambat')->nullable();

            // Status
            $table->enum('status', [
                'draft', 
                'submitted', 
                'verified', 
                'rejected', 
                'approved'
            ])->default('draft');

            // Audit Trail (Verifikasi)
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->text('verification_notes')->nullable();

            // Audit Trail (Approval)
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_notes')->nullable();

            // Timestamps & Soft Delete
            $table->timestamps();    // created_at, updated_at
            $table->softDeletes();   // deleted_at

            // Unique Constraint (kombinasi harus unik)
            $table->unique([
                'renstra_id', 
                'prodi_id', 
                'target_id', 
                'semester', 
                'tahun_evaluasi'
            ], 'evaluasi_unique');
        });
    }

    /**
     * Reverse the migrations (jalankan saat rollback)
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluasi');
    }
};
```

---

### **Jenis-jenis Tipe Kolom:**

```php
// Numeric
$table->id();                          // BIGINT UNSIGNED AUTO_INCREMENT
$table->integer('votes');              // INTEGER
$table->bigInteger('population');      // BIGINT
$table->decimal('amount', 8, 2);       // DECIMAL (8 digit, 2 desimal)
$table->float('height', 8, 2);         // FLOAT

// String
$table->string('name');                // VARCHAR(255)
$table->string('name', 100);           // VARCHAR(100)
$table->text('description');           // TEXT
$table->longText('content');           // LONGTEXT

// Date & Time
$table->date('birth_date');            // DATE
$table->dateTime('created_at');        // DATETIME
$table->timestamp('verified_at');      // TIMESTAMP
$table->timestamps();                  // created_at + updated_at
$table->year('year');                  // YEAR

// Enum
$table->enum('status', ['active', 'inactive']);

// Boolean
$table->boolean('is_active')->default(true);

// Foreign Key
$table->foreignId('user_id')
    ->constrained()                    // Auto ke tabel 'users'
    ->cascadeOnDelete();               // Cascade delete
```

---

### **Foreign Key Constraints:**

```php
// cascadeOnDelete() - Jika parent dihapus, child ikut dihapus
$table->foreignId('prodi_id')
    ->constrained('prodi')
    ->cascadeOnDelete();
// Contoh: Prodi dihapus → semua Evaluasi di prodi itu ikut terhapus

// nullOnDelete() - Jika parent dihapus, foreign key di child jadi NULL
$table->foreignId('bukti_id')
    ->nullable()
    ->constrained('evaluasi_bukti')
    ->nullOnDelete();
// Contoh: Bukti dihapus → bukti_id di Evaluasi jadi NULL

// restrictOnDelete() - Tidak bisa hapus parent kalau masih ada child
$table->foreignId('kategori_id')
    ->constrained('renstra_kategori')
    ->restrictOnDelete();
// Contoh: Tidak bisa hapus Kategori kalau masih ada Renstra yang pakai
```

---

### **Soft Delete (Hapus Lembut)**

```php
// Di migration
$table->softDeletes(); // Menambahkan kolom deleted_at

// Di Model
use Illuminate\Database\Eloquent\SoftDeletes;

class Evaluasi extends Model
{
    use SoftDeletes;
}

// Cara pakai:
$evaluasi->delete();           // Soft delete (deleted_at diisi)
$evaluasi->restore();          // Restore data
$evaluasi->forceDelete();      // Hard delete (hapus permanen)

// Query
Evaluasi::all();               // Hanya data yang belum dihapus
Evaluasi::withTrashed()->get(); // Semua data (termasuk yang dihapus)
Evaluasi::onlyTrashed()->get(); // Hanya data yang sudah dihapus
```

---

### **Perintah Migration:**

```bash
# Jalankan semua migration yang belum dijalankan
php artisan migrate

# Rollback migration terakhir
php artisan migrate:rollback

# Rollback 3 migration terakhir
php artisan migrate:rollback --step=3

# Rollback semua migration
php artisan migrate:reset

# Rollback semua lalu migrate ulang
php artisan migrate:refresh

# Drop semua tabel lalu migrate ulang (data hilang!)
php artisan migrate:fresh

# Drop, migrate, lalu jalankan seeder
php artisan migrate:fresh --seed

# Lihat status migration
php artisan migrate:status
```

---

### **Urutan Migration Penting!**

Migration dijalankan berdasarkan **timestamp di nama file**:
```
2024_01_01_000001_create_jabatan_table.php
2024_01_01_000002_create_prodi_table.php
2024_01_01_000003_add_role_to_users_table.php
2024_01_01_000010_create_evaluasi_table.php
```

**Aturan:**
- Tabel parent harus dibuat dulu sebelum tabel child
- Contoh: `prodi` harus dibuat dulu sebelum `evaluasi` (karena ada foreign key)

---

### **Membuat Migration Baru:**

```bash
# Migration biasa
php artisan make:migration create_renstra_table

# Migration dengan model sekaligus
php artisan make:model Renstra -m

# Migration untuk alter table
php artisan make:migration add_fakultas_id_to_users_table
```

**Contoh Migration Alter Table:**
```php
// File: database/migrations/2024_01_01_000003_add_role_to_users_table.php
public function up(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->string('role')->default('kaprodi')->after('email');
        $table->foreignId('prodi_id')->nullable()->after('role')->constrained();
    });
}

public function down(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropForeign(['prodi_id']);
        $table->dropColumn(['role', 'prodi_id']);
    });
}
```

---


## 6️⃣ AKSES (Authentication & Authorization)

### **A. AUTHENTICATION (Autentikasi - Siapa User?)**

Authentication = **Verifikasi identitas user** (login/logout)

---

#### **1. REGISTER (Daftar Akun)**

**Route: `routes/auth.php` (Baris 14-18)**
```php
Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');
    
    Route::post('register', [RegisteredUserController::class, 'store']);
});
```

**Controller: `app/Http/Controllers/Auth/RegisteredUserController.php`**
```php
public function store(Request $request): RedirectResponse
{
    // Validasi input
    $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
        'password' => ['required', 'confirmed', Rules\Password::defaults()],
    ]);

    // Buat user baru
    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password), // Hash password!
        'role' => 'kaprodi', // Default role
        'is_active' => true,
    ]);

    // Kirim email verifikasi (opsional)
    event(new Registered($user));

    // Login otomatis setelah register
    Auth::login($user);

    // Redirect ke dashboard
    return redirect(route('dashboard'));
}
```

**Penjelasan:**
- `Hash::make()` → Encrypt password (JANGAN simpan password plain text!)
- `Auth::login($user)` → Login otomatis setelah register
- `unique:users` → Email harus unik (tidak boleh duplikat)

---

#### **2. LOGIN (Masuk)**

**Route: `routes/auth.php` (Baris 20-23)**
```php
Route::get('login', [AuthenticatedSessionController::class, 'create'])
    ->name('login');

Route::post('login', [AuthenticatedSessionController::class, 'store']);
```

**Controller: `app/Http/Controllers/Auth/AuthenticatedSessionController.php`**
```php
public function store(LoginRequest $request): RedirectResponse
{
    // Validasi & authenticate user
    $request->authenticate();

    // Regenerate session (security)
    $request->session()->regenerate();

    // Redirect ke halaman yang dituju
    return redirect()->intended(route('dashboard'));
}
```

**LoginRequest (validasi):**
```php
public function authenticate(): void
{
    // Cek credentials
    if (!Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
        throw ValidationException::withMessages([
            'email' => __('Email atau password salah.'),
        ]);
    }
}
```

**Penjelasan:**
- `Auth::attempt()` → Cek email & password di database
- `$this->boolean('remember')` → Remember me (session lebih lama)
- `redirect()->intended()` → Redirect ke halaman yang user coba akses sebelumnya

---

#### **3. LOGOUT (Keluar)**

**Route: `routes/auth.php` (Baris 59-60)**
```php
Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
    ->name('logout');
```

**Controller:**
```php
public function destroy(Request $request): RedirectResponse
{
    // Logout user
    Auth::guard('web')->logout();

    // Invalidate session
    $request->session()->invalidate();

    // Regenerate CSRF token
    $request->session()->regenerateToken();

    // Redirect ke home
    return redirect('/');
}
```

---

#### **4. CEK USER SUDAH LOGIN**

**Di Controller:**
```php
// Cara 1: Helper auth()
$user = auth()->user();        // Ambil data user login
$userId = auth()->id();         // Ambil ID user login
$isLogin = auth()->check();     // Cek sudah login? (true/false)

// Cara 2: Request
$user = $request->user();

// Cara 3: Auth Facade
use Illuminate\Support\Facades\Auth;

$user = Auth::user();
$isLogin = Auth::check();
```

**Di Blade (View):**
```php
@auth
    <p>Halo, {{ auth()->user()->name }}</p>
@endauth

@guest
    <a href="{{ route('login') }}">Login</a>
@endguest
```

---

### **B. AUTHORIZATION (Otorisasi - Boleh Akses Apa?)**

Authorization = **Cek hak akses user** (role, permission, policy)

---

#### **1. ROLE-BASED ACCESS CONTROL (RBAC)**

**Model: `app/Models/User.php` (Baris 18-32)**
```php
// Konstanta Role
const ROLE_ADMIN = 'admin';
const ROLE_DEKAN = 'dekan';
const ROLE_GPM = 'GPM';
const ROLE_GKM = 'GKM';
const ROLE_KAPRODI = 'kaprodi';
const ROLE_BPAP = 'BPAP';

const ROLES = [
    self::ROLE_ADMIN,
    self::ROLE_DEKAN,
    self::ROLE_GPM,
    self::ROLE_GKM,
    self::ROLE_KAPRODI,
    self::ROLE_BPAP,
];
```

**Method Cek Role: `app/Models/User.php` (Baris 109-158)**
```php
// Cek apakah user adalah Admin
public function isAdmin(): bool
{
    return $this->role === self::ROLE_ADMIN;
}

// Cek apakah user adalah Kaprodi
public function isKaprodi(): bool
{
    return $this->role === self::ROLE_KAPRODI;
}

// Cek apakah user punya salah satu dari role tertentu
public function hasRole(string|array $roles): bool
{
    if (is_string($roles)) {
        return strtolower($this->role) === strtolower($roles);
    }
    
    $normalizedRoles = array_map('strtolower', $roles);
    return in_array(strtolower($this->role), $normalizedRoles);
}

// Cek apakah user bisa verify evaluasi
public function canVerify(): bool
{
    return $this->hasRole([self::ROLE_GPM, self::ROLE_DEKAN, self::ROLE_ADMIN]);
}
```

**Cara Pakai di Controller:**
```php
$user = auth()->user();

if ($user->isAdmin()) {
    // Admin bisa akses semua
}

if ($user->hasRole(['admin', 'kaprodi'])) {
    // Admin atau Kaprodi bisa akses
}

if ($user->canVerify()) {
    // GPM, Dekan, atau Admin bisa verify
}
```

---

#### **2. MIDDLEWARE ROLE**

**Middleware: `app/Http/Middleware/RoleMiddleware.php`**
```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // Cek apakah user sudah login
        if (!$request->user()) {
            return redirect()->route('login');
        }

        // Cek apakah akun aktif
        if (!$request->user()->is_active) {
            auth()->logout();
            return redirect()->route('login')
                ->with('error', 'Akun Anda tidak aktif.');
        }

        // Cek role user
        if (!empty($roles) && !$request->user()->hasRole($roles)) {
            abort(403, 'Unauthorized. Anda tidak punya akses ke halaman ini.');
        }

        return $next($request);
    }
}
```

**Cara Pakai di Route:**
```php
// File: routes/web.php (Baris 28-31)
// Hanya admin & BPAP
Route::middleware(['auth', 'role:admin,BPAP'])->group(function () {
    Route::resource('renstra', RenstraController::class);
});

// File: routes/web.php (Baris 49-51)
// Admin atau Kaprodi
Route::middleware(['auth', 'role:admin,kaprodi'])->group(function () {
    Route::resource('evaluasi', EvaluasiController::class);
});

// File: routes/web.php (Baris 86-88)
// Hanya Admin
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('users', UserController::class);
});
```

**Penjelasan:**
- `middleware(['auth'])` → Harus login dulu
- `middleware(['auth', 'role:admin,kaprodi'])` → Harus login + role admin ATAU kaprodi
- `abort(403)` → Return HTTP 403 Forbidden

---

#### **3. POLICY (Aturan Akses Detail)**

**Policy: `app/Policies/EvaluasiPolicy.php`**
```php
public function view(User $user, Evaluasi $evaluasi): bool
{
    // Admin bisa lihat semua
    if ($user->isAdmin()) {
        return true;
    }

    // User lain hanya bisa lihat evaluasi di prodi mereka
    return $user->canAccessProdi($evaluasi->prodi_id);
}

public function update(User $user, Evaluasi $evaluasi): bool
{
    // Admin bisa edit semua
    if ($user->isAdmin()) {
        return true;
    }

    // Kaprodi hanya bisa edit evaluasi yang dia buat sendiri
    if ($user->isKaprodi()) {
        return $evaluasi->created_by === $user->id 
            && in_array($evaluasi->status, ['draft', 'rejected']);
    }

    return false;
}

public function delete(User $user, Evaluasi $evaluasi): bool
{
    // Hanya admin atau creator yang bisa hapus
    return $user->isAdmin() || $evaluasi->created_by === $user->id;
}
```

**Cara Pakai di Controller:**
```php
// File: app/Http/Controllers/EvaluasiController.php (Baris 161)
public function show(Evaluasi $evaluasi): View
{
    // Cek izin view menggunakan Policy
    $this->authorize('view', $evaluasi);
    
    // Jika tidak punya izin, otomatis throw 403 Forbidden
    
    return view('evaluasi.show', compact('evaluasi'));
}

public function update(Request $request, Evaluasi $evaluasi)
{
    // Cek izin update
    $this->authorize('update', $evaluasi);
    
    // ... update logic
}
```

**Cara Pakai di Blade:**
```php
@can('update', $evaluasi)
    <a href="{{ route('evaluasi.edit', $evaluasi) }}">Edit</a>
@endcan

@can('delete', $evaluasi)
    <form action="{{ route('evaluasi.destroy', $evaluasi) }}" method="POST">
        @csrf
        @method('DELETE')
        <button type="submit">Hapus</button>
    </form>
@endcan
```

---

#### **4. FILTER DATA BERDASARKAN ROLE**

**Method di Model: `app/Models/User.php` (Baris 167-188)**
```php
/**
 * Ambil ID prodi yang bisa diakses user ini
 */
public function getAccessibleProdiIds(): array
{
    // Admin, BPAP, GPM bisa akses semua prodi
    if ($this->isAdmin() || $this->isBPAP() || $this->isGPM()) {
        return Prodi::pluck('id')->toArray();
    }

    // Dekan bisa akses prodi di fakultasnya
    if ($this->isDekan() && $this->fakultas_id) {
        return Prodi::where('fakultas_id', $this->fakultas_id)
            ->pluck('id')
            ->toArray();
    }

    // GKM & Kaprodi hanya bisa akses prodi sendiri
    if (($this->isGKM() || $this->isKaprodi()) && $this->prodi_id) {
        return [$this->prodi_id];
    }

    return [];
}
```

**Cara Pakai di Controller:**
```php
// File: app/Http/Controllers/EvaluasiController.php (Baris 36-42)
public function index(Request $request): View
{
    $user = $request->user();
    $query = Evaluasi::with(['renstra', 'prodi']);

    // Filter berdasarkan role
    $accessibleProdiIds = $user->getAccessibleProdiIds();
    if (!$user->isAdmin()) {
        $query->whereIn('prodi_id', $accessibleProdiIds);
    }

    $evaluasis = $query->latest()->paginate(15);
    
    return view('evaluasi.index', compact('evaluasis'));
}
```

**Penjelasan:**
- Admin → Lihat semua data
- Dekan → Lihat data prodi di fakultasnya
- Kaprodi/GKM → Lihat data prodi sendiri saja

---


## 7️⃣ CETAK / EXPORT PDF

### **Package yang Digunakan:**
**`barryvdh/laravel-dompdf`** - Generate PDF dari HTML/Blade view

**Install:**
```bash
composer require barryvdh/laravel-dompdf
```

---

### **Contoh Export PDF dari Aplikasi**

**Route: `routes/web.php` (Baris 92-94)**
```php
Route::middleware(['auth', 'role:admin,dekan,gpm'])->group(function () {
    Route::get('/reports/renstra', [ReportController::class, 'renstraReport'])
        ->name('reports.renstra');
    Route::get('/reports/renstra/pdf', [ReportController::class, 'exportPdf'])
        ->name('reports.renstra.pdf');
});
```

---

### **Controller: Export PDF**

**File: `app/Http/Controllers/ReportController.php` (Baris 52-82)**
```php
use Barryvdh\DomPDF\Facade\Pdf;

public function exportPdf(Request $request)
{
    // 1. Ambil data yang mau di-export
    $query = Renstra::with([
        'kategori',
        'kegiatan',
        'indikatorRelation',
        'target',
        'prodi'
    ]);

    // 2. Filter berdasarkan parameter (opsional)
    if ($request->filled('tahun')) {
        $query->byYear($request->tahun);
    }

    if ($request->filled('prodi')) {
        $query->where('prodi_id', $request->prodi);
    }

    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    // 3. Ambil data dan group by kategori
    $renstras = $query->get()->groupBy('kategori.nama_kategori');

    // 4. Load view Blade untuk PDF
    $pdf = Pdf::loadView('reports.renstra-summary-pdf', [
        'renstras' => $renstras,
        'tahun' => $request->tahun ?? 'Semua Tahun',
    ]);
    
    // 5. Set ukuran & orientasi kertas
    $pdf->setPaper('A4', 'landscape'); // atau 'portrait'
    
    // 6. Download PDF dengan nama file dinamis
    return $pdf->download('Renstra_Report_' . date('Ymd') . '.pdf');
}
```

---

### **View Blade untuk PDF**

**File: `resources/views/reports/renstra-summary-pdf.blade.php`**
```blade
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Renstra</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #000;
            padding: 5px;
        }
        th {
            background-color: #f0f0f0;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Laporan Renstra</h2>
        <p>Tahun: {{ $tahun }}</p>
    </div>

    @foreach($renstras as $kategori => $items)
        <h3>{{ $kategori }}</h3>
        <table>
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Indikator</th>
                    <th>Target</th>
                    <th>Prodi</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $renstra)
                    <tr>
                        <td>{{ $renstra->kode_renstra }}</td>
                        <td>{{ $renstra->indikator }}</td>
                        <td>{{ $renstra->target->target_value ?? '-' }}</td>
                        <td>{{ $renstra->prodi->nama_prodi ?? 'Universitas' }}</td>
                        <td>{{ $renstra->status }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <br>
    @endforeach
</body>
</html>
```

---

### **Opsi-opsi PDF:**

#### **1. Download vs Stream**
```php
// Download langsung (file didownload)
return $pdf->download('laporan.pdf');

// Stream di browser (tampil di browser, bisa save as)
return $pdf->stream('laporan.pdf');
```

#### **2. Ukuran Kertas**
```php
$pdf->setPaper('A4', 'portrait');   // A4 vertikal
$pdf->setPaper('A4', 'landscape');  // A4 horizontal
$pdf->setPaper('letter');           // Letter size
$pdf->setPaper([0, 0, 612, 792]);   // Custom size (dalam points)
```

#### **3. Opsi Tambahan**
```php
$pdf = Pdf::loadView('view.name', $data);

// Set options
$pdf->setOptions([
    'defaultFont' => 'Arial',
    'isHtml5ParserEnabled' => true,
    'isRemoteEnabled' => true, // Untuk load gambar dari URL
]);

$pdf->setPaper('A4', 'landscape');

return $pdf->download('file.pdf');
```

---

### **Contoh Export dengan Data Kompleks**

**File: `app/Http/Controllers/ReportController.php` (Baris 87-101)**
```php
public function renstraPdf(Request $request, Renstra $renstra)
{
    // Load semua relasi yang dibutuhkan
    $renstra->load([
        'kategori',
        'kegiatan',
        'indikatorRelation.targets',
        'prodi',
        'user',
        'evaluasis' => function ($query) {
            $query->with(['creator', 'bukti', 'verifier', 'approver', 'rtls'])
                  ->orderBy('tahun_evaluasi')
                  ->orderBy('semester');
        }
    ]);

    $pdf = Pdf::loadView('reports.renstra-detail-pdf', compact('renstra'));
    $pdf->setPaper('A4', 'portrait');
    
    return $pdf->download('Renstra_' . $renstra->kode_renstra . '.pdf');
}
```

---

### **Tips untuk PDF:**

#### **1. CSS Inline (Recommended)**
```blade
<!-- BAGUS -->
<h1 style="color: blue; font-size: 20px;">Judul</h1>

<!-- JANGAN (External CSS sering tidak work) -->
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
```

#### **2. Gambar Harus Absolute Path**
```blade
<!-- SALAH -->
<img src="{{ asset('images/logo.png') }}">

<!-- BENAR (pakai public_path) -->
<img src="{{ public_path('images/logo.png') }}">

<!-- ATAU pakai base64 -->
<img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('images/logo.png'))) }}">
```

#### **3. Table Auto Break Page**
```css
<style>
    table {
        page-break-inside: auto;
    }
    tr {
        page-break-inside: avoid;
        page-break-after: auto;
    }
    thead {
        display: table-header-group; /* Repeat header di setiap halaman */
    }
</style>
```

#### **4. Header & Footer**
```blade
<style>
    @page {
        margin: 100px 50px; /* Top, Right, Bottom, Left */
    }
    header {
        position: fixed;
        top: -80px;
        left: 0;
        right: 0;
        height: 50px;
    }
    footer {
        position: fixed;
        bottom: -80px;
        left: 0;
        right: 0;
        height: 50px;
    }
</style>

<header>
    <h3>Laporan Renstra - {{ now()->format('d/m/Y') }}</h3>
</header>

<footer>
    <p>Halaman <span class="pagenum"></span></p>
</footer>
```

---

### **Contoh Button Export di View**

**File: `resources/views/reports/renstra.blade.php`**
```blade
<div class="mb-4">
    <a href="{{ route('reports.renstra.pdf', request()->query()) }}" 
       class="btn btn-danger">
        <i class="fas fa-file-pdf"></i> Export PDF
    </a>
</div>

<!-- Tabel data -->
<table>
    <!-- ... -->
</table>
```

**Penjelasan:**
- `request()->query()` → Pass semua parameter filter (tahun, prodi, dll) ke route PDF

---

### **Alternative: Export Excel**

Jika butuh export Excel, pakai package **`maatwebsite/excel`**:

```bash
composer require maatwebsite/excel
```

```php
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\RenstraExport;

public function exportExcel()
{
    return Excel::download(new RenstraExport, 'renstra.xlsx');
}
```

---


## 8️⃣ FLOW LENGKAP APLIKASI

### **Request Lifecycle (Alur Request Laravel)**

```
1. USER              → Akses URL di browser
   ↓
2. PUBLIC/INDEX.PHP  → Entry point aplikasi
   ↓
3. BOOTSTRAP         → Load framework & config
   ↓
4. ROUTING           → Cari route yang match
   ↓
5. MIDDLEWARE        → Filter request (auth, role, dll)
   ↓
6. CONTROLLER        → Proses logic
   ↓
7. MODEL/ELOQUENT    → Interaksi dengan database
   ↓
8. DATABASE          → Ambil/simpan data
   ↓
9. VIEW/BLADE        → Generate HTML
   ↓
10. RESPONSE         → Kirim ke browser user
```

---

### **Contoh Flow: Buat Evaluasi Baru**

#### **Step-by-Step:**

**1. User Klik "Tambah Evaluasi"**
```
URL: http://localhost/evaluasi/create
Method: GET
```

**2. Route Menangkap Request**
```php
// File: routes/web.php (Baris 49-51)
Route::middleware(['auth', 'role:admin,kaprodi'])->group(function () {
    Route::resource('evaluasi', EvaluasiController::class)->except(['index', 'show']);
});

// Route resource otomatis bikin:
// GET /evaluasi/create → EvaluasiController@create
```

**3. Middleware Dijalankan**
```php
// Middleware 'auth' → Cek sudah login?
if (!auth()->check()) {
    return redirect('/login');
}

// Middleware 'role:admin,kaprodi' → Cek role user
if (!auth()->user()->hasRole(['admin', 'kaprodi'])) {
    abort(403, 'Unauthorized');
}
```

**4. Controller Method Dijalankan**
```php
// File: app/Http/Controllers/EvaluasiController.php (create method)
public function create(Request $request): View
{
    $user = $request->user();
    
    // Ambil data untuk dropdown form
    $renstras = Renstra::active()
        ->with(['kategori', 'kegiatan'])
        ->get();
    
    $prodis = Prodi::all();
    
    // Return view dengan data
    return view('evaluasi.create', compact('renstras', 'prodis'));
}
```

**5. View Ditampilkan**
```blade
<!-- File: resources/views/evaluasi/create.blade.php -->
<form action="{{ route('evaluasi.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    
    <select name="renstra_id">
        @foreach($renstras as $renstra)
            <option value="{{ $renstra->id }}">{{ $renstra->indikator }}</option>
        @endforeach
    </select>
    
    <input type="text" name="realisasi">
    <input type="file" name="bukti_file">
    
    <button type="submit">Simpan</button>
</form>
```

**6. User Isi Form & Submit**
```
URL: http://localhost/evaluasi
Method: POST
Data: renstra_id=1, prodi_id=2, realisasi=80, bukti_file=...
```

**7. Route Menangkap POST Request**
```php
// POST /evaluasi → EvaluasiController@store
```

**8. Middleware Dijalankan Lagi**
```php
// Cek auth & role
```

**9. Controller Store Method**
```php
public function store(StoreEvaluasiRequest $request): RedirectResponse
{
    // 9a. Validasi data (otomatis di Request)
    $validated = $request->validated();
    
    // 9b. Upload file
    if ($request->hasFile('bukti_file')) {
        $path = $request->file('bukti_file')->store('evaluasi-bukti', 'public');
        $bukti = EvaluasiBukti::create([...]);
    }
    
    // 9c. Simpan ke database via Eloquent
    $evaluasi = Evaluasi::create([
        'renstra_id' => $validated['renstra_id'],
        'created_by' => auth()->id(),
        'status' => 'draft',
    ]);
    
    // 9d. Catat audit log
    AuditLog::log('created', Evaluasi::class, $evaluasi->id, ...);
    
    // 9e. Redirect dengan flash message
    return redirect()->route('evaluasi.index')
        ->with('success', 'Evaluasi berhasil dibuat.');
}
```

**10. Database Query**
```sql
-- Eloquent generate query:
INSERT INTO evaluasi (renstra_id, prodi_id, created_by, status, created_at, updated_at) 
VALUES (1, 2, 5, 'draft', '2024-12-17 10:30:00', '2024-12-17 10:30:00');
```

**11. Redirect ke Index**
```
URL: http://localhost/evaluasi
Method: GET
Flash Session: success="Evaluasi berhasil dibuat."
```

**12. Tampilkan Pesan Sukses**
```blade
@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif
```

---

### **Diagram Flow:**

```
┌─────────────┐
│   USER      │ Klik "Tambah Evaluasi"
└──────┬──────┘
       │ GET /evaluasi/create
       ▼
┌─────────────┐
│   ROUTE     │ Match route → EvaluasiController@create
└──────┬──────┘
       │
       ▼
┌─────────────┐
│ MIDDLEWARE  │ Cek: login? role admin/kaprodi? ✓
└──────┬──────┘
       │
       ▼
┌─────────────┐
│ CONTROLLER  │ create() → ambil data renstra, prodi
└──────┬──────┘
       │
       ▼
┌─────────────┐
│   MODEL     │ Renstra::active()->get(), Prodi::all()
└──────┬──────┘
       │
       ▼
┌─────────────┐
│  DATABASE   │ SELECT * FROM renstra WHERE status='aktif'
└──────┬──────┘
       │
       ▼
┌─────────────┐
│    VIEW     │ evaluasi/create.blade.php → form
└──────┬──────┘
       │
       ▼
┌─────────────┐
│   USER      │ Isi form & klik "Simpan"
└──────┬──────┘
       │ POST /evaluasi + data
       ▼
┌─────────────┐
│   ROUTE     │ Match route → EvaluasiController@store
└──────┬──────┘
       │
       ▼
┌─────────────┐
│ MIDDLEWARE  │ Cek: login? role admin/kaprodi? ✓
└──────┬──────┘
       │
       ▼
┌─────────────┐
│  REQUEST    │ Validasi data (StoreEvaluasiRequest)
└──────┬──────┘
       │
       ▼
┌─────────────┐
│ CONTROLLER  │ store() → upload file, create evaluasi
└──────┬──────┘
       │
       ▼
┌─────────────┐
│   MODEL     │ Evaluasi::create([...])
└──────┬──────┘
       │
       ▼
┌─────────────┐
│  DATABASE   │ INSERT INTO evaluasi (...) VALUES (...)
└──────┬──────┘
       │
       ▼
┌─────────────┐
│  REDIRECT   │ redirect()->route('evaluasi.index')
└──────┬──────┘
       │
       ▼
┌─────────────┐
│   USER      │ Tampil halaman index + pesan sukses
└─────────────┘
```

---


## 9️⃣ PERTANYAAN YANG MUNGKIN DITANYA DOSEN

### **A. ROUTING**

#### **Q1: Jelaskan perbedaan Route::get() dan Route::post()!**
**Jawaban:**
- `Route::get()` → Untuk menampilkan halaman atau mengambil data. Contoh: tampilkan form, list data.
- `Route::post()` → Untuk mengirim/submit data. Contoh: simpan data dari form.
- Di aplikasi ini:
  ```php
  Route::get('/evaluasi/create', ...)  // Tampilkan form tambah
  Route::post('/evaluasi', ...)        // Simpan data evaluasi
  ```

#### **Q2: Apa fungsi Route::resource() dan sebutkan 7 route yang dibuatnya!**
**Jawaban:**
`Route::resource('evaluasi', EvaluasiController::class)` otomatis bikin 7 route CRUD:
1. `GET /evaluasi` → index() - Tampilkan semua data
2. `GET /evaluasi/create` → create() - Tampilkan form tambah
3. `POST /evaluasi` → store() - Simpan data baru
4. `GET /evaluasi/{id}` → show() - Tampilkan detail 1 data
5. `GET /evaluasi/{id}/edit` → edit() - Tampilkan form edit
6. `PUT/PATCH /evaluasi/{id}` → update() - Update data
7. `DELETE /evaluasi/{id}` → destroy() - Hapus data

#### **Q3: Jelaskan fungsi middleware di route!**
**Jawaban:**
Middleware adalah **filter/penjaga** yang jalan sebelum request masuk ke controller. Di aplikasi ini:
```php
Route::middleware(['auth', 'role:admin,kaprodi'])->group(function () {
    Route::resource('evaluasi', EvaluasiController::class);
});
```
- `auth` → Cek apakah user sudah login
- `role:admin,kaprodi` → Cek apakah user punya role admin ATAU kaprodi
- Kalau tidak memenuhi → redirect ke login atau error 403

#### **Q4: Apa fungsi ->name() di route?**
**Jawaban:**
Memberikan **nama/alias** untuk route supaya gampang dipanggil. Contoh:
```php
Route::get('/evaluasi', [...])->name('evaluasi.index');

// Cara pakai:
return redirect()->route('evaluasi.index'); // Lebih bagus
// vs
return redirect('/evaluasi'); // Hard-coded, kalau URL berubah harus ganti semua
```

#### **Q5: Apa itu Route Model Binding?**
**Jawaban:**
Laravel otomatis **ambil data dari database** berdasarkan parameter di URL. Contoh:
```php
// Route
Route::get('/evaluasi/{evaluasi}', [EvaluasiController::class, 'show']);

// Controller
public function show(Evaluasi $evaluasi) {  // Langsung dapat object Evaluasi!
    return view('evaluasi.show', compact('evaluasi'));
}

// Kalau akses /evaluasi/5 → Laravel otomatis jalankan: Evaluasi::findOrFail(5)
```

---

### **B. CONTROLLER**

#### **Q6: Jelaskan 7 method standar resource controller!**
**Jawaban:**
1. **index()** → Tampilkan list semua data (dengan pagination biasanya)
2. **create()** → Tampilkan form untuk tambah data baru
3. **store()** → Proses & simpan data baru ke database
4. **show($id)** → Tampilkan detail 1 data
5. **edit($id)** → Tampilkan form untuk edit data
6. **update($id)** → Proses & update data yang sudah ada
7. **destroy($id)** → Hapus data (soft delete atau hard delete)

#### **Q7: Apa fungsi Request $request di parameter controller?**
**Jawaban:**
`Request $request` berisi **semua informasi** tentang HTTP request:
- Input dari form: `$request->input('nama')`
- File upload: `$request->file('foto')`
- User yang login: `$request->user()`
- Parameter URL: `$request->query('filter')`
- Method HTTP: `$request->method()`

#### **Q8: Apa perbedaan return view() dan return redirect()?**
**Jawaban:**
- `return view('nama.view')` → **Tampilkan halaman** (render HTML)
- `return redirect()->route('nama.route')` → **Pindah ke URL lain** (tidak render view)
  
Contoh di aplikasi:
```php
// create() - tampilkan form
public function create() {
    return view('evaluasi.create'); // Tampil form
}

// store() - simpan data lalu redirect
public function store(Request $request) {
    Evaluasi::create([...]);
    return redirect()->route('evaluasi.index'); // Pindah ke halaman list
}
```

#### **Q9: Kapan pakai compact() vs with()?**
**Jawaban:**
Sama saja, kirim data ke view:
```php
// Cara 1: compact
$evaluasis = Evaluasi::all();
return view('evaluasi.index', compact('evaluasis'));

// Cara 2: with
return view('evaluasi.index')->with('evaluasis', $evaluasis);

// Cara 3: array
return view('evaluasi.index', ['evaluasis' => $evaluasis]);
```

#### **Q10: Apa itu $this->authorize() di controller?**
**Jawaban:**
Cek **izin akses** menggunakan Policy. Kalau user tidak punya izin, otomatis throw 403 Forbidden.
```php
public function show(Evaluasi $evaluasi) {
    $this->authorize('view', $evaluasi); // Cek izin view via EvaluasiPolicy
    return view('evaluasi.show', compact('evaluasi'));
}
```

---

### **C. CRUD & DATABASE**

#### **Q11: Jelaskan perbedaan Query Builder dan Eloquent!**
**Jawaban:**

| Aspek | Query Builder | Eloquent |
|-------|--------------|----------|
| **Cara** | `DB::table('evaluasi')` | `Evaluasi::...` |
| **Return** | Array/stdClass | Model instance |
| **Relasi** | Manual join | Otomatis (`with()`) |
| **Timestamps** | Manual | Otomatis |
| **Soft Delete** | Manual | Built-in |

Contoh:
```php
// Query Builder
DB::table('evaluasi')->where('status', 'approved')->get();

// Eloquent (lebih bagus)
Evaluasi::where('status', 'approved')->get();
```

#### **Q12: Apa itu Mass Assignment dan bagaimana proteksinya?**
**Jawaban:**
**Mass Assignment** = isi banyak field sekaligus via array.

**Proteksi:** Pakai `$fillable` atau `$guarded` di Model.
```php
// Model
protected $fillable = ['name', 'email', 'role']; // Hanya field ini boleh di-mass assign

// Controller
User::create($request->all()); // Aman, hanya field di $fillable yang terisi

// Kalau tidak pakai $fillable → error MassAssignmentException
```

**Kenapa perlu proteksi?**
Kalau tidak ada proteksi, user bisa kirim field `is_admin=1` lewat form dan langsung jadi admin!

#### **Q13: Sebutkan jenis-jenis relasi Eloquent!**
**Jawaban:**
Di aplikasi ini ada:
1. **BelongsTo** (Punya 1)
   ```php
   // Evaluasi punya 1 Prodi
   public function prodi(): BelongsTo {
       return $this->belongsTo(Prodi::class);
   }
   ```

2. **HasMany** (Punya Banyak)
   ```php
   // User punya banyak Evaluasi
   public function evaluasis(): HasMany {
       return $this->hasMany(Evaluasi::class, 'created_by');
   }
   ```

3. **HasOne** (Punya 1)
4. **BelongsToMany** (Many-to-Many)
5. **HasManyThrough**

#### **Q14: Apa perbedaan delete() dan forceDelete()?**
**Jawaban:**
- `delete()` → **Soft delete** - data tidak hilang, hanya di-mark `deleted_at`
- `forceDelete()` → **Hard delete** - data hilang permanen dari database

```php
$evaluasi->delete();       // Soft delete (bisa di-restore)
$evaluasi->restore();      // Restore data yang di-soft delete
$evaluasi->forceDelete();  // Hard delete (hapus permanen)
```

#### **Q15: Apa itu N+1 Query Problem dan solusinya?**
**Jawaban:**
**N+1 Problem** = Query berulang-ulang yang bikin aplikasi lambat.

**Contoh masalah:**
```php
$evaluasis = Evaluasi::all(); // 1 query

foreach ($evaluasis as $evaluasi) {
    echo $evaluasi->prodi->nama_prodi; // N query (1 per loop!)
}
// Total: 1 + 100 = 101 query (kalau ada 100 data) → LAMBAT!
```

**Solusi: Eager Loading**
```php
$evaluasis = Evaluasi::with('prodi')->all(); // 2 query saja

foreach ($evaluasis as $evaluasi) {
    echo $evaluasi->prodi->nama_prodi; // Tidak ada query tambahan
}
// Total: 2 query → CEPAT!
```

---

### **D. MIGRATION**

#### **Q16: Apa itu migration dan kenapa digunakan?**
**Jawaban:**
Migration adalah **version control untuk database**, seperti Git untuk kode.

**Keuntungan:**
- Tim bisa sync struktur database
- History perubahan tercatat
- Bisa rollback kalau error
- Portable ke berbagai environment
- Tidak perlu export/import SQL manual

#### **Q17: Jelaskan perbedaan up() dan down() di migration!**
**Jawaban:**
- `up()` → Dijalankan saat `php artisan migrate` (buat tabel/alter tabel)
- `down()` → Dijalankan saat `php artisan migrate:rollback` (batalkan perubahan)

```php
public function up() {
    Schema::create('evaluasi', function (...) {
        // Buat tabel
    });
}

public function down() {
    Schema::dropIfExists('evaluasi'); // Hapus tabel
}
```

#### **Q18: Jelaskan fungsi foreign key constraint cascadeOnDelete!**
**Jawaban:**
**cascadeOnDelete** = Kalau data parent dihapus, data child ikut terhapus otomatis.

```php
$table->foreignId('prodi_id')
    ->constrained('prodi')
    ->cascadeOnDelete();

// Contoh: Prodi dihapus → semua Evaluasi di prodi itu otomatis ikut terhapus
```

Pilihan lain:
- `nullOnDelete()` → Set foreign key jadi NULL
- `restrictOnDelete()` → Tidak boleh hapus parent kalau masih ada child

#### **Q19: Apa itu Soft Delete?**
**Jawaban:**
**Soft Delete** = Data tidak benar-benar dihapus, hanya diberi tanda `deleted_at`.

**Di Migration:**
```php
$table->softDeletes(); // Tambah kolom deleted_at
```

**Di Model:**
```php
use SoftDeletes;
```

**Keuntungan:**
- Data bisa di-restore
- History tetap ada
- Aman dari kesalahan hapus

---

### **E. AUTHENTICATION & AUTHORIZATION**

#### **Q20: Apa perbedaan Authentication dan Authorization?**
**Jawaban:**
- **Authentication** = Cek **siapa** user (login/logout)
- **Authorization** = Cek user boleh **akses apa** (role, permission, policy)

Contoh:
```php
// Authentication
if (auth()->check()) {  // Cek sudah login?
    echo "Halo, " . auth()->user()->name;
}

// Authorization
if (auth()->user()->hasRole('admin')) {  // Cek role
    // Admin bisa akses
}
```

#### **Q21: Bagaimana cara cek user sudah login?**
**Jawaban:**
```php
// Di Controller
auth()->check();      // return true/false
auth()->user();       // return User object atau null
$request->user();     // return User object atau null

// Di Blade
@auth
    Sudah login
@endauth

@guest
    Belum login
@endguest
```

#### **Q22: Jelaskan implementasi Role-Based Access Control di aplikasi!**
**Jawaban:**
1. **Simpan role di tabel users**
   ```php
   $table->string('role')->default('kaprodi');
   ```

2. **Buat method cek role di Model User**
   ```php
   public function hasRole($roles) {
       return in_array($this->role, (array) $roles);
   }
   ```

3. **Buat middleware RoleMiddleware**
   ```php
   if (!$request->user()->hasRole($roles)) {
       abort(403);
   }
   ```

4. **Pakai di route**
   ```php
   Route::middleware(['auth', 'role:admin,kaprodi'])->group(...);
   ```

#### **Q23: Apa fungsi Policy?**
**Jawaban:**
**Policy** = Aturan akses yang lebih **detail & spesifik** per model.

Contoh di aplikasi:
```php
// EvaluasiPolicy
public function update(User $user, Evaluasi $evaluasi) {
    // Admin bisa edit semua
    if ($user->isAdmin()) return true;
    
    // Kaprodi hanya bisa edit milik sendiri
    return $evaluasi->created_by === $user->id 
        && $evaluasi->status === 'draft';
}

// Controller
$this->authorize('update', $evaluasi);
```

#### **Q24: Bagaimana cara akses data user yang login?**
**Jawaban:**
```php
// Helper auth()
$user = auth()->user();
$userId = auth()->id();
$userName = auth()->user()->name;

// Via Request
$user = $request->user();

// Facade Auth
use Illuminate\Support\Facades\Auth;
$user = Auth::user();
```

---

### **F. EXPORT PDF**

#### **Q25: Package apa yang dipakai untuk generate PDF?**
**Jawaban:**
**`barryvdh/laravel-dompdf`**

Install:
```bash
composer require barryvdh/laravel-dompdf
```

#### **Q26: Bagaimana cara generate PDF dari view Blade?**
**Jawaban:**
```php
use Barryvdh\DomPDF\Facade\Pdf;

public function exportPdf() {
    $data = Renstra::all();
    
    $pdf = Pdf::loadView('reports.renstra-pdf', compact('data'));
    $pdf->setPaper('A4', 'landscape');
    
    return $pdf->download('laporan.pdf');
}
```

#### **Q27: Apa bedanya download() dan stream()?**
**Jawaban:**
- `download()` → File langsung **didownload** (save file)
- `stream()` → File **ditampilkan di browser** (bisa save as manual)

```php
return $pdf->download('laporan.pdf');  // Download
return $pdf->stream('laporan.pdf');    // Tampil di browser
```

---

### **G. KONSEP UMUM**

#### **Q28: Jelaskan flow request dari user sampai response!**
**Jawaban:**
```
USER → PUBLIC/INDEX.PHP → BOOTSTRAP → ROUTING → MIDDLEWARE 
→ CONTROLLER → MODEL → DATABASE → VIEW → RESPONSE
```

Detail:
1. User akses URL
2. Laravel cari route yang match
3. Middleware cek auth & role
4. Controller proses logic
5. Model query ke database
6. Data dikirim ke view
7. HTML di-render & dikirim ke browser

#### **Q29: Apa itu Validation dan dimana tempatnya?**
**Jawaban:**
**Validation** = Cek data input sebelum disimpan.

Bisa di 2 tempat:
1. **Di Controller**
   ```php
   $request->validate([
       'email' => 'required|email|unique:users',
       'password' => 'required|min:8',
   ]);
   ```

2. **Di Form Request** (lebih bagus, reusable)
   ```php
   // StoreEvaluasiRequest
   public function rules() {
       return [
           'renstra_id' => 'required|exists:renstra,id',
           'realisasi' => 'required|numeric|min:0',
       ];
   }
   ```

#### **Q30: Apa itu Eager Loading dan kenapa penting?**
**Jawaban:**
**Eager Loading** = Load relasi **sekaligus** di awal, bukan saat diakses.

**Kenapa penting?** Hindari N+1 Query Problem (banyak query berulang).

```php
// Eager Loading (BAGUS)
$evaluasis = Evaluasi::with(['prodi', 'renstra'])->get(); // 3 query

// Lazy Loading (LAMBAT kalau banyak data)
$evaluasis = Evaluasi::all(); // 1 query
foreach ($evaluasis as $e) {
    $e->prodi->nama_prodi;  // 1 query per loop → N+1 problem!
}
```

#### **Q31: Apa fungsi pagination?**
**Jawaban:**
**Pagination** = Membagi data jadi beberapa halaman supaya tidak terlalu banyak di 1 halaman.

```php
// Controller
$evaluasis = Evaluasi::paginate(15); // 15 data per halaman

// Blade
{{ $evaluasis->links() }} // Tampilkan button pagination (1, 2, 3, Next)
```

---


## 🎯 TIPS PRESENTASI

### **1. Persiapan Sebelum Presentasi**

✅ **Buka File-file Penting:**
- [routes/web.php](routes/web.php) - Routing
- [app/Http/Controllers/EvaluasiController.php](app/Http/Controllers/EvaluasiController.php) - Controller
- [app/Models/Evaluasi.php](app/Models/Evaluasi.php) - Model
- [app/Models/User.php](app/Models/User.php) - User & Role
- [database/migrations/2024_01_01_000010_create_evaluasi_table.php](database/migrations/2024_01_01_000010_create_evaluasi_table.php) - Migration

✅ **Siapkan Browser:**
- Tab 1: Aplikasi running (http://localhost)
- Tab 2: phpMyAdmin (lihat database)
- Tab 3: Dokumentasi ini

✅ **Siapkan Terminal:**
- Untuk demo command Laravel (`php artisan route:list`, dll)

---

### **2. Demo yang Harus Ditunjukkan**

#### **Demo 1: Routing**
```bash
# Lihat semua route
php artisan route:list

# Filter route tertentu
php artisan route:list --name=evaluasi
```
Jelaskan:
- Kolom Method (GET, POST, PUT, DELETE)
- Kolom URI
- Kolom Action (Controller@method)
- Kolom Middleware

#### **Demo 2: Flow CRUD Lengkap**
1. Login sebagai Kaprodi
2. Klik "Tambah Evaluasi" → Tunjukkan URL `/evaluasi/create`
3. Isi form → Submit
4. Tunjukkan data masuk ke database (buka phpMyAdmin)
5. Edit data → Tunjukkan URL `/evaluasi/{id}/edit`
6. Hapus data (soft delete) → Tunjukkan kolom `deleted_at` terisi

#### **Demo 3: Role-Based Access**
1. Login sebagai **Kaprodi** → Bisa akses Evaluasi
2. Logout → Login sebagai **Admin** → Bisa akses semua
3. Coba akses halaman yang tidak punya izin → Error 403

#### **Demo 4: Export PDF**
1. Buka halaman Laporan Renstra
2. Klik "Export PDF"
3. Tunjukkan file PDF yang dihasilkan

---

### **3. Cara Menjelaskan Konsep**

#### **Routing:**
> "Routing itu seperti peta jalan aplikasi. Ketika user akses URL tertentu, Laravel akan cari route yang cocok, lalu panggil controller yang sesuai. Di sini kita pakai middleware untuk filter akses berdasarkan role."

*Tunjukkan file routes/web.php baris 28-31*

#### **Controller:**
> "Controller adalah otak aplikasi yang memproses request. Kita pakai resource controller yang punya 7 method standar untuk CRUD. Contohnya method index() untuk tampilkan semua data, store() untuk simpan data baru."

*Tunjukkan EvaluasiController method index*

#### **Eloquent ORM:**
> "Eloquent adalah cara Laravel berinteraksi dengan database secara object-oriented. Kita tidak perlu menulis SQL manual, cukup pakai Model. Relasi antar tabel juga otomatis, seperti Evaluasi punya 1 Prodi, tinggal panggil `$evaluasi->prodi`."

*Tunjukkan Model Evaluasi dan relasi*

#### **Migration:**
> "Migration adalah version control untuk database. Setiap perubahan struktur tabel dicatat dalam file migration. Kalau ada masalah, kita bisa rollback. Tim juga gampang sync struktur database tanpa export/import SQL."

*Tunjukkan file migration create_evaluasi_table*

#### **Role-Based Access:**
> "Kita implementasi RBAC dengan menyimpan role di tabel users. Ada middleware RoleMiddleware yang cek role user sebelum akses halaman tertentu. Contohnya hanya Admin dan Kaprodi yang bisa buat Evaluasi."

*Tunjukkan RoleMiddleware dan User model*

---

### **4. Antisipasi Pertanyaan Sulit**

**Q: "Kenapa pakai Eloquent, tidak Query Builder saja?"**
**A:** "Eloquent lebih mudah untuk manage relasi antar tabel. Kita tidak perlu join manual. Plus ada fitur soft delete, timestamps otomatis, dan casting type yang mempermudah development."

**Q: "Bagaimana cara mengatasi N+1 query problem?"**
**A:** "Pakai Eager Loading dengan method `with()`. Contohnya `Evaluasi::with('prodi')->get()` hanya jalankan 2 query, bukan N+1 query."

**Q: "Kalau user biasa coba akses halaman admin gimana?"**
**A:** "Ada 2 layer proteksi: middleware di route cek role user, dan policy di controller cek izin spesifik. Kalau tidak punya izin, otomatis return error 403 Forbidden."

**Q: "Bedanya soft delete dan hard delete?"**
**A:** "Soft delete tidak benar-benar hapus data, hanya set kolom deleted_at. Data bisa di-restore. Hard delete (forceDelete) hapus permanen dari database."

---

### **5. Struktur Presentasi yang Bagus**

**1. Pengenalan (2 menit)**
- Nama aplikasi: Sistem Evaluasi Renstra
- Teknologi: Laravel 11, MySQL
- Fitur utama: CRUD Evaluasi, Role-Based Access, Export PDF

**2. Konsep Routing (3 menit)**
- Jelaskan apa itu routing
- Tunjukkan file routes/web.php
- Demo: `php artisan route:list`

**3. Konsep Controller (5 menit)**
- Jelaskan 7 method resource controller
- Tunjukkan EvaluasiController
- Demo: Flow tambah evaluasi (create → store)

**4. Konsep CRUD & Eloquent (5 menit)**
- Jelaskan perbedaan Query Builder vs Eloquent
- Tunjukkan Model & relasi
- Demo: Eager loading di phpMyAdmin (lihat query)

**5. Konsep Migration (3 menit)**
- Jelaskan fungsi migration
- Tunjukkan file migration
- Demo: `php artisan migrate:status`

**6. Konsep Authentication & Authorization (5 menit)**
- Jelaskan perbedaan autentikasi vs otorisasi
- Tunjukkan middleware & policy
- Demo: Login dengan role berbeda

**7. Konsep Export PDF (2 menit)**
- Jelaskan package yang dipakai
- Tunjukkan ReportController
- Demo: Export PDF

**8. Q&A (5 menit)**
- Siap jawab pertanyaan dosen

---

### **6. Command Laravel yang Berguna untuk Demo**

```bash
# Lihat semua route
php artisan route:list

# Lihat route tertentu
php artisan route:list --name=evaluasi

# Lihat status migration
php artisan migrate:status

# Lihat semua model
php artisan model:show Evaluasi

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Tinker (test code langsung)
php artisan tinker
>>> User::count()
>>> Evaluasi::with('prodi')->first()
```

---

### **7. Checklist Sebelum Presentasi**

- [ ] Aplikasi bisa dijalankan tanpa error
- [ ] Database sudah ada data sample (minimal 5-10 data per tabel)
- [ ] Semua role user sudah dibuat (admin, kaprodi, dekan, dll)
- [ ] Sudah test semua fitur (CRUD, login, export PDF)
- [ ] File-file penting sudah dibuka di editor
- [ ] Browser & phpMyAdmin sudah siap
- [ ] Sudah hapal jawaban pertanyaan umum
- [ ] Slide/dokumentasi pendukung sudah siap

---

## 📊 RINGKASAN CEPAT

### **Struktur Folder Laravel**

```
app/
├── Http/
│   ├── Controllers/      → Logic aplikasi
│   ├── Middleware/       → Filter request
│   └── Requests/         → Validasi form
├── Models/               → Eloquent Model (interaksi database)
└── Policies/             → Aturan otorisasi

routes/
├── web.php               → Route web aplikasi
└── auth.php              → Route autentikasi

database/
├── migrations/           → Struktur tabel
└── seeders/              → Data dummy

resources/
└── views/                → Template Blade (HTML)

public/
└── index.php             → Entry point aplikasi
```

---

### **Konsep Penting (1 Kalimat)**

| Konsep | Penjelasan Singkat |
|--------|-------------------|
| **Routing** | Peta jalan aplikasi: URL → Controller |
| **Controller** | Otak aplikasi yang proses request & return response |
| **Model** | Representasi tabel database dalam bentuk class |
| **Migration** | Version control untuk struktur database |
| **Eloquent** | ORM untuk interaksi database secara object-oriented |
| **Middleware** | Filter yang jalan sebelum request masuk controller |
| **Policy** | Aturan otorisasi detail per model |
| **Blade** | Template engine untuk view (HTML dengan PHP) |
| **Eager Loading** | Load relasi sekaligus untuk hindari N+1 query |
| **Mass Assignment** | Isi banyak field sekaligus via array |
| **Soft Delete** | Hapus data tanpa hilangkan dari database |

---

### **Flow Aplikasi (Ringkas)**

```
1. USER akses URL
2. ROUTING cari route yang match
3. MIDDLEWARE cek auth & role
4. CONTROLLER proses logic
5. MODEL query database via Eloquent
6. DATABASE return data
7. CONTROLLER kirim data ke VIEW
8. VIEW render HTML
9. RESPONSE dikirim ke browser
```

---

### **Role di Aplikasi**

| Role | Hak Akses |
|------|-----------|
| **Admin** | Akses semua (full control) |
| **BPAP** | Buat/edit Renstra |
| **Dekan** | Lihat & approve evaluasi fakultasnya |
| **GPM** | Verify evaluasi, lihat semua prodi |
| **GKM** | Buat RTL, verify evaluasi prodi sendiri |
| **Kaprodi** | Buat evaluasi prodi sendiri |

---

### **Method Eloquent Penting**

```php
// READ
Model::all()                          // Semua data
Model::find($id)                      // 1 data by ID
Model::where('status', 'aktif')->get() // Filter
Model::with('relasi')->get()          // Eager loading
Model::paginate(15)                   // Pagination

// CREATE
Model::create([...])                  // Insert data

// UPDATE
$model->update([...])                 // Update data
$model->save()                        // Save perubahan

// DELETE
$model->delete()                      // Soft delete
$model->forceDelete()                 // Hard delete
$model->restore()                     // Restore soft delete
```

---

## 🎓 KESIMPULAN

### **Mengapa Laravel?**
- **Mudah dipelajari** - Dokumentasi lengkap, komunitas besar
- **Produktif** - Banyak fitur built-in (auth, validation, dll)
- **Scalable** - Cocok untuk aplikasi kecil sampai besar
- **Secure** - Proteksi dari SQL injection, XSS, CSRF otomatis

### **Best Practices yang Diterapkan:**
1. ✅ Pakai **Eloquent ORM** untuk database
2. ✅ Pakai **Migration** untuk version control database
3. ✅ Pakai **Form Request** untuk validasi
4. ✅ Pakai **Policy** untuk otorisasi
5. ✅ Pakai **Eager Loading** untuk hindari N+1 query
6. ✅ Pakai **Soft Delete** untuk keamanan data
7. ✅ Pakai **Middleware** untuk filter akses
8. ✅ Pakai **Resource Controller** untuk struktur CRUD standard

---

**Semoga sukses presentasinya! 🚀**

*Jangan lupa practice demo beberapa kali sebelum presentasi agar lancar!*
