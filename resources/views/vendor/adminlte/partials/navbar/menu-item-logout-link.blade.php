@php( $logout_url = View::getSection('logout_url') ?? config('adminlte.logout_url', 'logout') )

@if (config('adminlte.use_route_url', false))
@php( $logout_url = $logout_url ? route($logout_url) : '' )
@else
@php( $logout_url = $logout_url ? url($logout_url) : '' )
@endif

{{-- Botão de logout --}}
<a class="nav-link" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
    {{ __('adminlte::adminlte.log_out') }}
</a>

{{-- Formulário de logout --}}
<form id="logout-form" action="{{ $logout_url }}" method="POST" style="display: none;">
    @if(config('adminlte.logout_method'))
    {{ method_field(config('adminlte.logout_method')) }}
    @endif
    {{ csrf_field() }}
</form>