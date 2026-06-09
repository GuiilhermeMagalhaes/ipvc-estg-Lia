@extends('adminlte::page')

@section('title', 'Itens em Manutenção')

@section('content')
<br>
<div>
    <form action="#" class="search-form">
        <input id="search" class="form-control search-input" name="search" type="text" placeholder="Procurar Itens em Manutenção..." style="width: 24%;" />
    </form>
    <br>
    <div class="row mycard">
        @foreach($unidades as $unidade)
        <div class="col-sm-3 mb-4">
            <div class="card h-100">
                <div class="card-body d-flex flex-column justify-content-center text-center">
                    <h1 class="card-title">{{ $unidade->item->nome }}</h1>
                    <small class="text-muted mb-2">Ref: {{ $unidade->item->ipvc_ref }}</small>
                    <p class="text-muted mb-2">LIA: {{ $unidade->lia_code }}</p>
                    <p class="card-text card-text-preco">{{ number_format($unidade->item->price_day, 2, ',', '.') }} € / dia</p>
                    <a class="btn btn-primary mx-auto" style="width: 140px;" href="{{ route('itens.show', ['id' => $unidade->id]) }}">VER DETALHES</a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

<script type="text/javascript">
    $.ajaxSetup({
        headers: {
            'csrftoken': '{{ csrf_token() }}'
        }
    });
    $('#search').on('keyup', function() {
        var value = $(this).val().trim();
        $.ajax({
            type: "get",
            url: "{{ route('itens.manutencao') }}", {{-- ← alterado --}}
            data: {
                'search': value
            },
            success: function(data) {
                $('.mycard').html(data);
            },
            error: function(xhr, status, error) {
                console.error('Erro na requisição Ajax:', error);
            }
        });
    });
</script>
@endsection