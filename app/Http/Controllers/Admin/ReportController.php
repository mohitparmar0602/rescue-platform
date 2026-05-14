<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agency;
use App\Models\Alert;
use App\Models\Message;
use App\Models\Resource;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    // ── Shared data builder ──────────────────────────────────────────────
    private function buildReportData(): array
    {
        // ── 1. Agency status breakdown ───────────────────────────────────
        $agencyStats = Agency::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $totalAgencies  = array_sum($agencyStats);
        $approvedCount  = $agencyStats['approved']  ?? 0;
        $pendingCount   = $agencyStats['pending']   ?? 0;
        $rejectedCount  = $agencyStats['rejected']  ?? 0;

        // ── 2. Agencies by type (for chart) ──────────────────────────────
        $agenciesByType = Agency::selectRaw('type, COUNT(*) as total')
            ->groupBy('type')
            ->pluck('total', 'type')
            ->toArray();

        // ── 3. Resource utilization per agency type ───────────────────────
        $resourceUtilization = Resource::join('agencies', 'resources.agency_id', '=', 'agencies.id')
            ->selectRaw('agencies.type, resources.status, COUNT(*) as total')
            ->groupBy('agencies.type', 'resources.status')
            ->get()
            ->groupBy('type')
            ->map(fn ($rows) => $rows->pluck('total', 'status')->toArray())
            ->toArray();

        // ── 4. Alert history + severity breakdown ─────────────────────────
        $alertsBySeverity = Alert::selectRaw('severity, COUNT(*) as total, SUM(is_active) as active')
            ->groupBy('severity')
            ->get();

        $recentAlerts = Alert::with('issuer')
            ->latest()
            ->limit(10)
            ->get();

        // ── 5. Alerts per day (last 30 days) — for timeline chart ─────────
        $alertsTimeline = Alert::selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total', 'date')
            ->toArray();

        // ── 6. All approved agencies for table ────────────────────────────
        $agencies = Agency::withCount(['resources', 'receivedMessages'])
            ->with(['resources'])
            ->where('status', 'approved')
            ->orderBy('name')
            ->get()
            ->map(function (Agency $a) {
                $total      = $a->resources->count();
                $available  = $a->resources->where('status', 'available')->count();
                $deployed   = $a->resources->where('status', 'deployed')->count();
                $maintenance= $a->resources->where('status', 'maintenance')->count();
                return [
                    'id'          => $a->id,
                    'name'        => $a->name,
                    'type'        => $a->type,
                    'resources'   => $total,
                    'available'   => $available,
                    'deployed'    => $deployed,
                    'maintenance' => $maintenance,
                    'utilization' => $total > 0 ? round(($deployed / $total) * 100) : 0,
                ];
            });

        return compact(
            'totalAgencies', 'approvedCount', 'pendingCount', 'rejectedCount',
            'agenciesByType', 'resourceUtilization', 'alertsBySeverity',
            'recentAlerts', 'alertsTimeline', 'agencies'
        );
    }

    // ── Main dashboard ───────────────────────────────────────────────────
    public function index()
    {
        return view('admin.reports.index', $this->buildReportData());
    }

    // ── CSV export ───────────────────────────────────────────────────────
    public function exportCsv()
    {
        $data = $this->buildReportData();

        $filename = 'rescue-platform-report-' . now()->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($data) {
            $f = fopen('php://output', 'w');

            // ── Agency Summary ────────────────────────────────────────
            fputcsv($f, ['RESCUE PLATFORM REPORT — ' . now()->format('d M Y H:i')]);
            fputcsv($f, []);
            fputcsv($f, ['AGENCY SUMMARY']);
            fputcsv($f, ['Total', 'Approved', 'Pending', 'Rejected']);
            fputcsv($f, [$data['totalAgencies'], $data['approvedCount'], $data['pendingCount'], $data['rejectedCount']]);

            fputcsv($f, []);
            fputcsv($f, ['APPROVED AGENCIES — RESOURCE UTILIZATION']);
            fputcsv($f, ['Agency', 'Type', 'Total Resources', 'Available', 'Deployed', 'Maintenance', 'Utilization %']);
            foreach ($data['agencies'] as $a) {
                fputcsv($f, [$a['name'], $a['type'], $a['resources'], $a['available'], $a['deployed'], $a['maintenance'], $a['utilization'] . '%']);
            }

            fputcsv($f, []);
            fputcsv($f, ['ALERT HISTORY']);
            fputcsv($f, ['Severity', 'Total Issued', 'Currently Active']);
            foreach ($data['alertsBySeverity'] as $row) {
                fputcsv($f, [$row->severity, $row->total, $row->active]);
            }

            fclose($f);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ── PDF export ───────────────────────────────────────────────────────
    public function exportPdf()
    {
        $data = $this->buildReportData();
        $data['generatedAt'] = now()->format('d M Y, H:i T');

        $pdf = Pdf::loadView('admin.reports.pdf', $data)
            ->setPaper('a4', 'landscape')
            ->setOption(['defaultFont' => 'sans-serif', 'isRemoteEnabled' => false]);

        return $pdf->download('rescue-platform-report-' . now()->format('Y-m-d') . '.pdf');
    }
}
