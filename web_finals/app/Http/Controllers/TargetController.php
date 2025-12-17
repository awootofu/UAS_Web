<?php

namespace App\Http\Controllers;

use App\Models\RenstraTarget;
use App\Models\RenstraIndikator;
use Illuminate\Http\Request;

class TargetController extends Controller
{
    public function index()
    {
        // Ambil data target beserta indikatornya, urutkan per tahun
        $targets = RenstraTarget::with('indikator')->orderBy('tahun', 'asc')->get(); 
        $indikators = RenstraIndikator::all(); 
        
        return view('renstra.target.index', compact('targets', 'indikators'));
    }

    public function store(Request $request)
    {
        // 1. Cek Data Lama (Soft Delete / Restore)
        $targetLama = RenstraTarget::withTrashed()
            ->where('indikator_id', $request->indikator_id)
            ->where('tahun', $request->tahun)
            ->first();

        if ($targetLama && $targetLama->trashed()) {
            $targetLama->restore();
            $targetLama->update([
                'target_value' => $request->target_value, // <--- PERBAIKAN DISINI
                'satuan'       => $request->satuan ?? $targetLama->satuan,
            ]);
            return redirect()->back()->with('success', 'Target lama (tahun tersebut) dipulihkan & diperbarui!');
        }

        // 2. Validasi
        $request->validate([
            'indikator_id' => 'required|exists:renstra_indikator,id',
            'tahun'        => 'required|integer|min:2020|max:2030',
            'target_value' => 'required|string|max:255', // <--- PERBAIKAN DISINI
            'satuan'       => 'nullable|string|max:50',
        ]);

        // 3. Cek Duplikasi (Agar tidak double input tahun yg sama)
        if (RenstraTarget::where('indikator_id', $request->indikator_id)
                         ->where('tahun', $request->tahun)
                         ->exists()) {
            return redirect()->back()->withErrors(['tahun' => 'Target untuk indikator dan tahun ini sudah ada.']);
        }

        // 4. Simpan Data
        RenstraTarget::create([
            'indikator_id' => $request->indikator_id,
            'tahun'        => $request->tahun,
            'target_value' => $request->target_value, // <--- PERBAIKAN DISINI (Sesuai nama kolom DB)
            'satuan'       => $request->satuan,
        ]);

        return redirect()->back()->with('success', 'Target berhasil ditambahkan');
    }

    public function destroy($id)
    {
        $target = RenstraTarget::findOrFail($id);
        $target->delete();

        return redirect()->back()->with('success', 'Target berhasil dihapus');
    }
}