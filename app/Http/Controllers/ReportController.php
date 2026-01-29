<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function exportExcel()
{
    
    return Excel::download(new FolhaPontoExport, 'folha-ponto.xlsx');
}

public function exportPdf()
{
    $pontos = CheckIn::all(); 
    $pdf = Pdf::loadView('pdf.folha', compact('pontos'));
    return $pdf->download('relatorio.pdf');
}
}
