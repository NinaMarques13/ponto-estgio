<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Estagiario;
use App\Models\RegistroPonto;
use carbon\Carbon;

class EstagiariosController extends Controller
{
    public function index()
    {
        return view("pages.inicio.inicio");
    }
    public function store(Request $request)
    {
        $cpf = $request->input('cpf');
        $estagiario = Estagiario::where('nr_matricula', $cpf)->first();
        if (!$estagiario) {
            dd('Estagiário não encontrado com o CPF: ' . $cpf);
        }
        $ultimoRegistro = RegistroPonto::where('estagiario_id', $estagiario->id)->first()
                            ->whereDate('hr_registro', Carbon::today())
                            ->orderBy('hr_registro', 'desc')
                            ->first();
        $motivo = 'Entrada';
                            if (!$ultimoRegistro && $ultimoRegistro->tp_registro === 'Entrada') {
            $motivo = 'Saída';
        }
        RegistroPonto::create([
            'estagiario_id' => $estagiario->id,
            'ds_motivo' => $motivo,
            'hr_registro' => Carbon::now(),
            'ip_registro' => $request->ip()
        ]);
        return redirect()->back()->with('sucesso', 'Ponto de '.$motivo.'registrado com sucesso!');      
    }
}
