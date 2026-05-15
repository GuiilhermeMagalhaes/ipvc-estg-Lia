@extends('index')

@section('content')
<link href="/css/custom.css" rel="stylesheet">
<div class="page-title">
    <nav class="breadcrumbs">
        <div class="container d-flex justify-content-between align-items-center">
            <ol class="d-flex mb-0">
                <li><a href="/"><i class="bi bi-house"></i></a></li>
                <li><a class="current" href="#">Novo Centro de Custos</a></li>
            </ol>
        </div>
    </nav>
</div>
<br>
    <div class="d-flex flex-column">
        <form action="{{ route('centro.store') }}" enctype="multipart/form-data" method="POST">
            @csrf
            @method('POST')
            <div class="form-group">
                <label for="name">Nome</label>
                <input type="text" name="name" class="form-control" value="{{ old('name') }}">
                <span style="color:red">{{$errors->first('name')}}</span>
            </div>
            
            <div class="form-group">
                <label for="user_id">Responsável pelo Centro</label>
                <br>
                <select id="user_id" name="user_id">
                    @foreach ($users as $user)
                        <option value={{ $user->id }}>{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-success">Criar Centro</button>
        </form>
    </div>

    <script>
        $(document).ready(function() {
        $('[data-toggle="popover"]').popover();
    });
    </script>
@endsection
