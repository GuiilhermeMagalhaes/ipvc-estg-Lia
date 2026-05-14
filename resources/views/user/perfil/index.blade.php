@extends('index')

@section('content')
<link href="/css/custom.css" rel="stylesheet">
<div class="page-title">
    <nav class="breadcrumbs">
        <div class="container d-flex justify-content-between align-items-center">
            <ol class="d-flex mb-0">
                <li><a href="/"><i class="bi bi-house"></i></a></li>
                <li><a class="current" href="#">Perfil</a></li>
            </ol>
        </div>
    </nav>
</div>
<br>
<div class="container">
    <div class="card card-solid">
        <div class="card-body">
            <div class="row">
                <div class="col-12 col-md-6">
                    <h1 class="my-1">{{ Auth::user()->name }}</h1>
                    <br>
                    <p style="font-size:16px"><b style="font-size:18px">Email: </b>{{ Auth::user()->email }}</p>
                    <p style="font-size:16px"><b style="font-size:18px">Telemóvel: </b>{{ Auth::user()->phone }}</p>
                    @foreach ($userTypes as $type)
                        @if ($type->id == Auth::user()->user_type_id)
                            <p style="font-size:16px"><b style="font-size:18px">Tipo de Utilizador: </b>{{ $type->description }}</p>
                        @endif
                    @endforeach
                    <div class="mt-4">
                        <form action="{{ route('perfil.edit') }}" method="GET" class="d-inline">
                            <button type="submit" class="btn btn-outline-dark mt-auto" style="width: 140px;">Editar Perfil</button>
                        </form>
                        @if (!Auth::user()->hasVerifiedEmail())
                            <form action="{{ route('verification.resend') }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-outline-dark mt-auto" style="width: 140px;">Verificar Email</button>
                            </form>
                        @else
                            <div class="text-success mt-2 ml-1"> Email Verificado <i class="fas fa-check-circle"></i></div>
                        @endif
                    </div>
                </div>
                <div class="col-12 col-md-6 text-md-right">
                    <img src="../{{ Auth::user()->image }}" class="img-fluid" style="max-width: 220px; border-radius:10px;" alt="User Image">
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('[data-toggle="popover"]').popover();
    });
</script>
@endsection