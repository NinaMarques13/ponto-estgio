<?php

namespace App\Domains\Estagiarios\Controllers;

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
                            <img src="/icons/qr-code.svg" width="20" height="20" style="vertical-align: middle; filter: brightness(0) invert(1);">
                        </button>
                        <button class="btn btn-sm btn-primary btn-editar-estagiario rounded-3" data-identificador="' . $row->id . '" data-bs-toggle="modal" data-bs-target="#modalEditarEstagiario">
                            <img src="/icons/square-pen.svg" width="20" height="20" style="vertical-align: middle; filter: brightness(0) invert(1);">
                        </button>
                        <button class="btn btn-sm btn-danger btn-excluir-estagiario rounded-3" data-identificador="' . $row->id . '" title="Excluir">
                            <img src="/icons/trash.svg" width="20" height="20" style="vertical-align: middle; filter: brightness(0) invert(1);">
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
                'nome' => 'required|string|max:100',
                'cpf' => 'required|string|max:14|' . Rule::unique('estagiarios', 'cpf')->where('ds_situacao', 1),
                'setor' => 'required|string|max:255',
                'telefone' => 'required|string|max:11|' . Rule::unique('estagiarios', 'nr_telefone')->where('ds_situacao', 1),
                'email' => 'required|email|max:255|' . Rule::unique('estagiarios', 'nm_email')->where('ds_situacao', 1),
            ]);

            $estagiarioService = new \App\Domains\Estagiarios\Services\EstagiarioService();
            $estagiario = $estagiarioService->criarOuAtualizar($request->all());
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
        $request->validate([
            'nome' => 'required|string|max:100',
            'cpf' => 'required|string|max:14|unique:estagiarios,cpf,' . $id,
            'setor' => 'required|string|max:255',
            'telefone' => 'required|string|max:11|unique:estagiarios,nr_telefone,' . $id,
            'email' => 'required|email|max:255|unique:estagiarios,nm_email,' . $id,
        ]);

        $estagiarioService = new \App\Domains\Estagiarios\Services\EstagiarioService();
        $estagiarioService->atualizar($id, $request->all());
        return response()->json([
            'status' => 'success',
            'message' => 'Estagiario atualizado com sucesso!'
        ]);
    }

    public function desativarCadastro(Request $request, $id)
    {
        $estagiarioService = new \App\Domains\Estagiarios\Services\EstagiarioService();
        $estagiarioService->desativar($id);
        return response()->json(['message' => 'Estagiário excluído com sucesso!']);
    }

}
