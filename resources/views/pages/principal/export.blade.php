@extends('pages.templates.layout')
@section('content')
    <div class="d-flex gap-3">
        <div class="conteudo-aba plan">
            <div class="col">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col">
                                <form action="/lista-estagiarios" method="post">
                                    @csrf
                                    <label class="form-label">Data</label>
                                    <input type="date" name="data-completa" id="data-completa" class="form-control">
                                </form>
                            </div>
                            <div class="col">
                                <form action="/lista-estagiarios" method="post">
                                    @csrf
                                    <label class="form-label">Mês</label>
                                    <input type="month" name="data-mes" id="data-mes" class="form-control">
                                </form>
                            </div>
                            <div class="col">
                                <form action="/lista-estagiarios" method="post">
                                    @csrf
                                    <label class="form-label">Ano</label>
                                    <input type="number" name="data-ano" id="data-ano" class="form-control" min="2000"
                                        max="2100" step="1" value="{{ date('Y') }}">
                                </form>
                            </div>
                            <div class="col">
                                <div class="col">
                                    <label class="form-label">Filtrar por Estagiário</label>
                                    <select id="filtro-estagiario" class="form-select">
                                        <option value="">Carregando...</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col">
                                <label for="filtro-motivo" class="form-label">Filtrar por Motivo</label>
                                <select id="filtro-motivo" class="form-select">
                                    <option value="">Todos</option>
                                    <option value="Presente">Presente (Registros Completos)</option>
                                    <option value="Em Andamento">Em Andamento (Só Entrada)</option>
                                    <option value="Falta">Falta</option>
                                    <option value="Dispensa">Dispensa</option>
                                    <option value="Folga">Folga</option>
                                    <option value="Atestado">Atestado</option>
                                    <option value="Recesso">Recesso</option>
                                </select>
                            </div>
                            <!-- <div class="col">
                                                <button class="btn btn-success">Excel</button>
                                                <button class="btn btn-bd-primary">PDF</button>
                                            </div> -->
                        </div>
                    </div>
                </div>
            </div>
            <div class="row g-3 mb-2 mt-2">
                <div class="col">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col">
                                    <div class="small">Estagiários</div>
                                    <div class="h3 fw-bold my-1" id="contador-presentes">...</div>
                                    <div class="small">Total</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col">
                                    <div class="small">Registros</div>
                                    <div class="h3 fw-bold my-1" id="registros-dia">...</div>
                                    <div class="small">Total</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col">
                                    <div class="small">Recesso</div>
                                    <div class="h3 fw-bold my-1" id="recesso-dia">...</div>
                                    <div class="small">Total</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col">
                                    <div class="small">Atestados</div>
                                    <div class="h3 fw-bold my-1" id="atestados-dia">...</div>
                                    <div class="small">Total</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col">
                                    <div class="small">Folga</div>
                                    <div class="h3 fw-bold my-1" id="folgas-dia">...</div>
                                    <div class="small">Total</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col">
                                    <div class="small">Dispensa</div>
                                    <div class="h3 fw-bold my-1" id="dispensas-dia">...</div>
                                    <div class="small">Total</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col">
                                    <div class="small">Falta</div>
                                    <div class="h3 fw-bold my-1" id="faltas-dia">...</div>
                                    <div class="small">Total</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row g-3 mb-2 mt-2">
                <table class="table table-bordered">
                    <thead>
                        <tr class="table-header-custom">
                            <th>Data</th>
                            <th>Hora Entrada</th>
                            <th>Hora Saída</th>
                            <th>Total Horas</th>
                            <th>Matrícula (CPF)</th>
                            <th>Nome</th>
                            <th>Motivo</th>
                            <th>Setor</th>
                            <th>Observação</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody class="table-secundary" id="tabela-estagiarios-corpo">

                    </tbody>
                </table>
            </div>
        </div>
    </div>
    </div>
    <!--Modal-->
    <div class="modal fade" id="modalEditarEstagiario" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="text-pmp-azul fs-5" id="staticBackdropLabel">Editar
                        Estagiário
                    </h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formEditarEstagiario">
                        @csrf
                        @method('put')
                        <input type="hidden" id="edit-id">
                        <div class="row mb-3">
                            <div class="col">
                                <label>Data</label>
                                <input type="date" id="edit-data" class="form-control">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col">
                                <label>Hora Entrada</label>
                                <input type="time" id="edit-entrada" class="form-control">
                            </div>
                            <div class="col">
                                <label>Hora Saída</label>
                                <input type="time" id="edit-saida" class="form-control">
                            </div>
                            <div class="col">
                                <label>Total Horas</label>
                                <input type="text" id="edit-total-horas" class="form-control">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col">
                                <label>Matricula</label>
                                <input type="text" id="edit-matricula" class="form-control">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col">
                                <label>Nome</label>
                                <input type="text" id="edit-nome" class="form-control">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col">
                                <label>Motivo</label>
                                <input type="text" id="edit-motivo" class="form-control">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col">
                                <label>Setor</label>
                                <input type="text" id="edit-setor" class="form-control">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col">
                                <label>Observação</label>
                                <input type="text" id="edit-obs" class="form-control">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" id="btn-cancelar"
                        data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-bd-primary" id="btn-salvar">Salvar</button>
                </div>
            </div>
        </div>
    </div>
@endsection