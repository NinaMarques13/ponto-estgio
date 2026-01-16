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
            $motivo = 'Saída';
        }
        RegistroPonto::create([
            'estagiario_id' => $estagiario->id,
            'ds_motivo' => $motivo,
            'hr_registro' => Carbon::now(),
            'ip_registro' => $request->ip(),
        ]);
        return redirect()->back()->with('sucesso', "Ponto de {$motivo} registrado com sucesso!");
    }
    public function relatorioEstagiarios(): JsonResponse
    {
        $qtdPresentes = Estagiario::whereHas('registroPonto', function ($query) {
            $query->whereDate('hr_registro', Carbon::today())
                ->where('ds_motivo', 'Entrada');
        })->count();
        return response()->json(['total' => $qtdPresentes]);
    }
    public function relatorioRegistros(): JsonResponse
    {
        $qtdRegistros = RegistroPonto::whereDate('hr_registro', Carbon::today())->count();
        return response()->json(['total' => $qtdRegistros]);
    }
    public function relatorioRecesso(): JsonResponse
    {
        $qtdRecessos = RegistroPonto::whereDate('hr_registro', Carbon::today())
            ->where('ds_motivo', 'Recesso')
            ->count();
        return response()->json(['total' => $qtdRecessos]);
    }
    public function relatorioAtestado(): JsonResponse
    {
        $qtdAtestados = RegistroPonto::whereDate('hr_registro', Carbon::today())
            ->where('ds_motivo', 'Atestado')
            ->count();
        return response()->json(['total' => $qtdAtestados]);
    }
    public function relatorioFolga(): JsonResponse
    {
        $qtdFolgas = RegistroPonto::whereDate('hr_registro', Carbon::today())
            ->where('ds_motivo', 'Folga')
            ->count();
        return response()->json(['total' => $qtdFolgas]);
    }
    public function relatorioDispensa(): JsonResponse
    {
        $qtdDispensas = RegistroPonto::whereDate('hr_registro', Carbon::today())
            ->where('ds_motivo', 'Dispensa')
            ->count();
        return response()->json(['total' => $qtdDispensas]);
    }
    public function relatorioFalta(): JsonResponse
    {
        $qtdFaltas = RegistroPonto::whereDate('hr_registro', Carbon::today())
            ->where('ds_motivo', 'Falta')
            ->count();
        return response()->json(['total' => $qtdFaltas]);
    }
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
    public function pesquisarData(Request $request, Response $response): JsonResponse
    {
        try {
            $dataSelecionada = $request->input('data-completa');
            $dataCarbon = Carbon::parse($dataSelecionada);
            $queryData = Estagiario::whereHas('registroPonto', function ($query) use ($dataCarbon) {
                $query->whereDate('hr_registro', $dataCarbon);
            })->with([
                        'registroPonto' => function ($query) use ($dataCarbon) {
                            $query->whereDate('hr_registro', $dataCarbon);
                        }
                    ])->get();
            $dadosFormatados = $queryData->map(function ($estagiario) use ($dataCarbon) {
                $entrada = $estagiario->registroPonto->where('ds_motivo', 'Entrada')->first();
                $saida = $estagiario->registroPonto->where('ds_motivo', 'Saída')->first();
                $registrosNaData = $estagiario->registroPonto;
                $ocorrencia = $registrosNaData->whereNotIn('ds_motivo', ['Entrada', 'Saída'])->first();
                if ($ocorrencia) {
                    $textoMotivo = $ocorrencia->ds_motivo;
                } elseif ($entrada && $saida) {
                    $textoMotivo = 'Presente';
                } elseif ($entrada) {
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
                return [
                    'id' => $estagiario->id,
                    'data' => $dataCarbon->format('d/m/Y'),
                    'nome' => $estagiario->nm_estagiarios,
                    'matricula' => $estagiario->nr_matricula,
                    'entrada' => $entrada ? Carbon::parse($entrada->hr_registro)->format('H:i:s') : '',
                    'saida' => $saida ? Carbon::parse($saida->hr_registro)->format('H:i:s') : '',
                    'motivo' => $textoMotivo,
                    'setor' => $estagiario->nm_setor,
                    'total_horas' => $totalHoras,
                ];
            });
            return response()->json(['data' => $dadosFormatados]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function listaEstagiariosDia(Request $request): JsonResponse
    {
        try {
            $query = Estagiario::with([
                'registroPonto' => function ($q) {
                    $q->whereDate('hr_registro', Carbon::today());
                }
            ]);
            if ($request->filled('estagiario_id')) {
                $query->where('id', $request->estagiario_id);
            }
            $estagiarios = $query->get();
            $dadosFormatados = $estagiarios->map(function ($estagiario) {
                $entrada = $estagiario->registroPonto->where('ds_motivo', 'Entrada')->first();
                $saida = $estagiario->registroPonto->where('ds_motivo', 'Saída')->first();
                $registrosHoje = $estagiario->registroPonto;
                $ocorrencia = $registrosHoje->whereNotIn('ds_motivo', ['Entrada', 'Saída'])->first();
                if ($ocorrencia) {
                    $textoMotivo = $ocorrencia->ds_motivo;
                } elseif ($entrada && $saida) {
                    $textoMotivo = 'Presente';
                } elseif ($entrada) {
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
                return [
                    'id' => $estagiario->id,
                    'data' => Carbon::today()->format('d/m/Y'),
                    'nome' => $estagiario->nm_estagiarios,
                    'matricula' => $estagiario->nr_matricula,
                    'entrada' => $entrada ? Carbon::parse($entrada->hr_registro)->format('H:i:s') : '',
                    'saida' => $saida ? Carbon::parse($saida->hr_registro)->format('H:i:s') : '',
                    'motivo' => $textoMotivo,
                    'setor' => $estagiario->nm_setor,
                    'total_horas' => $totalHoras,
                ];
            });
            if ($request->has('motivo') && $request->motivo != '') {
                $dadosFormatados = $dadosFormatados->filter(function ($item) use ($request) {
                    return $item['motivo'] === $request->motivo;
                });
                if ($request->has('estagiario_id') && $request->estagiario_id != '') {
                    $dadosFormatados = $dadosFormatados->filter(function ($item) use ($request) {
                        // Compara o ID do item com o ID que veio do select
                        return $item['id'] == $request->estagiario_id;
                    });
                }
            }
            return response()->json(['data' => $dadosFormatados->values()]);
        } catch (\Exception $e) {
            // Se der erro, retorna o motivo para aparecer no console do navegador
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function pesquisarMes(Request $request): JsonResponse
    {
        try {
            $mes = $request->input('mes');
            $inicioMes = Carbon::parse($mes)->startOfMonth()->format('Y-m-d');
            $fimMes = Carbon::parse($mes)->endOfMonth()->format('Y-m-d');
            
            $queryMes = Estagiario::whereHas('registroPonto', function ($query) use ($inicioMes, $fimMes) {
                $query->whereBetween('hr_registro', [$inicioMes, $fimMes]);
            })->with([
                'registroPonto' => function ($query) use ($inicioMes, $fimMes) {
                    $query->whereBetween('hr_registro', [$inicioMes, $fimMes])
                        ->orderBy('hr_registro', 'asc');
                }
            ])->get();

            $dadosMes = $queryMes->map(function ($estagiario) use ($inicioMes, $fimMes) {
                $periodo = CarbonPeriod::create($inicioMes, $fimMes);
                $registrosPorDia = [];

                foreach ($periodo as $data) {
                    $dataFormatada = $data->format('Y-m-d');
                    
                    $registrosNoDia = $estagiario->registroPonto->filter(function ($ponto) use ($dataFormatada) {
                        return str_starts_with($ponto->hr_registro, $dataFormatada);
                    });

                    $entrada = $registrosNoDia->where('ds_motivo', 'Entrada')->first();
                    $saida = $registrosNoDia->where('ds_motivo', 'Saída')->first();
                    $ocorrencia = $registrosNoDia->whereNotIn('ds_motivo', ['Entrada', 'Saída'])->first();

                    if ($ocorrencia) {
                        $textoMotivo = $ocorrencia->ds_motivo;
                    } elseif ($entrada && $saida) {
                        $textoMotivo = 'Presente';
                    } elseif ($entrada) {
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
                        'data' => $data->format('d/m/Y'),
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

            return response()->json(['data' => array_values($dadosMes->toArray())]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage() . ' na linha ' . $e->getLine()], 500);
        }
    }
    public function pesquisaAno (Request $request){
        try {
            $ano = $request->input('ano');
            $inicioAno = Carbon::parse($ano)->startOfYear()->format('Y-m-d');
            $fimAno = Carbon::parse($ano)->endOfYear()->format('Y-m-d');
            $queryAno = Estagiario::whereHas('registroPonto', function($query) use ($fimAno, $inicioAno){
                $query->whereBetween('hr_registro', [$inicioAno, $fimAno]);
            })->with(['registroPonto' => function($query) use ($inicioAno, $fimAno){
                $query->whereBetween('hr_registro', [$inicioAno, $fimAno])
                    ->orderBy('hr_registro', 'asc');
            }])->get();

            $dadosAno = $queryAno->map(function($estagiario) use ($inicioAno, $fimAno): array {
                $periodo = CarbonPeriod::create($inicioAno, $fimAno);
                $registrosPorDia = [];
                foreach ($periodo as $data) {
                    $dataFormatada = $data->format('Y-m-d');
                    $registrosNoDia = $estagiario->registroPonto->filter(function($ponto) use ($dataFormatada){
                        return str_starts_with($ponto->hr_registro, $dataFormatada);
                    });
                    $entrada = $registrosNoDia->where('ds_motivo', 'Entrada')->first();
                    $saida = $registrosNoDia->where('ds_motivo', 'Saída')->first();
                    $ocorrencia = $registrosNoDia->whereNotIn('ds_motivo', ['Entrada','Saída'])->first();

                    if ($ocorrencia){
                        $textoMotivo = $ocorrencia->ds_motivo;
                    } elseif ($entrada && $saida){
                        $textoMotivo = 'Presente';
                    } elseif ($entrada) {
                        $textoMotivo = 'Em Andamento';
                    } else {
                        $textoMotivo = '---';
                    }

                    $totalHoras = '---';
                    if ($entrada && $saida) {
                        $chegada = Carbon::parse($entrada->hr_registro);
                        $partida = Carbon::parse($saida->hr_registro);
                        $segundos = $chegada->diffInSeconds($partida);
                        $totalHoras = gmdate('H:i:s', $segundos);
                    }

                    $registrosPorDia[] = [
                        'id' => $estagiario->id,
                        'data' => $data->format('d/m/Y'),
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
            return response()->json(['data' => array_values($dadosAno->toArray())]);
        } catch (\Exception $th) {
            return response()->json(['error' => $th->getMessage() . ' na linha ' . $th->getLine()]);
        }
    }
}