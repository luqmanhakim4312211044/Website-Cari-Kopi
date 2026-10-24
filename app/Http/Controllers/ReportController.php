<?php

namespace App\Http\Controllers;

use App\Support\IncomeReport;
use Dompdf\Dompdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ReportController extends Controller
{
    /** Halaman laporan penghasilan di dashboard. */
    public function show(Request $request)
    {
        [$from, $to] = $this->range($request);

        return view('dashboard.report', [
            'report' => IncomeReport::make($request->user(), $from, $to),
            'from' => $from,
            'to' => $to,
        ]);
    }

    /** Unduh laporan yang sama dalam bentuk PDF. */
    public function pdf(Request $request)
    {
        [$from, $to] = $this->range($request);
        $report = IncomeReport::make($request->user(), $from, $to);

        $pdf = new Dompdf();
        $pdf->setPaper('A4', 'portrait');
        $pdf->loadHtml(view('reports.income-pdf', compact('report'))->render());
        $pdf->render();

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="laporan-penghasilan-'.date('d-m-Y').'.pdf"',
        ]);
    }

    /**
     * Rentang tanggal dari query (?from=&to=). Tanpa parameter: bulan ini. ?all=1: semua waktu.
     *
     * @return array{0: ?Carbon, 1: ?Carbon}
     */
    private function range(Request $request): array
    {
        if ($request->boolean('all')) {
            return [null, null];
        }

        $data = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        return [
            isset($data['from']) ? Carbon::parse($data['from'])->startOfDay() : now()->startOfMonth(),
            isset($data['to']) ? Carbon::parse($data['to'])->endOfDay() : now()->endOfDay(),
        ];
    }
}
