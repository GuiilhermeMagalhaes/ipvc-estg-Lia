@php
@endphp
@extends('adminlte::page')

@section('title', 'Editar Categoria')

@section('content')
<br>
<div class="d-flex flex-column">
    <div class="row">
        <div class="container-fluid">
            <form action="{{ route('category.update', $categoria->id) }}" method="post" enctype="multipart/form-data">
                @csrf
                @method('POST')
                <div class="form-group">
                    <label for="description">Nome</label>
                    <input type="text" name="description" class="form-control" value="{{ old('description', $categoria->description) }}">
                    <span style="color:red">{{ $errors->first('description') }}</span>
                </div>
                <div class="form-group">
                    <label for="">Imagem para Categoria</label>
                    <input type="file" class="form-control-file" name="image" id="image">
                </div>
                <button type="submit" class="btn btn-success">Atualizar Categoria</button>
            </form>
        </div>
    </div>
</div>
@endsection