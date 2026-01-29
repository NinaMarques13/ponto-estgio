<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\RegistroPonto; 
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;


class RelatorioController extends Controller
{
    public function exportExcel(Request $request)
    {
        
        $fileName = 'relatorio_folha_ponto_' . date('d-m-Y') . '.csv';

        return response()->streamDownload(function () use ($request) {
            $file = fopen('php://output', 'w');
            
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, ['ID Estagiário', 'Nome', 'Data/Hora', 'Motivo']);

            $query = RegistroPonto::with('estagiario');

            
            if ($request->filled('ano')) {
                $query->whereYear('created_at', $request->ano);
            }
            if ($request->filled('mes')) {
                $query->whereMonth('created_at', $request->mes);
            }
            if ($request->filled('dia')) {
                $query->whereDay('created_at', $request->dia);
            }
            if ($request->filled('ds_motivo')) {
                $query->where('ds_motivo', $request->ds_motivo);
            }

            
            $query->chunk(200, function ($registros) use ($file) {
                foreach ($registros as $registro) {
                    fputcsv($file, [
                        $registro->estagiario_id,
                        $registro->estagiario->name ?? 'Não encontrado', 
                        $registro->created_at->format('d/m/Y H:i:s'),
                        $registro->ds_motivo
                    ]);
                }
            });

            fclose($file);
        }, $fileName);
    }

    public function exportPdf(Request $request)
    {
        $query= RegistroPonto::with('estagiario');
        if ($request->filled('ano')) $query->whereYear('created_at', $request->ano);
        if ($request->filled('mes')) $query->whereMonth('created_at', $request->mes);
        if ($request->filled('dia')) $query->whereDay('created_at', $request->dia);
        if ($request->filled('ds_motivo')) {$query->where('ds_motivo', $request->ds_motivo);}

        $registros = $query->get();

        $pdf = Pdf::loadView('pdf.folha_ponto',compact('registros'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('folha_ponto_' . now()->format('dmY') . '.pdf');
    }
}