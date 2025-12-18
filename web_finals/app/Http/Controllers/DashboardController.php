<?php

namespace App\Http\Controllers;

use App\Models\Evaluasi;
use App\Models\RTL;
use App\Models\Renstra;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        
        // Get dashboard statistics based on role
        $stats = $this->getStats($user);
        
        // Get recent items
        $recentEvaluasis = $this->getRecentEvaluasis($user);
        $recentRTLs = $this->getRecentRTLs($user);
        $pendingItems = $this->getPendingItems($user);

        return view('dashboard', compact('stats', 'recentEvaluasis', 'recentRTLs', 'pendingItems'));
    }

    protected function getStats($user): array
    {
        $stats = [];

        // Base query modifiers based on role
        $prodiFilter = fn($query) => $user->prodi_id ? $query->where('prodi_id', $user->prodi_id) : $query;

        // Count statistics
        if ($user->isAdmin() || $user->isDekan() || $user->isGPM()) {
            $stats['total_renstra'] = Renstra::count();
            $stats['pending_evaluasi'] = Evaluasi::whereIn('status', ['draft', 'submitted'])->count();
            $stats['pending_rtl'] = RTL::where('status', 'pending')->count();
            $stats['overdue_rtl'] = RTL::where('deadline', '<', now())
                ->whereNotIn('status', ['completed', 'cancelled'])->count();
        } else {
            $stats['total_renstra'] = Renstra::when($user->prodi_id, fn($q) => $q->where('prodi_id', $user->prodi_id))->count();
            $stats['pending_evaluasi'] = Evaluasi::when($user->prodi_id, fn($q) => $q->where('prodi_id', $user->prodi_id))
                ->whereIn('status', ['draft', 'submitted'])->count();
            $stats['pending_rtl'] = RTL::when($user->prodi_id, fn($q) => $q->where('prodi_id', $user->prodi_id))
                ->where('status', 'pending')->count();
            $stats['overdue_rtl'] = RTL::when($user->prodi_id, fn($q) => $q->where('prodi_id', $user->prodi_id))
                ->where('deadline', '<', now())
                ->whereNotIn('status', ['completed', 'cancelled'])->count();
        }

        // Role-specific stats
        if ($user->isGPM() || $user->isDekan()) {
            $stats['awaiting_verification'] = Evaluasi::where('status', 'submitted')->count();
        }

        if ($user->isDekan()) {
            $stats['awaiting_approval'] = Evaluasi::where('status', 'verified')->count();
        }

        return $stats;
    }

    protected function getRecentEvaluasis($user)
    {
        $query = Evaluasi::with(['renstra', 'prodi', 'creator'])
            ->latest()
            ->limit(5);

        if (!$user->isAdmin() && !$user->isDekan() && !$user->isGPM()) {
            $query->where('prodi_id', $user->prodi_id);
        }

        return $query->get();
    }

    protected function getRecentRTLs($user)
    {
        $query = RTL::with(['evaluasi', 'prodi', 'user'])
            ->latest()
            ->limit(5);

        if (!$user->isAdmin() && !$user->isDekan() && !$user->isGPM()) {
            $query->where('prodi_id', $user->prodi_id);
        }

        return $query->get();
    }

    protected function getPendingItems($user): array
    {
        $items = [];

        // Items needing user action based on role
        if ($user->isKaprodi()) {
            $items['draft_evaluasi'] = Evaluasi::where('prodi_id', $user->prodi_id)
                ->whereIn('status', ['draft', 'rejected'])
                ->count();
        }

        if ($user->isGKM()) {
            $items['pending_rtl'] = RTL::where('prodi_id', $user->prodi_id)
                ->whereIn('status', ['pending', 'in_progress'])
                ->count();
        }

        if ($user->isGPM()) {
            $items['to_verify'] = Evaluasi::where('status', 'submitted')->count();
        }

        if ($user->isDekan()) {
            $items['to_approve'] = Evaluasi::where('status', 'verified')->count();
        }

        return $items;
    }
}
