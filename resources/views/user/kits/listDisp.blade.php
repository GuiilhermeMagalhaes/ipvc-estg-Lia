@extends('index')
@section('content')
<link href="/css/custom.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/shepherd.js/dist/css/shepherd.css">
<div class="page-title">
    <nav class="breadcrumbs">
        <div class="container d-flex justify-content-between align-items-center">
            <ol class="d-flex mb-0">
                <li><a href="/"><i class="bi bi-house"></i></a></li>
                <li><a class="current" href="#">Kits : Disponíveis</a></li>
            </ol>
            <li class="dropdown"><a href="#"><span>Ver</span> <i class="bi bi-chevron-down toggle-dropdown"></i></a>
                <ul>
                    <li><a href=" {{ route('user.kits.all') }}">Todos</a></li>
                    <li><a href=" {{ route('user.kits.disponivel') }}">Disponíveis</a></li>
                    <li><a href=" {{ route('user.kits.indisponivel') }}">Indisponíveis</a></li>
                </ul>
            </li>
            <form action="#" class="search-form">
                <input id="search" class="form-control search-input" name="search" type="text" placeholder="Procurar kits..."/>
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
    <div>
    <div class="row mycard gx-4 gx-lg-5 row-cols-2 row-cols-md-3 row-cols-xl-4 justify-content-center">
        
        @forelse ($kits as $kit)
        <div class="col mb-5">
            <div class="card h-100" id="kit">
                <img class="card-img-top rounded-top" src="../{{ $kit->image }}" alt="..." />
                <div class="card-body p-4">
                    <div class="text-center">
                      <h5 class="fw-bolder">{{ $kit->name }}</h5>
                        <h6>{{ $kit->description }}</h6>
                        {{number_format($kit->price_day, 2, ',', '.')}} € / dia
                    </div>
                </div>
                <div class="card-footer p-4 pt-0 border-top-0 bg-transparent">
                    <div class="text-center"><a class="btn btn-outline-dark mt-auto" href="/kit/{{ $kit->id }}" style="width: 140px;">Ver Detalhes</a></div>
                </div>
            </div>
        </div>
        
        @empty
        {{-- MENSAGEM DE ECRÃ VAZIO --}}
        <div class="col-12 text-center" style="margin-top: 80px; margin-bottom: 80px;">
            <i class="fas fa-box-open fa-4x text-muted mb-3"></i>
            <h3 class="text-muted font-weight-bold">Sem Kits Disponíveis</h3>
            <p class="text-muted" style="font-size: 1.1rem;">
                Neste momento, não existem Kits disponíveis para requisição nestas datas. <br>
                Tente alterar o período da sua reserva ou contacte o técnico do LIA.
            </p>
            <a href="{{ route('user.categoria.disponivel', 1) }}" class="btn btn-outline-info mt-2">Ver Itens Individuais</a>
        </div>
        @endforelse

    </div>
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
        title: 'Kit - Etapa 3/5',
        text: 'Se deseja adicionar kit à sua reserva, clique em "Ver Detalhes".',
        attachTo: {
            element: '#kit',
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
                url: "{{ route('user.kits.disponivel') }}",
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