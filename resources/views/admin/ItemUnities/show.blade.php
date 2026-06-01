@extends('adminlte::page')

@section('title', 'Item')

@section('content')
<br>
<div class="container-fluid">
    <div class="row">
        <div class="col-4">
            <div class="container-fluid">
                <img id="img" src="../../{{ $item->image }}" width=400px class="rounded">
            </div>
        </div>

        <div class="col-6">
            <div>
                <ul class="list-group">
                    <li class="list-group-item">Nome : {{ $item->nome }}</li>
                    <li class="list-group-item">Modelo : {{ $item->model }}</li>
                    
                    <form action="{{ route('unidades.updateUnity', $unidade->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <li class="list-group-item d-flex align-items-center">
                            <span class="mr-2">Código LIA:</span>
                            <input type="text" name="lia_code" class="form-control form-control-sm" value="{{ $unidade->lia_code }}" style="width: 150px; display: inline-block;">
                        </li>
                        
                        <li class="list-group-item d-flex align-items-center">
                            <span class="mr-2">Estado:</span>
                            <select name="item_unity_state_id" class="form-control form-control-sm mr-2" style="width: 150px; display: inline-block;">
                                <option value="1" {{ $unidade->item_unity_state_id == 1 ? 'selected' : '' }}>Ativo (Visível)</option>
                                <option value="2" {{ $unidade->item_unity_state_id == 2 ? 'selected' : '' }}>Oculto</option>
                            </select>
                            <button type="submit" class="btn btn-sm btn-success">Gravar Unidade</button>
                        </li>
                    </form>

                    <li class="list-group-item">Referência IPVC : {{ $item->ipvc_ref }}</li>
                    <li class="list-group-item">Número de Série : {{ $item->serial_number }}</li>
                    <li class="list-group-item">Preço / dia : {{ number_format($item->preco, 2, ',', '.') }} €</li>

                   

                    @foreach ($categoria as $cat)
                    @if ($item->categoria_id == $cat->id)
                    <li class="list-group-item">Categoria : {{ $cat->description }}</li>
                    @endif
                    @endforeach
                    
                    <li class="list-group-item">Observações : {{ $item->observation }}</li>
                    <li class="list-group-item">Acessórios : {{ $item->acessorio }}</li>
                     <li class="list-group-item bg-light font-weight-bold">
                        Mais unidades do mesmo item: 
                    </li>
                    
                    <li class="list-group-item">Quantidade Total : {{ $item->quantity }}</li>
                    <li class="list-group-item">Quantidade Disponível : {{ $item->quantity_disp }}</li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="container-fluid d-flex justify-content-end">
                                <form action="{{ route('itens.destroy', $item->id) }}" method="POST">
                                    <a href="{{ route('itens.edit', $item->id) }}" class="btn btn-primary" style="width: 140px;">Editar</a>
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger" style="width: 140px;">Eliminar</button>
                                </form>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection