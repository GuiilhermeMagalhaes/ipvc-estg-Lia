@extends('adminlte::page')

@section('title', 'Kit')

@section('content')
<br>
<div class="container-fluid">
    
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="font-weight-bold text-dark">{{ $kit->name }}</h1>
        </div>
    </div>

    <div class="row">
       
          <div class="col-md-4 col-sm-12 mb-4">
    <div class="text-center text-md-left">
        <img id="img" 
             src="../../{{ $kit->image }}" 
             class="img-fluid rounded shadow-sm" 
             style="max-width: 400px; width: 100%; height: 310px; object-fit: contain;">
    </div>
</div>

       
        <div class="col-md-8 col-sm-12">
            
            
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

                       {{-- O bloco só fica visível se o estado for 2 (Oculto) --}}
                        <li class="list-group-item" id="bloco-observacoes" style="display: {{ $unidade->kit_unity_state_id == 2 ? 'block' : 'none' }};">
                            <span class="mr-2 d-block mb-2">Motivo / Observações: </span>
                            <textarea id="observacoes" name="observacoes" class="form-control form-control-sm" rows="2" placeholder="Ex: Fecho da mala partido, a aguardar reparação.">{{ $unidade->observacoes }}</textarea>
                        </li>
                        {{-- FIM DO BLOCO --}}
                    </ul>   

                    
                    @foreach($unidade->itemUnities as $itemUnity)
                        <input type="hidden" name="items_kept[]" value="{{ $itemUnity->id }}">
                    @endforeach
                </form>
            </div>

            
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
                        <th>Código LIA</th>  
                        <th>Estado</th> 
                    </tr>
                </thead>
                <tbody>
    @foreach ($unidade->itemUnities as $itemUnity)
    <tr>
        <td><strong>{{ $itemUnity->item->nome }}</strong></td>
        <td>{{ $itemUnity->item->model ?? 'N/A' }}</td>
        <td>{{ number_format($itemUnity->item->price_day ?? 0, 2, ',', '.') }} €</td>
        <td>{{ $itemUnity->lia_code }}</td> 
        <td>
            <span class="badge 
                @if($itemUnity->item_unity_state_id == 1) badge-success 
                @elseif($itemUnity->item_unity_state_id == 2) badge-secondary 
                @elseif($itemUnity->item_unity_state_id == 4) badge-warning 
                @else badge-danger @endif">
                {{ $itemUnity->itemUnityState->description ?? 'Estado ' . $itemUnity->item_unity_state_id }}
            </span>

            {{-- AQUI ESTÁ A PARTE QUE MOSTRA O PORQUÊ PARA OS ITENS DENTRO DA MALA --}}
            @if(!empty($itemUnity->observacoes) && in_array($itemUnity->item_unity_state_id, [2, 4]))
                <span class="d-block text-muted mt-1 small" style="line-height: 1.2;">
                    <i class="fas fa-exclamation-triangle text-warning"></i> {{ $itemUnity->observacoes }}
                </span>
            @endif
        </td>
    </tr>
    @endforeach
</tbody>
            </table>
        </div>
    @else
        <div class="alert alert-light border text-muted italic small shadow-sm">
            Nenhum item associado a esta unidade de kit. Clique em "Gerir Itens" para adicionar.
        </div>
    @endif
</div>

            
            <div class="mb-4 pt-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="text-dark font-weight-bold m-0">Informações do Kit</h6>
                    <a href="{{ route('kits.edit', $kit->id) }}" class="btn btn-primary" style="width: 140px;">Editar Kit</a>
                </div>
                
                <ul class="list-group shadow-sm">
                    <li class="list-group-item">Descrição: {{ $kit->description }}</li>
                    <li class="list-group-item">Referência IPVC: {{ $kit->ipvc_ref ?? 'Não definida' }}</li>
                    <li class="list-group-item">Preço: {{ number_format($kit->price, 2, ',', '.') }} €</li>
                    <li class="list-group-item">Preço / dia : {{ number_format($kit->price_day, 2, ',', '.') }} € / dia</li>
                    <li class="list-group-item bg-light">Quantidade Total: {{ $kit->quantity }}</li>

                    <li class="list-group-item text-muted italic small">
                        Mais unidades do mesmo kit registadas: 
                    </li>


                    @if(isset($unidadesDoKit) && $unidadesDoKit->count() > 0)
                    <li class="list-group-item bg-light p-0">
                        <table class="table table-sm m-0">
                            <thead>
                                <tr>
                                    <th class="border-top-0 pl-3">Código LIA</th>
                                    <th class="border-top-0">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($unidadesDoKit as $u)
                                <tr>
                                    <td class="align-middle pl-3">
                                        <a href="{{ route('kits.show', $u->id) }}" style="color: black; font-weight: 500;">
                                            {{ $u->lia_code }}
                                        </a>
                                    </td>
                                    <td class="align-middle">
                                        @if($u->kit_unity_state_id == 1)
                                            <span class="badge badge-success">Ativo</span>
                                        @elseif($u->kit_unity_state_id == 2)
                                            <span class="badge badge-secondary">Oculto</span>
                                        @elseif($u->kit_unity_state_id == 3)
                                            <span class="badge badge-danger">Anulado</span>
                                        @endif

                                        @php $reservaAtual = $u->reservaAtiva(); @endphp
                                        @if($reservaAtual)
                                            <span class="badge badge-info ml-1">
                                                <i class="fas fa-user-check"></i> Em Uso (Reserva #{{ $reservaAtual }})
                                            </span>
                                        @endif

                                        {{-- AQUI ESTÁ A PARTE QUE MOSTRA O PORQUÊ DA MALA ESTAR OCULTA --}}
                                        @if(!empty($u->observacoes) && $u->kit_unity_state_id == 2)
                                            <span class="d-block text-muted mt-1 small" style="line-height: 1.2;">
                                                <i class="fas fa-info-circle"></i> {{ $u->observacoes }}
                                            </span>
                                        @endif

                                       
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </li>
                    @endif
                </ul>

                
                <div class="mt-3 pt-4 mb-5">
                    <form action="{{ route('kitUnity.destroy', $unidade->id) }}" method="POST" class="form-inline" onsubmit="return confirm('Tem a certeza que deseja anular esta unidade de kit? Os itens associados serão libertados.');">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger" style="width: 150px;">Eliminar Unidade</button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>


<div class="modal fade" id="modalGerarItens" tabindex="-1" role="dialog" aria-labelledby="modalGerarItensLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title" id="modalGerarItensLabel">Gerir Itens do Conjunto</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            
            <form id="form-gerar-itens" action="{{ route('kits.updateUnity', $unidade->id) }}" method="POST">
                @csrf
                @method('PUT')
                {{-- Inputs ocultos sincronizados com o formulário principal --}}
                <input type="hidden" id="modal_lia_code" name="lia_code" value="{{ $unidade->lia_code }}">
                <input type="hidden" id="modal_kit_state_id" name="kit_unity_state_id" value="{{ $unidade->kit_unity_state_id }}">

                <div class="modal-body">
                    {{-- Espaço de Alerta Dinâmico para Itens Ocultos --}}
                    <div id="aviso-item-oculto" class="alert alert-danger">
                        Existem itens com o estado Oculto selecionados. Ao gravar, esta unidade de Kit passará automaticamente para o estado Oculto.
                    </div>

                    <p class="text-muted mb-3">Selecione os itens que farão parte desta unidade de kit. Desmarque para remover.</p>
                    
                    {{-- Barra de Pesquisa Geral dentro do Modal --}}
                    <div class="mb-3">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                            </div>
                            <input type="text" class="form-control" id="search-items-modal" placeholder="Pesquise componentes por nome ou código LIA..." autocomplete="off">
                        </div>
                    </div>

                    {{-- Lista 1: Itens que já estão associados atualmente --}}
                    <h6 class="font-weight-bold text-secondary mb-2">Itens Atuais do Kit</h6>
                    <div class="available-items-container border p-2 rounded bg-white mb-4" style="max-height: 200px; overflow-y: auto;">
                        @foreach($unidade->itemUnities as $itemUnity)
                            <div class="item-row d-flex justify-content-between align-items-center mb-2 p-2 bg-light border rounded"
                                 data-nome="{{ $itemUnity->item->nome ?? 'Sem Nome' }}" 
                                 data-code="{{ $itemUnity->lia_code }}">
                                <span>
                                    <input type="checkbox" name="items_kept[]" value="{{ $itemUnity->id }}" class="check-item mr-2" data-state="{{ $itemUnity->item_unity_state_id }}" checked style="transform: scale(1.2);">
                                    <strong>{{ $itemUnity->item->nome ?? 'Sem Nome' }}</strong>
                                    <span class="ml-3 text-secondary small">{{ $itemUnity->lia_code }}</span>
                                    @if($itemUnity->item_unity_state_id == 2)
                                          <span class="ml-2" style="color: red; font-size: 0.8rem; font-weight: bold;">Oculto</span>
                                    @elseif($itemUnity->item_unity_state_id == 4)
                                          <span class="ml-2 text-warning" style="font-size: 0.8rem; font-weight: bold;">Manutenção</span>
                                    @endif
                                </span>
                            </div>
                        @endforeach
                    </div>

                    {{-- Lista 2: Novos Itens Livres (Apenas Ativos/Ocultos sem Kit) --}}
                    <h6 class="font-weight-bold text-secondary mb-2">Adicionar Novos Itens</h6>
                    <div class="available-items-container border p-2 rounded bg-white" style="max-height: 200px; overflow-y: auto;">
                        @if($itensLivres->count() > 0)
                            @foreach($itensLivres as $itemLivre)
                                <div class="item-row d-flex justify-content-between align-items-center mb-2 p-2 bg-light border rounded"
                                     data-nome="{{ $itemLivre->item->nome ?? 'Sem Nome' }}" 
                                     data-code="{{ $itemLivre->lia_code }}">
                                    <span>
                                        <input type="checkbox" name="items_kept[]" value="{{ $itemLivre->id }}" class="check-item mr-2" data-state="{{ $itemLivre->item_unity_state_id }}" style="transform: scale(1.2);">
                                        <strong>{{ $itemLivre->item->nome ?? 'Sem Nome' }}</strong>
                                        <span class="ml-3 text-secondary small">{{ $itemLivre->lia_code }}</span>
                                        @if($itemLivre->item_unity_state_id == 2)
                                            <span class="ml-2" style="color: red; font-size: 0.8rem; font-weight: bold;">Oculto</span>
                                        @elseif($itemLivre->item_unity_state_id == 4)
                                            <span class="ml-2 text-warning" style="font-size: 0.8rem; font-weight: bold;">Manutenção</span>
                                        @endif
                                    </span>
                                </div>
                            @endforeach
                        @else
                            <div class="text-center text-muted p-3 small">Nenhum item livre e elegível no sistema neste momento.</div>
                        @endif
                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                    <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
    $(document).ready(function() {

        // 1. VARIÁVEIS INICIAIS
        let liaOriginalValue = $('#lia_code').val();
        let obsOriginalValue = $('#observacoes').val();

        
        // 2. AUTO-SAVE (OBSERVAÇÕES, ESTADO E LIA)
        // Auto-save das observações ao clicar fora
        $('#observacoes').on('blur', function() {
            if ($(this).val().trim() !== (obsOriginalValue || '').trim()) {
                $('#form-unidade').submit();
            }
        });

        // Sincroniza e faz auto-save do Estado
        $('#kit_unity_state_id').on('change', function() {
            $('#modal_kit_state_id').val($(this).val());
            $('#form-unidade').submit();
        });

        // Auto-save do LIA Code ao clicar fora
        $('#lia_code').on('blur', function() {
            if ($(this).val().trim() !== liaOriginalValue) {
                $('#form-unidade').submit();
            }
        });

        // Previne submissão ao carregar no Enter no campo LIA (faz blur em vez disso)
        $('#lia_code').on('keypress', function(e) {
            if (e.which == 13) {
                e.preventDefault();
                $(this).blur(); 
            }
        });

        // Sincroniza o LIA com o input escondido do Modal
        $('#lia_code').on('input change blur', function() {
            $('#modal_lia_code').val($(this).val().trim());
        });

        // ==========================================
        // 3. LÓGICA DO MODAL (GERIR ITENS)
        // ==========================================
        
        function verificarItensOcultosSelecionados() {
            let itemOcultoMarcado = false;
            
            $('.check-item:checked').each(function() {
                let estadoItem = $(this).data('state');
                if (estadoItem == 2 || estadoItem == "2" || estadoItem == 4 || estadoItem == "4") {
                    itemOcultoMarcado = true;
                }
            });

            if (itemOcultoMarcado) {
                $('#aviso-item-oculto').removeClass('d-none');
            } else {
                $('#aviso-item-oculto').addClass('d-none');
            }
        }

        $(document).on('change', '.check-item', function() {
            verificarItensOcultosSelecionados();
        });

        $('#modalGerarItens').on('shown.bs.modal', function () {
            verificarItensOcultosSelecionados();
        });

        // Pesquisa no Modal
        $('#search-items-modal').on('keyup', function() {
            let valor = $(this).val().toLowerCase().trim();
            
            $('#modalGerarItens .item-row').each(function() {
                let nome = $(this).data('nome') ? $(this).data('nome').toString().toLowerCase() : '';
                let code = $(this).data('code') ? $(this).data('code').toString().toLowerCase() : '';
                
                if (nome.includes(valor) || code.includes(valor)) {
                    $(this).removeClass('d-none').addClass('d-flex');
                } else {
                    $(this).removeClass('d-flex').addClass('d-none');
                }
            });
        });

        // Validação ao submeter o Modal
        $('#form-gerar-itens').on('submit', function(e) {
            let totalMarcados = $('.check-item:checked').length;

            if (totalMarcados === 0) {
                e.preventDefault();
                alert('Ação bloqueada! O kit não pode ficar sem nenhum item associado. Selecione pelo menos um componente.');
                return false;
            }

            let temOculto = !$('#aviso-item-oculto').hasClass('d-none');
            if (temOculto) {
                let confirmacao = confirm('Os itens ocultos/em manutenção selecionados vão forçar esta unidade de Kit a ficar no estado Oculto.');
                if (!confirmacao) {
                    e.preventDefault();
                    return false;
                }
            }
        });

        // ==========================================
        // 4. MENSAGENS DE ERRO (TOASTS)
        // ==========================================
        @if($errors->any())
            let mensagemErro = "{{ $errors->first() }}";
            alert(mensagemErro);
        @endif
    });
</script>
@endsection