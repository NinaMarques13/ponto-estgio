<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Estagiario;
use App\Models\RegistroPonto;
use app\Models\Turno;
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
        $motivo = 'entrada';
        if ($ultimoRegistro && $ultimoRegistro->ds_motivo == 'entrada') {
            $motivo = 'saida';
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
        $motivoAlvo = $request->filled('motivo') ? $request->motivo : 'entrada';
        if ($motivoAlvo === 'Presente') {
            $query->whereHas('registroPonto', function ($q) use ($inicio, $fim) {
                $q->whereBetween('hr_registro', [$inicio, $fim])
                    ->where('ds_motivo', 'entrada');
            });
            $query->whereHas('registroPonto', function ($q) use ($inicio, $fim) {
                $q->whereBetween('hr_registro', [$inicio, $fim])
                    ->where('ds_motivo', 'saida');
            });
        } elseif ($motivoAlvo === 'Em Andamento') {
            $query->whereHas('registroPonto', function ($q) use ($inicio, $fim) {
                $q->whereBetween('hr_registro', [$inicio, $fim])
                    ->where('ds_motivo', 'entrada');
            });
            $query->whereDoesntHave('registroPonto', function ($q) use ($inicio, $fim) {
                $q->whereBetween('hr_registro', [$inicio, $fim])
                    ->where('ds_motivo', 'saida');
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
            $query->whereIn('ds_motivo', ['entrada', 'saida']);
        } else if ($motivoAlvo === 'Em Andamento') {
            $query->where('main.ds_motivo', 'entrada')
                ->whereNotExists(function ($q) use ($inicio, $fim, $tabela) {
                    $q->select(DB::raw(1))
                        ->from("$tabela as rp2")
                        ->whereColumn('rp2.estagiario_id', 'main.estagiario_id')
                        ->where('rp2.ds_motivo', 'saida')
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
            ->where('ds_motivo', 'recesso');
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
            ->where('ds_motivo', 'atestado');
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
            ->where('ds_motivo', 'folga');
        if ($request->filled('estagiario_id') && $request->estagiario_id != 'todos') {
            $query->where('estagiario_id', $request->estagiario_id);
        }
        $qtdFolgas = $query->count();
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
            ->where('ds_motivo', 'dispensa');
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
            ->where('ds_motivo', 'falta');
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
            $baseQuery = RegistroPonto::whereBetween('hr_registro', [$inicio, $fim])
                ->when($request->filled('estagiario_id'), function ($q) use ($request) {
                    $q->where('estagiario_id', $request->estagiario_id);
                })
                ->when($request->filled('motivo'), function ($q) use ($request) {
                    if ($request->motivo === 'presente') {
                        $q->whereIn('ds_motivo', ['entrada', 'saida']);
                    } elseif ($request->motivo === 'andamento') {
                        $q->where('ds_motivo', 'entrada');
                    } else {
                        $q->where('ds_motivo', $request->motivo);
                    }
                });
            if ($request->ajax()) {
                $queryDoPonto = (clone $baseQuery)->with(['estagiario'])
                    ->whereBetween('hr_registro', [$inicio, $fim])
                    ->where('ds_motivo', '!=', 'saida')
                    ->whereIn('registro_ponto.id', function ($query) use ($inicio, $fim) {
                        $query->select(DB::raw('MIN(id)'))
                            ->from('registro_ponto')
                            ->whereBetween('hr_registro', [$inicio, $fim])
                            ->where('ds_motivo', '!=', 'saida')
                            ->groupBy('estagiario_id', DB::raw('DATE(hr_registro)'));
                    });
                return DataTables::of($queryDoPonto)
                    ->addColumn('nome', function ($row) {
                        return $row->estagiario ? $row->estagiario->nm_estagiarios : 'Sem nome';
                    })
                    ->addColumn('matricula', function ($row) {
                        return $row->estagiario ? $row->estagiario->nr_matricula : 'Sem matrícula';
                    })
                    ->addColumn('setor', function ($row) {
                        return $row->estagiario ? $row->estagiario->nm_setor : 'Sem setor';
                    })
                    ->addColumn('total_horas', function ($row) use ($inicio, $fim) {
                        return $this->calculoHoras($inicio, $fim, $row->estagiario_id);
                    })
                    ->addColumn('data', function ($row) {
                        return Carbon::parse($row->hr_registro)->format('d/m/Y');
                    })
                    ->addColumn('motivo', function ($row) {
                        return ($row->ds_motivo);
                    })
                    ->addColumn('entrada', function ($row) {
                        if ($row->ds_motivo !== 'entrada') {
                            return '--:--';
                        }
                        return Carbon::parse($row->hr_registro)->format('H:i');
                    })
                    ->addColumn('saida', function ($row) {
                        if ($row->ds_motivo !== 'entrada') {
                            return '--:--';
                        }
                        $diaDoPonto = Carbon::parse($row->hr_registro)->format('Y-m-d');
                        $saida = RegistroPonto::where('estagiario_id', $row->estagiario_id)
                            ->where('ds_motivo', 'saida')
                            ->whereDate('hr_registro', $diaDoPonto)
                            ->first();
                        return $saida ? Carbon::parse($saida->hr_registro)->format('H:i') : 'Sem saída';
                    })
                    ->addColumn('observacao', function ($row) {
                        return $row->ds_observacao;
                    })
                    ->addColumn('action', function ($row) {
                        return '<button class="btn btn-primary btn-sm editar-btn" data-id="' . $row->id . '">Editar</button>';
                    })
                    ->with('cards', [
                        'presentes' => Estagiario::whereHas('registroPonto', function ($q) use ($inicio, $fim) {
                            $q->whereBetween('hr_registro', [$inicio, $fim])->where('ds_motivo', 'entrada');
                        })->count(),
                        'registros' => (clone $baseQuery)->count(),
                        'recessos' => (clone $baseQuery)->where('ds_motivo', 'recesso')->count(),
                        'atestados' => (clone $baseQuery)->where('ds_motivo', 'atestado')->count(),
                        'folgas' => (clone $baseQuery)->where('ds_motivo', 'folga')->count(),
                        'dispensa' => (clone $baseQuery)->where('ds_motivo', 'dispensa')->count(),
                        'falta' => (clone $baseQuery)->where('ds_motivo', 'falta')->count(),
                    ])
                    ->rawColumns(['action'])
                    ->make(true);
            }
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao processar dados: ' . $e->getMessage()
            ], 400);
        }
    }
    private function calculoHoras($inicio, $fim, $id)
    {
        $pontos = RegistroPonto::where('estagiario_id', $id)
            ->whereBetween('hr_registro', [$inicio, $fim])
            ->orderBy('hr_registro', 'asc')
            ->get()
            ->groupBy(function ($item) {
                return Carbon::parse($item->hr_registro)->format('Y-m-d');
            });
        $totalHoras = 0;
        foreach ($pontos as $registros) {
            $entrada = $registros->filter(fn($r) => in_array($r->ds_motivo, ['entrada', 'recesso', 'dispensa', 'folga']))->first();
            $saida = $registros->filter(fn($r) => in_array($r->ds_motivo, ['saida', 'recesso', 'dispensa', 'folga']))->last();
            if ($entrada && $saida) {
                $inicioPonto = Carbon::parse($entrada->hr_registro);
                $fimPonto = Carbon::parse($saida->hr_registro);
                if ($fimPonto->gt($inicioPonto)) {
                    $totalHoras += $inicioPonto->diffInMinutes($fimPonto);
                }
            }
        }
        $horas = floor($totalHoras / 60);
        $minutos = $totalHoras % 60;

        return sprintf('%02dh%02dm', $horas, $minutos);
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
            $timestampentrada = $data . ' ' . $request->entrada;

            $estagiario->registroPonto()
                ->where('ds_motivo', 'entrada')
                ->whereDate('hr_registro', $data)
                ->update([
                    'hr_registro' => $timestampentrada
                ]);
        }

        if ($request->filled('saida')) {
            $timestampsaida = $data . ' ' . $request->saida;

            $estagiario->registroPonto()
                ->where('ds_motivo', 'saida')
                ->whereDate('hr_registro', $data)
                ->update([
                    'hr_registro' => $timestampsaida
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
                'nr_matricula' => 'required|string|max:14|'. Rule::unique('estagiarios')->where('ds_situacao', 1),
                'nm_setor' => 'required|string|max:255',
                'nr_telefone' => 'required|string|max:11|'. Rule::unique('estagiarios')->where('ds_situacao', 1),
                'nm_email' => 'required|email|max:255|'. Rule::unique('estagiarios')->where('ds_situacao', 1),
            ]);

            $estagiario = Estagiario::updateOrCreate(

            ['nr_matricula' => $request->nr_matricula],

            [
                'nm_estagiarios' => $request->nm_estagiarios,
                'nm_setor' => $request->nm_setor,
                'nr_telefone' => $request->nr_telefone,
                'nm_email' => $request->nm_email,
                'ds_situacao' => 1
            ]
        );
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
    public function desativarCadastro (Request $request, $id)
    {
        $estagiario = Estagiario::findOrFail($id);

        $estagiario->update([
            'ds_situacao' => false
        ]);
        return response()->json(['message' => 'Estagiário excluído com sucesso!']);
    }
}