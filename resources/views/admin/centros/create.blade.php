@extends('adminlte::page')

@section('title', 'Novo Centro de Custo')

@section('content')
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
            <button type="submit" class="btn btn-success" style="width: 140px;">Criar Centro</button>
        </form>
    </div>
@endsection
