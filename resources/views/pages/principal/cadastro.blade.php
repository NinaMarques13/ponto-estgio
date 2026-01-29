@extends('pages.templates.layout')
@section('content')
    <div class="tela-cadastro">
        <div class="container my-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="text-success fw-normal">Cadastro de Estagiários</h5>
                <button class="btn btn-primary px-4 py-2">Adicionar estagiário</button>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label text-secondary">Nome</label>
                    <input type="text" class="form-control bg-light" placeholder="Buscar por nome">
                </div>

                <div class="col-md-6">
                    <label class="form-label text-secondary">CPF (Matrícula)</label>
                    <input type="text" class="form-control bg-light" placeholder="Buscar por CPF">
                </div>
            </div>

            <div class="table-responsive shadow-sm">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-header-custom">
                        <tr>
                            <th>Nome</th>
                            <th>CPF (Matrícula)</th>
                            <th>Setor</th>
                            <th>Telefone</th>
                            <th>Email</th>
                            <th class="text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-2">
                                    <button class="btn btn-purple btn-sm text-white border-0 btn-gerar-qr"
                                        data-bs-toggle="modal"
                                        data-bs-target="#qrModalCadastro"
                                        data-identificador="">
                                        <i class="bi bi-qr-code"></i>
                                    </button>
                                    <button class="btn btn-primary btn-sm border-0">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <button class="btn btn-danger btn-sm border-0">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="modal fade" id="qrModalCadastro" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">QR Code do Estagiario</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body d-flex flex-colum align-items-center justify-content-center text-center">
                            <div id="qrcodeCadastro"></div>
                        </div>
                        <div class="modal-footer justify-content-center">
                            <button type="button" class="btn btn-success" onclick="window.print();">
                                <i class="bi bi-printer"></i> Imprimir QR Code
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between mt-4">
                <a href="#" class="text-success text-decoration-none fw-bold border-bottom border-success">Ir para Ponto de
                    Registro</a>
                <a href="#" class="text-danger text-decoration-none fw-bold">Sair</a>
            </div>
        </div>
    </div>
@endsection