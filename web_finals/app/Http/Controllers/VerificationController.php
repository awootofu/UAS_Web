<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Submission;
use Illuminate\Support\Facades\Auth; // buat ambil ID user login
use Carbon\Carbon; // buat ambil timestamp
use Illuminate\Foundation\Auth\Access\AuthorizesRequests; // Import Trait

class VerificationController extends Controller
{
    use AuthorizesRequests; // Gunakan Trait di dalam class
    public function index()
    {
        // tampilin status 'pending' di paling atas
        $submissions = Submission::orderByRaw("FIELD(status, 'pending') DESC")
                                 ->orderBy('created_at', 'desc')
                                 ->get();

        return view('verifications.index', compact('submissions'));
    }

    // Proses Verifikasi (Approve/Reject)
    public function update(Request $request, $id)
{
    $submission = Submission::findOrFail($id);

    $this->authorize('verify', $submission);

    $request->validate([
        'action' => 'required|in:approved,rejected'
    ]);

    $submission->update([
        'status' => $request->action,
        'verifier_id' => auth()->id(),
        'verified_at' => now(),
    ]);

    return redirect()->route('verifications.index')
                     ->with('success', 'Status dokumen berhasil diperbarui!');
    }
}