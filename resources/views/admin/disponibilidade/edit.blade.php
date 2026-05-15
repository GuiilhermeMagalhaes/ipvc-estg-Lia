@php
@endphp
@extends('adminlte::page')

@section('title', 'Editar Horário')

@section('content')
<br>
<div class="container-fluid">
    <div class="d-flex flex-column">
        <form action="{{ route('disponibilidade.update', $horario->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="descricao">Descrição (Horas)</label>
                <input type="text" name="descricao" class="form-control">
                <span style="color:red">{{ $errors->first('descricao') }}</span>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 140px;">Editar Horário</button>
        </form>
    </div>
</div>
@endsection