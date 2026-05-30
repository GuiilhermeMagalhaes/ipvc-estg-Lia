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
                    
                    <li class="list-group-item">Código LIA : <span >{{ $unidade->lia_code }}</span></li>
                    
                    <li class="list-group-item">Estado: 
                        @if($unidade->item_unity_state_id == 1)
                            <span >{{ $unidade->itemUnityState->description ?? 'Ativo' }}</span>
                        @else
                            <span>{{ $unidade->itemUnityState->description ?? 'Oculto' }}</span>
                        @endif
                    </li>

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