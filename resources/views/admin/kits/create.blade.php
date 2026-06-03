@extends('adminlte::page')

@section('title', 'Novo Kit')

@section('content')
<br>
    <div class="d-flex flex-column">
        <form action="{{ route('kits.store') }}" enctype="multipart/form-data" method="POST">
            @csrf
            @method('POST')
            <div class="form-group">
                <label for="name">Nome</label>
                <input type="text" name="name" class="form-control" value="{{ old('name') }}">
                <span style="color:red">{{$errors->first('name')}}</span>
            </div>
            <div class="form-group">
                <label for="descricao">Descrição</label>
                <input type="text" name="description" class="form-control" value="{{ old('description') }}">
                <span style="color:red">{{$errors->first('description')}}</span>
            </div>
            <div class="form-group">
                <label for="ref_ipvc">Referência IPVC</label>
                <input type="text" name="ipvc_ref" class="form-control" value="{{ old('ipvc_ref') }}">
                <span style="color:red">{{$errors->first('ref_ipvc')}}</span>
            </div>
            <div class="form-group">
                <label for="preco">Preço</label>
                <input type="number" name="price" step="0.01" id="preco" class="form-control" value="{{ old('price') }}">
                <span style="color:red">{{$errors->first('price')}}</span>
            </div>
            <div class="form-group">
                <label for="price_day">Preço por Dia (€)</label>
                <input type="number" name="price_day" step="0.01" id="price_day" class="form-control" value="{{ old('price_day') }}">
                <span style="color:red">{{$errors->first('price_day')}}</span>
            </div>
            <div class="form-group">
                <label for="quantity">Quantidade Total</label>
                <input type="number" name="quantity" id="quantity" class="form-control" value="{{ old('quantity') }}">
                <span style="color:red">{{$errors->first('quantity')}}</span>
            </div>
            <!--
            <div class="form-group">
                <label for="categoria_id">Categoria</label>
                <br>
                <select id="categoria_id" name="categoria_id">
                    @foreach ($categorias as $cat)
                        <option value={{ $cat->id }}>{{ $cat->description }}</option>
                    @endforeach
                </select>
            </div>
            -->
            <button type="submit" class="btn btn-success" style="width: 140px;">Seguinte</button>
        </form>
        <br>
    </div>
@endsection