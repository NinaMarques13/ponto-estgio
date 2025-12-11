<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Área Administrativa</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
</head>

<body>

    <div class="container d-flex justify-content-center align-items-center vh-100">
        
        <div class="card p-5 shadow-lg login-card" style="max-width: 400px; border-radius: 20px;">
            <div class="card-body text-center p-0">
                
                <div class="logo-placeholder mb-3">
                    <img src="{{ asset('img/logotipo-dgp.png') }}" alt="Logo DGP" class="img-fluid logo">
                </div>
                
                <h1 class="h5 fw-bold mt-2" style="color: #0E622F;">ÁREA ADMINISTRATIVA</h1>
                <p class="text-secondary mb-4 small">Sistema de Estagiários</p>
                
                <form class="text-start">
                    <div class="mb-3">
                        <label for="usuario" class="form-label fw-bold">Usuário</label>
                        <input type="text" id="usuario" name="usuario" class="form-control" placeholder="Digite seu usuário">
                    </div>

                    <div class="mb-4">
                        <label for="senha" class="form-label fw-bold">Senha</label>
                        <input type="password" id="senha" name="senha" class="form-control" placeholder="Digite sua senha">
                    </div>

                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" class="btn btn-lg fw-bold" style="background-color: #0E622F; color: white;">
                           <i class="bi bi-lock-fill me-2"></i> ENTRAR
                        </button>
                    </div>
                </form>

                <p class="text-muted mt-4 small">
                    Credenciais padrão: <br>
                    Usuário: **admin** | Senha: **admin123**
                </p>
            </div>
        </div>
        
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>