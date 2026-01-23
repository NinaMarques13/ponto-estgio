<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ADM - Cadastro de Estagiários</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

    <link rel="stylesheet" href="{{ asset('style.css') }}">
</head>
<body>
    <div class="tela-cadastro">
        <div class="container my-4">
            <div class="text-center mb-2">
                <img src="{{ asset('img/logotipo-dgp.png') }}" alt="Logo DPG" style="width: 80px;">
            </div>
            
            <div class="text-center mb-4">
                <h2 class="titulo-sistema">Sistema de Estagiário</h2>
            </div>
            
            <nav class="nav nav-underline mb-4 border-bottom">
                <a class="nav-link text-dark" href="#">HOME</a>
                <a class="nav-link active fw-bold text-dark border-bottom border-3 border-dark" href="#">Cadastro de Estagiários</a>
                <a class="nav-link text-dark" href="#">Planilha de Exportação</a>
            </nav>
            
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
                            <td></td> <td></td> <td></td> <td></td> <td></td> <td class="text-center">
                                <div class="d-flex justify-content-center gap-2">
                                    <button class="btn btn-purple btn-sm text-white border-0" id="openQrCadastro">
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
                            <h5 class="modal-title">Modal de exemplo para qr code do estagiário</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body text-center">
                            <div id="qrcodeCadastro"></div>
                            <p class="mt-3 text-muted">Exemplo</p>
                        </div>
                    </div>
                </div>
            </div>

            
            <div class="d-flex justify-content-between mt-4">
                <a href="#" class="text-success text-decoration-none fw-bold border-bottom border-success">Ir para Ponto de Registro</a>
                <a href="#" class="text-danger text-decoration-none fw-bold">Sair</a>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs/qrcode.min.js"></script>
    <script src="{{ asset('script.js') }}"></script>

</body>
</html>