<?php

namespace App\Http\Controllers;

use App\Models\RenstraIndikator;
use App\Models\RenstraKegiatan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class IndikatorController extends Controller
{
    public function index()
    {
        // Ambil indikator beserta kegiatan induknya
        $indikators = RenstraIndikator::with('kegiatan')->get();
        // Ambil list kegiatan untuk dropdown
        $kegiatans = RenstraKegiatan::all();
        
        return view('renstra.indikator.index', compact('indikators', 'kegiatans'));
    }

    public function store(Request $request)
    {
        // 1. Cek Soft Delete (Restore otomatis)
        $indikatorLama = RenstraIndikator::withTrashed()
            ->where('kode_indikator', $request->kode_indikator)
            ->first();

        if ($indikatorLama && $indikatorLama->trashed()) {
            $indikatorLama->restore();
            $indikatorLama->update([
                'kegiatan_id' => $request->kegiatan_id,
                'nama_indikator'   => $request->indikator, // PERBAIKAN: Ubah jadi 'nama_indikator'
            ]);
            return redirect()->back()->with('success', 'Indikator lama dipulihkan & diperbarui!');
        }

        // 2. Validasi
        $request->validate([
            'kode_indikator' => [
                'required', 
                'string', 
                'max:10',
                Rule::unique('renstra_indikator', 'kode_indikator')->whereNull('deleted_at')
            ],
            'kegiatan_id' => 'required|exists:renstra_kegiatan,id',
            'indikator'   => 'required|string', // Validasi input 'indikator' dari form
        ]);

        // 3. Simpan
        RenstraIndikator::create([
            'kode_indikator' => $request->kode_indikator,
            'kegiatan_id'    => $request->kegiatan_id,
            // PERBAIKAN: Ubah mapping ke database
            'nama_indikator' => $request->indikator, // Kolom DB: nama_indikator, Input Form: indikator
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