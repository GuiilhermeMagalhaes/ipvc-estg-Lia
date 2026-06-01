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
                <label for="ipvc_ref">Referência IPVC</label>
                <input type="text" name="ipvc_ref" class="form-control" value="{{ old('ipvc_ref', $item->ipvc_ref) }}">
                <span style="color:red">{{$errors->first('ipvc_ref')}}</span>
            </div>
            <div class="form-group">
                <label for="lia_code">Código LIA</label>
                <input type="text" name="lia_code" class="form-control" value="{{ old('lia_code', $item->lia_code) }}">
                <span style="color:red">{{$errors->first('lia_code')}}</span>
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
                <label for="data_aquisicao">Data de Aquisição</label>
                <input type="date" name="data_aquisicao" id="data_aquisicao" class="form-control" value="{{ old('data_aquisicao', $item->data_aquisicao ? $item->data_aquisicao->format('Y-m-d') : '') }}">
                <span style="color:red">{{$errors->first('data_aquisicao')}}</span>
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
                <label for="categoria_id">Categoria</label>
                <br>
                <select id="categoria_id" name="categoria_id">
                    @foreach ($categorias as $cat)
                        <option value={{ $cat->id }} <?php if($item->categoria_id == $cat->id){echo("selected");}?>>{{ $cat->description }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="item_state_id">Estado</label>
                <br>
                <select id="item_state_id" name="item_state_id">
                    <option value=1 <?php if($item->item_state_id == '1'){echo("selected");}?>>Visível</option>
                    <option value=2 <?php if($item->item_state_id == '2'){echo("selected");}?>>Oculto</option>
                    <option value=3 <?php if($item->item_state_id == '3'){echo("selected");}?>>Anulado</option>
                </select>
            </div>
            <div class="form-group">
                <label for="">Imagem para Item</label>
                <input type="file" class="form-control-file" name="image" id="image">
            </div>
            <button type="submit" class="btn btn-success" style="width: 140px; float:right;">Atualizar Item</button>
            <br><br>
        </form>
        <br>
    </div>
@endsection