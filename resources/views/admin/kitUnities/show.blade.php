@extends('adminlte::page')

@section('title', 'Kit')

@section('content')
<br>
<div class="container-fluid">
    {{-- Cabeçalho com Nome do Kit --}}
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="font-weight-bold text-dark">{{ $kit->name }}</h1>
        </div>
    </div>

    <div class="row">
        {{-- Coluna da Imagem --}}
        <div class="col-md-4 col-sm-12 mb-4">
            <div class="text-center text-md-left">
                <img id="img" src="{{ asset('storage/' . $kit->image) }}" class="img-fluid rounded shadow-sm" style="max-width: 400px; width: 100%; object-fit: cover;">
            </div>
        </div>

        {{-- Coluna das Informações (Lado Direito) --}}
        <div class="col-md-8 col-sm-12">
            
            {{-- 1. Bloco de Informações da Unidade Atual --}}
            <div class="mb-4">
                <h6 class="text-dark font-weight-bold mb-3">Informações da Unidade Atual</h6>
                
                <form id="form-unidade" action="{{ route('kits.updateUnity', $unidade->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <ul class="list-group shadow-sm">
                        <li class="list-group-item d-flex align-items-center">
                            <span class="mr-2">Código LIA: </span>
                            <input type="text" id="lia_code" name="lia_code" class="form-control form-control-sm" value="{{ $unidade->lia_code }}" style="width: 180px; display: inline-block;">
                        </li>
                        
                        <li class="list-group-item d-flex align-items-center">
                            <span class="mr-2">Estado: </span>
                            <select id="kit_unity_state_id" name="kit_unity_state_id" class="form-control form-control-sm mr-2" style="width: 180px; display: inline-block;">
                                <option value="1" {{ $unidade->kit_unity_state_id == 1 ? 'selected' : '' }}>Ativo (Visível)</option>
                                <option value="2" {{ $unidade->kit_unity_state_id == 2 ? 'selected' : '' }}>Oculto</option>
                            </select>
                        </li>
                    </ul>

                    {{-- Envia os itens atuais ocultos para que o envio automático por blur/change não desassocie os itens --}}
                    @foreach($unidade->itemUnities as $itemUnity)
                        <input type="hidden" name="items_kept[]" value="{{ $itemUnity->id }}">
                    @endforeach
                </form>
            </div>

            {{-- 2. Lista de Itens Vinculados a esta Unidade --}}
            <div class="mb-4 pt-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="text-dark font-weight-bold m-0">Itens Incluídos no Conjunto</h6>
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalGerarItens" style="width: 140px;">
                        Gerir Itens
                    </button>
                </div>

                @if($unidade->itemUnities->count() > 0)
                    <div class="table-responsive shadow-sm rounded">
                        <table class="table table-striped table-hover bg-white m-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Nome</th>
                                    <th>Modelo</th>
                                    <th>Preço / dia</th>
                                    <th>Ref IPVC</th>
                                    <th>Código LIA</th>  
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($unidade->itemUnities as $itemUnity)
                                <tr>
                                    <td><strong>{{ $itemUnity->item->nome }}</strong></td>
                                    <td>{{ $itemUnity->item->model ?? 'N/A' }}</td>
                                    <td>{{ number_format($itemUnity->item->price_day ?? 0, 2, ',', '.') }} €</td>
                                    <td><span class="badge badge-light p-2 border">{{ $itemUnity->item->ipvc_ref ?? 'N/A' }}</span></td>
                                    <td><span class="badge badge-secondary p-2">LIA: {{ $itemUnity->lia_code }}</span></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    {{-- CORRIGIDO: Removida a div de fecho órfã que estragava o layout --}}
                    <div class="alert alert-light border text-muted italic small shadow-sm">
                        Nenhum item associado a esta unidade de kit. Clique em "Gerir Itens" para adicionar.
                    </div>
                @endif
            </div>

            {{-- 3. Bloco de Informações Gerais do Kit --}}
            <div class="mb-4 pt-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="text-dark font-weight-bold m-0">Informações do Kit</h6>
                    <a href="{{ route('kits.edit', $kit->id) }}" class="btn btn-primary" style="width: 140px;">Editar Kit</a>
                </div>
                
                <ul class="list-group shadow-sm">
                    <li class="list-group-item">Descrição: {{ $kit->description }}</li>
                    <li class="list-group-item">Preço: {{ number_format($kit->price, 2, ',', '.') }} €</li>
                    <li class="list-group-item">Preço / dia : {{ number_format($kit->price_day, 2, ',', '.') }} € / dia</li>
                    <li class="list-group-item bg-light">Quantidade Total: {{ $kit->quantity }}</li>
                </ul>

                {{-- Botão de Eliminar/Anular Unidade --}}
                <div class="mt-3 pt-4 mb-5">
                    <form action="{{ route('kitUnity.destroy', $unidade->id) }}" method="POST" class="form-inline" onsubmit="return confirm('Tem a certeza que deseja anular esta unidade de kit? Os itens associados serão libertados.');">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger" style="width: 160px;">Eliminar Unidade</button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- MODAL DE GESTÃO DE ITENS --}}
<div class="modal fade" id="modalGerarItens" tabindex="-1" role="dialog" aria-labelledby="modalGerarItensLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title" id="modalGerarItensLabel">Gerir Componentes da Unidade</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('kits.updateUnity', $unidade->id) }}" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="lia_code" value="{{ $unidade->lia_code }}">
                <input type="hidden" name="kit_unity_state_id" value="{{ $unidade->kit_unity_state_id }}">

                <div class="modal-body">
                    <p class="text-muted mb-3">Marque os itens que deseja manter ou associar a esta unidade de kit. Desmarque os que pretende remover.</p>
                    
                    <h6 class="font-weight-bold text-secondary">Itens Atuais do Kit</h6>
                    <div class="table-responsive mb-4">
                        <table class="table table-striped table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th width="10%">Manter</th>
                                    <th>Item</th>
                                    <th>Código LIA</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($unidade->itemUnities as $itemUnity)
                                    <tr>
                                        <td>
                                            <input type="checkbox" name="items_kept[]" value="{{ $itemUnity->id }}" checked style="transform: scale(1.3);">
                                        </td>
                                        <td><strong>{{ $itemUnity->item->nome }}</strong></td>
                                        <td><span class="text-muted">{{ $itemUnity->lia_code }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <h6 class="font-weight-bold text-success">Adicionar Novos Itens Disponíveis</h6>
                    <div class="table-responsive" style="max-height: 250px; overflow-y: auto;">
                        <table class="table table-striped table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th width="10%">Incluir</th>
                                    <th>Item</th>
                                    <th>Código LIA</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if($itensLivres->count() > 0)
                                    @foreach($itensLivres as $itemLivre)
                                        <tr>
                                            <td>
                                                <input type="checkbox" name="items_kept[]" value="{{ $itemLivre->id }}" style="transform: scale(1.3);">
                                            </td>
                                            <td>{{ $itemLivre->item->nome }}</td>
                                            <td><span class="text-muted">{{ $itemLivre->lia_code }}</span></td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="3" class="text-center text-muted small">Nenhum item avulso disponível no sistema neste momento.</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                    <button type="submit" class="btn btn-success">Salvar Alterações</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
    $(document).ready(function() {
        let liaOriginalValue = $('#lia_code').val();

        // 1. Quando o Select muda de estado, grava automaticamente
        $('#kit_unity_state_id').on('change', function() {
            $('#form-unidade').submit();
        });

        // 2. Quando o utilizador clica fora do input do Código LIA (Blur)
        $('#lia_code').on('blur', function() {
            if ($(this).val().trim() !== liaOriginalValue) {
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