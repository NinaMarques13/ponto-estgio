@extends('pages.templates.layout')
@section('content')
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
                                <div class="d-flex gap-2">
                                    <div class="d-flex flex-column w-100">
                                        <label class="form-label mb-2">Dia:</label>
                                        <select name="data-ano" id="data-semana-inicio" class="form-control">
                                            <option value="" selected>...</option>
                                            @php
                                                $semanaInicio = 01;
                                                $semanaFim = 31;
                                            @endphp
                                            @for ($i = $semanaInicio; $i <= $semanaFim; $i++)
                                                <option value="{{ $i }}">
                                                    {{ $i }}
                                                </option>
                                            @endfor
                                        </select>
                                    </div>
                                    <div class="d-flex flex-column w-100">
                                        <label class="form-label mb-2">Até dia:</label>
                                        <select name="data-ano" id="data-semana-fim" class="form-control">
                                            <option value="" selected>...</option>
                                            @for ($i = $semanaFim; $i >= $semanaInicio; $i--)
                                                <option value="{{ $i }}">
                                                    {{ $i }}
                                                </option>
                                            @endfor
                                        </select>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="col">
                            <form action="/lista-estagiarios" method="post">
                                @csrf
                                <label class="form-label">Ano</label>
                                <select name="data-ano" id="data-ano" class="form-control">
                                    <option value="" selected>Selecione um ano...</option>
                                    @php
                                        $anoInicio = 2000;
                                        $anoFim = 2100;
                                    @endphp

                                    @for ($i = $anoInicio; $i <= $anoFim; $i++)
                                        <option value="{{ $i }}">
                                            {{ $i }}
                                        </option>
                                    @endfor
                                </select>

                            </form>
                        </div>
                        <div class="col">
                            <label for="filtro-motivo" class="form-label">Filtrar por Motivo</label>
                            <select id="filtro-motivo" class="form-select">
                                <option value="">Todos</option>
                                <option value="presente">Presente (Registros Completos)</option>
                                <option value="entrada">Em Andamento (Só Entrada)</option>
                                <option value="falta">Falta</option>
                                <option value="dispensa">Dispensa</option>
                                <option value="folga">Folga</option>
                                <option value="atestado">Atestado</option>
                                <option value="recesso">Recesso</option>
                            </select>
                        </div>
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
            <table id="myTable" class="table table-bordered align-middle mb-4 mt-4 w-100" style="width:100%">
                <thead>
                    <tr class="table-header-custom">
                        <th class="text-center">Data</th>
                        <th class="text-center">Hora Entrada</th>
                        <th class="text-center">Hora Saída</th>
                        <th class="text-center">Total Horas</th>
                        <th class="text-center">Matrícula (CPF)</th>
                        <th class="text-center">Nome</th>
                        <th class="text-center">Motivo</th>
                        <th class="text-center">Setor</th>
                        <th class="text-center">Observação</th>
                    </tr>
                </thead>
                <tbody class="table-secundary" id="tabela-estagiarios-corpo">

                </tbody>
            </table>
        </div>
    </div>
    </div>
@endsection