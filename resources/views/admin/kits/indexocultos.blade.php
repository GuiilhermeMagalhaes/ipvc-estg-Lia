@extends('adminlte::page')

@section('title', 'Kits Ocultos')

@section('content')
<div>
    <br>
    <form action="#" class="search-form">
        <input id="search" class="form-control search-input" name="search" type="text" placeholder="Procurar kits..." style="width: 24%;" />
    </form>
    <br>
    <div class="row mycard">
        @foreach($kits as $kit)
        <div class="col-sm-3 mb-4">
            <div class="card h-100">
                <div class="card-body d-flex flex-column justify-content-center text-center">
                    <h5 class="card-title">{{$kit->name}}</h5>
                    <p class="card-text">{{$kit->lia_code}}</p>
                    <p class="card-text">{{ number_format($kit->price, 2, ',', '.') }} € / dia</p>
                    <a class="btn btn-primary mx-auto" style="width: 140px;" href="{{ route('kits.show', ['id' => $kit->id])}}">VER DETALHES</a>
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
            url: "{{ route('kits.indexocultos') }}",
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
