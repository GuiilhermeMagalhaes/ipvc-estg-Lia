@extends('adminlte::page')

@section('title', 'Kit')

@section('content')
<br>
<div class="container-fluid">
    <div class="row">
        <div class="col-4">
            <div class="container-fluid">
                <img id="img" src="../../{{ $kit->image }}" width=400px class="rounded">
            </div>
        </div>

        <div class="col-6">
            <div>
                <ul class="list-group">
                    <li class="list-group-item">Nome : {{ $kit->name }}</li>
                    <li class="list-group-item">Referência IPVC : {{ $kit->ipvc_ref }}</li>
                     <li class="list-group-item">Código LIA : {{ $kit->lia_code }}</li>
                    <li class="list-group-item">Preço / dia : {{ number_format($kit->price, 2, ',', '.') }} €</li>
                    @foreach ($categoria as $cat)
                    @if ($kit->categoria_id == $cat->id)
                    <li class="list-group-item">Categoria : {{ $cat->description }}</li>
                    @endif
                    @endforeach
                    <li class="list-group-item">Descrição : {{ $kit->description }}</li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="container-fluid d-flex justify-content-end">
                                <form action="{{ route('kits.destroy', $kit->id) }}" method="POST">
                                    <a href="{{ route('kits.edit', $kit->id)}}" class="btn btn-primary" style="width: 140px;">Editar</a>
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
    <br>

    @isset($kit->items[0])
    <h3>Itens incluidos no conjunto</h3>
    <div class="row col-12">
        <table class="table">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Modelo</th>
                    <th>Preço</th>
                    <th>Serial Number</th>
                    <th>Ref IPVC</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($kit->items as $item)
                <tr>
                    <td>{{ $item->nome }}</td>
                    <td>{{ $item->model }}</td>
                    <td>{{ $item->preco }}</td>
                    <td>{{ $item->serial_number }}</td>
                    <td>{{ $item->ipvc_ref }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endisset
</div>
@endsection