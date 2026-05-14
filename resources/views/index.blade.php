@extends('adminlte::master')

@section('adminlte_css_pre')
    <link rel="stylesheet" href="/css/custom.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@latest/css/boxicons.min.css">
@endsection

@section('body')
    @include('layouts.navbar')
    <div class="wrapper">
        <div class="{{ config('adminlte.classes_content') ?: 'container' }}">
            @yield('content')
        </div>

        <!-- Footer específico para o index -->
        @if(request()->is('/')) <!-- Verifica se está na página principal (index) -->
            <footer class="bg-light text-center py-3 mt-auto">
                <div class="container">
                    <span class="text-muted">Auditoria realizada por Alunos do Curso de Engenharia de Computação Gráfica e Multimédia.</span>
                </div>
            </footer>
        @endif
    </div>
@endsection

@section('adminlte_js')

@stop