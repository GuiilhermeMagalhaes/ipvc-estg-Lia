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

                    foreach ($reserve_itens as $ri) {
                        foreach ($itens as $i) {
                            if ($i->id == $ri->item_id) {
                                $qtd = $ri->quantity ?? 1;
                                $custo_calculado += ($i->price_day * $dias * $qtd);
                            }
                        }
                    }

                    foreach ($reserve_kits as $rk) {
                        foreach ($kits as $k) {
                            if ($k->id == $rk->kit_id) {
                                $qtd = $rk->quantity ?? 1;
                                $custo_calculado += ($k->price_day * $dias * $qtd);
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
                            <b>Data de inicio: </b> {{ \Carbon\Carbon::parse($reserve->start_date)->format('d/m/Y') }}
                        </li>
                        <li class="list-group-item">
                            <b>Data de fim: </b>{{ \Carbon\Carbon::parse($reserve->end_date)->format('d/m/Y') }}
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
                            <b>Custo Total ({{ $dias }} {{ $dias == 1 ? 'dia' : 'dias' }}): </b>
                            <span class="text-success font-weight-bold">{{ number_format($custo_calculado, 2, ',', '.') }} €</span>
                        </li>

                        @if (in_array($reserve->reserveState->id, [2, 4, 5, 6, 7, 8, 9]))
                        <li class="list-group-item d-flex justify-content-start align-items-center">
                            <b>Requisição(PDF): </b>
                            <a href="{{ route('pdf-download', $reserve->id) }}" class="btn btn-sm btn-outline-danger ml-2">
                                <i class="fas fa-file-pdf"></i> Download
                            </a>
                        </li>
                        @endif

                        <li class="list-group-item">
                            <b>Estado do Pagamento: </b>
                            @if($reserve->is_paid)
                                <span class="badge badge-success">Paga</span>
                            @else
                                <span class="badge badge-danger">Por Pagar</span>
                            @endif
                        </li>

                        @if (in_array($reserve->reserveState->id, [4, 5, 6, 7, 8, 9]))
                        <li class="list-group-item">
                            <b>Data de Entrega do Equipamento: </b> {{ \Carbon\Carbon::parse($reserve->delivery_date)->format('d/m/Y') }}
                        </li>
                        @endif

                        @if (in_array($reserve->reserveState->id, [5, 6, 8, 9]))
                        <li class="list-group-item">
                            <b>Data da Retoma do Equipamento: </b> {{ \Carbon\Carbon::parse($reserve->return_date)->format('d/m/Y') }}
                        </li>
                        @endif

                        @if($reserve->return_notes)
                        <li class="list-group-item" style="background-color: #fff3f3;">
                            <b class="text-danger"><i class="fas fa-exclamation-triangle"></i> Problema na Devolução: </b>
                            <span class="text-danger">{{ $reserve->return_notes }}</span>
                        </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <br>

    {{-- AÇÕES --}}
    <div class="row">
        <div class="container-fluid">

            {{-- Estado 1: Pendente - Autorizar / Recusar --}}
            @if ($reserve->reserveState->id == 1)
            <table>
                <tr>
                    <td>
                        <form action="{{ route('reserve.autorize', $reserve->id) }}" method="post">
                            @csrf
                            <button type="submit" class="btn btn-success">Autorizar</button>
                        </form>
                    </td>
                    <td>
                        <form action="{{ route('reserve.decline', $reserve->id) }}" method="post">
                            @csrf
                            <button type="submit" class="btn btn-danger">Recusar</button>
                        </form>
                    </td>
                </tr>
            </table>
            @endif

            {{-- Marcar como Paga (exceto estados 1 e 3) --}}
            @if (!$reserve->is_paid && $reserve->reserve_state_id != 1 && $reserve->reserve_state_id != 3)
            <div class="mt-3 mb-3">
                <form action="{{ route('reserve.pay', $reserve->id) }}" method="post">
                    @csrf
                    <button type="submit" class="btn btn-warning font-weight-bold" onclick="return confirm('Confirmar pagamento desta reserva?')">
                        Marcar como Paga <i class="fas fa-euro-sign"></i>
                    </button>
                </form>
            </div>
            @endif

            {{-- Estado 2: Autorizada - Atribuição de Equipamento (Entrega) --}}
            {{-- FORMULÁRIO DE ENTREGA COMPLETO (KITS + ITENS) --}}
@if ($reserve->reserveState->id == 2)
<div class="card card-warning card-outline mt-3">
    <div class="card-header">
        <h3 class="card-title">Atribuição de Equipamento (Entrega)</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('reserve.deliver', $reserve->id) }}" method="POST">
            @csrf
            <div class="row">
                
                {{-- A. ATRIBUIR KITS --}}
                    @foreach ($reserve_kits as $rk)
                        @php
                            $assignedKitCount = \Illuminate\Support\Facades\DB::table('kit_unity_reserve')
                                            ->where('kit_reserve_id', $rk->id)->count();
                            $quantidadeKitPedida = $rk->quantity ?? 1;
                            $toAssignKit = $quantidadeKitPedida - $assignedKitCount;

                            // 1. LÓGICA DE SEGURANÇA: Criar uma "lista negra" de Kits Incompletos
                            // Procura todos os itens que pertencem a um kit e que NÃO estão disponíveis (!= 1)
                            $kitsIncompletos = \App\Models\ItemUnity::whereNotNull('kit_unity_id')
                                ->where('item_unity_state_id', '!=', 1)
                                ->pluck('kit_unity_id')
                                ->toArray();

                            // 2. Buscar apenas os Kits físicos que estão no estado 1 (Disponível) 
                            // E que NÃO estão na "lista negra" de kits incompletos
                            $kitsDisponiveis = \App\Models\KitUnity::where('kit_id', $rk->kit_id)
                                ->where('kit_unity_state_id', 1)
                                ->whereNotIn('id', $kitsIncompletos)
                                ->get();
                        @endphp

                        @if ($toAssignKit > 0)
                            @for ($i = 0; $i < $toAssignKit; $i++)
                            <div class="col-md-4 mb-3" style="background-color: #f8f9fa; padding: 10px; border-radius: 8px;">
                                <label class="text-primary">
                                    @foreach($kits as $k) @if($k->id == $rk->kit_id) <strong><i class="fas fa-box"></i> {{ $k->name }} (KIT)</strong> @endif @endforeach
                                    <br><small class="text-dark">Unid. {{ $i + 1 }}/{{ $toAssignKit }}</small>
                                </label>
                                <select name="atribuicao_kit[{{ $rk->id }}][]" class="form-control border-primary" required>
                                    <option value="">-- Escolha LIA do Kit --</option>
                                    {{-- Agora usamos a variável $kitsDisponiveis perfeitamente limpa --}}
                                    @foreach($kitsDisponiveis as $unity)
                                        <option value="{{ $unity->id }}">LIA: {{ $unity->lia_code }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @endfor
                        @endif
                    @endforeach

                {{-- 2. ATRIBUIR UNIDADES FÍSICAS DOS ITENS --}}
                @foreach ($reserve_itens as $ri)
                    @php
                        $assignedCount = \Illuminate\Support\Facades\DB::table('item_unity_reserve')
                                        ->where('item_reserve_id', $ri->id)
                                        ->count();
                        
                        $quantidadePedida = $ri->quantity ?? 1;
                        $toAssign = $quantidadePedida - $assignedCount;
                    @endphp

                    @if ($toAssign > 0)
                        @for ($i = 0; $i < $toAssign; $i++)
                        <div class="col-md-4 mb-3">
                            <label>
                                <strong>{{ $ri->item->nome }} (Item)</strong>
                                <br><small>Unid. {{ $i + 1 }}/{{ $toAssign }}</small>
                            </label>
                            {{-- Repara no nome: atribuicao --}}
                            <select name="atribuicao[{{ $ri->id }}][]" class="form-control" required>
                                <option value="">-- Escolha LIA do Item --</option>
                                @foreach(\App\Models\ItemUnity::where('item_id', $ri->item_id)->where('item_unity_state_id', 1)->get() as $unity)
                                    <option value="{{ $unity->id }}">LIA: {{ $unity->lia_code }} (Ref: {{ $ri->item->ipvc_ref }})</option>
                                @endforeach
                            </select>
                        </div>
                        @endfor
                    @endif
                @endforeach

            </div>
            
            <button type="submit" class="btn btn-success btn-lg mt-3">
                <i class="fas fa-truck"></i> Confirmar Entrega
            </button>
        </form>
    </div>
</div>
@endif  

            {{-- Estados 4 e 7: Receber equipamento --}}
            @if (in_array($reserve->reserveState->id, [4, 7]))
            <div class="d-flex mb-3">
                <form action="{{ route('reserve.receive', $reserve->id) }}" method="post" class="mr-2">
                    @csrf
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check"></i> Receber (Tudo OK)
                    </button>
                </form>
                <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#modalProblema">
                    <i class="fas fa-exclamation-triangle"></i> Receber c/ Problemas
                </button>
            </div>

            <div class="modal fade" id="modalProblema" tabindex="-1" role="dialog" aria-labelledby="modalProblemaLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content border-danger">
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title" id="modalProblemaLabel">
                                <i class="fas fa-exclamation-triangle"></i> Registar Anomalia
                            </h5>
                            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form action="{{ route('reserve.receive', $reserve->id) }}" method="post">
                            @csrf
                            <div class="modal-body">
                                
                                <p><strong>1. Selecione as unidades avariadas:</strong></p>
                                <div style="max-height: 180px; overflow-y: auto; border: 1px solid #ccc; padding: 10px; border-radius: 5px; margin-bottom: 15px; background-color: #f8f9fa;">
                                    
                                    {{-- Listar KITS para Checkbox --}}
                                    @foreach ($reserve_kits as $rk)
                                        @php
                                            $kit = $kits->firstWhere('id', $rk->kit_id);
                                            $assignedKitUnities = \Illuminate\Support\Facades\DB::table('kit_unity_reserve')
                                                ->join('kit_unity', 'kit_unity_reserve.kit_unity_id', '=', 'kit_unity.id')
                                                ->where('kit_unity_reserve.kit_reserve_id', $rk->id)
                                                ->select('kit_unity.id', 'kit_unity.lia_code')
                                                ->get();
                                        @endphp
                                        @if($kit)
                                            @foreach($assignedKitUnities as $unity)
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="broken_kits[]" value="{{ $unity->id }}" id="kit_{{ $unity->id }}">
                                                    <label class="form-check-label text-danger font-weight-bold" for="kit_{{ $unity->id }}">
                                                        [KIT] {{ $kit->name }} (LIA: {{ $unity->lia_code }})
                                                    </label>
                                                </div>
                                            @endforeach
                                        @endif
                                    @endforeach

                                    {{-- Listar ITENS para Checkbox --}}
                                    @foreach ($reserve_itens as $ri)
                                        @php
                                            $item = $itens->firstWhere('id', $ri->item_id);
                                            $assignedUnities = \Illuminate\Support\Facades\DB::table('item_unity_reserve')
                                                ->join('item_unity', 'item_unity_reserve.item_unity_id', '=', 'item_unity.id')
                                                ->where('item_unity_reserve.item_reserve_id', $ri->id)
                                                ->select('item_unity.id', 'item_unity.lia_code')
                                                ->get();
                                        @endphp
                                        @if($item)
                                            @foreach($assignedUnities as $unity)
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="broken_items[]" value="{{ $unity->id }}" id="item_{{ $unity->id }}">
                                                    <label class="form-check-label text-danger font-weight-bold" for="item_{{ $unity->id }}">
                                                        [ITEM] {{ $item->nome }} (LIA: {{ $unity->lia_code }})
                                                    </label>
                                                </div>
                                            @endforeach
                                        @endif
                                    @endforeach

                                </div>

                                <p><strong>2. Descreva o problema geral:</strong></p>
                                <div class="form-group">
                                    <textarea class="form-control" name="return_notes" rows="3" placeholder="Ex: O utilizador deixou cair a câmara com o LIA XPTO..." required></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-danger">Confirmar Receção c/ Problema</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @endif

            {{-- Estados 8 e 9: Finalizar reserva --}}
            @if (in_array($reserve->reserveState->id, [8, 9]))
            <div class="mt-3">
                <form action="{{ route('reserve.finalize', $reserve->id) }}" method="post">
                    @csrf
                    <button type="submit" class="btn btn-success">Finalizar Reserva</button>
                </form>
            </div>
            @endif

        </div>
    </div>
    <br>
{{-- KITS DA RESERVA (VISUALIZAÇÃO) --}}
    <div class="card card-dark card-outline">
        <div class="card-header">KITS DA RESERVA E UNIDADES ATRIBUÍDAS</div>
        <div class="card-body">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Descrição / Quantidade</th>
                        <th>Estado / LIA Atribuído</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($reserve_kits as $rk)
                        @foreach ($kits as $k)
                            @if ($k->id == $rk->kit_id)
                            <tr>
                                <td>{{ $k->name }}</td>
                                <td>
                                    {{ $k->description }}
                                    <span class="badge badge-secondary ml-2">Qtd: {{ $rk->quantity ?? 1 }}</span>
                                </td>
                                <td>
                                    @php
                                        // Vai à tabela pivot dos KITS procurar os LIAs
                                        $assignedKitUnities = \Illuminate\Support\Facades\DB::table('kit_unity_reserve')
                                            ->join('kit_unity', 'kit_unity_reserve.kit_unity_id', '=', 'kit_unity.id')
                                            ->where('kit_unity_reserve.kit_reserve_id', $rk->id)
                                            ->pluck('kit_unity.lia_code');
                                    @endphp

                                    @if ($assignedKitUnities->count() > 0)
                                        @foreach($assignedKitUnities as $lia)
                                            <span class="badge badge-info mb-1" style="font-size: 14px;">LIA: {{ $lia }}</span><br>
                                        @endforeach
                                    @else
                                        <span class="badge badge-warning">Pendente de Entrega</span>
                                    @endif
                                </td>
                            </tr>
                            @endif
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- ITENS DA RESERVA (VISUALIZAÇÃO) --}}
    <div class="card card-dark card-outline mt-3">
        <div class="card-header">ITENS DA RESERVA E UNIDADES ATRIBUÍDAS</div>
        <div class="card-body">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Modelo / Quantidade</th>
                        <th>Estado / LIA Atribuído</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($reserve_itens as $ri)
                    <tr>
                        <td>{{ $ri->item->nome }}</td>
                        <td>
                            {{ $ri->item->model }} 
                            <span class="badge badge-secondary ml-2">Qtd: {{ $ri->quantity ?? 1 }}</span>
                        </td>
                        <td>
                            @php
                                // Vai à tabela pivot dos ITENS procurar os LIAs
                                $assignedUnities = \Illuminate\Support\Facades\DB::table('item_unity_reserve')
                                    ->join('item_unity', 'item_unity_reserve.item_unity_id', '=', 'item_unity.id')
                                    ->where('item_unity_reserve.item_reserve_id', $ri->id)
                                    ->pluck('item_unity.lia_code');
                            @endphp

                            @if ($assignedUnities->count() > 0)
                                @foreach($assignedUnities as $lia)
                                    <span class="badge badge-info mb-1" style="font-size: 14px;">LIA: {{ $lia }}</span><br>
                                @endforeach
                            @else
                                <span class="badge badge-warning">Pendente de Entrega</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>


</div>
@endsection
