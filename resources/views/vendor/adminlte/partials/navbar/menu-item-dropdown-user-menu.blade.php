@php( $logout_url = View::getSection('logout_url') ?? config('adminlte.logout_url', 'logout') )
@php( $profile_url = View::getSection('profile_url') ?? config('adminlte.profile_url', 'logout') )

@if (config('adminlte.usermenu_profile_url', false))
@php( $profile_url = Auth::user()->adminlte_profile_url() )
@endif

@if (config('adminlte.use_route_url', false))
@php( $profile_url = $profile_url ? route($profile_url) : '' )
@php( $logout_url = $logout_url ? route($logout_url) : '' )
@else
@php( $profile_url = $profile_url ? url($profile_url) : '' )
@php( $logout_url = $logout_url ? url($logout_url) : '' )
@endif

<li class="nav-item user-menu d-flex">
    {{-- Link para a página inicial --}}
    <a href="/admin/home" class="nav-link">Painel</a>

    {{-- Botão de logout --}}
    <a class="nav-link" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
        Sair
    </a>

    {{-- Formulário de logout --}}
    <form id="logout-form" action="{{ $logout_url }}" method="POST" style="display: none;">
        @if(config('adminlte.logout_method'))
        {{ method_field(config('adminlte.logout_method')) }}
        @endif
        {{ csrf_field() }}
    </form>
</li>