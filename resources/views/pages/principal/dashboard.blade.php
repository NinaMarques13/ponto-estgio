@extends('pages.templates.layout')

@section('content')
    <h1 class="h4 fw-bold mb-3" style="color:rgb(20, 143, 69)" title="Bem-vindo ao Sistema de Estagiários">Bem-vindo ao
        Sistema de Estagiários</h1>
    <p class="text-secondary" title="Selecione uma opção no menu acima para começar">Selecione uma opção no menu acima para
        começar.</p>
     <!--Modal-->
    <!-- <div class="modal fade" id="modalEditarEstagiario" tabindex="-1">
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
                                <input type="text" id="edit-total-horas" class="form-control" disabled>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col">
                                <label>Matricula</label>
                                <input type="text" id="edit-matricula" class="form-control" disabled>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col">
                                <label>Nome</label>
                                <input type="text" id="edit-nome" class="form-control" disabled>
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
                                <input type="text" id="edit-setor" class="form-control" disabled>
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
    </div> -->
@endsection