<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Estagiario;
use App\Models\RegistroPonto;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Log;


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
            'ds_observacao' => $motivo
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
        $tabela = (new RegistroPonto)->getTable();
        $query = RegistroPonto::query()->from("$tabela as main");
        $query->whereBetween('main.hr_registro', [$inicio, $fim]);
        if ($request->filled('estagiario_id') && $request->estagiario_id != 'todos') {
            $query->where('estagiario_id', $request->estagiario_id);
        }
        $motivoAlvo = $request->filled('motivo') && $request->motivo !== 'todos' ? $request->motivo : null;
        if ($motivoAlvo === 'Presente') {
            $query->whereIn('ds_motivo', ['Entrada', 'Saida']);
        } else if ($motivoAlvo === 'Em Andamento') {
            $query->where('main.ds_motivo', 'Entrada')
                ->whereNotExists(function ($q) use ($inicio, $fim, $tabela) {
                    $q->select(DB::raw(1))
                        ->from("$tabela as rp2")
                        ->whereColumn('rp2.estagiario_id', 'main.estagiario_id')
                        ->where('rp2.ds_motivo', 'Saida')
                        ->whereBetween('rp2.hr_registro', [$inicio, $fim]);
                });
        } elseif (!empty($motivoAlvo)) {
            $query->where('main.ds_motivo', $motivoAlvo);
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
        $query->whereBetween('hr_registro', [$inicio, $fim])
            ->where('ds_motivo', 'Recesso');
        if ($request->filled('estagiario_id') && $request->estagiario_id != 'todos') {
            $query->where('estagiario_id', $request->estagiario_id);
        }
        $qtdRecessos = $query->count();
        return response()->json(['total' => $qtdRecessos]);
    }
    public function relatorioAtestado(Request $request): JsonResponse
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
        $query->whereBetween('hr_registro', [$inicio, $fim])
            ->where('ds_motivo', 'Atestado');
        if ($request->filled('estagiario_id') && $request->estagiario_id != 'todos') {
            $query->where('estagiario_id', $request->estagiario_id);
        }
        $qtdAtestados = $query->count();
        return response()->json(['total' => $qtdAtestados]);
    }
    public function relatorioFolga(Request $request): JsonResponse
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
        $query->whereBetween('hr_registro', [$inicio, $fim])
            ->where('ds_motivo', 'Folga');
        if ($request->filled('estagiario_id') && $request->estagiario_id != 'todos') {
            $query->where('estagiario_id', $request->estagiario_id);
        }
        $qtdFolgas = $query->count();
        // return response()->json(['total' => $qtdFolgas]);


        // $qtdFolgas = RegistroPonto::whereDate('hr_registro', Carbon::today())
        //     ->where('ds_motivo', 'Folga')
        //     ->count();
        return response()->json(['total' => $qtdFolgas]);
    }
    public function relatorioDispensa(Request $request): JsonResponse
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
        $query->whereBetween('hr_registro', [$inicio, $fim])
            ->where('ds_motivo', 'Dispensa');
        if ($request->filled('estagiario_id') && $request->estagiario_id != 'todos') {
            $query->where('estagiario_id', $request->estagiario_id);
        }
        $qtdDispensas = $query->count();
        return response()->json(['total' => $qtdDispensas]);
    }
    public function relatorioFalta(Request $request): JsonResponse
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
        $query->whereBetween('hr_registro', [$inicio, $fim])
            ->where('ds_motivo', 'Falta');
        if ($request->filled('estagiario_id') && $request->estagiario_id != 'todos') {
            $query->where('estagiario_id', $request->estagiario_id);
        }
        $qtdFaltas = $query->count();
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
            $periodo = CarbonPeriod::create($inicio, $fim);
            if ($request->filled('estagiario_id') && $request->estagiario_id != '') {
                $query->where('id', $request->estagiario_id);
            }
            $estagiarios = $query->with([
                'registroPonto' => function ($q) use ($inicio, $fim) {
                    $q->whereBetween('hr_registro', [$inicio->format('Y-m-d H:i:s'), $fim->format('Y-m-d H:i:s')])
                        ->orderBy('hr_registro', 'asc');
                }
            ])->get();

            $dadosFormatados = $estagiarios->flatMap(function ($estagiario) use ($periodo) {
                $registrosPorDia = [];
                $pontosAgrupados = $estagiario->registroPonto->groupBy(function ($ponto) {
                    return Carbon::parse($ponto->hr_registro)->format('Y-m-d');
                });
                foreach ($periodo as $dataLoop) {
                    $dataFormatada = $dataLoop->format('Y-m-d');
                    $registrosNoDia = $pontosAgrupados->get($dataFormatada, collect());
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
                    $obsReal = '';
                    if ($ocorrencia) {
                        $obsReal = $ocorrencia->ds_observacao;
                    } elseif ($entrada && $entrada->ds_observacao) {
                        $obsReal = $entrada->ds_observacao;
                    } elseif ($saida && $saida->ds_observacao) {
                        $obsReal = $saida->ds_observacao;
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
                        'ds_observacao' => $obsReal ?: '---'
                    ];
                }
                return $registrosPorDia;
            });
            if ($request->has('motivo') && $request->motivo != '') {
                $dadosFormatados = $dadosFormatados->filter(function ($item) use ($request) {
                    return $item['motivo'] === $request->motivo;
                });
            }
            return response()->json(['data' => $dadosFormatados->values()]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'data' => 'required|date',
            'entrada' => 'nullable',
            'saida' => 'nullable',
            'matricula' => 'required|max:14',
            'nome' => 'required',
            'motivo' => 'nullable|string',
            'setor' => 'required',
            'observacao' => 'nullable|string'
        ]);

        $estagiario = Estagiario::findOrFail($id);

        $estagiario->update([
            'nm_estagiarios' => $request->nome,
            'nr_matricula' => $request->matricula,
            'nm_setor' => $request->setor
        ]);

        $data = $request->data;

        $estagiario->registroPonto()
            ->whereDate('hr_registro', $data)
            ->update([
                'ds_motivo' => $request->motivo,
                'ds_observacao' => $request->observacao
            ]);

        if ($request->filled('entrada')) {
            $timestampEntrada = $data . ' ' . $request->entrada;

            $estagiario->registroPonto()
                ->where('ds_motivo', 'Entrada')
                ->whereDate('hr_registro', $data)
                ->update([
                    'hr_registro' => $timestampEntrada
                ]);
        }

        if ($request->filled('saida')) {
            $timestampSaida = $data . ' ' . $request->saida;

            $estagiario->registroPonto()
                ->where('ds_motivo', 'Saida')
                ->whereDate('hr_registro', $data)
                ->update([
                    'hr_registro' => $timestampSaida
                ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Estagiário atualizado com sucesso',
            'data' => $estagiario
        ]);
    }

    public function listagemCadastrados(Request $request)
    {
        try {
            $query = Estagiario::query();

            return DataTables::of($query)
                ->addColumn('action', function ($row) {
                    return '
                    <div class="d-flex justify-content-center gap-2">
                        <button class="btn btn-sm btn-success btn-gerar-qr" data-identificador="' . $row->id . '" data-bs-toggle="modal" data-bs-target="#qrModalCadastro" title="Gerar QR Code">
                            <i class="bi bi-qr-code"></i>
                        </button>
                        <button class="btn btn-sm btn-primary btn-editar-estagiario" data-identificador="' . $row->id . '" data-bs-toggle="modal" data-bs-target="#modalEditarEstagiario">
                            <i class="bi bi-pencil-square"></i>
                        </button>
                        <button class="btn btn-sm btn-danger btn-excluir-estagiario" data-identificador="' . $row->id . '" title="Excluir">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>';
                })
                ->rawColumns(['action'])
                ->make(true);

        } catch (\Exception $e) {
            Log::error("Erro na listagem de cadastrados: " . $e->getMessage());

            return response()->json([
                'error' => 'Erro interno ao carregar a listagem.',
                'message' => $e->getMessage()
            ], 500);
        }
    }


    public function storeEstagiario(Request $request)
    {

        try {
            $request->validate([

                'nm_estagiarios' => 'required|string|max:100',
                'nr_matricula' => 'required|string|max:14|unique:estagiarios,nr_matricula',
                'nm_setor' => 'required|string|max:255',
                'nr_telefone' => 'required|string|max:11|unique:estagiarios,nr_telefone',
                'nm_email' => 'required|email|max:255|unique:estagiarios,nm_email',
            ]);

            $estagiario = Estagiario::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Estagiario cadastrado com sucesso!',
                'dados' => $estagiario
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação!',
                'errors' => $e->errors()
            ], 422);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Erro',
                'data' => $th
            ]);
        }
    }


    public function updateCadastro(Request $request, $id)
    {

        $estagiario = Estagiario::where('id', $id)->firstOrFail();

        if (!$estagiario) {
            return response()->json(['message' => 'Estagiário não encontrado'], 404);
        }

        $request->validate([
            'nome' => 'required|string|max:100,' . $estagiario->id,
            'cpf' => 'required|string|max:14|unique:estagiarios,nr_matricula,' . $estagiario->id,
            'setor' => 'required|string|max:255,' . $estagiario->id,
            'telefone' => 'required|string|max:11|unique:estagiarios,nr_telefone,' . $estagiario->id,
            'email' => 'required|email|max:255|unique:estagiarios,nm_email,' . $estagiario->id,
        ]);

        $estagiario->update([
            'nm_estagiarios' => $request->nome,
            'nr_matricula' => $request->cpf,
            'nm_setor' => $request->setor,
            'nr_telefone' => $request->telefone,
            'nm_email' => $request->email
        ]);
        return response()->json([
            'status' => 'success',
            'message' => 'Estagiario atualizado com sucesso!'
        ]);


    }

}