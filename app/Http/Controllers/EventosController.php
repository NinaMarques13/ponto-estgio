<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Estagiario;
use App\Models\RegistroPonto;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
                            <img src="' . asset('icons/plus.svg') . '" width="20" height="20" style="vertical-align: middle; filter: brightness(0) invert(1);">
                        </button>
                        <button class="btn btn-sm btn-danger btn-listar-eventos rounded-3" data-identificador="' . $row->id . '" data-nome="' . htmlspecialchars($row->nm_estagiarios) . '" title="Visualizar / Excluir Ocorrências">
                            <img src="' . asset('icons/trash.svg') . '" width="20" height="20" style="vertical-align: middle; filter: brightness(0) invert(1);">
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

            $inicio = Carbon::parse($request->data_inicio);
            $fim = Carbon::parse($request->data_fim);
            $period = CarbonPeriod::create($inicio, $fim);

            DB::transaction(function () use ($period, $request) {
                foreach ($period as $date) {
                    if ($request->motivo === 'correcao') {
                        if ($request->filled('hora_entrada')) {
                            // Deleta somente entrada existente no mesmo dia
                            RegistroPonto::where('estagiario_id', $request->estagiario_id)
                                ->whereDate('hr_registro', $date->format('Y-m-d'))
                                ->where('ds_motivo', 'entrada')
                                ->delete();

                            $dataEntrada = Carbon::parse($date->format('Y-m-d') . ' ' . $request->hora_entrada);
                            RegistroPonto::create([
                                'estagiario_id' => $request->estagiario_id,
                                'ds_motivo' => 'entrada',
                                'hr_registro' => $dataEntrada,
                                'ip_registro' => $request->ip(),
                                'ds_observacao' => $request->observacao ? 'Correção: ' . $request->observacao : 'Correção de ponto (entrada)',
                                'is_abonado' => false,
                            ]);
                        }

                        if ($request->filled('hora_saida')) {
                            // Deleta somente saída existente no mesmo dia
                            RegistroPonto::where('estagiario_id', $request->estagiario_id)
                                ->whereDate('hr_registro', $date->format('Y-m-d'))
                                ->where('ds_motivo', 'saida')
                                ->delete();

                            $dataSaida = Carbon::parse($date->format('Y-m-d') . ' ' . $request->hora_saida);
                            RegistroPonto::create([
                                'estagiario_id' => $request->estagiario_id,
                                'ds_motivo' => 'saida',
                                'hr_registro' => $dataSaida,
                                'ip_registro' => $request->ip(),
                                'ds_observacao' => $request->observacao ? 'Correção: ' . $request->observacao : 'Correção de ponto (saída)',
                                'is_abonado' => false,
                            ]);
                        }
                    } else {
                        // Deleta registros de ponto existentes no mesmo dia
                        RegistroPonto::where('estagiario_id', $request->estagiario_id)
                            ->whereDate('hr_registro', $date->format('Y-m-d'))
                            ->delete();

                        // Determina se a ocorrência é abonada
                        $isAbonado = false;
                        if (in_array($request->motivo, ['recesso', 'folga'])) {
                            $isAbonado = true;
                        } elseif (in_array($request->motivo, ['atestado', 'dispensa'])) {
                            $isAbonado = $request->has('is_abonado') ? (bool) $request->is_abonado : false;
                        }

                        // Cria o registro da ocorrência
                        RegistroPonto::create([
                            'estagiario_id' => $request->estagiario_id,
                            'ds_motivo' => $request->motivo,
                            'hr_registro' => $date->startOfDay(),
                            'ip_registro' => $request->ip(),
                            'ds_observacao' => $request->observacao,
                            'is_abonado' => $isAbonado,
                        ]);
                    }
                }
            });

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
