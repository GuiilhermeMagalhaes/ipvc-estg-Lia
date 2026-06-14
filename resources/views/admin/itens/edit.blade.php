@php
@endphp
@extends('adminlte::page')

@section('title', 'Editar Item')

@section('content')
<br>
    <div class="d-flex flex-column">
        <form action="{{ route('itens.update', $item->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label for="nome">Nome</label>
                <input type="text" name="nome" class="form-control" value="{{ old('nome', $item->nome) }}">
                <span style="color:red">{{$errors->first('nome')}}</span>
            </div>
            <div class="form-group">
                <label for="model">Modelo</label>
                <input type="text" name="model" class="form-control" value="{{ old('model', $item->model) }}">
                <span style="color:red">{{$errors->first('model')}}</span>
            </div>

             <div class="form-group">
                <label for="categoria_id">Categoria</label>
                <select id="categoria_id" name="categoria_id" class="form-control">
                    @foreach ($categorias as $cat)
                        {{-- O old() recebe um segundo parâmetro que serve como valor padrão (o que está na BD) --}}
                        <option value="{{ $cat->id }}" {{ old('categoria_id', $item->categoria_id) == $cat->id ? 'selected' : '' }}>
                            {{ $cat->description }}
                        </option>
                    @endforeach
                </select>
                <span style="color:red">{{ $errors->first('categoria_id') }}</span>
            </div>


            <div class="form-group">
                <label for="ipvc_ref">Referência IPVC</label>
                <input type="text" name="ipvc_ref" class="form-control" value="{{ old('ipvc_ref', $item->ipvc_ref) }}">
                <span style="color:red">{{$errors->first('ipvc_ref')}}</span>
            </div>
            <div class="form-group">
                <label for="serial_number">Número de série</label>
                <input type="text" name="serial_number" class="form-control" value="{{ old('serial_number', $item->serial_number) }}">
                <span style="color:red">{{$errors->first('serial_number')}}</span>
            </div>
            <div class="form-group">
                <label for="preco">Preço</label>
                <input type="number" name="preco" id="preco" class="form-control" step="0.01" value="{{ old('preco', $item->preco) }}">
                <span style="color:red">{{$errors->first('preco')}}</span>
            </div>
            <div class="form-group">
                <label for="observation">Observações</label>
                <input type="text" name="observation" class="form-control" value="{{ old('observation', $item->observation) }}">
                <span style="color:red">{{$errors->first('observation')}}</span>
            </div>
            <div class="form-group">
                <label for="acessorio">Acessórios</label>
                <input type="text" name="acessorio" class="form-control" value="{{ old('acessorio', $item->acessorio) }}">
                <span style="color:red">{{$errors->first('acessorio')}}</span>
            </div>
            <div class="form-group">
                <label for="preco">Preço</label>
                <input type="number" name="preco" id="preco" class="form-control" step="0.01" value="{{ old('preco', $item->preco) }}">
                <span style="color:red">{{$errors->first('preco')}}</span>
            </div>
            <div class="form-group">
                <label for="price_day">Preço por Dia</label>
                <input type="number" name="price_day" id="price_day" class="form-control" step="0.01" value="{{ old('price_day', $item->price_day) }}">
                <span style="color:red">{{$errors->first('price_day')}}</span>
            </div>

            <div class="form-group">
                <label for="quantity">Quantidade Total</label>
                <input type="number" name="quantity" id="quantity" class="form-control" value="{{ old('quantity', $item->quantity) }}">
                <span style="color:red">{{$errors->first('quantity')}}</span>
            </div>

           
            <div class="form-group">
                <label for="">Imagem para Item</label>
                <input type="file" class="form-control-file" name="image" id="image">
            </div>
            <button type="submit" class="btn btn-primary" style="width: 140px; float:right;">Atualizar Item</button>
            <br><br>
        </form>
        <br>
    </div>
@endsection