@extends('pages.templates.layout')
@section('content')
    <div class="tela-cadastro">
        <div class="container my-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="text-success fw-normal">Cadastro de Estagiários</h5>
                <button class="btn btn-bd-primary px-4 py-2" data-bs-toggle="modal"
                    data-bs-target="#modalAdicionarEstagiario">Cadastrar estagiário</button>
            </div>

            <!-- <div class="row g-3 mb-4">
                                        <div class="col-md-6">
                                            <label class="form-label text-secondary">Nome</label>
                                            <input type="text" id="buscaNome" class="form-control bg-light mb-3" placeholder="Buscar por nome">
                                            <button type="button" class="btn btn-primary">Buscar</button>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label text-secondary">CPF (Matrícula)</label>
                                            <input type="text" id="buscaCPF" class="form-control bg-light mb-3" placeholder="Buscar por CPF">
                                            <button type="button" class="btn btn-primary">Buscar</button>
                                        </div>
                                    </div> -->

            <div class="table-responsive shadow-sm">
                <table id="tabela-estagiarios-cadastrados" class="table table-bordered align-middle mb-4 mt-4 w-100">
                    <thead class="table-header-custom">
                        <tr>
                            <th class="text-center">id</th>
                            <th class="text-center">Nome</th>
                            <th class="text-center">CPF (Matrícula)</th>
                            <th class="text-center">Setor</th>
                            <th class="text-center">Telefone</th>
                            <th class="text-center">Email</th>
                            <th class="text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="modal fade" id="qrModalCadastro" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title text-pmp-azul">QR Code do Estagiario</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body d-flex flex-column align-items-center justify-content-center text-center">
                        <div id="qrcodeCadastro"></div>
                    </div>
                    <div class="modal-footer justify-content-center">
                        <button type="button" class="btn btn-bd-primary" onclick="window.print();">
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
                        <h5 class="modal-title text-pmp-azul">Cadastrar Novo Estagiário</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="formAdicionarEstagiario">
                        @csrf
                        @method('put')
                        <div class="modal-body">
                            <div class="row g-3">
                                <input type="hidden" id="index_edicao" value="">
                                <div class="col-12">
                                    <label class="form-label fw-bold text-secondary">Nome <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="nome_cadastro" name="nome"
                                        placeholder="Nome completo" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold text-secondary">CPF (Matrícula) <span
                                            class="text-danger">*</span></label>
                                    <input type="tel" class="form-control" id="cpf_cadastro" name="cpf"
                                        placeholder="Apenas números" maxlength="11" pattern="\d*" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold text-secondary">Setor <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="setor_cadastro" name="setor"
                                        placeholder="Ex: DGP / TI" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-secondary">Telefone</label>
                                    <input type="tel" class="form-control" id="telefone_cadastro" name="telefone"
                                        placeholder="(00) 00000-0000" maxlength="11" pattern="\d*">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-secondary">Email</label>
                                    <input type="email" class="form-control" id="email_cadastro" name="email"
                                        placeholder="exemplo@pm.pr.gov.br">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-bd-primary px-4">Salvar Cadastro</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalEditarEstagiario" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-light">
                        <h5 class="modal-title text-pmp-azul">Editar Informações do Estagiário</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="formEditarEstagiario">
                        @csrf
                        <div class="modal-body">
                            <div class="row g-3">
                                <input type="hidden" id="id_estagiario_editar" name="id_estagiario_editar">
                                <div class="col-12">
                                    <label class="form-label fw-bold text-secondary">Nome <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="nome_editar" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold text-secondary">CPF (Matrícula) <span
                                            class="text-danger">*</span></label>
                                    <input type="tel" class="form-control" id="cpf_editar" maxlength="11" pattern="\d*"
                                        required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold text-secondary">Setor <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="setor_editar" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-secondary">Telefone</label>
                                    <input type="tel" class="form-control" id="telefone_editar" maxlength="11"
                                        pattern="\d*">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-secondary">Email</label>
                                    <input type="email" class="form-control" id="email_editar">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-bd-primary px-4">Atualizar Cadastro</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>

    </div>
@endsection