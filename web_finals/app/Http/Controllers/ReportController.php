<?php

namespace App\Http\Controllers;

use App\Models\Prodi;
use App\Models\Renstra;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

/**
 * Controller for generating reports.
 * 
 * Authorization is handled via route middleware in routes/web.php:
 * - All report routes: admin, dekan, GPM roles only
 * 
 * @see \App\Http\Middleware\RoleMiddleware
 */
class ReportController extends Controller
{
    /**
     * Display the Renstra report page.
     */
    public function renstraReport(Request $request): View
    {
        $query = Renstra::with(['kategori', 'kegiatan', 'indikatorRelation', 'target', 'prodi']);

        // Filter by year if provided
        if ($request->filled('tahun')) {
            $query->byYear($request->tahun);
        }

        // Filter by prodi if provided
        if ($request->filled('prodi')) {
            $query->where('prodi_id', $request->prodi);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $renstras = $query->orderBy('created_at', 'desc')->paginate(20);
        $prodis = Prodi::orderBy('nama_prodi')->get();

        return view('reports.renstra', compact('renstras', 'prodis'));
    }

    /**
     * Export Renstra report as PDF.
     */
    public function exportPdf(Request $request)
    {
        $query = Renstra::with(['kategori', 'kegiatan', 'indikatorRelation', 'target', 'prodi']);

        // Filter by year if provided
        if ($request->filled('tahun')) {
            $query->byYear($request->tahun);
        }

        // Filter by prodi if provided
        if ($request->filled('prodi')) {
            $query->where('prodi_id', $request->prodi);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $renstras = $query->get()->groupBy('kategori.nama_kategori');

        $pdf = Pdf::loadView('reports.renstra-summary-pdf', [
            'renstras' => $renstras,
            'tahun' => $request->tahun ?? 'Semua Tahun',
        ]);
        
        $pdf->setPaper('A4', 'landscape');
        
        return $pdf->download('Renstra_Report_' . date('Ymd') . '.pdf');
    }

    /**
     * Generate PDF report for a Renstra.
     */
    public function renstraPdf(Request $request, Renstra $renstra)
    {
        $renstra->load([
            'kategori',
            'kegiatan',
            'indikatorRelation.targets',
            'prodi',
            'user',
            'evaluasis' => function ($query) {
                $query->with(['creator', 'bukti', 'verifier', 'approver', 'rtls'])
                      ->orderBy('tahun_evaluasi')
                      ->orderBy('semester');
            }
        ]);

        // TODO: Customize PDF styling to match Renstra layout
        // - Add university header/logo
        // - Format table with borders
        // - Add page numbers and footer
        // - Include signature fields

        $pdf = Pdf::loadView('reports.renstra-pdf', compact('renstra'));
        
        $pdf->setPaper('A4', 'portrait');
        
        $filename = 'Renstra_' . $renstra->kode_renstra . '_' . date('Ymd') . '.pdf';
        
        return $pdf->download($filename);
    }

    /**
     * Generate summary report for all Renstra items.
     */
    public function renstraSummaryPdf(Request $request)
    {
        $query = Renstra::with(['kategori', 'kegiatan', 'indikatorRelation', 'target', 'prodi']);

        // Filter by year if provided
        if ($request->filled('tahun')) {
            $query->byYear($request->tahun);
        }

        // Filter by prodi if provided
        if ($request->filled('prodi')) {
            $query->where('prodi_id', $request->prodi);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $renstras = $query->get()->groupBy('kategori.nama_kategori');

        $pdf = Pdf::loadView('reports.renstra-summary-pdf', [
            'renstras' => $renstras,
            'tahun' => $request->tahun ?? 'Semua Tahun',
        ]);
        
        $pdf->setPaper('A4', 'landscape');
        
        return $pdf->download('Renstra_Summary_' . date('Ymd') . '.pdf');
    }

    /**
     * Generate RTL monitoring report.
     */
    public function rtlMonitoringPdf(Request $request)
    {
        $user = $request->user();
        
        $query = \App\Models\RTL::with(['evaluasi.renstra', 'prodi', 'user']);

        // Role-based filtering
        if (!$user->isAdmin() && !$user->isDekan() && !$user->isGPM()) {
            $query->where('prodi_id', $user->prodi_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by prodi
        if ($request->filled('prodi')) {
            $query->where('prodi_id', $request->prodi);
        }

        $rtls = $query->orderBy('deadline')->get();

        $pdf = Pdf::loadView('reports.rtl-monitoring-pdf', compact('rtls'));
        
        $pdf->setPaper('A4', 'landscape');
        
        return $pdf->download('RTL_Monitoring_' . date('Ymd') . '.pdf');
    }
}
