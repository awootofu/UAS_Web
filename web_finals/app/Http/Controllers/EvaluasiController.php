<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Evaluasi;
use App\Models\EvaluasiBukti;
use App\Models\Prodi;
use App\Models\Renstra;
use App\Models\RenstraTarget;
use App\Http\Requests\StoreEvaluasiRequest;
use App\Http\Requests\UpdateEvaluasiRequest;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests; // <--- PERBAIKAN 1: Import Trait

/**
 * Controller for managing Evaluasi (Evaluation) resources.
 * 
 * Authorization is handled via route middleware in routes/web.php:
 * - View routes (index, show): All authenticated users (prodi-filtered)
 * - Management routes (create, store, edit, update): admin, kaprodi, GPM, dekan
 * - Verification routes (approve, reject): admin, GPM, dekan
 * 
 * @see \App\Http\Middleware\RoleMiddleware
 * @see \App\Policies\EvaluasiPolicy
 */
class EvaluasiController extends Controller
{
    use AuthorizesRequests; // <--- PERBAIKAN 2: Gunakan Trait di dalam class

    /**
     * Display a listing of evaluasi.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        
        $query = Evaluasi::with(['renstra', 'prodi', 'target', 'creator', 'bukti']);

        // Role-based filtering based on accessible prodi
        $accessibleProdiIds = $user->getAccessibleProdiIds();
        if (!$user->isAdmin()) {
            $query->whereIn('prodi_id', $accessibleProdiIds);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by prodi
        if ($request->filled('prodi')) {
            // Ensure user can only filter by accessible prodi
            if (in_array($request->prodi, $accessibleProdiIds) || $user->isAdmin()) {
                $query->where('prodi_id', $request->prodi);
            }
        }

        // Filter by semester
        if ($request->filled('semester')) {
            $query->where('semester', $request->semester);
        }

        // Filter by year
        if ($request->filled('tahun')) {
            $query->where('tahun_evaluasi', $request->tahun);
        }

        $evaluasis = $query->latest()->paginate(15);
        
        // Only show accessible prodis in filter dropdown
        $prodis = Prodi::whereIn('id', $accessibleProdiIds)->orderBy('nama_prodi')->get();

        return view('evaluasi.index', compact('evaluasis', 'prodis'));
    }

    /**
     * Show the form for creating a new evaluasi.
     * Only kaprodi can create evaluasi for their prodi.
     */
    public function create(Request $request): View
    {
        $user = $request->user();
        $accessibleProdiIds = $user->getAccessibleProdiIds();
        
        // Get available renstra items for accessible prodis
        $renstras = Renstra::active()
            ->where(function($q) use ($accessibleProdiIds) {
                $q->whereIn('prodi_id', $accessibleProdiIds)
                  ->orWhereNull('prodi_id');
            })
            ->with(['kategori', 'kegiatan', 'indikatorRelation'])
            ->get();

        $targets = RenstraTarget::with('indikator')->get();
        
        // Get accessible prodis
        $prodis = Prodi::whereIn('id', $accessibleProdiIds)->orderBy('nama_prodi')->get();

        return view('evaluasi.create', compact('renstras', 'targets', 'prodis'));
    }

    /**
     * Store a newly created evaluasi.
     */
    public function store(StoreEvaluasiRequest $request): RedirectResponse
    {
        $user = $request->user();
        
        // Request already validated with conditional logic
        $validated = $request->validated();

        // Handle file upload
        $buktiId = null;
        if ($request->hasFile('bukti_file')) {
            $file = $request->file('bukti_file');
            $path = $file->store('evaluasi-bukti', 'public');
            
            $bukti = EvaluasiBukti::create([
                'nama_file' => $file->getClientOriginalName(),
                'file_path' => $path,
                'file_type' => $file->getClientOriginalExtension(),
                'file_size' => $file->getSize(),
                'uploaded_by' => $user->id,
            ]);
            
            $buktiId = $bukti->id;
        }

        // Restrict prodi for non-admin (already handled in prepareForValidation)
        if (!$user->isAdmin() && $user->prodi_id) {
            $validated['prodi_id'] = $user->prodi_id;
        }

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

        AuditLog::log('created', Evaluasi::class, $evaluasi->id, null, $evaluasi->toArray());

        return redirect()->route('evaluasi.index')
            ->with('success', 'Evaluasi berhasil dibuat.');
    }

    /**
     * Display the specified evaluasi.
     */
    public function show(Evaluasi $evaluasi): View
    {
        $this->authorize('view', $evaluasi);
        
        $evaluasi->load(['renstra.kategori', 'renstra.kegiatan', 'prodi', 'target', 'creator', 'bukti', 'verifier', 'approver', 'rtls']);

        return view('evaluasi.show', compact('evaluasi'));
    }

    /**
     * Show the form for editing the evaluasi.
     */
    public function edit(Evaluasi $evaluasi): View
    {
        $this->authorize('update', $evaluasi);
        
        $user = auth()->user();
        
        $renstras = Renstra::active()
            ->when($user->prodi_id, fn($q) => $q->where('prodi_id', $user->prodi_id))
            ->with(['kategori', 'kegiatan', 'indikatorRelation'])
            ->get();

        $targets = RenstraTarget::with('indikator')->get();
        $prodis = $user->isAdmin() ? Prodi::orderBy('nama_prodi')->get() : collect([$user->prodi]);

        return view('evaluasi.edit', compact('evaluasi', 'renstras', 'targets', 'prodis'));
    }

    /**
     * Update the specified evaluasi.
     */
    public function update(UpdateEvaluasiRequest $request, Evaluasi $evaluasi): RedirectResponse
    {
        // Authorization is handled in UpdateEvaluasiRequest
        
        // Request already validated with conditional logic
        $validated = $request->validated();

        // Handle new file upload
        if ($request->hasFile('bukti_file')) {
            $file = $request->file('bukti_file');
            $path = $file->store('evaluasi-bukti', 'public');
            
            // Delete old file if exists
            if ($evaluasi->bukti) {
                Storage::disk('public')->delete($evaluasi->bukti->file_path);
                $evaluasi->bukti->delete();
            }
            
            $bukti = EvaluasiBukti::create([
                'nama_file' => $file->getClientOriginalName(),
                'file_path' => $path,
                'file_type' => $file->getClientOriginalExtension(),
                'file_size' => $file->getSize(),
                'uploaded_by' => auth()->id(),
            ]);
            
            $validated['bukti_id'] = $bukti->id;
        }

        $oldValues = $evaluasi->toArray();
        $evaluasi->update($validated);

        AuditLog::log('updated', Evaluasi::class, $evaluasi->id, $oldValues, $evaluasi->fresh()->toArray());

        return redirect()->route('evaluasi.index')
            ->with('success', 'Evaluasi berhasil diperbarui.');
    }

    /**
     * Submit evaluasi for verification.
     */
    public function submit(Evaluasi $evaluasi): RedirectResponse
    {
        $this->authorize('update', $evaluasi);
        
        if (!in_array($evaluasi->status, ['draft', 'rejected'])) {
            return back()->with('error', 'Evaluasi tidak dapat disubmit.');
        }

        $oldValues = $evaluasi->toArray();
        $evaluasi->update(['status' => 'submitted']);

        AuditLog::log('submitted', Evaluasi::class, $evaluasi->id, $oldValues, ['status' => 'submitted']);

        return back()->with('success', 'Evaluasi berhasil disubmit untuk verifikasi.');
    }

    /**
     * Verify the evaluasi (GPM role).
     */
    public function verify(Request $request, Evaluasi $evaluasi): RedirectResponse
    {
        $this->authorize('verify', $evaluasi);
        
        $validated = $request->validate([
            'verification_notes' => 'nullable|string|max:1000',
        ]);

        $oldValues = $evaluasi->toArray();
        $evaluasi->update([
            'status' => 'verified',
            'verified_by' => auth()->id(),
            'verified_at' => now(),
            'verification_notes' => $validated['verification_notes'] ?? null,
        ]);

        AuditLog::log('verified', Evaluasi::class, $evaluasi->id, $oldValues, $evaluasi->fresh()->toArray());

        return back()->with('success', 'Evaluasi berhasil diverifikasi.');
    }

    /**
     * Approve the evaluasi (Dekan role).
     */
    public function approve(Request $request, Evaluasi $evaluasi): RedirectResponse
    {
        $this->authorize('approve', $evaluasi);
        
        $validated = $request->validate([
            'approval_notes' => 'nullable|string|max:1000',
        ]);

        $oldValues = $evaluasi->toArray();
        $evaluasi->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'approval_notes' => $validated['approval_notes'] ?? null,
        ]);

        AuditLog::log('approved', Evaluasi::class, $evaluasi->id, $oldValues, $evaluasi->fresh()->toArray());

        return back()->with('success', 'Evaluasi berhasil diapprove.');
    }

    /**
     * Reject the evaluasi.
     */
    public function reject(Request $request, Evaluasi $evaluasi): RedirectResponse
    {
        $this->authorize('verify', $evaluasi);
        
        $validated = $request->validate([
            'rejection_notes' => 'required|string|max:1000',
        ]);

        $oldValues = $evaluasi->toArray();
        $evaluasi->update([
            'status' => 'rejected',
            'verification_notes' => $validated['rejection_notes'],
        ]);

        AuditLog::log('rejected', Evaluasi::class, $evaluasi->id, $oldValues, $evaluasi->fresh()->toArray());

        return back()->with('success', 'Evaluasi berhasil ditolak.');
    }

    /**
     * Remove the specified evaluasi.
     */
    public function destroy(Evaluasi $evaluasi): RedirectResponse
    {
        $this->authorize('delete', $evaluasi);
        
        $oldValues = $evaluasi->toArray();
        
        // Delete associated file
        if ($evaluasi->bukti) {
            Storage::disk('public')->delete($evaluasi->bukti->file_path);
            $evaluasi->bukti->delete();
        }
        
        $evaluasi->delete();

        AuditLog::log('deleted', Evaluasi::class, $evaluasi->id, $oldValues, null);

        return redirect()->route('evaluasi.index')
            ->with('success', 'Evaluasi berhasil dihapus.');
    }
}