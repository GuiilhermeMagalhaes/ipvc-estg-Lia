@section('title', 'Kits')

@section('content_header')
    <h1>{{$kit->description}}</h1>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-4">
                <div class="container-fluid">
                    <img id="img" src="https://images.unsplash.com/photo-1502920917128-1aa500764cbd?ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&ixlib=rb-1.2.1&auto=format&fit=crop&w=2250&q=80" height="340px">
                </div>
            </div>

            <div class="col-6">
                <div>
                    <ul class="list-group">
                        <li class="list-group-item">Código LIA: {{ $kit->lia_code }}</li>
                        <li class="list-group-item">Categoria:</li>
                        <li class="list-group-item">Preço: {{ $kit->price }}€</li>
                        <li class="list-group-item">Referência IPVC: {{ $kit->ipvc_ref }}</li>
                        <li class="list-group-item">Observações: {{ $kit->observation }}</li>
                    </ul>
                </div>
                <br>
                <div class="row">
                    <div class="container-fluid">
                        <a href="/admin/kits/{{ $kit->id }}/edit" class="btn btn-primary">Editar</a>
                        @csrf
                        @method('DELETE')
                        <a href="/admin/kits/{{ $kit->id }}" class="btn btn-danger">Eliminar</a>
                    </div>
                </div>
            </div>
        </div>
        <br>
        
        @isset($kit->items[0])
            <h3>Items incluidos no conjunto</h3>
            <div class="row col-12">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Descrição</th>
                            <th>Modelo</th>
                            <th>Serial Number</th>
                            <th>Ref IPVC</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($kit->items as $item)
                                <tr>
                                    <td scope="row">{{ $item->description }}</td>
                                    <td>{{ $item->model }}</td>
                                    <td>{{ $item->serial_number }}</td>
                                    <td>{{ $item->ipvc_ref }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                </table>
            </div>
        @endisset
        
        <br>
        @isset($kit->kits[0])
            <h3>Conjuntos incluidos</h3>
            <div class="row col-12">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Descrição</th>
                            <th>Código LIA</th>
                            <th>Ref IPVC</th>
                            <th>Observações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($kit->kits as $item)
                            <tr data-widget="expandable-table" aria-expanded="false">
                                <td>{{ $item->description }}</td>
                                <td>{{ $item->lia_code }}</td>
                                <td>{{ $item->ipvc_ref }}</td>
                                <td>{{ $item->observation }}</td>
                            </tr>
                            <tr class="expandable-body d-none">
                                <td colspan="4">
                                    <div>
                                        <table class="table">
                                            <tbody>
                                                @foreach ($item->items as $data)
                                                    <tr>
                                                        <td>
                                                            {{ $data->description }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endisset
    </div>
@stop