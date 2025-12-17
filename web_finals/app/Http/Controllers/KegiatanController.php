<?php

namespace App\Http\Controllers;

use App\Models\RenstraKegiatan;
use App\Models\RenstraKategori;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule; // <--- Penting

class KegiatanController extends Controller
{
    public function index()
    {
        $kegiatans = RenstraKegiatan::with('kategori')->get(); 
        $kategoris = RenstraKategori::all(); 
        
        return view('renstra.kegiatan.index', compact('kegiatans', 'kategoris'));
    }

    public function store(Request $request)
    {
        // 1. Cek Soft Delete (Fitur Restore otomatis)
        $kegiatanLama = RenstraKegiatan::withTrashed()
            ->where('kode_kegiatan', $request->kode_kegiatan)
            ->first();

        if ($kegiatanLama && $kegiatanLama->trashed()) {
            $kegiatanLama->restore();
            $kegiatanLama->update([
                'kategori_id' => $request->kategori_id,
                'nama_kegiatan' => $request->nama_kegiatan,
            ]);
            return redirect()->back()->with('success', 'Kegiatan lama dipulihkan & diperbarui!');
        }

        // 2. Validasi Normal
        $request->validate([
            'kode_kegiatan' => [
                'required', 
                'string', 
                'max:10',
                Rule::unique('renstra_kegiatan', 'kode_kegiatan')->whereNull('deleted_at')
            ],
            'kategori_id' => 'required|exists:renstra_kategori,id',
            'nama_kegiatan' => 'required|string|max:255',
        ]);

        // 3. Simpan Data
        RenstraKegiatan::create([
            'kode_kegiatan' => $request->kode_kegiatan, // <--- Sudah ditambahkan
            'kategori_id' => $request->kategori_id,
            'nama_kegiatan' => $request->nama_kegiatan,
        ]);

        return redirect()->back()->with('success', 'Kegiatan berhasil ditambahkan');
    }

    public function destroy($id)
    {
        $kegiatan = RenstraKegiatan::findOrFail($id);
        $kegiatan->delete();

        return redirect()->back()->with('success', 'Kegiatan berhasil dihapus');
    }
}