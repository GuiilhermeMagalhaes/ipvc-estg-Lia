@section('title', 'Kits')

@section('content_header')
    <h1>Kits</h1>
@stop

<div>
    <input wire:model="search" class="form-control" type="text" placeholder="Procurar kits..."/>
    <br>
    <div class="row">
        @foreach($kits as $kit)
            <div class="col-sm-3">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">{{$kit->description}}</h4>
                        <p class="card-text">{{$kit->lia_code}}</p>
                        <p class="card-text card-text-preco">{{$kit->price}}€</p>
                        <button class="btn btn-primary" wire:click="showKit({{ $kit->id }})">VER DETALHES</button>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
