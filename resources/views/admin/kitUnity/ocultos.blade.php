@extends('adminlte::page')

@section('title', 'Unidades Ocultas')

@section('content')
<div>
    <br>
    
    <form action="#" class="search-form" onsubmit="return false;">
        <input id="search-ocultos" class="form-control search-input" name="search" type="text" placeholder="Procurar kits ocultos..." style="width: 30%;" autocomplete="off" />
    </form>
    <br>
    
    <div class="row mycard">
        @forelse($unidades as $unidade)
        <div class="col-sm-3 mb-4">
            <div class="card h-100 border-secondary">
                <div class="card-body d-flex flex-column justify-content-center text-center">
                    {{-- Nome do Kit Pai --}}
                    <h5 class="card-title font-weight-bold">{{ $unidade->kit->name }}</h5>
                    
                    {{-- Código LIA da Unidade --}}
                    <p class="text-dark mb-1"><strong>LIA:</strong> {{ $unidade->lia_code }}</p>
                    
                    {{-- Preço por dia do Kit Pai --}}
                    <p class="card-text">{{ number_format($unidade->kit->price_day, 2, ',', '.') }} € / dia</p>
                    
                    {{-- Rota para os detalhes --}}
                    <a class="btn btn-secondary mx-auto" style="width: 140px;" href="{{ route('kitUnity.show', ['id' => $unidade->id]) }}">VER DETALHES</a>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12 text-center my-5">
            <p class="text-muted">Nenhuma unidade oculta encontrada.</p>
        </div>
        @endforelse
    </div>
</div>

<script type="text/javascript">
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // AJAX LIVE SEARCH adaptado para a rota de ocultos
    $('#search-ocultos').on('keyup', function() {
        var value = $(this).val().trim(); 
        
        $.ajax({
            type: "get",
            // IMPORTANTE: Altera 'kitUnity.ocultos' para o nome correto que deres à tua rota no web.php
            url: "{{ route('kits.indexocultos') }}", 
            data: {
                'search': value
            },
            success: function(data) {
                // Caso a pesquisa retorne vazia no AJAX, exibe uma mensagem amigável
                if(data.trim() === "") {
                    $('.mycard').html('<div class="col-12 text-center my-5"><p class="text-muted">Nenhum resultado corresponde à sua pesquisa.</p></div>');
                } else {
                    $('.mycard').html(data); 
                }
            },
            error: function(xhr, status, error) {
                console.error('Erro na requisição Ajax:', status, error);
            }
        });
    });
</script>
@endsection