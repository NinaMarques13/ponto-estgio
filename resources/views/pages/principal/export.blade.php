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
                                                <p>Data</p>
                                                <input type="date" name="data-completa" id="data-completa"
                                                    class="form-control">
                                            </div>
                                            <div class="col">
                                                <p>Mês</p>
                                                <input type="month" name="data-mes" id="data-completa"
                                                    class="form-control">
                                            </div>
                                            <div class="col">
                                                <p>Ano</p>
                                                <input type="datetime-local" name="data-ano" id="data-completa"
                                                    class="form-control">
                                            </div>
                                            <div class="col">
                                                <p>Filtrar por Estagiário</p>
                                                <input type=search" name="sc-estagiario" id="sc-estagiario"
                                                    class="form-control">
                                            </div>
                                            <div class="col">
                                                <p>Filtrar por Motivo</p>
                                                <input type=search" name="sc-motivo" id="sc-motivo"
                                                    class="form-control">
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
                                                    <div class="h3 fw-bold my-1">5</div>
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
                                                    <div class="small">Estagiários</div>
                                                    <div class="h3 fw-bold my-1">5</div>
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
                                                    <div class="small">Estagiários</div>
                                                    <div class="h3 fw-bold my-1">5</div>
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
                                                    <div class="small">Estagiários</div>
                                                    <div class="h3 fw-bold my-1">5</div>
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
                                                    <div class="small">Estagiários</div>
                                                    <div class="h3 fw-bold my-1">5</div>
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
                                                    <div class="small">Estagiários</div>
                                                    <div class="h3 fw-bold my-1">5</div>
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
                                                    <div class="small">Estagiários</div>
                                                    <div class="h3 fw-bold my-1">5</div>
                                                    <div class="small">Do dia</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row g-7 mb-2 mt-2">
                                <div class="col">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col">
                                                    <div class="small">Data</div>
                                                </div>
                                                <div class="col">
                                                    <div class="small">Data</div>
                                                </div>
                                                <div class="col">
                                                    <div class="small">Data</div>
                                                </div>
                                                <div class="col">
                                                    <div class="small">Data</div>
                                                </div>
                                                <div class="col">
                                                    <div class="small">Data</div>
                                                </div>
                                                <div class="col">
                                                    <div class="small">Data</div>
                                                </div>
                                                <div class="col">
                                                    <div class="small">Data</div>
                                                </div>
                                                <div class="col">
                                                    <div class="small">Data</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
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