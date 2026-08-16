<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - Área Administrativa</title>

    <link rel="stylesheet" href="{{ asset('css/vendor/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('style.css') }}">
</head>

<body>

    <div class="container-fluid d-flex justify-content-center align-items-center vh-100 p-0 ponto-container">

        <div class="card px-4 shadow-lg ponto-card" style="max-width: 550px; border-radius: 20px;">
            <div class="card-body text-center p-0">

                <div class="text-center pt-3 pb-3">
                    <img src="{{ asset('img/dgp_transparente.png') }}" alt="Brasão DGP" style="width: 40%;"
                        class="img-fluid brasao-topo" title="Brasão da DGP">
                </div>

                <h1 class="h5 fw-bold mt-2" style="color: #004A22;" title="Novo Administrador">NOVO ADMINISTRADOR</h1>
                <p class="text-secondary mb-2 small" title="Cadastro de Sistema">Crie sua conta administrativa</p>

                <form class="text-start" action="{{ route('admin.register.submit') }}" method="POST">
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
                        <label for="nome" class="form-label fw-bold" title="Nome Completo">Nome Completo</label>
                        <input type="text" id="nome" name="nome" class="form-control"
                            placeholder="Digite seu nome" required value="{{ old('nome') }}">
                    </div>

                    <div class="mb-3">
                        <label for="cpf" class="form-label fw-bold" title="CPF">CPF</label>
                        <input type="text" id="cpf" name="cpf" class="form-control"
                            placeholder="Apenas números" required value="{{ old('cpf') }}">
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label fw-bold" title="E-mail">E-mail</label>
                        <input type="email" id="email" name="email" class="form-control"
                            placeholder="exemplo@email.com" required value="{{ old('email') }}">
                    </div>

                    <div class="mb-3">
                        <label for="senha" class="form-label fw-bold" title="Senha">Senha</label>
                        <input type="password" id="senha" name="password" class="form-control"
                            placeholder="Mínimo de 6 caracteres" required>
                    </div>

                    <div class="mb-4">
                        <label for="senha_confirmation" class="form-label fw-bold" title="Confirmar Senha">Confirmar Senha</label>
                        <input type="password" id="senha_confirmation" name="password_confirmation" class="form-control"
                            placeholder="Repita a senha" required>
                    </div>

                    <div class="d-grid gap-2 mt-4">
                        <button id="btn_cadastrar" type="submit" class="btn btn-success btn-lg fw-bold"
                            title="Cadastrar">CADASTRAR</button>
                    </div>
                </form>

                <div class="mt-4 mb-3">
                    <a href="{{ route('admin.login') }}" class="text-success text-decoration-none fw-bold small">Já possui uma conta? Entrar</a>
                </div>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
