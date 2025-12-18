<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Evaluasi;
use App\Models\Prodi;
use App\Models\RTL;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

/**
 * Controller for managing RTL (Rencana Tindak Lanjut / Follow-up Plan) resources.
 * 
 * Authorization is handled via route middleware in routes/web.php:
 * - View routes (index, show): All authenticated users (prodi-filtered)
 * - Management routes (create, store, edit, update, complete): admin, GKM
 * - Verification routes (verify): admin, GPM, dekan
 * 
 * @see \App\Http\Middleware\RoleMiddleware
 * @see \App\Policies\RTLPolicy
 */
class RTLController extends Controller
{
    use AuthorizesRequests;
   

    /**
     * Display a listing of RTLs.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        
        $query = RTL::with(['evaluasi.renstra', 'prodi', 'user', 'verifier']);

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

        // Filter overdue
        if ($request->boolean('overdue')) {
            $query->where('deadline', '<', now())
                  ->whereNotIn('status', ['completed', 'cancelled']);
        }

        $rtls = $query->latest()->paginate(15);
        
        // Only show accessible prodis in filter dropdown
        $prodis = Prodi::whereIn('id', $accessibleProdiIds)->orderBy('nama_prodi')->get();

        return view('rtl.index', compact('rtls', 'prodis'));
    }

    /**
     * Show the form for creating a new RTL.
     */
    public function create(Request $request): View
    {
        $user = $request->user();
        $accessibleProdiIds = $user->getAccessibleProdiIds();
        
        // Get evaluations that have been submitted by kaprodi
        // Include submitted, verified, and approved evaluations
        $evaluasis = Evaluasi::with(['renstra', 'prodi'])
            ->whereIn('prodi_id', $accessibleProdiIds)
            ->whereIn('status', ['submitted', 'verified', 'approved'])
            ->get();

        $prodis = Prodi::whereIn('id', $accessibleProdiIds)->orderBy('nama_prodi')->get();

        return view('rtl.create', compact('evaluasis', 'prodis'));
    }

    /**
     * Store a newly created RTL.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        
        $validated = $request->validate([
            'evaluasi_id' => 'required|exists:evaluasi,id',
            'prodi_id' => 'required|exists:prodi,id',
            'rtl' => 'required|string',
            'deadline' => 'required|date|after:today',
            'pic_rtl' => 'required|string|max:255',
            'keterangan' => 'nullable|string',
            'bukti_file' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,zip|max:10240',
        ]);

        // Handle file upload
        $buktiPath = null;
        if ($request->hasFile('bukti_file')) {
            $buktiPath = $request->file('bukti_file')->store('rtl-bukti', 'public');
        }

        // Restrict prodi for non-admin
        if (!$user->isAdmin() && $user->prodi_id) {
            $validated['prodi_id'] = $user->prodi_id;
        }

        $rtl = RTL::create([
            'evaluasi_id' => $validated['evaluasi_id'],
            'users_id' => $user->id,
            'prodi_id' => $validated['prodi_id'],
            'rtl' => $validated['rtl'],
            'deadline' => $validated['deadline'],
            'pic_rtl' => $validated['pic_rtl'],
            'bukti_rtl' => $buktiPath,
            'keterangan' => $validated['keterangan'] ?? null,
            'status' => 'pending',
        ]);

        AuditLog::log('created', RTL::class, $rtl->id, null, $rtl->toArray());

        return redirect()->route('rtl.index')
            ->with('success', 'RTL berhasil dibuat.');
    }

    /**
     * Display the specified RTL.
     */
    public function show(RTL $rtl): View
    {
        $this->authorize('view', $rtl);
        
        $rtl->load(['evaluasi.renstra', 'prodi', 'user', 'verifier']);

        return view('rtl.show', compact('rtl'));
    }

    /**
     * Show the form for editing the RTL.
     */
    public function edit(RTL $rtl): View
    {
        $this->authorize('update', $rtl);
        
        $user = auth()->user();
        
        $evaluasis = Evaluasi::with(['renstra', 'prodi'])
            ->when($user->prodi_id, fn($q) => $q->where('prodi_id', $user->prodi_id))
            ->where('ketercapaian', '<', 100)
            ->whereIn('status', ['verified', 'approved'])
            ->get();

        $prodis = $user->isAdmin() ? Prodi::orderBy('nama_prodi')->get() : collect([$user->prodi]);

        return view('rtl.edit', compact('rtl', 'evaluasis', 'prodis'));
    }

    /**
     * Update the specified RTL.
     */
    public function update(Request $request, RTL $rtl): RedirectResponse
    {
        $this->authorize('update', $rtl);
        
        $validated = $request->validate([
            'rtl' => 'required|string',
            'deadline' => 'required|date',
            'pic_rtl' => 'required|string|max:255',
            'keterangan' => 'nullable|string',
            'bukti_file' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,zip|max:10240',
        ]);

        // Handle file upload
        if ($request->hasFile('bukti_file')) {
            // Delete old file
            if ($rtl->bukti_rtl) {
                Storage::disk('public')->delete($rtl->bukti_rtl);
            }
            $validated['bukti_rtl'] = $request->file('bukti_file')->store('rtl-bukti', 'public');
        }

        $oldValues = $rtl->toArray();
        $rtl->update($validated);

        AuditLog::log('updated', RTL::class, $rtl->id, $oldValues, $rtl->fresh()->toArray());

        return redirect()->route('rtl.index')
            ->with('success', 'RTL berhasil diperbarui.');
    }

    /**
     * Mark RTL as in progress.
     */
    public function startProgress(RTL $rtl): RedirectResponse
    {
        $this->authorize('update', $rtl);
        
        if ($rtl->status !== 'pending') {
            return back()->with('error', 'RTL tidak dapat diubah statusnya.');
        }

        $oldValues = $rtl->toArray();
        $rtl->update(['status' => 'in_progress']);

        AuditLog::log('status_changed', RTL::class, $rtl->id, $oldValues, ['status' => 'in_progress']);

        return back()->with('success', 'RTL dimulai.');
    }

    /**
     * Mark RTL as complete (GKM role).
     */
    public function complete(Request $request, RTL $rtl): RedirectResponse
    {
        $this->authorize('complete', $rtl);
        
        $validated = $request->validate([
            'bukti_file' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,zip|max:10240',
            'keterangan' => 'nullable|string',
        ]);

        // Handle file upload
        if ($rtl->bukti_rtl) {
            Storage::disk('public')->delete($rtl->bukti_rtl);
        }
        $buktiPath = $request->file('bukti_file')->store('rtl-bukti', 'public');

        $oldValues = $rtl->toArray();
        $rtl->update([
            'status' => 'completed',
            'bukti_rtl' => $buktiPath,
            'keterangan' => $validated['keterangan'] ?? $rtl->keterangan,
            'completed_at' => now(),
        ]);

        AuditLog::log('completed', RTL::class, $rtl->id, $oldValues, $rtl->fresh()->toArray());

        return back()->with('success', 'RTL berhasil diselesaikan.');
    }

    /**
     * Verify RTL (GPM/Dekan role).
     */
    public function verify(Request $request, RTL $rtl): RedirectResponse
    {
        $this->authorize('verify', $rtl);
        
        if ($rtl->status !== 'completed') {
            return back()->with('error', 'Hanya RTL yang sudah completed yang bisa diverifikasi.');
        }

        $validated = $request->validate([
            'verification_notes' => 'nullable|string|max:1000',
        ]);

        $oldValues = $rtl->toArray();
        $rtl->update([
            'status' => 'verified',
            'verified_by' => auth()->id(),
            'verified_at' => now(),
            'verification_notes' => $validated['verification_notes'] ?? null,
        ]);

        AuditLog::log('verified', RTL::class, $rtl->id, $oldValues, $rtl->fresh()->toArray());

        return back()->with('success', 'RTL berhasil diverifikasi.');
    }

    /**
     * Reject RTL (GPM/Dekan role).
     */
    public function reject(Request $request, RTL $rtl): RedirectResponse
    {
        $this->authorize('verify', $rtl);
        
        if ($rtl->status !== 'completed') {
            return back()->with('error', 'Hanya RTL yang sudah completed yang bisa ditolak.');
        }

        $validated = $request->validate([
            'verification_notes' => 'required|string|max:1000',
        ]);

        $oldValues = $rtl->toArray();
        $rtl->update([
            'status' => 'rejected',
            'verified_by' => auth()->id(),
            'verified_at' => now(),
            'verification_notes' => $validated['verification_notes'],
        ]);

        AuditLog::log('rejected', RTL::class, $rtl->id, $oldValues, $rtl->fresh()->toArray());

        return back()->with('success', 'RTL berhasil ditolak. GKM perlu melakukan perbaikan.');
    }

    /**
     * Remove the specified RTL.
     */
    public function destroy(RTL $rtl): RedirectResponse
    {
        $this->authorize('delete', $rtl);
        
        $oldValues = $rtl->toArray();
        
        // Delete associated file
        if ($rtl->bukti_rtl) {
            Storage::disk('public')->delete($rtl->bukti_rtl);
        }
        
        $rtl->delete();

        AuditLog::log('deleted', RTL::class, $rtl->id, $oldValues, null);

        return redirect()->route('rtl.index')
            ->with('success', 'RTL berhasil dihapus.');
    }
}
