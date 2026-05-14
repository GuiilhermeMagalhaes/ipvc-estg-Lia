<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Disponibilidade;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DisponibilidadeController extends Controller
{
    public function index()
    {
        if (Auth::user()->user_type_id == 1) {

            $horariosExpirados = Disponibilidade::where('data_expiracao', '<', Carbon::now())->get();

            foreach ($horariosExpirados as $horario) {
                $horario->delete();
            }

            // Obter todos os horários
            $horarios = Disponibilidade::all();

            // Agrupar os horários pelo campo 'entredatas'
            $agrupados = [];
            foreach ($horarios as $horario) {
                if ($horario->entredatas) {
                    // Usar o campo 'entredatas' como chave para o agrupamento
                    $agrupados[$horario->entredatas] = $horario;
                } else {
                    // Adicionar horários não agrupados normalmente
                    $agrupados[] = $horario;
                }
            }

            // Converter o array associativo para um array simples
            $horariosParaMostrar = array_values($agrupados);

            return view('admin.disponibilidade.info', ['horarios' => $horariosParaMostrar]);
        }
        return redirect('/');
    }


    public function create()
    {
        if (Auth::user()->user_type_id == 1) {
            return view('admin.disponibilidade.create', ['horarios' => Disponibilidade::all()]);
        }
        return redirect('/');
    }


    public function edit($id)
    {
        if (Auth::user()->user_type_id == 1) {
            $horario = Disponibilidade::findOrFail($id);
            return view('admin.disponibilidade.edit', compact('horario'));
        }
        return redirect('/');
    }

    public function update(Request $request, $id)
    {
        $horario = Disponibilidade::findOrFail($id);

        $request->validate(
            [
                'descricao' => 'required'
            ],
            [
                'descricao.required' => 'É necessário introduzir uma descrição!'
            ]
        );

        // Verificar se 'entredatas' não é nulo
        if ($horario->entredatas !== null) {
            // Atualizar todos os horários com o mesmo 'entredatas'
            Disponibilidade::where('entredatas', $horario->entredatas)->update([
                'descricao' => $request->descricao
            ]);
        }
        $horario->update([
            'descricao' => $request->descricao
        ]);

        $horario->save();

        return redirect()->route('disponibilidade.info')->with('toast_success', 'Horário Atualizado!');
    }

    public function destroy($id)
    {
        $horario = Disponibilidade::find($id);

        if ($horario) {
            $entredatas = $horario->entredatas;

            // Verificar se 'entredatas' não é nulo e eliminar horários relacionados
            if ($entredatas !== null) {
                Disponibilidade::where('entredatas', $entredatas)->delete();
            }

            // Eliminar o horário pelo ID recebido
            $horario->delete();

            return redirect('admin/disponibilidade')->with('toast_success', 'Horário eliminado!');
        }

        return redirect('admin/disponibilidade')->with('toast_error', 'Horário não encontrado.');
    }


    public function destroyAll()
    {
        // Excluir todos os horários de disponibilidade
        Disponibilidade::truncate();

        return redirect('admin/disponibilidade')->with('toast_success', 'Horários Eliminados!');
    }

    public function store(Request $request)
    {
        $request->validate(
            [
                'tipo' => 'required',
                'descricao' => 'required',
                'data' => 'required_if:tipo,unico|unique:disponibilidade,data|nullable',
                'dia_semana' => 'required_if:tipo,recorrente|integer|between:0,6|nullable',
                'semanas' => 'required_if:tipo,recorrente|integer|min:1|nullable',
                'data_inicio' => 'required_if:tipo,entredatas|date|before_or_equal:data_fim|nullable',
                'data_fim' => 'required_if:tipo,entredatas|date|after_or_equal:data_inicio|nullable'
            ],
            [
                'tipo.required' => 'É necessário escolher um tipo de horário!',
                'data.required_if' => 'É necessário introduzir uma data!',
                'data.unique' => 'Já existe um horário com esta data!',
                'dia_semana.required_if' => 'É necessário escolher um dia da semana!',
                'dia_semana.integer' => 'O dia da semana deve ser um número válido!',
                'dia_semana.between' => 'O dia da semana deve estar entre 0 (Domingo) e 6 (Sábado)!',
                'semanas.required_if' => 'É necessário introduzir o número de semanas!',
                'semanas.integer' => 'O número de semanas deve ser um número válido!',
                'semanas.min' => 'O número de semanas deve ser pelo menos 1!',
                'data_inicio.required_if' => 'É necessário introduzir uma data de início!',
                'data_inicio.date' => 'A data de início deve ser uma data válida!',
                'data_inicio.before_or_equal' => 'A data de início deve ser antes ou igual à data de fim!',
                'data_fim.required_if' => 'É necessário introduzir uma data de fim!',
                'data_fim.date' => 'A data de fim deve ser uma data válida!',
                'data_fim.after_or_equal' => 'A data de fim deve ser depois ou igual à data de início!',
                'descricao.required' => 'É necessário introduzir uma descrição!'
            ]
        );

        if ($request->tipo == 'unico') {
            // Processar a data única
            Disponibilidade::create([
                'data' => $request->data,
                'descricao' => $request->descricao,
                'data_expiracao' => Carbon::parse($request->data)->addWeek(), // Expira uma semana após a data
                'entredatas' => null
            ]);

            return redirect('admin/disponibilidade')->with('toast_success', 'Horário Criado');
        } else if ($request->tipo == 'recorrente') {
            // Processar a data recorrente
            $diaSemana = $request->dia_semana;
            $descricao = $request->descricao;
            $semanas = $request->semanas;

            // Mapear o dia da semana numérico para uma string correspondente
            $diasDaSemana = [
                0 => 'Sunday',
                1 => 'Monday',
                2 => 'Tuesday',
                3 => 'Wednesday',
                4 => 'Thursday',
                5 => 'Friday',
                6 => 'Saturday',
            ];
            // A data inicial será a primeira ocorrência do dia da semana escolhido a partir de hoje
            $dataInicial = Carbon::now()->next($diasDaSemana[$diaSemana]);

            $horarios = [];
            $maxIterations = $semanas; // Limite para evitar loop infinito

            for ($i = 0; $i < $maxIterations; $i++) {
                $data = $dataInicial->copy()->addWeeks($i);
                // Verificar se já existe um horário com esta data
                $existingHorario = Disponibilidade::where('data', $data->toDateString())->first();

                if (!$existingHorario) {
                    // Definir a data de expiração para cada horário recorrente
                    $dataExpiracao = $data->copy()->addWeek(); // Expira uma semana após a data

                    $horarios[] = [
                        'data' => $data->toDateString(),
                        'descricao' => $descricao,
                        'data_expiracao' => $dataExpiracao,
                        'entredatas' => null
                    ];
                }
            }

            if (count($horarios)) {
                Disponibilidade::insert($horarios);
            }

            return redirect('admin/disponibilidade')->with('toast_success', 'Horários Criados');
        } else if ($request->tipo == 'entredatas') {
            // Processar datas entre intervalo
            $dataInicio = Carbon::parse($request->data_inicio);
            $dataFim = Carbon::parse($request->data_fim);
            $descricao = $request->descricao;
            $descricaoDatas = Carbon::parse($dataInicio)->format('d/m/Y') . ' até ' . Carbon::parse($dataFim)->format('d/m/Y');

            $horarios = [];

            for ($date = $dataInicio; $date->lte($dataFim); $date->addDay()) {
                $existingHorario = Disponibilidade::where('data', $date->toDateString())->first();

                if (!$existingHorario) {
                    // Definir a data de expiração para cada horário entre datas
                    $dataExpiracao = $date->copy()->addWeek(); // Expira uma semana após a data

                    $horarios[] = [
                        'data' => $date->toDateString(),
                        'descricao' => $descricao,
                        'data_expiracao' => $dataExpiracao,
                        'entredatas' => $descricaoDatas
                    ];

                    // Adicionar a data ao array de datas criadas
                    $datasCriadas[] = $date->toDateString();
                }
            }

            if (count($horarios)) {
                Disponibilidade::insert($horarios);
            }

            // Logar as datas criadas
            Log::info('Datas criadas:', $datasCriadas);

            return redirect('admin/disponibilidade')->with('toast_success', 'Horários Criados');
        }
    }
}
