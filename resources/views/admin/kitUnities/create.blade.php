@extends('adminlte::page')

@section('title', 'Configurar Unidades do Kit')

@section('content')
<style>
    
.selected-items-list {
    min-height: 40px;
    border: 1px dashed #ced4da;
    padding: 10px;
    background: #ffffff;
    border-radius: 4px;
}

.available-items-container {
    max-height: 280px;
    overflow-y: auto;
}

.available-item {
    cursor: pointer;
    transition: background 0.2s;
}

.available-item:hover {
    background: #f0f8ff;
}
</style>

<br>
<div class="d-flex flex-column">
     <p class="text-dark list-group-item-text" style="font-size: 1.2rem;">Códigos LIA para as {{ $quantity }} Unidades de "{{ $kitName }}" </p> 
    <hr class="w-100" style="margin-left: 0;">

    <form action="{{ route('kits.storeUnities') }}" method="POST" class="w-100" novalidate >
        @csrf
        @method('POST')

        
        @for ($i = 0; $i < $quantity; $i++)
            <div class="mb-5">
                <label>Código LIA da Unidade #{{ $i + 1 }}</label>
                
                {{-- Campo do Código LIA desta unidade específica --}}
                <div class="form-group">
                    <input type="text" name="lia_codes[{{ $i }}]" class="form-control" value="{{ old('lia_codes.'.$i) }}" required>
                    @if($errors->has("lia_codes.$i"))
                        <span style="color:red">{{ $errors->first("lia_codes.$i") }}</span>
                    @endif
                </div>

                
                <div class="form-group">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="m-0">Itens integrados nesta Unidade:</label>
                        {{-- Botão elegante para disparar o Modal --}}
                        <button type="button" class="btn btn-sm btn-outline-primary open-selector-btn" data-unit="{{ $i }}">
                            <i class="fas fa-plus"></i> Associar Itens
                        </button>
                    </div>

                    
                    <div class="alert alert-danger py-2 px-3 mb-2 d-none hidden-item-warning" id="warning-unity-{{ $i }}">
                        <i class="fas fa-exclamation-triangle"></i> Selecionou um item oculto! Esta unidade vai ficar com estado oculto automaticamente.
                    </div>

                    <div class="selected-items-list" id="selected-container-{{ $i }}">
                        <p class="text-muted m-0 small visual-placeholder">Nenhum item adicionado a esta unidade.</p>
                    </div>

                    @if($errors->has("items_for_unity.$i"))
                        <span style="color:red; display:block;" class="mt-1 small">
                            {{ $errors->first("items_for_unity.$i") }}
                        </span>
                    @endif
                </div>
            </div>
            @if($i < $quantity - 1)
                <hr class="my-4">
            @endif
        @endfor

        
        <div class="d-flex mb-5 mt-4">
            <button type="button" onclick="window.history.back();" class="btn btn-secondary" style="width: 140px;">Voltar</button>
            <button type="submit" class="btn btn-primary" style="width: 180px; margin-right: 10px;">Criar Kit</button>
        </div>
    </form>
</div>


<div class="modal fade" id="itemSelectorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title">Adicionar Itens à <span id="modal-unit-title" class="font-weight-bold">Unidade</span></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="p-1">
                    <h6><i class="fas fa-search"></i> Procurar e Adicionar Itens Livres</h6>
                    <input type="text" class="form-control mb-3" id="search-items-modal" placeholder="Pesquise itens por nome ou código lia..." autocomplete="off">
                    
                    <div class="available-items-container border p-2 rounded bg-white">
                        @foreach ($itensLivres as $item)
                            
                            <div class="available-item d-flex justify-content-between align-items-center mb-2 p-2 bg-light border rounded"
                                data-nome="{{ $item->item->nome ?? 'Sem Nome' }}" 
                                data-code="{{ $item->lia_code }}"
                                data-state="{{ $item->item_unity_state_id }}">
                                <span>
                                    <strong>{{ $item->item->nome ?? 'Sem Nome' }}</strong> 
                                   <span class="ml-3 text-secondary" style="font-size: 0.95rem; font-weight: 500;">
                                        {{ $item->lia_code }}
                                    </span>
                                    
                                   
                                    @if($item->item_unity_state_id == 2)
                                        <span class="ml-2" style="color: red; font-size: 0.8rem; font-weight: bold;">Oculto</span>
                                    @endif
                                </span>
                                <button type="button" class="btn btn-primary btn-sm modal-add-btn" 
                                        data-id="{{ $item->id }}" 
                                        data-nome="{{ $item->item->nome ?? 'Sem Nome' }}" 
                                        data-code="{{ $item->lia_code }}"
                                        data-state="{{ $item->item_unity_state_id }}">
                                    Adicionar
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-white">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Concluir</button>
            </div>
        </div>
    </div>
</div>

{{-- Teu Modal de Alerta Original adaptado --}}
<div class="modal fade" id="itemAlreadySelectedModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title text-white">Aviso</h5>
            </div>
            <div class="modal-body text-center">
                <p class="font-weight-bold m-0">Este item já foi adicionado nesta ou noutra unidade deste kit!</p>
                <p style="color:gray;" class="small mt-2 mb-0">Clique em "Ok" para prosseguir.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Ok</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function () {
    
    let activeUnitId = null;

    // Ação ao clicar no botão "+ Associar Itens" de qualquer unidade
    $(document).on('click', '.open-selector-btn', function() {
        activeUnitId = $(this).attr('data-unit');
        
        $('#modal-unit-title').text('Unidade #' + (parseInt(activeUnitId) + 1));
        
        $('#search-items-modal').val('');
        $('.available-item').show();
        
        $('.modal-add-btn').each(function() {
            const itemId = $(this).attr('data-id');
            if ($(`input[value="${itemId}"][name^="items_for_unity"]`).length > 0) {
                $(this).prop('disabled', true).text('Adicionado');
            } else {
                $(this).prop('disabled', false).text('Adicionar');
            }
        });

        $('#itemSelectorModal').modal('show');
    });

    // Pesquisa simplificada e funcional por atributos limpos
    $(document).on('keyup', '#search-items-modal', function() {
        var valor = $(this).val().toLowerCase().trim();
        
        $('.available-items-container .available-item').each(function() {
            var nome = ($(this).attr('data-nome') || '').toLowerCase();
            var codigo = ($(this).attr('data-code') || '').toLowerCase();
            
            if (nome.indexOf(valor) > -1 || codigo.indexOf(valor) > -1) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

    // Evento de adicionar item
    $(document).on('click', '.modal-add-btn', function(e) {
        e.preventDefault();
        
        const btn = $(this);
        const itemId = btn.attr('data-id');
        const nome = btn.attr('data-nome');
        const code = btn.attr('data-code');
        const state = btn.attr('data-state'); 

        if ($(`input[value="${itemId}"][name^="items_for_unity"]`).length > 0) {
            $('#itemAlreadySelectedModal').modal('show');
            return;
        }

        $(`#selected-container-${activeUnitId} .visual-placeholder`).remove();

        const itemRow = `
            <div class="d-flex justify-content-between align-items-center mb-2 p-2 border rounded bg-light style-row" 
                 id="selected-item-${itemId}" data-state="${state}">
                <input type="hidden" name="items_for_unity[${activeUnitId}][]" value="${itemId}">
                <span>
                    <strong>${nome}</strong> 
                    <span class="ml-3 text-secondary" style="font-size: 0.95rem; font-weight: 500;">(${code})</span>
                    ${state == 2 ? '<span class="badge badge-danger ml-2">Oculto</span>' : ''}
                </span>
                <button type="button" class="btn btn-danger btn-sm remove-item-btn" data-id="${itemId}" data-unit="${activeUnitId}">X</button>
            </div>
        `;

        $(`#selected-container-${activeUnitId}`).append(itemRow);
        btn.prop('disabled', true).text('Adicionado');

        
        verificarItensOcultos(activeUnitId);
    });

    // Evento de remover item
    $(document).on('click', '.remove-item-btn', function() {
        const itemId = $(this).attr('data-id');
        const unitId = $(this).attr('data-unit');

        $(`#selected-item-${itemId}`).remove();

        if ($(`#selected-container-${unitId} .style-row`).length === 0) {
            $(`#selected-container-${unitId}`).html('<p class="text-muted m-0 small visual-placeholder">Nenhum item adicionado a esta unidade.</p>');
        }

        
        verificarItensOcultos(unitId);
    });

    // Função global de deteção
    function verificarItensOcultos(unitId) {
        let temOculto = false;
        
        $(`#selected-container-${unitId} .style-row`).each(function() {
            if ($(this).attr('data-state') == '2') {
                temOculto = true;
            }
        });

        if (temOculto) {
            $(`#warning-unity-${unitId}`).removeClass('d-none');
        } else {
            $(`#warning-unity-${unitId}`).addClass('d-none');
        }
    }
});
</script>
@endsection