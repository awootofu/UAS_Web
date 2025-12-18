<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Evaluasi;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class VerificationController extends Controller
{
    use AuthorizesRequests;
    
    public function index()
    {
        $user = auth()->user();
        
        // Get evaluations that need verification based on user role
        $query = Evaluasi::with(['renstra', 'prodi', 'creator', 'verifier', 'approver'])
            ->whereIn('status', ['submitted', 'verified']);
        
        // Filter based on accessible prodis
        $accessibleProdiIds = $user->getAccessibleProdiIds();
        if (!$user->isAdmin()) {
            $query->whereIn('prodi_id', $accessibleProdiIds);
        }
        
        // Order: submitted first, then by created date
        $submissions = $query->orderByRaw("FIELD(status, 'submitted') DESC")
                             ->orderBy('created_at', 'desc')
                             ->get();

        return view('verifications.index', compact('submissions'));
    }

    // Verify evaluasi (GPM and Dekan role - same authority)
    public function verify(Request $request, $id)
    {
        $evaluasi = Evaluasi::findOrFail($id);
        
        $this->authorize('verify', $evaluasi);

        $request->validate([
            'action' => 'required|in:verified,rejected',
            'notes' => 'nullable|string|max:1000'
        ]);

        if ($request->action === 'verified') {
            $evaluasi->update([
                'status' => 'verified',
                'verified_by' => auth()->id(),
                'verified_at' => now(),
                'verification_notes' => $request->notes,
            ]);
            $message = 'Evaluasi berhasil diverifikasi!';
        } else {
            $evaluasi->update([
                'status' => 'rejected',
                'verification_notes' => $request->notes,
            ]);
            $message = 'Evaluasi ditolak!';
        }

        return redirect()->route('verifications.index')
                         ->with('success', $message);
    }
    
    // Approve evaluasi (GPM and Dekan role - same authority, just different naming for final approval)
    public function approve(Request $request, $id)
    {
        $evaluasi = Evaluasi::findOrFail($id);
        
        // Both GPM and Dekan can approve
        $this->authorize('verify', $evaluasi);

        $request->validate([
            'action' => 'required|in:approved,rejected',
            'notes' => 'nullable|string|max:1000'
        ]);

        if ($request->action === 'approved') {
            $evaluasi->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'approval_notes' => $request->notes,
            ]);
            $message = 'Evaluasi berhasil disetujui!';
        } else {
            $evaluasi->update([
                'status' => 'rejected',
                'approval_notes' => $request->notes,
            ]);
            $message = 'Evaluasi ditolak!';
        }

        return redirect()->route('verifications.index')
                         ->with('success', $message);
    }
}