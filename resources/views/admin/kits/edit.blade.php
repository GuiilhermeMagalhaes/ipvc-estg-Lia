@extends('adminlte::page')

@section('title', 'Editar Kit')

@section('content')
<br>
    <div class="d-flex flex-column">
        {{-- 1. Rota alterada para o Update enviando o ID do Kit --}}
        <form action="{{ route('kits.update', $kit->id) }}" enctype="multipart/form-data" method="POST" novalidate>
            @csrf
            {{-- 2. Mudança obrigatória para PUT em formulários de edição --}}
            @method('PUT')
            
            <div class="form-group">
                <label for="name">Nome</label>
                {{-- Usa o old() ou o valor atual do modelo se o old não existir --}}
                <input type="text" name="name" class="form-control" value="{{ old('name', $kit->name) }}">
                <span style="color:red">{{$errors->first('name')}}</span>
            </div>

            <div class="form-group">
                <label for="descricao">Descrição</label>
                <input type="text" name="description" class="form-control" value="{{ old('description', $kit->description) }}">
                <span style="color:red">{{$errors->first('description')}}</span>
            </div>

            <div class="form-group">
                <label for="ref_ipvc">Referência IPVC</label>
                <input type="text" name="ipvc_ref" class="form-control" value="{{ old('ipvc_ref', $kit->ipvc_ref) }}">
                <span style="color:red">{{$errors->first('ipvc_ref')}}</span>
            </div>

            <div class="form-group">
                <label for="preco">Preço (€)</label>
                <input type="number" name="price" step="0.01" id="preco" class="form-control" value="{{ old('price', $kit->price) }}">
                <span style="color:red">{{$errors->first('price')}}</span>
            </div>

            <div class="form-group">
                <label for="price_day">Preço por Dia (€)</label>
                <input type="number" name="price_day" step="0.01" id="price_day" class="form-control" value="{{ old('price_day', $kit->price_day) }}">
                <span style="color:red">{{$errors->first('price_day')}}</span>
            </div>

            <div class="form-group">
                <label for="image">Alterar Imagem do Kit</label>
                <input type="file" class="form-control-file" name="image" id="image">
                @if($errors->has('image'))
                    <span style="color:red">{{ $errors->first('image') }}</span>
                @endif

                {{-- Exibe uma miniatura da imagem atual, se ela existir na BD --}}
               {{-- @if($kit->image)
                    <div class="mt-3">
                        <p class="small text-secondary mb-1">Imagem atual:</p>
                        <img src="{{ asset('storage/' . $kit->image) }}" alt="Imagem do Kit" class="img-thumbnail" style="max-height: 120px;">
                    </div>
                @endif
                --}}
            </div>

            {{-- Botões de fluxo mudados para o contexto de Edição --}}
            <div class="mt-4">
                <button type="submit" class="btn btn-primary" style="width: 160px; margin-left: 5px;">Atualizar Kit</button>
            </div>
        </form>
        <br>
    </div>
@endsection