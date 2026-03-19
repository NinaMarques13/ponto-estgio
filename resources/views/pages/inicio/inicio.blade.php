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
        <a href="{{ asset('views/login/adm') }}" class="admin-access-btn" title="Acessar Área Administrativa" style="align-content: center;">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
             stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-circle-user-icon lucide-circle-user">
             <circle cx="12" cy="12" r="10"/><circle cx="12" cy="10" r="3"/><path d="M7 20.662V19a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v1.662"/></svg>
                <circle cx="12" cy="7" r="4"></circle>
            </svg><text>Área Admin</text>
        </a>
        <div class="card ponto-card border-start border-end border-2 px-3">
            <div class="text-center pt-4 pb-3">
                <img src="{{ asset('img/dgp_transparente.png') }}" alt="Brasão DGP" class="img-fluid brasao-topo" p->
                <h1 class="ponto-titulo mt-4 mb-0" title="Ponto de registro de estagiários">PONTO DE REGISTRO<br>DE ESTAGIÁRIOS</h1>
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
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                     fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                      class="lucide lucide-camera-icon lucide-camera"><path d="M13.997 4a2 2 0 0 1 1.76 1.05l.486.9A2
                                       2 0 0 0 18.003 7H20a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V9a2 2 0 0 1 2-2h1.997a2 2 0 0
                                        0 1.759-1.048l.489-.904A2 2 0 0 1 10.004 4z"></path><circle cx="12" cy="13" r="3"></circle></svg></span>
                            </div>
                        </div>

                        <div class="registro-opcoes mb-2 mx-2">
                            <span id="entradaTxt" class="registro-link entrada-link active" title="Entrada">Primeiro registro do dia: Entrada</span>
                            |
                            <span id="saidaTxt" class="registro-link saida-link" title="Saida">Segundo registro: Saída</span>
                        </div>

                        <div class="d-grid mb-2">
                            <button id="registrarBtn" type="submit" class="btn btn-success btn-lg registrar-btn" title="Registrar">
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