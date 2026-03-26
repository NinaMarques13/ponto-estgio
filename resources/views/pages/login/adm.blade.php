<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Área Administrativa</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

    <link rel="stylesheet" href="{{ asset('style.css') }}">
</head>

<body>

    <div class="container-fluid d-flex justify-content-center align-items-center vh-100 p-0 ponto-container">

        <div class="card px-4 shadow-lg ponto-card" style="max-width: 550px; border-radius: 20px;">
            <div class="card-body text-center p-0">

                <div class="text-center pt-3 pb-3">
                    <img src="{{ asset('img/dgp_transparente.png') }}" alt="Brasão DGP" style="width: 40%;"
                        class="img-fluid brasao-topo" p- title="Brasão da DGP">
                </div>

                <h1 class="h5 fw-bold mt-2" style="color: #004A22;" title="Área Administrativa">ÁREA ADMINISTRATIVA</h1>
                <p class="text-secondary mb-2 small" title="Sistema de Estagiários">Sistema de Estagiários</p>

                <form class="text-start" action="{{ route('admin.login.submit') }}" method="POST">
                    @csrf

                    @if ($errors->any())
                        <div class="alert alert-danger p-2 small">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <div class="mb-3">
                        <div class="icon-wrapper">
                            <svg class="mx-2" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                            <label for="email" class="form-label fw-bold" title="Usuário">Usuário</label>
                            <input type="email" id="usuario" name="email" class="form-control"
                                placeholder="Digite seu e-mail" title="Digite seu e-mail">
                        </div>

                        <div class="mb-4 mt-4">
                            <svg class="mx-2" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round"
                                class="lucide lucide-lock-keyhole-icon lucide-lock-keyhole">
                                <circle cx="12" cy="16" r="1" />
                                <rect x="3" y="10" width="18" height="12" rx="2" />
                                <path d="M7 10V7a5 5 0 0 1 10 0v3" />
                            </svg>
                            <label for="senha" class="form-label fw-bold" title="Senha">Senha</label>
                            <input type="password" id="senha" name="password" class="form-control"
                                placeholder="Digite sua senha" title="Digite sua senha">
                        </div>

                        <div class="d-grid gap-2 mt-4">
                            <button id="btn_entrar" type="submit" class="btn btn-lg fw-bold"
                                title="Entrar">ENTRAR</button>
                        </div>
                </form>

                <p class="text-muted mt-4 mx-2 small">
                    Credenciais padrão: <br>
                    Usuário: **admin@admin.com** | Senha: **admin123**
                </p>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>