<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ponto de Registro de Estagiários</title>
    <link rel="stylesheet" href="{{ asset('css/vendor/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{asset('style.css')}}">
</head>

<body>
    <div class="container-fluid d-flex justify-content-center align-items-center vh-100 p-0 ponto-container">
        <a href="{{ asset('views/login/adm') }}" class="admin-access-btn" title="Acessar Área Administrativa"
            style="align-content: center;">
                <img src="{{ asset('icons/circle-user.svg') }}" class="mx-1" alt="Ícone de ADM" style="filter: invert();">
            <text>Área Admin</text>
        </a>
        <div class="card ponto-card border-start border-end border-2 px-3">
            <div class="text-center pt-4 pb-3">
                <img src="{{ asset('img/dgp_transparente.png') }}" alt="Brasão DGP" class="img-fluid brasao-topo" p->
                <h1 class="ponto-titulo mt-4 mb-0" title="Ponto de registro de estagiários">PONTO DE REGISTRO<br>DE
                    ESTAGIÁRIOS</h1>
            </div>
            <form action="{{ route('registrar-ponto') }}" method="POST">
                @csrf <div class="form-group">
                    <div class="card-body">
                        <div class="mb-4">
                            <label for="cpf" class="form-label matricula-label mx-2" title="CPF">Matrícula (CPF)</label>
                            <div class="input-group">
                                <input type="text" class="form-control cpf-input" name="cpf" id="cpf"
                                    placeholder="Digite o CPF" title="Digite o CPF">
                                <span class="input-group-text camera-icon" title="Câmera">
                                    <img src="{{ asset('icons/camera.svg') }}" class="mx-2" alt="Ícone de Câmera" width="30px"></span>
                            </div>
                        </div>

                        <div class="registro-opcoes mb-2 mx-2">
                            <span id="entradaTxt" class="registro-link entrada-link active" title="Entrada">Primeiro
                                registro do dia: Entrada</span>
                            |
                            <span id="saidaTxt" class="registro-link saida-link" title="Saida">Segundo registro:
                                Saída</span>
                        </div>

                        <div class="d-grid mb-2">
                            <button id="registrarBtn" type="submit" class="btn btn-success btn-lg registrar-btn"
                                title="Registrar">
                                REGISTRAR
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script src="{{asset('script.js')}}"></script>

</body>

</html>