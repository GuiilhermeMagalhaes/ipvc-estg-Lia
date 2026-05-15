@extends('adminlte::page')

@section('title', 'Categorias')

@section('content')
<br>
<a href="{{ route('category.create') }}" class="btn btn-success" style="width: 140px;">Nova categoria</a>
<p></p>
<div class="row">
    @foreach ($categories as $category)
    <div class="col-sm-4">
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h5>{{ $category->description }}</h5>
                    </div>
                    <div class="col-auto">
                        <a class="btn btn-primary" href="{{ route('category.edit', ['id' => $category->id]) }}">Editar</a>
                    </div>
                </div>
            </div>
            <div class="card-content">
                <img src="../../{{ $category->image }}" class="imgcardcategories">
            </div>
        </div>
    </div>
    @endforeach
</div>

<style>
    .imgcardcategories {
        width: 100%;
        height: 300px;
        object-fit: cover;
    }
</style>
@endsection