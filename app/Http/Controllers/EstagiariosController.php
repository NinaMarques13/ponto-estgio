<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Estagiario;
use App\Models\RegistroPonto;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\JsonResponse;
use Nette\Utils\Json;
use PhpParser\Node\Stmt\Foreach_;
use Symfony\Component\HttpFoundation\Response;


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
            return redirect()->back()->with('erro', "Estagiário não encontrado com o CPF: {$cpf}");
        }
        $agora = Carbon::now();
        $inicioPermitido = Carbon::today()->setTime(5, 0, 0);
        $fimPermitido = Carbon::today()->setTime(23, 59, 59);
        if (!$agora->between($inicioPermitido, $fimPermitido)) {
            return redirect()->back()->with('Ponto fechado');
        }
        $ultimoRegistro = RegistroPonto::where('estagiario_id', $estagiario->id)
            ->whereDate('hr_registro', Carbon::today())
            ->orderBy('hr_registro', 'desc')
            ->first();
        $motivo = 'Entrada';
        if ($ultimoRegistro && $ultimoRegistro->ds_motivo == 'Entrada') {
            $motivo = 'Saida';
        }
        RegistroPonto::create([
            'estagiario_id' => $estagiario->id,
            'ds_motivo' => $motivo,
            'hr_registro' => Carbon::now(),
            'ip_registro' => $request->ip(),
        ]);
        return redirect()->back()->with('sucesso', "Ponto de {$motivo} registrado com sucesso!");
    }
    public function relatorioEstagiarios(Request $request): JsonResponse
    {
        if ($request->filled('data')) {
            $inicio = Carbon::parse($request->data)->startOfDay();
            $fim = Carbon::parse($request->data)->endOfDay();
        } elseif ($request->filled('mes')) {
            $inicio = Carbon::parse($request->mes)->startOfMonth();
            $fim = Carbon::parse($request->mes)->endOfMonth();
        } elseif ($request->filled('ano')) {
            $inicio = Carbon::createFromDate($request->ano, 1, 1)->startOfYear();
            $fim = Carbon::createFromDate($request->ano, 12, 31)->endOfYear();
        } else {
            $inicio = Carbon::today()->startOfDay();
            $fim = Carbon::today()->endOfDay();
        }
        $query = Estagiario::query();
        if ($request->filled('estagiario_id') && $request->estagiario_id != '') {
            $query->where('id', $request->estagiario_id);
        }
        $motivoAlvo = $request->filled('motivo') ? $request->motivo : 'Entrada';
        if ($motivoAlvo === 'Presente') {
            $query->whereHas('registroPonto', function ($q) use ($inicio, $fim) {
                $q->whereBetween('hr_registro', [$inicio, $fim])
                    ->where('ds_motivo', 'Entrada');
            });
            $query->whereHas('registroPonto', function ($q) use ($inicio, $fim) {
                $q->whereBetween('hr_registro', [$inicio, $fim])
                    ->where('ds_motivo', 'Saida');
            });
        } elseif ($motivoAlvo === 'Em Andamento') {
            $query->whereHas('registroPonto', function ($q) use ($inicio, $fim) {
                $q->whereBetween('hr_registro', [$inicio, $fim])
                    ->where('ds_motivo', 'Entrada');
            });
            $query->whereDoesntHave('registroPonto', function ($q) use ($inicio, $fim) {
                $q->whereBetween('hr_registro', [$inicio, $fim])
                    ->where('ds_motivo', 'Saida');
            });
        } elseif ($motivoAlvo === 'Todos' || $motivoAlvo === 'todos') {
            $query->whereHas('registroPonto', function ($q) use ($inicio, $fim) {
                $q->whereBetween('hr_registro', [$inicio, $fim]);
            });
        } else {
            $query->whereHas('registroPonto', function ($q) use ($inicio, $fim, $motivoAlvo) {
                $q->whereBetween('hr_registro', [$inicio, $fim])
                    ->where('ds_motivo', $motivoAlvo);
            });
        }
        $qtdPresentes = $query->count();
        return response()->json(['total' => $qtdPresentes]);
    }
    public function relatorioRegistros(Request $request): JsonResponse
    {
        if ($request->filled('data')) {
            $inicio = Carbon::parse($request->data)->startOfDay();
            $fim = Carbon::parse($request->data)->endOfDay();
        } elseif ($request->filled('mes')) {
            $inicio = Carbon::parse($request->mes)->startOfMonth();
            $fim = Carbon::parse($request->mes)->endOfMonth();
        } elseif ($request->filled('ano')) {
            $inicio = Carbon::createFromDate($request->ano, 1, 1)->startOfYear();
            $fim = Carbon::createFromDate($request->ano, 12, 31)->endOfYear();
        } else {
            $inicio = Carbon::today()->startOfDay();
            $fim = Carbon::today()->endOfDay();
        }
        $query = RegistroPonto::query();
        $query->whereBetween('hr_registro', [$inicio, $fim]);
        if ($request->filled('estagiario_id') && $request->estagiario_id != 'todos') {
            $query->where('estagiario_id', $request->estagiario_id);
        }
        $motivoAlvo = $request->filled('motivo') ? $request->motivo : 'Entrada';
        if ($motivoAlvo === 'Presente') {
            $query->whereIn('ds_motivo', ['Entrada', 'Saida']);
        } else if ($motivoAlvo === 'Em Andamento') {
            $query->whereBetween('hr_registro', [$inicio, $fim])
                ->where('ds_motivo', 'Entrada');
        } elseif ($motivoAlvo === 'Todos' || $motivoAlvo === 'todos') {
            $query->where('ds_motivo', $motivoAlvo);
        } 
        $qtdRegistros = $query->count();
        return response()->json(['total' => $qtdRegistros]);
    }
    public function relatorioRecesso(Request $request): JsonResponse
    {
        if ($request->filled('data')) {
            $inicio = Carbon::parse($request->data)->startOfDay();
            $fim = Carbon::parse($request->data)->endOfDay();
        } elseif ($request->filled('mes')) {
            $inicio = Carbon::parse($request->mes)->startOfMonth();
            $fim = Carbon::parse($request->mes)->endOfMonth();
        } elseif ($request->filled('ano')) {
            $inicio = Carbon::createFromDate($request->ano, 1, 1)->startOfYear();
            $fim = Carbon::createFromDate($request->ano, 12, 31)->endOfYear();
        } else {
            $inicio = Carbon::today()->startOfDay();
            $fim = Carbon::today()->endOfDay();
        }
        $query = RegistroPonto::query();
        $query->whereBetween('hr_registro', [$inicio, $fim]);
        if ($request->filled('estagiario_id') && $request->estagiario_id != 'todos') {
            $query->where('estagiario_id', $request->estagiario_id);
        }
        
        
        $qtdRecessos= $query->count();
        return response()->json(['total' => $qtdRecessos]);
    }
    // public function relatorioAtestado(): JsonResponse
    // {
    //     $qtdAtestados = RegistroPonto::whereDate('hr_registro', Carbon::today())
    //         ->where('ds_motivo', 'Atestado')
    //         ->count();
    //     return response()->json(['total' => $qtdAtestados]);
    // }
    // public function relatorioFolga(): JsonResponse
    // {
    //     $qtdFolgas = RegistroPonto::whereDate('hr_registro', Carbon::today())
    //         ->where('ds_motivo', 'Folga')
    //         ->count();
    //     return response()->json(['total' => $qtdFolgas]);
    // }
    // public function relatorioDispensa(): JsonResponse
    // {
    //     $qtdDispensas = RegistroPonto::whereDate('hr_registro', Carbon::today())
    //         ->where('ds_motivo', 'Dispensa')
    //         ->count();
    //     return response()->json(['total' => $qtdDispensas]);
    // }
    // public function relatorioFalta(): JsonResponse
    // {
    //     $qtdFaltas = RegistroPonto::whereDate('hr_registro', Carbon::today())
    //         ->where('ds_motivo', 'Falta')
    //         ->count();
    //     return response()->json(['total' => $qtdFaltas]);
    // }

    public function pesquisarEstagiarios(): JsonResponse
    {
        try {
            $estagiarios = Estagiario::select('id', 'nm_estagiarios')
                ->orderBy('nm_estagiarios', 'ASC')
                ->get();

            return response()->json(['data' => $estagiarios]);
        } catch (\Exception $e) {
            // Se der erro, retorna o motivo para aparecer no console do navegador
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function listaEstagiariosDia(Request $request): JsonResponse
    {
        try {
            if ($request->filled('data')) {
                $inicio = Carbon::parse($request->data)->startOfDay();
                $fim = Carbon::parse($request->data)->endOfDay();
            } elseif ($request->filled('mes') && $request->filled('ano')) {
                $inicio = Carbon::parse($request->mes)->startOfMonth();
                $fim = Carbon::parse($request->mes)->endOfMonth();
            } elseif ($request->filled('ano')) {
                $inicio = Carbon::createFromDate($request->ano, 1, 1)->startOfYear();
                $fim = Carbon::createFromDate($request->ano, 12, 31)->endOfYear();
            } else {
                $inicio = Carbon::today()->startOfDay();
                $fim = Carbon::today()->endOfDay();
            }
            $query = Estagiario::query();

            if ($request->filled('estagiario_id') && $request->estagiario_id != '') {
                $query->where('id', $request->estagiario_id);
            }
            $estagiarios = $query->with([
                'registroPonto' => function ($q) use ($inicio, $fim) {
                    $q->whereBetween('hr_registro', [$inicio->format('Y-m-d H:i:s'), $fim->format('Y-m-d H:i:s')])
                        ->orderBy('hr_registro', 'asc');
                }
            ])->get();

            $dadosFormatados = $estagiarios->map(function ($estagiario) use ($inicio, $fim) {
                $periodo = CarbonPeriod::create($inicio, $fim);
                $registrosPorDia = [];

                foreach ($periodo as $dataLoop) {
                    $dataFormatada = $dataLoop->format('Y-m-d');
                    $registrosNoDia = $estagiario->registroPonto->filter(function ($ponto) use ($dataFormatada) {
                        return str_starts_with($ponto->hr_registro, $dataFormatada);
                    });
                    $entrada = $registrosNoDia->where('ds_motivo', 'Entrada')->first();
                    $saida = $registrosNoDia->where('ds_motivo', 'Saida')->first();
                    $ocorrencia = $registrosNoDia->whereNotIn('ds_motivo', ['Entrada', 'Saida'])->first();

                    if ($ocorrencia) {
                        $textoMotivo = $ocorrencia->ds_motivo;
                    } elseif ($entrada && $saida) {
                        $textoMotivo = 'Presente';
                    } elseif ($entrada && !$saida) {
                        $textoMotivo = 'Em Andamento';
                    } else {
                        $textoMotivo = '---';
                    }

                    $totalHoras = '---';
                    if ($entrada && $saida) {
                        $chegada = Carbon::parse($entrada->hr_registro);
                        $partida = Carbon::parse($saida->hr_registro);
                        $totalHoras = $chegada->diff($partida)->format('%H:%I:%S');
                    }

                    $registrosPorDia[] = [
                        'id' => $estagiario->id,
                        'data' => $dataLoop->format('d/m/Y'),
                        'nome' => $estagiario->nm_estagiarios,
                        'matricula' => $estagiario->nr_matricula,
                        'entrada' => $entrada ? Carbon::parse($entrada->hr_registro)->format('H:i:s') : '',
                        'saida' => $saida ? Carbon::parse($saida->hr_registro)->format('H:i:s') : '',
                        'motivo' => $textoMotivo,
                        'setor' => $estagiario->nm_setor,
                        'total_horas' => $totalHoras,
                    ];
                }
                return $registrosPorDia;
            })->collapse();
            if ($request->has('motivo') && $request->motivo != '') {
                $dadosFormatados = $dadosFormatados->filter(function ($item) use ($request) {
                    return $item['motivo'] === $request->motivo;
                });
            }
            return response()->json(['data' => $dadosFormatados->values()]);
        } catch (\Exception $e) {
            // Se der erro, retorna o motivo para aparecer no console do navegador
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}