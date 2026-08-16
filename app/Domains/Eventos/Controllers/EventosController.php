<?php

namespace App\Domains\Eventos\Controllers;

use Illuminate\Http\Request;
use App\Domains\Estagiarios\Models\Estagiario;
use App\Domains\ControleDePonto\Models\RegistroPonto;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class EventosController extends Controller
{
    public function ListaEstagiariosEventos(Request $request)
    {
        try {
            $query = Estagiario::query()->where('ds_situacao', true);

            return DataTables::of($query)
                ->addColumn('action', function ($row) {
                    return '
                    <div class="d-flex justify-content-center gap-2">
                        <button class="btn btn-sm btn-success btn-gerar-evento rounded-3" data-identificador="' . $row->id . '" data-nome="' . htmlspecialchars($row->nm_estagiarios) . '" title="Gerar Ocorrência">
                            <img src="/icons/plus.svg" width="20" height="20" style="vertical-align: middle; filter: brightness(0) invert(1);">
                        </button>
                        <button class="btn btn-sm btn-danger btn-listar-eventos rounded-3" data-identificador="' . $row->id . '" data-nome="' . htmlspecialchars($row->nm_estagiarios) . '" title="Visualizar / Excluir Ocorrências">
                            <img src="/icons/trash.svg" width="20" height="20" style="vertical-align: middle; filter: brightness(0) invert(1);">
                        </button>
                    </div>';
                })
                ->rawColumns(['action'])
                ->make(true);

        } catch (\Exception $e) {
            Log::error("Erro na listagem de cadastrados para eventos: " . $e->getMessage());

            return response()->json([
                'error' => 'Erro interno ao carregar a listagem.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function storeEvento(Request $request)
    {
        try {
            $request->validate([
                'estagiario_id' => 'required|exists:estagiarios,id',
                'data_inicio' => 'required|date',
                'data_fim' => 'required|date|after_or_equal:data_inicio',
                'motivo' => 'required|in:falta,dispensa,folga,atestado,recesso,correcao',
                'observacao' => 'nullable|string|max:255',
                'is_abonado' => 'nullable|boolean',
                'hora_entrada' => 'nullable|date_format:H:i',
                'hora_saida' => 'nullable|date_format:H:i',
            ]);

            if ($request->motivo === 'correcao') {
                if (!$request->filled('hora_entrada') && !$request->filled('hora_saida')) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'hora_entrada' => ['Forneça pelo menos o horário de entrada ou de saída para a correção.'],
                        'hora_saida' => ['Forneça pelo menos o horário de entrada ou de saída para a correção.']
                    ]);
                }
            }

            $eventoService = new \App\Domains\Eventos\Services\EventoService();
            $eventoService->gerarOcorrenciaEmMassa($request->all(), $request->ip());

            return response()->json([
                'success' => true,
                'message' => 'Evento(s) gerado(s) com sucesso!'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação!',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error("Erro ao salvar evento: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro interno ao salvar evento: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getEventosEstagiario(Request $request, $id)
    {
        try {
            $estagiario = Estagiario::findOrFail($id);
            $query = RegistroPonto::where('estagiario_id', $id)
                ->where(function ($q) {
                    $q->whereIn('ds_motivo', ['falta', 'dispensa', 'folga', 'atestado', 'recesso'])
                          ->orWhere(function ($subQuery) {
                              $subQuery->whereIn('ds_motivo', ['entrada', 'saida'])
                                       ->where('ds_observacao', 'LIKE', 'Correção%');
                          });
                });

            if ($request->has('incluir_excluidos') && $request->incluir_excluidos == 'true') {
                $query->withTrashed();
            }

            $eventos = $query->orderBy('hr_registro', 'desc')
                ->get()
                ->map(function ($row) {
                    $motivoLabel = ucfirst($row->ds_motivo);
                    
                    if (in_array($row->ds_motivo, ['entrada', 'saida'])) {
                        $horaFormatada = Carbon::parse($row->hr_registro)->format('H:i');
                        $motivoLabel .= " às " . $horaFormatada;
                    } elseif (in_array($row->ds_motivo, ['atestado', 'dispensa'])) {
                        $motivoLabel .= $row->is_abonado ? ' (Abonado)' : ' (Descontado)';
                    }
                    
                    return [
                        'id' => $row->id,
                        'tipo_bruto' => $row->ds_motivo,
                        'motivo' => $motivoLabel,
                        'data' => Carbon::parse($row->hr_registro)->format('d/m/Y'),
                        'observacao' => $row->ds_observacao ?? '-',
                        'excluido' => $row->trashed(),
                    ];
                });

            return response()->json([
                'success' => true,
                'estagiario' => $estagiario->nm_estagiarios,
                'eventos' => $eventos
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar eventos: ' . $e->getMessage()
            ], 500);
        }
    }

    public function verificarPeriodo(Request $request, $id)
    {
        try {
            $request->validate([
                'inicio' => 'required|date',
                'fim' => 'required|date|after_or_equal:inicio',
            ]);

            $inicio = Carbon::parse($request->inicio)->startOfDay();
            $fim = Carbon::parse($request->fim)->endOfDay();

            $registros = RegistroPonto::where('estagiario_id', $id)
                ->whereBetween('hr_registro', [$inicio, $fim])
                ->orderBy('hr_registro', 'asc')
                ->get()
                ->map(function ($row) {
                    $dataFormatada = Carbon::parse($row->hr_registro)->format('d/m/Y');
                    $horaFormatada = Carbon::parse($row->hr_registro)->format('H:i');
                    
                    if (in_array($row->ds_motivo, ['entrada', 'saida'])) {
                        $label = ucfirst($row->ds_motivo) . " às " . $horaFormatada;
                    } else {
                        $label = ucfirst($row->ds_motivo);
                        if (in_array($row->ds_motivo, ['atestado', 'dispensa'])) {
                            $label .= $row->is_abonado ? ' (Abonado)' : ' (Descontado)';
                        }
                    }

                    return [
                        'id' => $row->id,
                        'data' => $dataFormatada,
                        'label' => $label,
                    ];
                });

            return response()->json([
                'success' => true,
                'registros' => $registros
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao verificar período: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroyEvento($id)
    {
        try {
            $evento = RegistroPonto::findOrFail($id);
            $evento->delete();

            return response()->json([
                'success' => true,
                'message' => 'Registro excluído com sucesso!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao excluir registro: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroyEventosLote(Request $request)
    {
        try {
            $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'exists:registro_ponto,id'
            ]);

            RegistroPonto::whereIn('id', $request->ids)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Ocorrências excluídas com sucesso!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao excluir ocorrências: ' . $e->getMessage()
            ], 500);
        }
    }
}
