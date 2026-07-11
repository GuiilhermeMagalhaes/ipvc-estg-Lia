@extends('adminlte::page')

@section('title', 'Kits Ocultos')

@section('content')
<div>
    <br>
    
   <form action="#" class="search-form" onsubmit="return false;" style="display:flex; gap:10px; align-items:center;">
        <input id="search-ocultos" class="form-control search-input" name="search" type="text" placeholder="Procurar kits ocultos..." style="width: 30%;" autocomplete="off" />
        <select id="sort-ocultos" class="form-control" style="width: 220px;">
            <option value="nome">Ordenar por: Nome (A-Z)</option>
            <option value="lia">Ordenar por: Código LIA</option>
        </select>
    </form>
    <br>
    
    <div class="row mycard">
        @forelse($unidades as $unidade)
        <div class="col-sm-3 mb-4">
            <div class="card h-100 border-secondary">
                <div class="card-body d-flex flex-column justify-content-center text-center">
                 
                    <h5 class="card-title ">{{ $unidade->kit->name }}</h5>
                    <small class="text-muted mb-2">Ref: {{ $unidade->kit->ipvc_ref ?? 'N/A' }}</small>
                    
                   
                    <p class="text-muted mb-2">LIA: {{ $unidade->lia_code }}</p>
                    
                    
                    <p class="card-text card-text-preco">{{ number_format($unidade->kit->price_day, 2, ',', '.') }} € / dia</p>
                    
                   
                    <a class="btn btn-primary mx-auto" style="width: 140px;" href="{{ route('kits.show', ['id' => $unidade->id]) }}">VER DETALHES</a>
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
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

    function carregarKitsOcultos() {
        $.ajax({
            type: "get",
            url: "{{ route('kits.indexocultos') }}",
            data: { 'search': $('#search-ocultos').val().trim(), 'sort': $('#sort-ocultos').val() },
            success: function(data) {
                if (data.trim() === "") {
                    $('.mycard').html('<div class="col-12 text-center my-5"><p class="text-muted">Nenhum resultado corresponde à sua pesquisa.</p></div>');
                } else {
                    $('.mycard').html(data);
                }
            },
            error: function(xhr, status, error) { console.error('Erro na requisição Ajax:', status, error); }
        });
    }

    $('#search-ocultos').on('keyup', carregarKitsOcultos);
    $('#sort-ocultos').on('change', carregarKitsOcultos);
</script>
@endsection