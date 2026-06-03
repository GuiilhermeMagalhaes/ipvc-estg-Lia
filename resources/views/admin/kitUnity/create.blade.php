@extends('adminlte::page')

@section('title', 'Configurar Unidades do Kit')

@section('content')
<style>
/* Mantendo o teu estilo original de caixas e scroll */
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

    <form action="{{ route('kits.storeUnities') }}" method="POST" class="w-100">
        @csrf
        @method('POST')

        {{-- Loop dinâmico com base na quantidade inserida no Passo 1 --}}
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

                {{-- Contentor de Itens Selecionados para ESTA unidade --}}
                <div class="form-group">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="m-0">Itens integrados nesta Unidade:</label>
                        {{-- Botão elegante para disparar o Modal --}}
                        <button type="button" class="btn btn-sm btn-outline-primary open-selector-btn" data-unit="{{ $i }}">
                            <i class="fas fa-plus"></i> + Associar Itens
                        </button>
                    </div>
                    <div class="selected-items-list" id="selected-container-{{ $i }}">
                        <p class="text-muted m-0 small visual-placeholder">Nenhum item adicionado a esta unidade.</p>
                    </div>
                </div>
            </div>
            @if($i < $quantity - 1)
                <hr class="my-4">
            @endif
        @endfor

        {{-- Botões de Ação Final --}}
        <div class="d-flex mb-5 mt-4">
            <button type="submit" class="btn btn-success" style="width: 180px; margin-right: 10px;">Criar Kit</button>
            <a href="{{ route('kits.create') }}" class="btn btn-secondary" style="width: 140px;">Cancelar</a>
        </div>
    </form>
</div>

{{-- NOVO: O teu motor de busca original agora isolado num único Modal Global --}}
<div class="modal fade" id="itemSelectorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title">Adicionar Itens à <span id="modal-unit-title" class="text-primary font-weight-bold">Unidade</span></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="p-1">
                    <h6><i class="fas fa-search"></i> Procurar e Adicionar Itens Livres</h6>
                    <input type="text" class="form-control mb-3" id="search-items-modal" placeholder="Digite para filtrar os itens disponíveis..." autocomplete="off">
                    
                    <div class="available-items-container border p-2 rounded bg-white">
                        @foreach ($itensLivres as $item)
                            <div class="available-item d-flex justify-content-between align-items-center mb-2 p-2 bg-light border rounded"
                                 data-nome="{{ $item->nome }}" data-code="{{ $item->lia_code }}">
                                <span>
                                    <strong>{{ $item->nome }}</strong> 
                                    <small class="text-muted">({{ $item->lia_code }})</small>
                                </span>
                                <button type="button" class="btn btn-primary btn-sm modal-add-btn" 
                                        data-id="{{ $item->id }}" 
                                        data-nome="{{ $item->nome }}" 
                                        data-code="{{ $item->lia_code }}">
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
    
    // Variável para guardar o ID do bloco da unidade ativa
    let activeUnitId = null;

    // 1. Ação ao clicar no botão "+ Associar Itens" de qualquer unidade
    $(document).on('click', '.open-selector-btn', function() {
        activeUnitId = $(this).data('unit');
        
        // Atualiza dinamicamente o cabeçalho do Modal
        $('#modal-unit-title').text('Unidade #' + (parseInt(activeUnitId) + 1));
        
        // Reseta o input de pesquisa do Modal ao abrir
        $('#search-items-modal').val('');
        $('.available-item').show();
        
        // Percorre os botões do modal e verifica se o item já foi associado algures
        $('.modal-add-btn').each(function() {
            const itemId = $(this).data('id');
            if ($(`input[value="${itemId}"][name^="items_for_unity"]`).length > 0) {
                $(this).prop('disabled', true).text('Adicionado');
            } else {
                $(this).prop('disabled', false).text('Adicionar');
            }
        });

        // Mostra o Modal do motor de busca
        $('#itemSelectorModal').modal('show');
    });

    // 2. Filtragem de pesquisa dentro do Modal único
    $(document).on('keyup', '#search-items-modal', function() {
        var valor = $(this).val().toLowerCase();
        
        $('.available-items-container .available-item').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(valor) > -1);
        });
    });

    // 3. Evento para adicionar o item a partir de dentro do Modal
    $(document).on('click', '.modal-add-btn', function(e) {
        e.preventDefault();
        
        const btn = $(this);
        const itemId = btn.data('id');
        const nome = btn.data('nome');
        const code = btn.data('code');

        // Validação Global: Impede duplicações entre unidades
        if ($(`input[value="${itemId}"][name^="items_for_unity"]`).length > 0) {
            $('#itemAlreadySelectedModal').modal('show');
            return;
        }

        // Remove o texto placeholder da unidade ativa
        $(`#selected-container-${activeUnitId} .visual-placeholder`).remove();

        // Estrutura HTML idêntica à tua original para a lista visível
        const itemRow = `
            <div class="d-flex justify-content-between align-items-center mb-2 p-2 border rounded bg-light style-row" id="selected-item-${itemId}">
                <input type="hidden" name="items_for_unity[${activeUnitId}][]" value="${itemId}">
                <span>${nome} - <small class="text-muted"><b>código:</b> ${code}</small></span>
                <button type="button" class="btn btn-danger btn-sm remove-item-btn" data-id="${itemId}" data-unit="${activeUnitId}">X</button>
            </div>
        `;

        // Coloca o item na lista correspondente lá atrás, na página principal
        $(`#selected-container-${activeUnitId}`).append(itemRow);
        
        // Bloqueia e altera o texto do botão dentro do modal para dar feedback
        btn.prop('disabled', true).text('Adicionado');
    });

    // 4. Função para remover o item se clicar no "X" diretamente na página principal
    $(document).on('click', '.remove-item-btn', function() {
        const itemId = $(this).data('id');
        const unitId = $(this).data('unit');

        // Remove a linha selecionada
        $(`#selected-item-${itemId}`).remove();

        // Se a caixa ficou vazia, volta a colocar o texto placeholder original
        if ($(`#selected-container-${unitId} .style-row`).length === 0) {
            $(`#selected-container-${unitId}`).html('<p class="text-muted m-0 small visual-placeholder">Nenhum item adicionado a esta unidade.</p>');
        }
    });
});
</script>
@endsection