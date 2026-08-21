<?php

namespace App\Domains\ControleDePonto\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Domains\Estagiarios\Models\Estagiario;
use App\Domains\ControleDePonto\Models\RegistroPonto;
use App\Domains\ControleDePonto\Models\Turno;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Log;
use App\Domains\Estagiarios\Requests\AtualizarEstagiarioRequest;
use App\Domains\ControleDePonto\Services\PontoService;

class PontoController extends Controller
{
    public function index()
    {
        return view("pages.inicio.inicio");
    }

    public function store(Request $request)
    {
        $cpf = preg_replace('/\D/', '', $request->input('cpf'));
        $estagiario = Estagiario::where('cpf', $cpf)->first();
        if (!$estagiario) {
            session()->flash('erro', "Estagiário não encontrado com o CPF: {$cpf}");
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => "Estagiário não encontrado com o CPF: {$cpf}"
                ], 302);
            }
            return redirect()->back();
        }
        $agora = Carbon::now();
        $inicioPermitido = Carbon::today()->setTimeFromTimeString(config('app.ponto.inicio'));
        $fimPermitido = Carbon::today()->setTimeFromTimeString(config('app.ponto.fim'));
        if (!$agora->between($inicioPermitido, $fimPermitido)) {
            session()->flash('erro', 'Ponto fechado');
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ponto fechado'
                ], 403);
            }
            return redirect()->back();
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
        session()->flash('sucesso', "Ponto de {$motivo} registrado com sucesso!");
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Ponto de {$motivo} registrado com sucesso!"
            ]);
        }
        return redirect()->back();
    }
    public function listaEstagiariosDia(Request $request)
    {
        try {
            $pontoService = new PontoService();
            // Extrai ano e mês do campo 'mes' se ele vier formatado como YYYY-MM
            $ano = $request->input('ano');
            $mes = $request->input('mes');
            if ($mes && strpos($mes, '-') !== false) {
                $partes = explode('-', $mes);
                $ano = $partes[0];
                $mes = $partes[1];
            }
            if (!$ano) {
                $ano = now()->year;
            }
            if (!$mes) {
                $mes = now()->month;
            }

            if ($request->filled('data')) {
                $inicio = Carbon::parse($request->data)->startOfDay();
                $fim = Carbon::parse($request->data)->endOfDay();
            } elseif ($request->filled('inicioSemana') && $request->filled('fimSemana')) {
                $inicio = Carbon::create($ano, $mes, $request->inicioSemana)->startOfDay();
                $fim = Carbon::create($ano, $mes, $request->fimSemana)->endOfDay();
            } elseif ($request->filled('mes')) {
                $inicio = Carbon::createFromDate($ano, $mes, 1)->startOfMonth();
                $fim = Carbon::createFromDate($ano, $mes, 1)->endOfMonth();
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
                        // Registro completo: tem Entrada com Saída correspondente no mesmo dia
                        $q->where('ds_motivo', 'entrada')
                          ->whereExists(function ($query) {
                              $query->select(DB::raw(1))
                                    ->from('registro_ponto as rp_saida')
                                    ->whereColumn('rp_saida.estagiario_id', 'registro_ponto.estagiario_id')
                                    ->where('rp_saida.ds_motivo', 'saida')
                                    ->whereRaw('DATE(rp_saida.hr_registro) = DATE(registro_ponto.hr_registro)');
                          });
                    } elseif ($request->motivo === 'andamento') {
                        // Em andamento: tem Entrada SEM Saída correspondente no mesmo dia
                        $q->where('ds_motivo', 'entrada')
                          ->whereNotExists(function ($query) {
                              $query->select(DB::raw(1))
                                    ->from('registro_ponto as rp_saida')
                                    ->whereColumn('rp_saida.estagiario_id', 'registro_ponto.estagiario_id')
                                    ->where('rp_saida.ds_motivo', 'saida')
                                    ->whereRaw('DATE(rp_saida.hr_registro) = DATE(registro_ponto.hr_registro)');
                          });
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
                            ->whereNull('deleted_at')
                            ->groupBy('estagiario_id', DB::raw('DATE(hr_registro)'));
                    });
                return DataTables::of($queryDoPonto)
                    ->addColumn('nome', function ($row) {
                        return $row->estagiario ? $row->estagiario->nm_estagiarios : 'Sem nome';
                    })
                    ->addColumn('cpf', function ($row) {
                        return $row->estagiario ? $row->estagiario->cpf : 'Sem matrícula';
                    })
                    ->addColumn('setor', function ($row) {
                        return $row->estagiario ? $row->estagiario->nm_setor : 'Sem setor';
                    })
                    ->addColumn('total_horas', function ($row) use ($inicio, $fim, $pontoService) {
                        return $pontoService->calculoHoras($inicio, $fim, $row->estagiario_id);
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

    public function atualizarEstagiario(AtualizarEstagiarioRequest $request, $id)
    {

        $estagiario = Estagiario::findOrFail($id);

        $estagiario->update([
            'nm_estagiarios' => $request->nome,
            'cpf' => $request->cpf,
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

    public function processarQrcode(Request $request): JsonResponse
    {
        $cpf = preg_replace('/\D/', '', $request->input('cpf'));
        $estagiario = Estagiario::where('cpf', $cpf)->first();
        if (!$estagiario) {
            return response()->json([
                'status' => 'erro',
                'mensagem' => 'Estagiário não encontrado com a matrícula: ' . $cpf
            ], 404);
        }
        return response()->json([
            'status' => 'sucesso',
            'data' => 'Estagiário: ' . $estagiario->nm_estagiarios . ' | Matrícula: ' . $cpf
        ]);
    }
}
