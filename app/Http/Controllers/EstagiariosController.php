<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Estagiario;
use App\Models\RegistroPonto;
use carbon\Carbon;
<<<<<<< HEAD
use Carbon\CarbonPeriod;
=======
>>>>>>> db14817 (tela inicial funcional)

class EstagiariosController extends Controller
{
    public function index()
    {
        return view("pages.inicio.inicio");
    }
    public function store(Request $request)
    {
        $cpf = $request->input('cpf');
<<<<<<< HEAD
        $estagiario = Estagiario::where('nr_matricula', $cpf)->first();    
        if (!$estagiario) {
             dd('Estagiário não encontrado com o CPF: ' . $cpf);
        }
        $agora = Carbon::now();
        $inicioPermitido=Carbon::today()->setTime(5, 0, 0); 
        $fimPermitido=Carbon::today()->setTime(23, 59, 59);
        if (!$agora->between($inicioPermitido, $fimPermitido)) {
            return redirect()->back()->with('Ponto fechado');    
        }
        $ultimoRegistro = RegistroPonto::where('estagiario_id', $estagiario->id)
=======
        $estagiario = Estagiario::where('nr_matricula', $cpf)->first();
        if (!$estagiario) {
            dd('Estagiário não encontrado com o CPF: ' . $cpf);
        }
        $ultimoRegistro = RegistroPonto::where('estagiario_id', $estagiario->id)->first()
>>>>>>> db14817 (tela inicial funcional)
                            ->whereDate('hr_registro', Carbon::today())
                            ->orderBy('hr_registro', 'desc')
                            ->first();
        $motivo = 'Entrada';
<<<<<<< HEAD
        if ($ultimoRegistro && $ultimoRegistro->ds_motivo == 'Entrada') {
=======
                            if (!$ultimoRegistro && $ultimoRegistro->tp_registro === 'Entrada') {
>>>>>>> db14817 (tela inicial funcional)
            $motivo = 'Saída';
        }
        RegistroPonto::create([
            'estagiario_id' => $estagiario->id,
            'ds_motivo' => $motivo,
            'hr_registro' => Carbon::now(),
<<<<<<< HEAD
            'ip_registro' => $request->ip(),
=======
            'ip_registro' => $request->ip()
>>>>>>> db14817 (tela inicial funcional)
        ]);
        return redirect()->back()->with('sucesso', 'Ponto de '.$motivo.'registrado com sucesso!');      
    }
}
