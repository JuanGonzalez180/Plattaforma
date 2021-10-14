<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <!-- Required meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Plattaforma Admin') }} - @yield('title', '')</title>

        <!-- Bootstrap CSS -->
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/open-iconic/1.1.1/font/css/open-iconic-bootstrap.min.css" integrity="sha256-BJ/G+e+y7bQdrYkS2RBTyNfBHpA9IuGaPmf9htub5MQ=" crossorigin="anonymous" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/open-iconic/1.1.1/font/css/open-iconic-bootstrap.min.css" integrity="sha256-BJ/G+e+y7bQdrYkS2RBTyNfBHpA9IuGaPmf9htub5MQ=" crossorigin="anonymous" />
        <!-- Jquery -->
        <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
        <!-- Datatables -->
        <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap4.min.css">
        <!-- SweetAlert -->
        <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <!-- Fontawesome -->
        <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
        <link href="{{ asset('assets/css/app.css') }}" rel="stylesheet">
    </head>
    <body>
        @guest
        @else
        <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/') }}">
                    {{ config('app.name', 'Laravel') }}
                </a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav mr-auto">

                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item dropdown">
                            <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                {{ Auth::user()->name }}
                            </a>

                            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                <a class="dropdown-item" href="{{ route('logout') }}"
                                    onclick="event.preventDefault();
                                                    document.getElementById('logout-form').submit();">
                                    {{ __('auth.logout') }}
                                </a>

                                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                    @csrf
                                </form>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
        @endguest

        <div class="container-fluid">
            <div class="row">
                @guest
                @else
                    <nav class="col-md-2 d-none d-md-block bg-light sidebar">
                        <div class="sidebar-sticky">
                            <ul class="nav flex-column">

                                <li class="nav-item">
                                    <a class="nav-link collapsed text-truncate" href="#companySubmenu" data-toggle="collapse" data-target="#companySubmenu"><span class="d-none d-sm-inline">Compañias</span></a>
                                    <div class="collapse" id="companySubmenu" aria-expanded="false">
                                        <ul class="flex-column pl-2 nav">
                                            <li class="nav-item">
                                                <a class="nav-link py-0" href="{{ route('companies-type', 'Demanda') }}">
                                                    <i class="fas fa-angle-right"></i>&nbsp;Demanda
                                                </a>
                                            </li>
                                            <div class="dropdown-divider"></div>
                                            <li class="nav-item">
                                                <a class="nav-link py-0" href="{{ route('companies-type', 'Oferta') }}">
                                                    <i class="fas fa-angle-right"></i>&nbsp;Oferta
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </li>

                                <li class="nav-item">
                                    <a class="nav-link collapsed text-truncate" href="#publicitySubmenu" data-toggle="collapse" data-target="#publicitySubmenu"><span class="d-none d-sm-inline">Publicidad</span></a>
                                    <div class="collapse" id="publicitySubmenu" aria-expanded="false">
                                        <ul class="flex-column pl-2 nav">
                                            <li class="nav-item">
                                                <a class="nav-link py-0" href="{{ route('publicity_plan.index') }}">
                                                    <i class="fas fa-angle-right"></i>&nbsp;Planes
                                                </a>
                                            </li>
                                            <div class="dropdown-divider"></div>
                                            <li class="nav-item">
                                                <a class="nav-link py-0" href="{{ route('img_publicity_plan.index') }}">
                                                    <i class="fas fa-angle-right"></i>&nbsp;Imagenes planes
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </li>

                                <!--<li class="nav-item">
                                    <a class="nav-link" href="{{ route('companies.index') }}">
                                        <span data-feather="file"></span>
                                        Compañias 2
                                    </a>
                                </li>
                                -->
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('countries.index') }}">
                                        <span data-feather="file"></span>
                                        Paises
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('typesentity.index') }}">
                                        <span data-feather="file"></span>
                                        Tipos de Entidad
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('category.index') }}">
                                        <span data-feather="file"></span>
                                        Categorías
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('categoryservices.index') }}">
                                        <span data-feather="file"></span>
                                        Categorías Servicios
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('brand.index') }}">
                                        <span data-feather="file"></span>
                                        Marcas
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('typeproject.index') }}">
                                        <span data-feather="file"></span>
                                        Tipos de proyectos
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('socialnetwork.index') }}">
                                        <span data-feather="file"></span>
                                        Redes Sociales
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('staticcontent.index') }}">
                                        <span data-feather="file"></span>
                                        Contenido estático
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('products_stripe.index') }}">
                                        <span data-feather="file"></span>
                                        Productos Stripe
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('plans.index') }}">
                                        <span data-feather="file"></span>
                                        Planes Stripe
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('testing.index') }}">
                                        <span data-feather="file"></span>
                                        Test
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link collapsed text-truncate" href="#upfalesubmenu" data-toggle="collapse" data-target="#upfalesubmenu"><span class="d-none d-sm-inline">Subir Archivos</span></a>
                                    <div class="collapse" id="upfalesubmenu" aria-expanded="false">
                                        <ul class="flex-column pl-2 nav">
                                            <li class="nav-item">
                                                <a class="nav-link py-0" href="{{ route('template-product-file.index') }}">
                                                    <i class="fas fa-angle-right"></i>&nbsp;Productos - plantilla CSV
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </li>

                            </ul>
                        </div>
                    </nav>
                @endguest
            </div>
        </div>
        
        @yield('content')

        <!-- Optional JavaScript -->
        <!-- jQuery first, then Popper.js, then Bootstrap JS -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
        <!-- Datatables -->
        <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
        <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap4.min.js"></script>
        <script src="{{ asset('js/datatables/app.js') }}"></script>
        <script src="{{ asset('js/tooltip/app.js') }}"></script>
        @yield('js')
    </body>
</html>