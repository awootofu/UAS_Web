<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Renstra;
use App\Models\RenstraIndikator;
use App\Models\RenstraKategori;
use App\Models\RenstraKegiatan;
use App\Models\RenstraTarget;
use App\Models\Prodi;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

/**
 * Controller for managing Renstra (Strategic Plan) resources.
 * 
 * Authorization is handled via route middleware in routes/web.php:
 * - View routes (index, show): All authenticated users
 * - Management routes (create, store, edit, update, destroy): admin, BPAP roles only
 * 
 * @see \App\Http\Middleware\RoleMiddleware
 * @see \App\Policies\RenstraPolicy
 */
class RenstraController extends Controller
{
   

    /**
     * Display a listing of renstra items.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $query = Renstra::with(['kategori', 'kegiatan', 'indikatorRelation', 'target', 'prodi', 'user']);

        // Filter by accessible prodi based on user role
        $accessibleProdiIds = $user->getAccessibleProdiIds();
        if (!$user->isAdmin() && !$user->isBPAP()) {
            $query->where(function ($q) use ($accessibleProdiIds) {
                $q->whereIn('prodi_id', $accessibleProdiIds)
                  ->orWhereNull('prodi_id'); // Include university-wide renstra
            });
        }

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

        // Filter by periode
        if ($request->filled('periode')) {
            $query->byPeriode($request->periode);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('kode_renstra', 'like', "%{$search}%")
                  ->orWhere('indikator', 'like', "%{$search}%");
            });
        }

        $renstras = $query->latest()->paginate(15);
        $kategoris = RenstraKategori::orderBy('urutan')->get();
        
        // Only show accessible prodis in filter dropdown
        $prodis = Prodi::whereIn('id', $accessibleProdiIds)->orderBy('nama_prodi')->get();

        return view('renstra.index', compact('renstras', 'kategoris', 'prodis'));
    }

    /**
     * Show the form for creating a new renstra.
     * Only admin and BPAP can create renstra.
     */
    public function create(): View
    {
        $user = auth()->user();
        
        // Get accessible prodis based on user role
        $accessibleProdiIds = $user->getAccessibleProdiIds();
        $prodis = Prodi::whereIn('id', $accessibleProdiIds)->orderBy('nama_prodi')->get();
        $periodes = Renstra::getPeriodeOptions();

        return view('renstra.create', compact('prodis', 'periodes'));
    }

    /**
     * Store a newly created renstra.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'indikator' => 'required|string',
            'indikator_value' => 'required|numeric|min:0',
            'kategori' => 'required|string|max:255',
            'kegiatan' => 'required|string|max:255',
            'prodi_id' => 'nullable|exists:prodi,id',
            'periode' => 'required|string',
            'status' => 'required|in:draft,active,completed,archived',
            'keterangan' => 'nullable|string',
        ]);

        // Auto-create kategori if not exist
        $kategori = RenstraKategori::firstOrCreate(
            ['nama_kategori' => $validated['kategori']],
            ['kode_kategori' => 'KAT-' . date('YmdHis'), 'urutan' => RenstraKategori::max('urutan') + 1]
        );
        $validated['kategori_id'] = $kategori->id;
        unset($validated['kategori']);

        // Auto-create kegiatan if not exist (dengan kategori_id)
        $kegiatan = RenstraKegiatan::firstOrCreate(
            ['nama_kegiatan' => $validated['kegiatan'], 'kategori_id' => $kategori->id],
            ['kode_kegiatan' => 'KEG-' . date('YmdHis'), 'urutan' => RenstraKegiatan::max('urutan') + 1]
        );
        $validated['kegiatan_id'] = $kegiatan->id;
        unset($validated['kegiatan']);

        // Auto-create indikator if not exist (dengan kegiatan_id)
        $indikator = RenstraIndikator::firstOrCreate(
            ['nama_indikator' => $validated['indikator'], 'kegiatan_id' => $kegiatan->id],
            [
                'kode_indikator' => 'IND-' . date('YmdHis'), 
                'urutan' => RenstraIndikator::max('urutan') + 1,
                'satuan' => '%'
            ]
        );
        $validated['indikator_id'] = $indikator->id;
        // Keep 'indikator' field for renstra table (it has both indikator text and indikator_id)

        $validated['user_id'] = auth()->id();

        $renstra = Renstra::create($validated);

        AuditLog::log('created', Renstra::class, $renstra->id, null, $validated);

        return redirect()->route('renstra.index')
            ->with('success', 'Renstra berhasil dibuat.');
    }

    /**
     * Display the specified renstra.
     */
    public function show(Renstra $renstra): View
    {
        $user = auth()->user();
        
        // Check if user can access this renstra's prodi
        if (!$user->isAdmin() && !$user->isBPAP() && $renstra->prodi_id) {
            if (!$user->canAccessProdi($renstra->prodi_id)) {
                abort(403, 'Anda tidak memiliki akses ke renstra ini.');
            }
        }
        
        $renstra->load(['kategori', 'kegiatan', 'indikatorRelation', 'target', 'prodi', 'user', 'evaluasis']);

        return view('renstra.show', compact('renstra'));
    }

    /**
     * Show the form for editing the renstra.
     */
    public function edit(Renstra $renstra): View
    {
        $user = auth()->user();
        
        // Get accessible prodis based on user role
        $accessibleProdiIds = $user->getAccessibleProdiIds();
        $prodis = Prodi::whereIn('id', $accessibleProdiIds)->orderBy('nama_prodi')->get();
        $periodes = Renstra::getPeriodeOptions();

        return view('renstra.edit', compact('renstra', 'prodis', 'periodes'));
    }

    /**
     * Update the specified renstra.
     */
    public function update(Request $request, Renstra $renstra): RedirectResponse
    {
        $validated = $request->validate([
            'indikator' => 'required|string',
            'indikator_value' => 'required|numeric|min:0',
            'kategori' => 'required|string|max:255',
            'kegiatan' => 'required|string|max:255',
            'prodi_id' => 'nullable|exists:prodi,id',
            'periode' => 'required|string',
            'status' => 'required|in:draft,active,completed,archived',
            'keterangan' => 'nullable|string',
        ]);

        $oldValues = $renstra->toArray();
        $renstra->update($validated);

        AuditLog::log('updated', Renstra::class, $renstra->id, $oldValues, $validated);

        return redirect()->route('renstra.index')
            ->with('success', 'Renstra berhasil diperbarui.');
    }

    /**
     * Remove the specified renstra.
     */
    public function destroy(Renstra $renstra): RedirectResponse
    {
        $oldValues = $renstra->toArray();
        $renstra->delete();

        AuditLog::log('deleted', Renstra::class, $renstra->id, $oldValues, null);

        return redirect()->route('renstra.index')
            ->with('success', 'Renstra berhasil dihapus.');
    }

    // ========================================
    // Kategori Management
    // ========================================

    public function kategoriIndex(): View
    {
        $kategoris = RenstraKategori::withCount('kegiatan')->orderBy('urutan')->get();
        return view('renstra.kategori.index', compact('kategoris'));
    }

    public function kategoriStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'kode_kategori' => 'required|string|max:20|unique:renstra_kategori,kode_kategori',
            'nama_kategori' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'urutan' => 'nullable|integer',
        ]);

        RenstraKategori::create($validated);

        return back()->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function kategoriUpdate(Request $request, RenstraKategori $kategori): RedirectResponse
    {
        $validated = $request->validate([
            'kode_kategori' => 'required|string|max:20|unique:renstra_kategori,kode_kategori,' . $kategori->id,
            'nama_kategori' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'urutan' => 'nullable|integer',
        ]);

        $kategori->update($validated);

        return back()->with('success', 'Kategori berhasil diperbarui.');
    }

    public function kategoriDestroy(RenstraKategori $kategori): RedirectResponse
    {
        $kategori->delete();
        return back()->with('success', 'Kategori berhasil dihapus.');
    }

    // ========================================
    // Kegiatan Management
    // ========================================

    public function kegiatanIndex(): View
    {
        $kegiatans = RenstraKegiatan::with('kategori')->withCount('indikators')->orderBy('urutan')->get();
        $kategoris = RenstraKategori::orderBy('urutan')->get();
        return view('renstra.kegiatan.index', compact('kegiatans', 'kategoris'));
    }

    public function kegiatanStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'kode_kegiatan' => 'required|string|max:20|unique:renstra_kegiatan,kode_kegiatan',
            'nama_kegiatan' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'kategori_id' => 'required|exists:renstra_kategori,id',
            'urutan' => 'nullable|integer',
        ]);

        RenstraKegiatan::create($validated);

        return back()->with('success', 'Kegiatan berhasil ditambahkan.');
    }

    public function kegiatanUpdate(Request $request, RenstraKegiatan $kegiatan): RedirectResponse
    {
        $validated = $request->validate([
            'kode_kegiatan' => 'required|string|max:20|unique:renstra_kegiatan,kode_kegiatan,' . $kegiatan->id,
            'nama_kegiatan' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'kategori_id' => 'required|exists:renstra_kategori,id',
            'urutan' => 'nullable|integer',
        ]);

        $kegiatan->update($validated);

        return back()->with('success', 'Kegiatan berhasil diperbarui.');
    }

    public function kegiatanDestroy(RenstraKegiatan $kegiatan): RedirectResponse
    {
        $kegiatan->delete();
        return back()->with('success', 'Kegiatan berhasil dihapus.');
    }

    // ========================================
    // Indikator Management
    // ========================================

    public function indikatorIndex(): View
    {
        $indikators = RenstraIndikator::with('kegiatan.kategori')->withCount('targets')->orderBy('urutan')->get();
        $kegiatans = RenstraKegiatan::with('kategori')->orderBy('urutan')->get();
        return view('renstra.indikator.index', compact('indikators', 'kegiatans'));
    }

    public function indikatorStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'kode_indikator' => 'required|string|max:20|unique:renstra_indikator,kode_indikator',
            'nama_indikator' => 'required|string',
            'deskripsi' => 'nullable|string',
            'satuan' => 'nullable|string|max:50',
            'kegiatan_id' => 'required|exists:renstra_kegiatan,id',
            'urutan' => 'nullable|integer',
        ]);

        RenstraIndikator::create($validated);

        return back()->with('success', 'Indikator berhasil ditambahkan.');
    }

    public function indikatorUpdate(Request $request, RenstraIndikator $indikator): RedirectResponse
    {
        $validated = $request->validate([
            'kode_indikator' => 'required|string|max:20|unique:renstra_indikator,kode_indikator,' . $indikator->id,
            'nama_indikator' => 'required|string',
            'deskripsi' => 'nullable|string',
            'satuan' => 'nullable|string|max:50',
            'kegiatan_id' => 'required|exists:renstra_kegiatan,id',
            'urutan' => 'nullable|integer',
        ]);

        $indikator->update($validated);

        return back()->with('success', 'Indikator berhasil diperbarui.');
    }

    public function indikatorDestroy(RenstraIndikator $indikator): RedirectResponse
    {
        $indikator->delete();
        return back()->with('success', 'Indikator berhasil dihapus.');
    }

    // ========================================
    // Target Management
    // ========================================

    public function targetIndex(): View
    {
        $targets = RenstraTarget::with('indikator.kegiatan.kategori')->orderBy('tahun')->get();
        $indikators = RenstraIndikator::with('kegiatan')->orderBy('urutan')->get();
        return view('renstra.target.index', compact('targets', 'indikators'));
    }

    public function targetStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'indikator_id' => 'required|exists:renstra_indikator,id',
            'tahun' => 'required|integer|min:2020|max:2050',
            'target_value' => 'required|numeric|min:0',
            'satuan' => 'nullable|string|max:50',
            'keterangan' => 'nullable|string',
        ]);

        // Check unique constraint
        $exists = RenstraTarget::where('indikator_id', $validated['indikator_id'])
            ->where('tahun', $validated['tahun'])
            ->exists();

        if ($exists) {
            return back()->withErrors(['tahun' => 'Target untuk indikator dan tahun ini sudah ada.']);
        }

        RenstraTarget::create($validated);

        return back()->with('success', 'Target berhasil ditambahkan.');
    }

    public function targetUpdate(Request $request, RenstraTarget $target): RedirectResponse
    {
        $validated = $request->validate([
            'indikator_id' => 'required|exists:renstra_indikator,id',
            'tahun' => 'required|integer|min:2020|max:2050',
            'target_value' => 'required|numeric|min:0',
            'satuan' => 'nullable|string|max:50',
            'keterangan' => 'nullable|string',
        ]);

        // Check unique constraint (excluding current)
        $exists = RenstraTarget::where('indikator_id', $validated['indikator_id'])
            ->where('tahun', $validated['tahun'])
            ->where('id', '!=', $target->id)
            ->exists();

        if ($exists) {
            return back()->withErrors(['tahun' => 'Target untuk indikator dan tahun ini sudah ada.']);
        }

        $target->update($validated);

        return back()->with('success', 'Target berhasil diperbarui.');
    }

    public function targetDestroy(RenstraTarget $target): RedirectResponse
    {
        $target->delete();
        return back()->with('success', 'Target berhasil dihapus.');
    }
}
