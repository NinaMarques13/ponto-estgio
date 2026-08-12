<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Estagiario;
use App\Models\RegistroPonto;
use App\Models\Turno;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Log;

class CadastroController extends Controller
{
    public function listagemCadastrados(Request $request)
    {
        try {
            $query = Estagiario::query()->where('ds_situacao', true);

            return DataTables::of($query)
                ->addColumn('action', function ($row) {
                    return '
                    <div class="d-flex justify-content-center gap-2">
                        <button class="btn btn-sm btn-success btn-gerar-qr rounded-3" data-identificador="' . $row->id . '" data-bs-toggle="modal" data-bs-target="#qrModalCadastro" title="Gerar QR Code">
                            <img src="' . asset('icons/qr-code.svg') . '" width="20" height="20" style="vertical-align: middle; filter: brightness(0) invert(1);">
                        </button>
                        <button class="btn btn-sm btn-primary btn-editar-estagiario rounded-3" data-identificador="' . $row->id . '" data-bs-toggle="modal" data-bs-target="#modalEditarEstagiario">
                            <img src="' . asset('icons/square-pen.svg') . '" width="20" height="20" style="vertical-align: middle; filter: brightness(0) invert(1);">
                        </button>
                        <button class="btn btn-sm btn-danger btn-excluir-estagiario rounded-3" data-identificador="' . $row->id . '" title="Excluir">
                            <img src="' . asset('icons/trash.svg') . '" width="20" height="20" style="vertical-align: middle; filter: brightness(0) invert(1);">
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
                'nr_matricula' => 'required|string|max:14|' . Rule::unique('estagiarios')->where('ds_situacao', 1),
                'nm_setor' => 'required|string|max:255',
                'nr_telefone' => 'required|string|max:11|' . Rule::unique('estagiarios')->where('ds_situacao', 1),
                'nm_email' => 'required|email|max:255|' . Rule::unique('estagiarios')->where('ds_situacao', 1),
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

    public function desativarCadastro(Request $request, $id)
    {
        $estagiario = Estagiario::findOrFail($id);

        $estagiario->update([
            'ds_situacao' => false
        ]);
        return response()->json(['message' => 'Estagiário excluído com sucesso!']);
    }

}
