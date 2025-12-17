<?php

namespace App\Http\Controllers;

use App\Models\RenstraIndikator;
use App\Models\RenstraKegiatan;
use Illuminate\Http\Request;

class IndikatorController extends Controller
{
    public function index()
    {
        // Ambil indikator + info kegiatannya (eager loading)
        // Jika error 'kegiatan' not found, hapus ->with('kegiatan')
        $indikators = RenstraIndikator::all(); 
        $kegiatans = RenstraKegiatan::all(); // Untuk dropdown
        
        return view('renstra.indikator.index', compact('indikators', 'kegiatans'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kegiatan_id' => 'required|exists:renstra_kegiatan,id',
            'nama_indikator' => 'required|string|max:255',
        ]);

        RenstraIndikator::create([
            'kegiatan_id' => $request->kegiatan_id,
            'nama_indikator' => $request->nama_indikator,
            // Tambahkan kolom lain jika ada, misal: 'satuan', 'bobot', dll
        ]);

        return redirect()->back()->with('success', 'Indikator berhasil ditambahkan');
    }

    public function destroy($id)
    {
        $indikator = RenstraIndikator::findOrFail($id);
        $indikator->delete();

        return redirect()->back()->with('success', 'Indikator berhasil dihapus');
    }
}