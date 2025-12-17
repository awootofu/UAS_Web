<?php

namespace App\Http\Controllers;

use App\Models\RenstraTarget;
use App\Models\RenstraIndikator;
use Illuminate\Http\Request;

class TargetController extends Controller
{
    public function index()
    {
        // Ambil data target + info indikatornya
        $targets = RenstraTarget::with('indikator')->get(); 
        $indikators = RenstraIndikator::all(); // Untuk dropdown
        
        return view('renstra.target.index', compact('targets', 'indikators'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'indikator_id' => 'required|exists:renstra_indikator,id',
            'target' => 'required|string|max:255', // Sesuaikan nama kolom jika beda
            // 'satuan' => 'required|string', // Aktifkan jika ada kolom satuan
        ]);

        RenstraTarget::create([
            'indikator_id' => $request->indikator_id,
            'target' => $request->target,
            // 'satuan' => $request->satuan,
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