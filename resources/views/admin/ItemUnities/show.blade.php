@extends('adminlte::page')

@section('title', 'Item')

@section('content')
<br>
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="font-weight-bold text-dark">{{ $item->nome }}</h1>
            <p class="text-muted list-group-item-text" style="font-size: 1.2rem;">Modelo: <strong>{{ $item->model }}</strong></p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 col-sm-12 mb-4">
            <div class="text-center text-md-left">
                <img id="img" src="../../{{ $item->image }}" class="img-fluid rounded shadow-sm" style="max-width: 400px; width: 100%;">
            </div>
        </div>

        <div class="col-md-8 col-sm-12">
            
            <div class="mb-4">
                <h6 class="text-dark font-weight-bold mb-3">Informações da Unidade Atual</h6>
                
                <form id="form-unidade" action="{{ route('unidades.updateUnity', $unidade->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <ul class="list-group shadow-sm">
                        <li class="list-group-item d-flex align-items-center">
                            <span>Código LIA: </span>
                            <input type="text" id="lia_code" name="lia_code" class="form-control form-control-sm" value="{{ $unidade->lia_code }}" style="width: 180px; display: inline-block;">
                        </li>
                        
                        <li class="list-group-item d-flex align-items-center">
                            <span>Estado: </span>
                            <select id="item_unity_state_id" name="item_unity_state_id" class="form-control form-control-sm mr-2" style="width: 180px; display: inline-block;">
                                <option value="1" {{ $unidade->item_unity_state_id == 1 ? 'selected' : '' }}>Ativo (Visível)</option>
                                <option value="2" {{ $unidade->item_unity_state_id == 2 ? 'selected' : '' }}>Oculto</option>
                                <option value="4" {{ $unidade->item_unity_state_id == 4 ? 'selected' : '' }}>Manutenção</option>
                            </select>
                        </li>
                        
                        <li class="list-group-item d-flex align-items-center">
                            <span style="width: 150px; display: inline-block;">Data de Aquisição: </span>
                            <input type="date" id="data_aquisicao" name="data_aquisicao" class="form-control form-control-sm" value="{{ $unidade->data_aquisicao ? $unidade->data_aquisicao->format('Y-m-d') : '' }}" max="{{ date('Y-m-d') }}" style="width: 180px; display: inline-block;">
                        </li>

                         <li class="list-group-item">
                            <span>Tempo de Vida: </span>
                            {{ $unidade->tempo_de_vida }}
                        </li>
                    </ul>
                </form>
            </div>

                <div class="mb-4 pt-5">
                {{-- Alinhamento vertical do Título com o Botão Editar à direita --}}
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="text-dark font-weight-bold m-0">Informações do Item</h6>
                    <a href="{{ route('itens.edit', $item->id) }}" class="btn btn-primary" style="width: 140px;">Editar Item</a>
                </div>
                
                <ul class="list-group shadow-sm">
                    {{-- Bloco de Identificação --}}
                    <li class="list-group-item">Categoria: {{ $item->itemCategorie->description ?? 'Sem categoria' }}</li>
                    <li class="list-group-item">Número de Série: {{ $item->serial_number ?? 'Sem número de série'}}</li>
                    <li class="list-group-item">Referência IPVC: {{ $item->ipvc_ref ?? 'Não definida' }}</li>

                    {{-- Bloco Financeiro e Ciclo de Vida --}}
                    <li class="list-group-item">Preço do Item: {{ $item->preco ? number_format($item->preco, 2, ',', '.') . ' €' : 'Não registado' }}</li>
                    <li class="list-group-item">Preço / dia: {{ $item->price_day ? number_format($item->price_day, 2, ',', '.') . ' €' : '0,00 €' }}</li>
                    
                    {{-- Detalhes Adicionais --}}
                    <li class="list-group-item">Acessórios: {{ $item->acessorio ?? 'Nenhum acessório registado' }}</li>
                    <li class="list-group-item">Observações: {{ $item->observation ?? 'Nenhuma observação' }}</li>
                    
                    {{-- Bloco de Inventário/Stock --}}
                   
                    <li class="list-group-item text-muted italic small">
                         Mais unidades do mesmo item registadas: 
                    </li>

                     <li class="list-group-item bg-light">
                        Quantidade Total: {{ $item->quantity }}
                    </li>

                    @if(isset($unidadesDoItem) && $unidadesDoItem->count() > 0)
                        <li class="list-group-item bg-light text-break">
                            Códigos LIA:
                            @foreach($unidadesDoItem as $u)
                                {{ $u->lia_code }}{{ !$loop->last ? ' ' : '' }}
                            @endforeach
                        </li>
                    @endif
        
                    
                   
                </ul>

                 {{-- Botões de Ação do Item --}}
                    <div class="mt-3 pt-4">
                        
                           <form action="{{ route('unidades.anular', $unidade->id) }}" method="POST" class="form-inline" onsubmit="return confirm('Tem a certeza que deseja anular esta unidade?');">

                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger" style="width: 150px;">Eliminar Unidade</button>
                            </form>
                       
            </div>

        </div>
    </div>
</div>
@endsection

@section('js')
<script>
    $(document).ready(function() {
        let liaOriginalValue = $('#lia_code').val();
        let dataOriginalValue = $('#data_aquisicao').val();

        // 1. Quando o Select muda de estado, grava na hora
        $('#item_unity_state_id').on('change', function() {
            $('#form-unidade').submit();
        });

        $('#data_aquisicao').on('change', function() {
            if ($(this).val() !== dataOriginalValue) {
                $('#form-unidade').submit();
            }
        });

        // 2. Quando o utilizador clica fora do input do Código LIA (Blur)
        $('#lia_code').on('blur', function() {
            if ($(this).val() !== liaOriginalValue) {
                $('#form-unidade').submit();
            }
        });

        // Grava ao carregar no "Enter" dentro do input LIA
        $('#lia_code').on('keypress', function(e) {
            if (e.which == 13) {
                e.preventDefault();
                $(this).blur();
            }
        });
         @if($errors->any())
            let mensagemErro = "{{ $errors->first() }}";
            alert(mensagemErro);
        @endif
    });

</script>
@endsection