@extends('index')
@section('content')
<link href="/css/custom.css" rel="stylesheet">
<div class="page-title">
    <nav class="breadcrumbs">
        <div class="container d-flex justify-content-between align-items-center">
            <ol class="d-flex mb-0">
                <li><a href="/"><i class="bi bi-house"></i></a></li>
                <li><a class="current" href="#">Disponibilidade do Técnico</a></li>
            </ol>
        </div>
    </nav>
</div>
<br>
<div class="container">
    <div class="card card-solid">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <a class="btn btn-outline-dark" href="../../../disponibilidade/anterior/{{ $month }}/{{ $year }}">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h2 id="mesNome" class="mb-0"></h2>
                <a class="btn btn-outline-dark" href="../../../disponibilidade/proximo/{{ $month }}/{{ $year }}">
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            <table id="calendar" class="table">
                <thead>
                    <tr class="text-center">
                        <th style="border: none;">Dom</th>
                        <th style="border: none;">2ª</th>
                        <th style="border: none;">3ª</th>
                        <th style="border: none;">4ª</th>
                        <th style="border: none;">5ª</th>
                        <th style="border: none;">6ª</th>
                        <th style="border: none;">Sab</th>
                    </tr>
                </thead>
                <tbody id="calendar-body"></tbody>
            </table>
            <h3>Horários</h3>
            <p id="descricao"></p>
        </div>
    </div>
</div>

<script>
    var currentMonth = {{$month}} - 1;
    const currentYear = {{$year}};

    const months = [
        "Janeiro", "Fevereiro", "Março", "Abril", "Maio", "Junho",
        "Julho", "Agosto", "Setembro", "Outubro", "Novembro", "Dezembro"
    ];

    const mesNome = document.getElementById("mesNome");
    mesNome.innerHTML = months[currentMonth] + " " + currentYear;;

    function getNumOfDays(month, year) {
        return new Date(year, month, 0).getDate();
    }

    function createCalendar(month, year) {
        const calendarBody = document.getElementById("calendar-body");
        calendarBody.innerHTML = "";

        const numOfDays = getNumOfDays(month + 1, year);
        const firstDayOfMonth = new Date(year, month, 1);
        const startingDay = firstDayOfMonth.getDay();

        var dias = @json($dias); // Substitua por sua lógica para obter os dias
        var descricoes = @json($descricoes); // Substitua por sua lógica para obter as descrições

        const today = new Date();
        const todayDate = today.getDate();
        const todayMonth = today.getMonth();
        const todayYear = today.getFullYear();

        let day = 1;
        let row;

        for (let i = 0; i < 6; i++) {
            row = document.createElement("tr");
            for (let j = 0; j < 7; j++) {
                let cell = document.createElement("td");

                if (i === 0 && j < startingDay) {
                    cell.textContent = "";
                    cell.classList.add("disabled-cell");
                } else if (day > numOfDays) {
                    cell.textContent = "";
                    cell.classList.add("disabled-cell");
                } else {
                    cell.textContent = day;

                    if (year < todayYear ||
                        (year === todayYear && month < todayMonth) ||
                        (year === todayYear && month === todayMonth && day < todayDate)) {
                        cell.classList.add("disabled-cell");
                    } else {
                        cell.addEventListener("click", function() {
                            let found = false;
                            for (let k = 0; k < dias.length; k++) {
                                if (parseInt(cell.textContent) === dias[k]) {
                                    document.getElementById("descricao").innerHTML = descricoes[k];
                                    found = true;
                                    break;
                                }
                            }
                            if (!found) {
                                document.getElementById("descricao").innerHTML = "Sem horário disponível...";
                            }
                        });
                    }

                    if (day === todayDate && month === todayMonth && year === todayYear) {
                        cell.classList.add("today-cell");
                    }

                    for (let k = 0; k < dias.length; k++) {
                        if (parseInt(cell.textContent) === dias[k]) {
                            cell.classList.add("highlight-cell");
                            break;
                        }
                    }

                    day++;
                }

                row.appendChild(cell);
            }
            calendarBody.appendChild(row);
            if (day > numOfDays) break;
        }
    }

    createCalendar(currentMonth, currentYear);

    $(document).ready(function() {
        $('[data-toggle="popover"]').popover();
    });
</script>

<style>
    .table td {
        border-top: none;
    }

    td {
        text-align: center;
    }

    td:hover {
        background-color: #343a40;
        color: white;
        border-radius: 10px;
    }

    .highlight-cell {
        background-color: #343a40;
        color: white;
        border-radius: 10px;
        padding: 20px;
        /* Reduzindo o padding para diminuir o tamanho do destaque */
    }

    .highlight-cell:hover {
        background-color: black !important;
        /* Fundo preto para dias com reservas */
        color: white;
        /* Texto branco nos dias com reservas */
        border-radius: 10px;
    }

    .today-cell {
        color: deepskyblue;
        font-weight: bold;
    }

    .disabled-cell {
        color: #ccc;
        pointer-events: none;
        /* Desabilita o clique */
    }
</style>
@endsection