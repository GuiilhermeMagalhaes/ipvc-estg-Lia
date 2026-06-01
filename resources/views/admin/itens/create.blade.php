@extends('adminlte::page')

@section('title', 'Novo Item')

@section('content')
<br>
    <div class="d-flex flex-column">
        <form action="{{ route('itens.store') }}" enctype="multipart/form-data" method="POST">
            @csrf
            @method('POST')
            <div class="form-group">
                <label for="nome">Nome</label>
                <input type="text" name="nome" class="form-control" value="{{ old('nome') }}">
                <span style="color:red">{{$errors->first('nome')}}</span>
            </div>
            <div class="form-group">
                <label for="model">Modelo</label>
                <input type="text" name="model" class="form-control" value="{{ old('model') }}">
                <span style="color:red">{{$errors->first('model')}}</span>
            </div>

            <div class="form-group">
                <label for="categoria_id">Categoria</label>
                <select id="categoria_id" name="categoria_id" class="form-control">
                    @foreach ($categorias as $cat)
                        <option value="{{ $cat->id }}" {{ old('categoria_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->description }}
                        </option>
                    @endforeach
                </select>
                <span style="color:red">{{$errors->first('categoria_id')}}</span>
            </div>
            <!--<div class="form-group">
                <label for="lia_code">Código LIA</label>
                <input type="text" name="lia_code" class="form-control" value="{{ old('lia_code') }}">
                <span style="color:red">{{$errors->first('lia_code')}}</span>
            </div>
-->
            <div class="form-group">
                <label for="serial_number">Número de série</label>
                <input type="text" name="serial_number" class="form-control" value="{{ old('serial_number') }}">
                <span style="color:red">{{$errors->first('serial_number')}}</span>
            </div>
            <div class="form-group">
                <label for="ipvc_ref">Referência IPVC</label>
                <input type="text" name="ipvc_ref" class="form-control" value="{{ old('ipvc_ref') }}">
                <span style="color:red">{{$errors->first('ipvc_ref')}}</span>
            </div>
             <div class="form-group">
                <label for="preco">Preço do Item</label>
                <input type="number" name="preco" id="preco" class="form-control" step="0.01" value="{{ old('preco') }}">
                <span style="color:red">{{$errors->first('preco')}}</span>
            </div>
            <div class="form-group">
                <label for="price_day">Preço por Dia (Aluguer/Requisição)</label>
                <input type="number" name="price_day" id="price_day" class="form-control" step="0.01" value="{{ old('price_day') }}">
                <span style="color:red">{{$errors->first('price_day')}}</span>
            </div>
            
                 <div class="form-group">
                <label for="data_aquisicao">Data de Aquisição</label>
                <input type="date" name="data_aquisicao" id="data_aquisicao" class="form-control" value="{{ old('data_aquisicao') }}">
                <span style="color:red">{{$errors->first('data_aquisicao')}}</span>
            </div>

             <div class="form-group">
                <label for="acessorio">Acessórios</label>
                <input type="text" name="acessorio" id="acessorio" class="form-control" value="{{ old('acessorio') }}">
                <span style="color:red">{{$errors->first('acessorio')}}</span>
            </div>

    
           

            
            <div class="form-group">
                <label for="observation">Observações</label>
                <input type="text" name="observation" id="observation" class="form-control" value="{{ old('observation') }}">
                <span style="color:red">{{$errors->first('observation')}}</span>
            </div>
            
             <div class="form-group">
                <label for="quantity">Quantidade Total</label>
                <input type="number" name="quantity" id="quantity" class="form-control" value="{{ old('quantity') }}">
                <span style="color:red">{{$errors->first('quantity')}}</span>
            </div>
            <!--
            <div class="form-group">
                <label for="item_state_id">Estado</label>
                <br>
                <select id="item_state_id" name="item_state_id">
                    <option value=1>Visível</option>
                    <option value=2>Oculto</option>
                    <option value=3>Anulado</option>
                </select>
            </div>
            -->
            <div class="form-group">
                <label for="">Imagem para Item</label>
                <input type="file" class="form-control-file" name="image" id="image">
            </div>
            <button type="submit" class="btn btn-primary" style="width: 140px;">Seguinte</button>
        </form>
        <br>
    </div>
@endsection
