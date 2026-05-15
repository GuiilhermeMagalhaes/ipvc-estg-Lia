@php
@endphp
@extends('index')

@section('content')
<link href="/css/custom.css" rel="stylesheet">
<div class="page-title">
    <nav class="breadcrumbs">
        <div class="container d-flex justify-content-between align-items-center">
            <ol class="d-flex mb-0">
                <li><a href="/"><i class="bi bi-house"></i></a></li>
                <li><a href="/perfil">Perfil</a></li>
                <li><a class="current" href="#">Editar Perfil</a></li>
            </ol>
        </div>
    </nav>
</div>
<br>
    <div class="d-flex flex-column">
        <form action="{{ route('perfil.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label for="name">Nome</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', Auth::user()->name) }}">
                <span style="color:red">{{$errors->first('name')}}</span>
            </div>
            <div class="form-group">
                <label for="phone">Telemóvel</label>
                <input type="number" name="phone" class="form-control" value="{{ old('phone', Auth::user()->phone) }}">
                <span style="color:red">{{$errors->first('phone')}}</span>
            </div>
            <div class="form-group">
                <label for="tipo_user">Tipo de Utilizador</label>
                <br>
                <select name="tipo_user" id="tipo_user">
                    <option value=1 <?php if(Auth::user()->tipo_user == '1'){echo("selected");}?>>Aluno</option>
                    <option value=2 <?php if(Auth::user()->tipo_user == '2'){echo("selected");}?>>Docente</option>
                    <option value=3 <?php if(Auth::user()->tipo_user == '3'){echo("selected");}?>>Funcionário</option>
                    <option value=4 <?php if(Auth::user()->tipo_user == '4'){echo("selected");}?>>Outro</option>
                </select>
            </div>
            <div class="form-group">
                <label for="curso">Curso</label>
                <input type="text" name="curso" class="form-control" value="{{ old('curso', Auth::user()->curso) }}">
                <span style="color:red">{{$errors->first('curso')}}</span>
            </div>
            <div class="form-group">
                <label for="n_aluno">Número</label>
                <input type="text" name="n_aluno" class="form-control" value="{{ old('n_aluno', Auth::user()->n_aluno) }}">
                <span style="color:red">{{$errors->first('n_aluno')}}</span>
            </div>
            <div class="form-group">
                <label for="">Foto de Perfil</label>
                <input type="file" class="form-control-file" name="image" id="image">
            </div>
            <button type="submit" class="btn btn-outline-dark mt-auto" style="width: 140px;">Atualizar Dados</button>
            <p></p>
        </form>
    </div>

    <script>
        $(document).ready(function() {
        $('[data-toggle="popover"]').popover();
    });
    </script>
@endsection