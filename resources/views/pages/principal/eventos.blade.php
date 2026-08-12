@extends('pages.templates.layout')

@section('content')
    <h1 class="h4 fw-bold mb-3" style="color:rgb(20, 143, 69)" title="Eventos e Ocorrências">Eventos e Ocorrências</h1>
    <p class="text-secondary" title="Gerencie faltas, recessos, atestados, dispensas e folgas dos estagiários">
        Gerencie faltas, recessos, atestados, dispensas e folgas dos estagiários selecionando as ações na tabela abaixo.
    </p>

    <div class="table-responsive shadow-sm">
        <table id="tabela-estagiarios-eventos" class="table table-bordered align-middle mb-4 mt-4 w-100" title="Tabela de estagiários">
            <thead class="table-header-custom">
                <tr>
                    <th class="text-center">id</th>
                    <th class="text-center" title="Nome">Nome</th>
                    <th class="text-center" title="CPF">CPF (Matrícula)</th>
                    <th class="text-center" title="Setor">Setor</th>
                    <th class="text-center" title="Telefone">Telefone</th>
                    <th class="text-center" title="Email">E-mail</th>
                    <th class="text-center" title="Ações">Ações</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>

    <!-- Modal Adicionar Evento -->
    <div class="modal fade" id="modalAdicionarEvento" tabindex="-1" aria-labelledby="modalAdicionarEventoLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="modalAdicionarEventoLabel" style="color: #0E622F;">Gerar Ocorrência / Evento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formAdicionarEvento">
                    @csrf
                    <input type="hidden" name="estagiario_id" id="add-evento-estagiario-id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Estagiário</label>
                            <input type="text" id="add-evento-estagiario-nome" class="form-control bg-light" readonly>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <label for="data_inicio" class="form-label fw-bold">Data de Início</label>
                                <input type="date" name="data_inicio" id="add-evento-data-inicio" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label for="data_fim" class="form-label fw-bold">Data de Fim</label>
                                <input type="date" name="data_fim" id="add-evento-data-fim" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="motivo" class="form-label fw-bold">Tipo do Evento</label>
                            <select name="motivo" id="add-evento-motivo" class="form-select" required>
                                <option value="" selected disabled>Selecione o tipo...</option>
                                <option value="recesso">Recesso</option>
                                <option value="atestado">Atestado Médico</option>
                                <option value="falta">Falta</option>
                                <option value="dispensa">Dispensa</option>
                                <option value="folga">Folga</option>
                                <option value="correcao">Correção dia (Ponto Normal)</option>
                            </select>
                        </div>

                        <!-- Campos para Correção Dia -->
                        <div class="row g-2 mb-3 d-none" id="add-evento-secao-horarios">
                            <div class="col-md-6">
                                <label for="hora_entrada" class="form-label fw-bold">Hora de Entrada</label>
                                <input type="time" name="hora_entrada" id="add-evento-hora-entrada" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label for="hora_saida" class="form-label fw-bold">Hora de Saída</label>
                                <input type="time" name="hora_saida" id="add-evento-hora-saida" class="form-control">
                            </div>
                        </div>

                        <!-- Opção para Abonar/Descontar (Atestado/Dispensa) -->
                        <div class="mb-3 d-none" id="add-evento-secao-abono">
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="is_abonado" id="add-evento-is-abonado" value="1">
                                <label class="form-check-label fw-bold" for="add-evento-is-abonado">
                                    Abonar Ocorrência (Contar como Horas Trabalhadas)
                                </label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="observacao" class="form-label fw-bold">Observação</label>
                            <textarea name="observacao" id="add-evento-observacao" class="form-control" rows="3" placeholder="Insira detalhes adicionais sobre a ocorrência (opcional)"></textarea>
                        </div>

                        <!-- Seção de conflitos / registros existentes -->
                        <div id="add-evento-secao-conflitos" class="d-none mt-3">
                            <div class="card border-warning">
                                <div class="card-header bg-warning text-dark fw-bold py-2">
                                    Registros Existentes no Período Selecionado
                                </div>
                                <div class="card-body p-2">
                                    <p class="text-muted small mb-2">Os registros abaixo coincidem com o período. Você pode excluí-los individualmente ou salvar para substituí-los automaticamente.</p>
                                    <ul class="list-group list-group-flush small" id="lista-registros-conflito">
                                        <!-- Preenchido via AJAX -->
                                    </ul>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success rounded-3 px-4">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Listar e Excluir Eventos -->
    <div class="modal fade" id="modalListarEventos" tabindex="-1" aria-labelledby="modalListarEventosLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="modalListarEventosLabel" style="color: #0E622F;">Ocorrências de <span id="listar-evento-estagiario-nome"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row align-items-center mb-3">
                        <div class="col-md-5 d-flex align-items-center gap-2">
                            <label for="filtro-tipo-ocorrencia" class="form-label mb-0 fw-semibold text-muted text-nowrap">Filtrar por Tipo:</label>
                            <select id="filtro-tipo-ocorrencia" class="form-select form-select-sm">
                                <option value="todos">Todos</option>
                                <option value="falta">Falta</option>
                                <option value="atestado">Atestado</option>
                                <option value="folga">Folga</option>
                                <option value="recesso">Recesso</option>
                                <option value="dispensa">Dispensa</option>
                                <option value="correcao">Correção de Ponto</option>
                            </select>
                        </div>
                        <div class="col-md-7 text-end d-flex align-items-center justify-content-end gap-3">
                            <div class="form-check form-switch mb-0 text-start">
                                <input class="form-check-input" type="checkbox" id="chk-mostrar-excluidos">
                                <label class="form-check-label text-muted text-nowrap" for="chk-mostrar-excluidos" style="font-size: 0.9em; user-select: none;">Exibir excluídos</label>
                            </div>
                            <button type="button" id="btn-excluir-lote-ocorrencias" class="btn btn-danger btn-sm d-none">
                                <i class="fa fa-trash-alt me-1"></i> Excluir Selecionados (<span id="count-excluir-lote">0</span>)
                            </button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped align-middle" id="tabela-ocorrencias-estagiario">
                            <thead>
                                <tr>
                                    <th style="width: 40px;" class="text-center">
                                        <input type="checkbox" class="form-check-input" id="selecionar-todas-ocorrencias">
                                    </th>
                                    <th>Data</th>
                                    <th>Tipo de Ocorrência</th>
                                    <th>Observação</th>
                                    <th class="text-center">Excluir</th>
                                </tr>
                            </thead>
                            <tbody id="corpo-tabela-ocorrencias">
                                <!-- Preenchido dinamicamente via JS -->
                            </tbody>
                        </table>
                    </div>
                    <div id="aviso-sem-ocorrencias" class="text-center py-4 text-muted d-none">
                        Nenhum evento cadastrado para este estagiário.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>
@endsection