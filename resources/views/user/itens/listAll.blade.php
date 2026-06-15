@extends('index')
@section('content')
<link href="/css/custom.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/shepherd.js/dist/css/shepherd.css">
<div class="page-title">
    <nav class="breadcrumbs">
        <div class="container d-flex justify-content-between align-items-center">
            <ol class="d-flex mb-0">
                <li><a href="/"><i class="bi bi-house"></i></a></li>
                <li><a class="current" href="#">{{ $category->description }} : Todos</a></li>
            </ol>
            <li class="dropdown"><a href="#"><span>Ver</span> <i class="bi bi-chevron-down toggle-dropdown"></i></a>
                <ul>
                    <li><a href=" {{ route('user.categoria.all', $category->id) }}">Todos</a></li>
                    <li><a href=" {{ route('user.categoria.disponivel', $category->id) }}">Disponíveis</a></li>
                    <li><a href=" {{ route('user.categoria.indisponivel', $category->id) }}">Indisponíveis</a></li>
                </ul>
            </li>
            <form action="#" class="search-form">
                <input id="search" class="form-control search-input" name="search" type="text" placeholder="Procurar itens..."/>
                <button type="submit" class="search-button">
                    <i class="bi bi-search"></i>
                </button>
            </form>
        </div>
        <button id="start-tutorial" class="btn btn-info" style="float: right; margin-right: 10px;">Ajuda</button>
    </nav>
</div>
<br>
<div>
    <div class="row mycard gx-4 gx-lg-5 row-cols-2 row-cols-md-3 row-cols-xl-4 justify-content-center">
        
        @forelse ($itens as $item)
        <div class="col mb-5">
            <div class="card h-100" id="item">
                <img class="card-img-top rounded-top" src="../../{{ $item->image }}" alt="..." />
                <div class="card-body p-4">
                    <div class="text-center">
                        <h5 class="fw-bolder">{{ $item->nome }}</h5>
                        {{number_format($item->price_day, 2, ',', '.')}} € / dia
                    </div>
                </div>
                <div class="card-footer p-4 pt-0 border-top-0 bg-transparent">
                    <div class="text-center"><a class="btn btn-outline-dark mt-auto" href="/item/{{ $item->id }}" style="width: 140px;">Ver Detalhes</a></div>
                </div>
            </div>
        </div>

        @empty
        {{-- MENSAGEM DE ECRÃ VAZIO --}}
        <div class="col-12 text-center" style="margin-top: 80px; margin-bottom: 80px;">
            <i class="fas fa-box-open fa-4x text-muted mb-3"></i>
            <h3 class="text-muted font-weight-bold">Sem Equipamento Disponível</h3>
            <p class="text-muted" style="font-size: 1.1rem;">
                Neste momento, não há itens disponíveis nesta categoria para o período selecionado. <br>
                Explore outras categorias ou verifique os nossos Kits.
            </p>
        </div>
        @endforelse

    </div>
</div>

<!-- Scroll Top -->
<a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>
<script src="https://cdn.jsdelivr.net/npm/shepherd.js/dist/js/shepherd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
    const tour = new Shepherd.Tour({
        defaultStepOptions: {
            scrollTo: true,
            cancelIcon: {
                enabled: true
            },
            classes: 'shepherd-theme-arrows',
            modalOverlayOpeningPadding: 5,
            modalOverlayOpeningRadius: 5
        }
    });

    tour.addStep({
        title: 'Item - Etapa 3/5',
        text: 'Se deseja adicionar item à sua reserva, clique em "Ver Detalhes".',
        attachTo: {
            element: '#item',
            on: 'top'
        },
        buttons: [
            {
                text: 'Terminar',
                action: tour.complete
            }
        ]
    });

    // Botão para iniciar o tutorial
    document.getElementById('start-tutorial').addEventListener('click', function () {
        tour.start();
    });
});

</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
    // Seleciona o elemento com o id 'scroll-top' e armazena na variável scrollTop
    let scrollTop = document.querySelector('#scroll-top');

    // Função para alternar a visibilidade do botão de scroll-to-top com base na posição de rolagem da janela
    function toggleScrollTop() {
        // Verifica se o elemento scrollTop existe
        if (scrollTop) {
            // Verifica se a posição vertical de rolagem da janela é maior do que 100 pixels
            // Se for maior, adiciona a classe 'active' ao elemento scrollTop, caso contrário, remove a classe 'active'
            window.scrollY > 100 ? scrollTop.classList.add('active') : scrollTop.classList.remove('active');
        }
    }

    // Adiciona um evento de clique ao elemento scrollTop
    scrollTop.addEventListener('click', (e) => {
        // Previne o comportamento padrão do clique no link
        e.preventDefault();
        // Rola a janela suavemente até o topo da página
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });

    // Evento que é acionado quando a página e todos os recursos são completamente carregados
    window.addEventListener('load', toggleScrollTop);
    // Evento que é acionado quando ocorre um evento de rolagem na página
    document.addEventListener('scroll', toggleScrollTop);

    // AJAX LIVE SEARCH
    $('#search').on('keyup', function() {
            var value = $(this).val().trim(); // Captura o valor e remove espaços em branco extras
            $.ajax({
                type: "get",
                url: "{{ route('user.categoria.all', $category->id) }}",
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
});

$(document).ready(function() {
        $('[data-toggle="popover"]').popover();
    });
</script>
@endsection