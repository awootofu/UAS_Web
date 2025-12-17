<?php

namespace App\Http\Controllers;

use App\Models\RenstraKategori;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KategoriController extends Controller
{
    public function index()
    {
        $kategoris = RenstraKategori::all();
        return view('renstra.kategori.index', compact('kategoris'));
    }

    public function store(Request $request)
    {
        // 1. Cek apakah kode ini sudah ada di database (termasuk yang sudah dihapus/soft delete)
        $kategoriLama = RenstraKategori::withTrashed()
            ->where('kode_kategori', $request->kode_kategori)
            ->first();

        // 2. Skenario: Data ditemukan TAPI statusnya terhapus (Soft Delete)
        // Solusi: Kita Restore (kembalikan) dan update namanya dengan inputan baru
        if ($kategoriLama && $kategoriLama->trashed()) {
            $kategoriLama->restore(); 
            $kategoriLama->update([
                'nama_kategori' => $request->nama_kategori,
                'urutan' => 1
            ]);
            
            return redirect()->back()->with('success', 'Kategori lama ditemukan dan berhasil dipulihkan!');
        }

        // 3. Skenario Normal: Data belum pernah ada, atau data aktif
        // Lakukan validasi standar Laravel
        $request->validate([
            'kode_kategori' => [
                'required',
                'string',
                'max:10',
                // Cek unique hanya pada data yang aktif (deleted_at NULL)
                Rule::unique('renstra_kategori', 'kode_kategori')->whereNull('deleted_at')
            ],
            'nama_kategori' => 'required|string|max:255',
        ]);

        // Buat data baru
        RenstraKategori::create([
            'kode_kategori' => $request->kode_kategori,
            'nama_kategori' => $request->nama_kategori,
            'urutan' => 1
        ]);

        return redirect()->back()->with('success', 'Kategori berhasil ditambahkan');
    }

    public function destroy($id)
    {
        $kategori = RenstraKategori::findOrFail($id);
        $kategori->delete();

        return redirect()->back()->with('success', 'Kategori berhasil dihapus');
    }
}