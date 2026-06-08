@extends('index')
@section('content')
<link href="/css/custom.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/shepherd.js/dist/css/shepherd.css">
<div class="page-title">
    <nav class="breadcrumbs">
        <div class="container d-flex justify-content-between align-items-center">
            <ol class="d-flex mb-0">
                <li><a href="/"><i class="bi bi-house"></i></a></li>
                <li><a class="current" href="#">{{ $category->description }} : Indisponíveis</a></li>
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
        @foreach ($itens as $item)
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
        @endforeach
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
        let scrollTop = document.querySelector('#scroll-top');

        function toggleScrollTop() {
            if (scrollTop) {
                window.scrollY > 100 ? scrollTop.classList.add('active') : scrollTop.classList.remove('active');
            }
        }

        scrollTop.addEventListener('click', (e) => {
            e.preventDefault();
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });

        window.addEventListener('load', toggleScrollTop);
        document.addEventListener('scroll', toggleScrollTop);

        // AJAX LIVE SEARCH
        $('#search').on('keyup', function() {
            var value = $(this).val().trim(); // Captura o valor e remove espaços em branco extras
            $.ajax({
                type: "get",
                url: "{{ route('user.categoria.indisponivel', $category->id) }}",
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