<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ADM - Home</title>

  <link rel="stylesheet" href="{{ asset('css/vendor/bootstrap.min.css') }}">
  <link rel="stylesheet" href="{{ asset('css/style.css')}}">
  <link rel="stylesheet" href="{{ asset('css/vendor/datatables.min.css') }}">

</head>

<body class="home-body">
  <div class="container home-container mt-5">
    <div class="card main-card shadow-lg p-0">
      <div class="card-body p-5 position-relative">
        <div class="text-center mb-4 position-relative">
          <img src="{{ asset('img/dgp_transparente.png') }}" alt="Logo DGP" class="img-fluid" style="height: 80px;">
          <h2 class="h6 mt-2 fw-bold" style="color: #0E622F;">Sistema de Estagiários</h2>
        </div>

        <nav class="navbar navbar-expand p-0 navbar-top-menu">
          <div class="container-fluid justify-content-start">
            <div class="navbar-nav">
              <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                Home
              </a>

              <a class="nav-link {{ request()->routeIs('cadastro') ? 'active' : '' }}" href="{{ route('cadastro') }}">
                Cadastro de Estagiários
              </a>

              <a class="nav-link {{ request()->routeIs('export') ? 'active' : '' }}" href="{{ route('export') }}">
                Planilha de Exportação
              </a>
            </div>
          </div>
        </nav>
        <hr class="mt-2 mb-5">
        <div class="text-center mt-5">
          <div class="container">
            @yield('content')
          </div>

        </div>

      </div>

      <div class="d-flex justify-content-between align-items-center p-3 footer-links">
        <div>
          <a href="{{ route('inicio.index') }}">Ir para Ponto de Registro</a>
        </div>
        <div>
          <form action="{{ route('admin.logout') }}" method="POST" style="display: inline;">
            @csrf
            <button type="submit" class="btn btn-outline-danger px-2 py-1"; text-decoration: none;">
              Sair
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
  <div class="footer-links">
    <div class="container d-flex justify-content-between aling-itens-center p-3">
      <div>
        <a href="{{ route('inicio.index') }}">Ir para Ponto de Registro</a>
      </div>
      <div>
        <form action="{{ route('admin.logout') }}" method="POST" style="display: inline;">
          @csrf
          <button type="submit" class="btn btn-outline-danger px-2 py-1" ; text-decoration: none;">
            Sair
          </button>
        </form>
      </div>
    </div>
  </div>
  <script src="{{ asset('js/vendor/jquery-4.0.0.min.js') }}"></script>
  <script src="{{ asset('js/vendor/bootstrap.bundle.min.js') }}"></script>
  <script src="{{ asset('js/vendor/datatables.min.js') }}"></script>
  <script src="{{ asset('js/vendor/dataTables.bootstrap5.min.js') }}"></script>
  <script src="{{ asset('js/script.js') }}"></script>
</body>

</html>