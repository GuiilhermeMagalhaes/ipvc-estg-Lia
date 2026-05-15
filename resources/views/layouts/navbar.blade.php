<link href="/css/navbar.css" rel="stylesheet">
<link href="/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">

<header id="header" class="header d-flex align-items-center sticky-top">
    <div class="container-fluid position-relative d-flex align-items-center justify-content-between">

        <a href="/" class="logo d-flex align-items-center me-auto me-xl-0">
            <img src="{{url('/images/logo_1.png')}}">
        </a>

        <nav id="navmenu" class="navmenu">
            <ul>
                <li><a href="/kits/">{{ "Kits" }}<br></a></li>
                <li class="dropdown"><a href="#"><span>ITENS</span> <i class="bi bi-chevron-down toggle-dropdown"></i></a>
                    <ul>
                        @foreach (session()->get('categories') as $category)
                        <li><a href="/categoria/{{ $category->id }}">{{ $category->description }}</a></li>
                        @endforeach
                    </ul>
                </li>
            </ul>
        </nav>

        <div class="header-user">
            @guest
            <a href="{{ route('login') }}">Entrar</a>
            @else
            <div class="user-info">
                <li class="dropdown"><a href="/perfil"><span>{{ Auth::user()->name }}</span> <i class="bi bi-chevron-down toggle-dropdown"></i></a>
                    <ul>
                        @if (session()->has('reserve'))
                        <li><a href="/reserve/info">Reserva</a></li>
                        @else
                        <li><a href="/reserve">Iniciar Reserva</a></li>
                        @endif
                        <li><a href="/perfil/reserves">Minhas Reservas</a></li>
                        @if (Auth::user()->user_type_id == 1)
                        <li><a href="{{ route('admin.home') }}">Admin</a></li>
                        @endif
                        @if (Auth::user()->user_type_id == 2)
                        <li><a href="{{ route('admin.home') }}">Técnico</a></li>
                        @endif
                        @if (Auth::user()->user_type_id == 3) 
                        <!-- Auth::user()->user_type_id == 1 || Auth::user()->user_type_id == 2 || -->
                        <li><a href="/lia-space">Espaço Lia</a></li>
                        @endif
                        @if (Auth::user()->user_type_id == 3)
                        <li><a href="{{ route('orientador.centro.create') }}">Criar Centro Custo</a></li>
                        @endif
                        <li><a href="{{ route('disponibilidade.index') }}">Disp. Técnico</a></li>
                    </ul>
                </li>
                <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Sair</a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
            </div>
            @endguest
        </div>

    </div>
</header>