<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Área Administrativa</title>

    <link rel="stylesheet" href="{{ asset('css/vendor/bootstrap.min.css') }}">
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
                            <img src="{{ asset('icons/user.svg') }}" class="mx-2" alt="Ícone de Usuário">
                            <label for="email" class="form-label fw-bold" title="Usuário">Usuário</label>
                            <input type="text" id="usuario" name="login" class="form-control"
                                placeholder="Digite seu e-mail ou CPF" title="Digite seu e-mail">
                        </div>

                        <div class="mb-4 mt-4">
                            <img src="{{ asset('icons/lock-keyhole.svg') }}" class="mx-2" alt="Ícone de Senha">
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
                    Usuário: **admin@admin.com** | Senha: **adm123**
                </p>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>