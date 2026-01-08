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
use Illuminate\Http\JsonResponse;
use Nette\Utils\Json;

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
    public function relatorioEstagiarios(): JsonResponse{
        $qtdPresentes = Estagiario::whereHas('registroPonto', function ($query) {
            $query->whereDate('hr_registro', Carbon::today())
                  ->where('ds_motivo', 'Entrada');
        })->count();
        return response()->json(['total' => $qtdPresentes]);
    }
    public function relatorioRegistros(): JsonResponse{
        $qtdRegistros = RegistroPonto::whereDate('hr_registro', Carbon::today())->count();
        return response()->json(['total' => $qtdRegistros]);    
    }
    public function relatorioRecesso(): JsonResponse{
        $qtdRecessos = RegistroPonto::whereDate('hr_registro', Carbon::today())
                            ->where('ds_motivo', 'Recesso')
                            ->count();
        return response()->json(['total' => $qtdRecessos]);    
    }
    public function relatorioAtestado(): JsonResponse{
        $qtdAtestados = RegistroPonto::whereDate('hr_registro', Carbon::today())
                            ->where('ds_motivo', 'Atestado')
                            ->count();
        return response()->json(['total' => $qtdAtestados]);    
    }    
    public function relatorioFolga(): JsonResponse{
        $qtdFolgas = RegistroPonto::whereDate('hr_registro', Carbon::today())
                            ->where('ds_motivo', 'Folga')
                            ->count();
        return response()->json(['total' => $qtdFolgas]);    
    }
    public function relatorioDispensa(): JsonResponse{
        $qtdDispensas = RegistroPonto::whereDate('hr_registro', Carbon::today())
                            ->where('ds_motivo', 'Dispensa')
                            ->count();
        return response()->json(['total' => $qtdDispensas]);    
    }
    public function relatorioFalta(): JsonResponse{
        $qtdFolgas = RegistroPonto::whereDate('hr_registro', Carbon::today())
                            ->where('ds_motivo', 'Folga')
                            ->count();
        return response()->json(['total' => $qtdFolgas]);    
    }
    public function listaEstagiariosDia(): JsonResponse{
        $estagiarios = Estagiario::with(['RegistroPonto' => function ($query) {
            $query->whereDate('hr_registro', Carbon::today());
        }])->get();
        $dadosFormatadados = $estagiarios->map(function($estagiario){
            $entrada = $estagiario->registroPonto->where('ds_motivo', 'Entrada')->first();
            $saida = $estagiario->registroPonto->where('ds_motivo', 'Saída')->first();
            
            return [
                'id' => $estagiario->id,
                'data'=> Carbon::today()->format('d/m/Y'),
                'nome' => $estagiario->nm_estagiario,
                'matricula' => $estagiario->nr_matricula,
                'entrada' => $entrada ? Carbon::parse($entrada->hr_registro)->format('H:i:s') : '',
                'saida' => $saida ? Carbon::parse($saida->hr_registro)->format('H:i:s') : '',
                'motivo' => $entrada ? $entrada->ds_motivo : '',
                'setor' => $estagiario->ds_setor,
            ];
        });
        return response()->json(['data' => $dadosFormatadados]);
      }
}