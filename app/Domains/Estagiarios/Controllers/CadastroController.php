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
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle;"><rect width="5" height="5" x="3" y="3" rx="1"/><rect width="5" height="5" x="16" y="3" rx="1"/><rect width="5" height="5" x="3" y="16" rx="1"/><path d="M21 16h-3a2 2 0 0 0-2 2v3"/><path d="M21 21v.01"/><path d="M12 7v3a2 2 0 0 1-2 2H7"/><path d="M3 12h.01"/><path d="M12 3h.01"/><path d="M12 16v.01"/><path d="M16 12h1"/><path d="M21 12v.01"/><path d="M12 21v-1"/></svg>
                        </button>
                        <button class="btn btn-sm btn-primary btn-editar-estagiario rounded-3" data-identificador="' . $row->id . '" data-bs-toggle="modal" data-bs-target="#modalEditarEstagiario" title="Editar Cadastro">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle;"><path d="M12 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.375 2.625a1 1 0 0 1 3 3l-9.013 9.014a2 2 0 0 1-.853.505l-2.873.84a.5.5 0 0 1-.62-.62l.84-2.873a2 2 0 0 1 .506-.852z"/></svg>
                        </button>
                        <button class="btn btn-sm btn-danger btn-excluir-estagiario rounded-3" data-identificador="' . $row->id . '" title="Excluir">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle;"><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
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
