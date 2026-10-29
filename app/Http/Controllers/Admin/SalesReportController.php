<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SalesReportRequest;
use App\Services\Admin\SalesReportService;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SalesReportController extends Controller
{
    public function __invoke(SalesReportRequest $request, SalesReportService $service): View
    {
        $filters = $request->validated();
        $report = $service->report($filters);

        return view('pages.admin.reports.index', [
            'filters' => $filters,
            'summary' => $report['summary'],
            'rows' => $report['rows']->appends($filters),
        ]);
    }

    public function export(SalesReportRequest $request, SalesReportService $service): StreamedResponse
    {
        return $service->export($request->validated());
    }
}
