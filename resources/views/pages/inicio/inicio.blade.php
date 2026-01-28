<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ponto de Registro de Estagiários</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{asset('style.css')}}">
</head>

<body>
    <div class="container-fluid d-flex justify-content-center align-items-center vh-100 p-0 ponto-container">
        <a href="{{ asset('views/login/adm') }}" class="admin-access-btn" title="Acessar Área Administrativa">
                <i class="bi bi-person-circle me-1"></i>
                Área Admin
            </a>
        <div class="card ponto-card">
<<<<<<< HEAD
            <form action="/registrar-ponto" method="post">
                @csrf <div class="form-group">
                    <div class="text-center pt-4 pb-3">
                        <img src="{{asset('img/logotipo-dgp.png')}}" alt="Brasão DGP" class="img-fluid brasao-topo">
                        <h1 class="ponto-titulo mt-3 mb-4">PONTO DE REGISTRO<br>DE ESTAGIÁRIOS</h1>
                    </div>
=======
            <div class="text-center pt-4 pb-3">
                <img src="{{asset('img/logotipo-dgp.png')}}" alt="Brasão DGP" class="img-fluid brasao-topo">
                <h1 class="ponto-titulo mt-3 mb-4">PONTO DE REGISTRO<br>DE ESTAGIÁRIOS</h1>
            </div>
            <form action="{{ route('estagiarios.store') }}" method="POST">
                @csrf <div class="form-group">
>>>>>>> db14817 (tela inicial funcional)
                    <div class="card-body">
                        <div class="mb-4">
                            <label for="cpf" class="form-label matricula-label">Matrícula (CPF)</label>
                            <div class="input-group">
<<<<<<< HEAD
                                <input type="text" class="form-control cpf-input" name="cpf" id="cpf"
                                    placeholder="Digite o CPF">
=======
                                <input type="text" class="form-control cpf-input" name="cpf" id="cpf" placeholder="Digite o CPF">
>>>>>>> db14817 (tela inicial funcional)
                                <span class="input-group-text camera-icon">
                                    <i class="bi bi-camera-fill"></i> </span>
                            </div>
                        </div>

                        <div class="registro-opcoes mb-5">
                            <p class="text-intro">Primeiro registro do dia: </p>
                            <span id="entradaTxt" class="registro-link entrada-link active">Entrada</span>
                            |
                            <span id="saidaTxt" class="registro-link saida-link">Segundo registro. Saída</span>
                        </div>

                        <div class="d-grid gap-2">
                            <button id="registrarBtn" type="submit" class="btn btn-lg registrar-btn">
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