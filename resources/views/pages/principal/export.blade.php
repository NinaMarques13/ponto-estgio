<DOCTYPE html>
    <html lang="pt-br">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>ADM - Export</title>

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

        <link rel="stylesheet" href="{{ asset('style.css') }}">

    </head>

    <body class="home-body">

        <div class="container home-container">
            <div class="card main-card shadow-lg p-0">
                <div class="card-body p-5 position-relative" style="z-index: 1;">
                    <img src="{{ asset('img/logotipo-de-fundo.png') }}" alt="PM PR Fundo" class="bg-watermark">
                    <div class="text-center mb-4 position-relative">
                        <img src="{{ asset('img/logotipo-dgp.png') }}" alt="Logo DGP" class="img-fluid"
                            style="height: 80px;">
                        <h2 class="h6 mt-2 fw-bold" style="color: #0E622F;">Sistema de Estagiários</h2>
                    </div>
                    <nav class="navbar navbar-expand p-0 navbar-top-menu">
                        <div class="container-fluid justify-content-start">
                            <div class="navbar-nav">
                                <button class="nav-link active" id="home" aria-current="page" href="#">Home</button>
                                <button class="nav-link" id="cad" href="#">Cadastro de Estagiários</button>
                                <button class="nav-link" id="plan" href="#">Planilha de Exportação</button>
                            </div>
                        </div>
                    </nav>
                    <hr class="mt-2 mb-5">
                    <div class="d-flex gap-3">
                        <div class="conteudo-aba home">
                            <p>Olá mundo 1</p>
                        </div>
                        <div class="conteudo-aba cad">
                            <p>Olá mundo 2</p>
                        </div>
                        <div class="conteudo-aba plan">
                            <div class="col">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col">
                                                <form action="/pesquisar-data" method="post">
                                                @csrf
                                                <p>Data</p>
                                                <input type="date" name="data-completa" id="data-completa"
                                                    class="form-control">
                                                </form>
                                            </div>
                                            <div class="col">
                                                <p>Mês</p>
                                                <input type="month" name="data-mes" id="data-mes"
                                                    class="form-control">
                                            </div>
                                            <div class="col">
                                                <p>Ano</p>
                                                <input type="number" 
                                                        name="data-ano" 
                                                        id="data-ano" 
                                                        class="form-control" 
                                                        min="2020" 
                                                        max="2030" 
                                                        step="1" 
                                                        value="{{ date('Y') }}">
                                            </div>
                                            <div class="col">
                                                <div class="col-md-3">
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
                                                </select>
                                            </div>
                                            <div class="col">
                                                <button id="excel-btn">Excel</button>
                                                <button id="pdf-btn">PDF</button>
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
                                                    <div class="small">Do dia</div>
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
                                                    <div class="small">Diário</div>
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
                                                    <div class="small">Diário</div>
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
                                                    <div class="small">Diário</div>
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
                                                    <div class="small">Diário</div>
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
                                                    <div class="small">Diário</div>
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
                                                    <div class="small">Diário</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row g-3 mb-2 mt-2">
                                <table class="table">
                                    <thead>
                                        <tr class="table-primary">
                                            <th>Data</th>
                                            <th>Hora Entrada</th>
                                            <th>Hora Saída</th>
                                            <th>Total Horas</th>
                                            <th>Matrícula (CPF)</th>
                                            <th>Nome</th>
                                            <th>Motivo</th>
                                            <th>Setor</th>
                                            <th>Observação</th>
                                        </tr>
                                    </thead>
                                    <tbody class="table-secundary" id="tabela-estagiarios-corpo">

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center p-3 footer-links">
                    <div>
                        <a href="#">Ir para Ponto de Registro</a>
                    </div>
                    <div>
                        <a href="#" style="color: #dc3545;">Sair</a>
                    </div>
                </div>
            </div>
        </div>
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <script src="{{ asset('script.js') }}"></script>
    </body>

    </html>