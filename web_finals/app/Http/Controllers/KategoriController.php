<?php

namespace App\Http\Controllers;

use App\Models\RenstraKategori; // <-- Pakai model yang sudah ada
use Illuminate\Http\Request;

class KategoriController extends Controller
{
    public function index()
    {
        $kategoris = RenstraKategori::all();
        return view('renstra.kategori.index', compact('kategoris'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_kategori' => 'required|string|max:10|unique:renstra_kategori,kode_kategori',
            'nama_kategori' => 'required|string|max:255',
        ]);

        RenstraKategori::create([
            'kode_kategori' => $request->kode_kategori,
            'nama_kategori' => $request->nama_kategori,
            'urutan' => 1 // Default urutan, bisa diupdate nanti
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