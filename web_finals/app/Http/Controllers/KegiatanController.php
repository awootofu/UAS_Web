<?php

namespace App\Http\Controllers;

use App\Models\RenstraKegiatan;
use App\Models\RenstraKategori;
use Illuminate\Http\Request;

class KegiatanController extends Controller
{
    public function index()
    {
        // Ambil data kegiatan beserta nama kategorinya (jika ada relasi)
        // Jika error 'kategori' not found, hapus ->with('kategori')
        $kegiatans = RenstraKegiatan::all(); 
        $kategoris = RenstraKategori::all(); // Untuk dropdown di form tambah
        
        return view('renstra.kegiatan.index', compact('kegiatans', 'kategoris'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kategori_id' => 'required|exists:renstra_kategori,id',
            'nama_kegiatan' => 'required|string|max:255',
        ]);

        RenstraKegiatan::create([
            'kategori_id' => $request->kategori_id,
            'nama_kegiatan' => $request->nama_kegiatan,
            // Tambahkan kolom lain jika ada di database (misal: kode_kegiatan)
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