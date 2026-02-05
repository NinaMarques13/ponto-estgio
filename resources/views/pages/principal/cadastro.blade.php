@extends('pages.templates.layout')
@section('content')
    <div class="tela-cadastro">
        <div class="container my-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="text-success fw-normal">Cadastro de Estagiários</h5>
                <button class="btn btn-primary px-4 py-2" data-bs-toggle="modal" data-bs-target="#modalAdicionarEstagiario">Adicionar estagiário</button>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label text-secondary">Nome</label>
                    <input type="text" id="buscaNome" class="form-control bg-light" placeholder="Buscar por nome">
                </div>

                <div class="col-md-6">
                    <label class="form-label text-secondary">CPF (Matrícula)</label>
                    <input type="text" id="buscaCPF" class="form-control bg-light" placeholder="Buscar por CPF">
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
                    <tbody id="tabela-estagiarios" class="table">
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
                                    <button class="btn btn-primary btn-sm border-0 btn-editar-estagiario">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <button class="btn btn-danger btn-sm border-0 btn-excluir-estagiario">
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
                        <div class="modal-body d-flex flex-column align-items-center justify-content-center text-center">
                        <div class="modal-body d-flex flex-column align-items-center justify-content-center text-center">
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
            
            <div class="modal fade" id="modalAdicionarEstagiario" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header bg-light">
                            <h5 class="modal-title text-success">Cadastrar Novo Estagiário</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form id="formAdicionarEstagiario">
                            <div class="modal-body">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label fw-bold text-secondary">Nome <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="nome_cadastro" name="nome" placeholder="Nome completo" required>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-bold text-secondary">CPF (Matrícula) <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="cpf_cadastro" name="cpf" placeholder="000.000.000-00" required>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-bold text-secondary">Setor <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="setor_cadastro" name="setor" placeholder="Ex: DGP / TI" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold text-secondary">Telefone</label>
                                        <input type="tel" class="form-control" id="telefone_cadastro" name="telefone" placeholder="(00) 00000-0000">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold text-secondary">Email</label>
                                        <input type="email" class="form-control" id="email_cadastro" name="email" placeholder="exemplo@pm.pr.gov.br">
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer bg-light">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-success px-4">Salvar Cadastro</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    </div>
@endsection