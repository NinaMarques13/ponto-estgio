<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ADM - Home</title>
  <link rel="stylesheet" href="{{ asset('css/vendor/bootstrap.min.css') }}">
  <link rel="stylesheet" href="{{ asset('css/style.css') }}">
  <link rel="stylesheet" href="{{ asset('css/vendor/datatables.min.css') }}">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">

</head>

<body class="home-body">
  <div class="container home-container mt-5">
    <div class="card main-card shadow-lg rounded-3">
      <div class="card-body p-5 position-relative">
        <div class="text-center mb-4 position-relative">
          <img src="{{ asset('img/dgp_transparente.png') }}" alt="Logo DGP" class="img-fluid" style="height: 80px;">
          <h2 class="h6 mt-2 fw-bold" style="color: #0E622F;">Sistema de Estagiários</h2>
        </div>

        <nav class="navbar navbar-expand p-0 navbar-top-menu">
          <div class="container-fluid justify-content-start">
            <div class="navbar-nav">
              <a class="nav-link {{ request()->routeIs('eventos') ? 'active' : '' }}" 
                href="{{ route('eventos') }}">
                Eventos
              </a>

              <a class="nav-link {{ request()->routeIs('cadastro') ? 'active' : '' }}" 
                href="{{ route('cadastro') }}">
                Cadastro de Estagiários
              </a>

              <a class="nav-link {{ request()->routeIs('export') ? 'active' : '' }}" 
                href="{{ route('export') }}">
                Registros 
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
                <button type="submit" class="btn btn-outline-danger px-2 py-1"; text-decoration: none;">
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
  
  <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

  <script src="{{ asset('js/script.js') }}?v=1.1"></script>
</body>

</html>