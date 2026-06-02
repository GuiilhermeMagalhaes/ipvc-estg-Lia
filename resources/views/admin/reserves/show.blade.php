@extends('adminlte::page')

@section('title', 'Reserva')

@section('content')
<br>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-3">
            <div class="card card-dark card-outline">
                <div class="card-header">
                    RESERVANTE
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-unbordered mb-3">
                        <li class="list-group-item">
                            <b>Nome:</b>
                            <div class="float-right">{{ $reserve->user->name }}</div>
                        </li>
                        <li class="list-group-item">
                            <b>Email:</b>
                            <div class="float-right">{{ $reserve->user->email }}</div>
                        </li>
                        <li class="list-group-item">
                            <b>Telefone:</b>
                            <div class="float-right">{{ $reserve->user->phone }}</div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-9">
            <div class="card card-dark card-outline">
                <div class="card-header">
                    INFO RESERVA
                </div>
                
                @php
                    $start = \Carbon\Carbon::parse($reserve->start_date);
                    $end = \Carbon\Carbon::parse($reserve->end_date);
                    $dias = 0;

                    // Calcula os dias tendo em conta se é cíclica ou não
                    if ($reserve->ciclica_id == 1 || $reserve->ciclica_id == null) {
                        $dias = $start->diffInDays($end);
                        if ($dias == 0) $dias = 1;
                    } else {
                        $diaSemanaAlvo = $reserve->ciclica_id - 2; 
                        $dias = $start->diffInDaysFiltered(function (\Carbon\Carbon $date) use ($diaSemanaAlvo) {
                            return $date->dayOfWeek === $diaSemanaAlvo;
                        }, $end);
                        
                        if ($end->dayOfWeek === $diaSemanaAlvo) {
                            $dias++;
                        }
                        if ($dias == 0) $dias = 1;
                    }

                    $custo_calculado = 0;
                    
                    // Soma dos Itens
                    foreach ($reserve_itens as $ri) {
                        foreach ($itens as $i) {
                            if ($i->id == $ri->item_id) {
                                $qtd = $ri->quantity ?? 1;
                                $custo_calculado += ($i->preco * $dias * $qtd);
                            }
                        }
                    }
                    
                    // Soma dos Kits
                    foreach ($reserve_kits as $rk) {
                        foreach ($kits as $k) {
                            if ($k->id == $rk->kit_id) {
                                $qtd = $rk->quantity ?? 1;
                                $custo_calculado += ($k->price * $dias * $qtd);
                            }
                        }
                    }
                @endphp
                <div class="card-body">
                    <ul class="list-group list-group-unbordered mb-3">
                        <li class="list-group-item">
                            <b>Descrição: </b>{{ $reserve->description }}
                        </li>
                        <li class="list-group-item">
                            <b>Data de inicio: </b> {{\Carbon\Carbon::parse($reserve->start_date)->format('d/m/Y')}}
                        </li>
                        <li class="list-group-item">
                            <b>Data de fim: </b>{{\Carbon\Carbon::parse($reserve->end_date)->format('d/m/Y')}}
                        </li>
                        <li class="list-group-item">
                            <b>Cíclica: </b>{{ $reserve->ciclica->dia_semana }}
                        </li>
                        <li class="list-group-item">
                            <b>Estado da reserva: </b>{{ $reserve->reserveState->description }}
                        </li>
                        <li class="list-group-item">
                            <b>Centro de custos: </b>{{ $reserve->costCenter->name }}
                        </li>
                        <li class="list-group-item">
                            <b>Custo Total ({{ $dias }} {{ $dias == 1 ? 'dia' : 'dias' }}): </b><span class="text-success font-weight-bold">{{ number_format($custo_calculado, 2, ',', '.') }} €</span>
                        </li>
                        @if ($reserve->reserveState->id == 2 || $reserve->reserveState->id == 4 || $reserve->reserveState->id == 5 || $reserve->reserveState->id == 6 || $reserve->reserveState->id == 7 || $reserve->reserveState->id == 8 || $reserve->reserveState->id == 9)
                        <li class="list-group-item d-flex justify-content-start align-items-center">
                            <b>Requisição(PDF): </b>
                            <a href="{{ route('pdf-download', $reserve->id) }}" class="btn btn-sm btn-outline-danger ml-2">
                                <i class="fas fa-light fa-file-pdf"></i>
                                Download
                            </a>
                        </li>
                        @endif
                        @if ($reserve->reserveState->id == 4 || $reserve->reserveState->id == 5 || $reserve->reserveState->id == 6 || $reserve->reserveState->id == 7 || $reserve->reserveState->id == 8 || $reserve->reserveState->id == 9)
                        <li class="list-group-item">
                            <b>Data de Entrega do Equipamento: </b> {{\Carbon\Carbon::parse($reserve->delivery_date)->format('d/m/Y')}}
                        </li>
                        @endif
                        @if ($reserve->reserveState->id == 5 || $reserve->reserveState->id == 6 || $reserve->reserveState->id == 8 || $reserve->reserveState->id == 9)
                        <li class="list-group-item">
                            <b>Data da Retoma do Equipamento </b> {{\Carbon\Carbon::parse($reserve->return_date)->format('d/m/Y')}}
                        </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <br>
    
    <div class="row">
        <div class="container-fluid">
            @if ($reserve->reserveState->id == 1)
            <table>
                <tr>
                    <td>
                        <form action="{{ route('reserve.autorize', $reserve->id) }}" method="post">
                            @csrf
                            @method('POST')
                            <button type="submit" class="btn btn-success">Autorizar</button>
                        </form>
                    </td>
                    <td>
                        <form action="{{ route('reserve.decline', $reserve->id) }}" method="post">
                            @csrf
                            @method('POST')
                            <button type="submit" class="btn btn-danger">Recusar</button>
                        </form>
                    </td>
                </tr>
            </table>
            @endif
            @if ($reserve->reserveState->id == 2)
            <form action="{{ route('reserve.deliver', $reserve->id) }}" method="post">
                @csrf
                @method('POST')
                <button type="submit" class="btn btn-success">Entregar Material ao Reservante</button>
            </form>
            @endif

            @if ($reserve->reserveState->id == 7 || $reserve->reserveState->id == 4)
            <form action="{{ route('reserve.receive', $reserve->id) }}" method="post">
                @csrf
                @method('POST')
                <button type="submit" class="btn btn-success">Receber Material</button>
            </form>
            @endif
            @if ($reserve->reserveState->id == 9 || $reserve->reserveState->id == 8)
            <table>
                <tr>
                    <td>
                        <form action="{{ route('reserve.finalize', $reserve->id) }}" method="post">
                            @csrf
                            @method('POST')
                            <button type="submit" class="btn btn-success">Finalizar reserva</button>
                        </form>
                    </td>
                    <td>
                        <form action="{{ route('reserve.deliver', $reserve->id) }}" method="post">
                            @csrf
                            @method('POST')
                            <button type="submit" class="btn btn-success">Entregar Material ao Reservante</button>
                        </form>
                    </td>
                </tr>
            </table>
            @endif
        </div>
    </div>
    <br>
    <div class="card card-dark card-outline">
        <div class="card-header">
            KITS DA RESERVA
        </div>
        <table id="reserves" class="table table-hover">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th class="no-sort">Descrição</th>
                    <th>Preço / dia</th>
                    <th>Código LIA</th>
                    <th>Referência IPVC</th>
                    <th class="no-sort"></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($reserve_kits as $reserve_kit)
                @foreach ($kits as $kit)
                @if ($kit->id == $reserve_kit->kit_id)
                <tr>
                    <td>{{ $kit->name }}</td>
                    <td>{{ $kit->description }}</td>
                    <td>{{ number_format($kit->price, 2, ',', '.') }} €</td>
                    <td>{{ $kit->lia_code }}</td>
                    <td>{{ $kit->ipvc_ref }}</td>
                </tr>
                @endif
                @endforeach
                @endforeach
            </tbody>
        </table>
    </div>
    <br>
    <div class="card card-dark card-outline">
        <div class="card-header">
            ITENS DA RESERVA
        </div>
        <table id="reserves" class="table table-hover">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th class="no-sort">Modelo</th>
                    <th>Preço / dia</th>
                    <th>Número de Série</th>
                    <th>Referência IPVC</th>
                    <th class="no-sort"></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($reserve_itens as $reserve_item)
                @foreach ($itens as $item)
                @if ($item->id == $reserve_item->item_id)
                <tr>
                    <td>{{ $item->nome }}</td>
                    <td>{{ $item->model }}</td>
                    <td>{{ number_format($item->preco, 2, ',', '.') }} €</td>
                    <td>{{ $item->serial_number }}</td>
                    <td>{{ $item->ipvc_ref }}</td>
                </tr>
                @endif
                @endforeach
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection