<aside class="main-sidebar {{ config('adminlte.classes_sidebar', 'sidebar-dark-primary elevation-4') }}">

    {{-- Sidebar brand logo --}}
    @if (config('adminlte.logo_img_xl'))
        @include('adminlte::partials.common.brand-logo-xl')
    @else
        @include('adminlte::partials.common.brand-logo-xs')
    @endif

    {{-- Sidebar menu --}}
    @if (Auth::user()->user_type_id == 1)
        <div class="sidebar">
            <nav class="pt-2">
                <ul class="nav nav-pills nav-sidebar flex-column {{ config('adminlte.classes_sidebar_nav', '') }}"
                    data-widget="treeview" role="menu"
                    @if (config('adminlte.sidebar_nav_animation_speed') != 300) data-animation-speed="{{ config('adminlte.sidebar_nav_animation_speed') }}" @endif
                    @if (!config('adminlte.sidebar_nav_accordion')) data-accordion="false" @endif>
                    {{-- Configured sidebar links --}}
                    @each('adminlte::partials.sidebar.menu-item', $adminlte->menu('sidebar'), 'item')
                </ul>
            </nav>
        </div>
    @endif

    @if (Auth::user()->user_type_id == 2)
        <div class="sidebar">
            <nav class="pt-2">
                <ul class="nav nav-pills nav-sidebar flex-column {{ config('adminlte.classes_sidebar_nav', '') }}"
                    data-widget="treeview" role="menu"
                    @if (config('adminlte.sidebar_nav_animation_speed') != 300) data-animation-speed="{{ config('adminlte.sidebar_nav_animation_speed') }}" @endif
                    @if (!config('adminlte.sidebar_nav_accordion')) data-accordion="false" @endif>
                    {{-- Configured sidebar links --}}
                    <h6 class="dropdown-header">Itens</h6>
                    <li class="nav-item">
                        <a href="{{ route('itens.index') }}" class="nav-link">
                            <i class="nav-icon fas fa-fw fa-th"></i>
                            <p>Lista de Itens</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('itens.ocultos') }}" class="nav-link">
                            <i class="nav-icon fas fa-fw fa-th"></i>
                            <p>Lista de Itens Ocultos</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('itens.manutencao') }}" class="nav-link">
                            <i class="nav-icon fas fa-fw fa-tools"></i>
                            <p>Itens em Manutenção</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('itens.create') }}" class="nav-link">
                            <i class="nav-icon fas fa-fw fa-th"></i>
                            <p>Criar novo Item</p>
                        </a>
                    </li>
                    <h6 class="dropdown-header">Kits</h6>
                    <li class="nav-item">
                        <a href="{{ route('kits.index') }}" class="nav-link">
                            <i class="nav-icon fas fa-fw fa-th"></i>
                            <p>Lista de Kits</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('kits.indexocultos') }}" class="nav-link">
                            <i class="nav-icon fas fa-fw fa-th"></i>
                            <p>Lista de Kits Ocultos</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('kits.create') }}" class="nav-link">
                            <i class="nav-icon fas fa-fw fa-th"></i>
                            <p>Criar novo Kit</p>
                        </a>
                    </li>
                    <h6 class="dropdown-header">Reservas</h6>
                    <li class="nav-item">
                        <a href="{{ route('reserves.all') }}" class="nav-link">
                            <i class="nav-icon fas fa-shopping-cart"></i>
                            <p>Todas as reservas</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('reserves.pending') }}" class="nav-link">
                            <i class="nav-icon fas fa-shopping-cart"></i>
                            <p>Reservas pendentes</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('reserves.delayed') }}" class="nav-link">
                            <i class="nav-icon fas fa-shopping-cart"></i>
                            <p>Reservas em atraso</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('reserves.ongoing') }}" class="nav-link">
                            <i class="nav-icon fas fa-shopping-cart"></i>
                            <p>Reservas em curso</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('reserves.unauthorized') }}" class="nav-link">
                            <i class="nav-icon fas fa-shopping-cart"></i>
                            <p>Reservas não Autorizadas</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('reserves.completed') }}" class="nav-link">
                            <i class="nav-icon fas fa-shopping-cart"></i>
                            <p>Reservas Finalizadas</p>
                        </a>
                    </li>
                    <h6 class="dropdown-header">Espaço LIA</h6>
                    <li class="nav-item">
                        <a href="{{ route('space.index') }}" class="nav-link">
                            <i class="fa-solid fas fa-laptop"></i>
                            <p>Espaço Lia</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('space.reservas') }}" class="nav-link">
                            <i class="fa-solid fas fa-laptop"></i>
                            <p>Reservas</p>
                        </a>
                    </li>
                    <h6 class="dropdown-header">Outros</h6>
                    <li class="nav-item">
                        <a href="{{ route('user.index') }}" class="nav-link">
                            <i class="fa-solid fas fa-user"></i>
                            <p>Utilizadores</p>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    @endif
</aside>
