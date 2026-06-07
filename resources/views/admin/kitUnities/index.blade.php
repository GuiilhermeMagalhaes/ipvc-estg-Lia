@extends('adminlte::page')

@section('title', 'Kits')

@section('content')
<div>
    <br>
    <form action="#" class="search-form">
        <input id="search" class="form-control search-input" name="search" type="text" placeholder="Procurar kits..." style="width: 24%;" />
    </form>
    <br>
   <div class="row mycard">
        {{-- Alterado de $kits para $unidades --}}
        @foreach($unidades as $unidade)
        <div class="col-sm-3 mb-4">
            <div class="card h-100">
                <div class="card-body d-flex flex-column justify-content-center text-center">
                    {{-- Nome do Kit Pai --}}
                    <h5 class="card-title ">{{ $unidade->kit->name }}</h5>
                    <small class="text-muted mb-2">Ref: {{ $unidade->kit->ipvc_ref ?? 'N/A' }}</small>
                    
                    {{-- Código LIA da Unidade --}}
                    <p class="text-muted mb-2">LIA: {{ $unidade->lia_code }}</p>
                    
                    {{-- Preço por dia do Kit Pai (price_day) --}}
                    <p class="card-text card-text-preco">{{ number_format($unidade->kit->price_day, 2, ',', '.') }} € / dia</p>
                    
                    {{-- Rota apontando para o ID da unidade --}}
                    <a class="btn btn-primary mx-auto" style="width: 140px;" href="{{ route('kits.show', ['id' => $unidade->id]) }}">VER DETALHES</a>
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
    // AJAX LIVE SEARCH
    $('#search').on('keyup', function() {
        var value = $(this).val().trim(); // Captura o valor e remove espaços em branco extras
        $.ajax({
            type: "get",
            url: "{{ route('kits.index') }}",
            data: {
                'search': value
            },
            success: function(data) {
                $('.mycard').html(data); // Substitui o conteúdo atual com os novos resultados
            },
            error: function(xhr, status, error) {
                console.error('Erro na requisição Ajax:', status, error);
            }
        });
    });
</script>
@endsection
