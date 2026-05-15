@extends('index')

@section('content')
<link href="/css/custom.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.15/index.global.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/@fullcalendar/daygrid@6.1.15/index.global.min.js'></script>
<script>
// async function carregarPostos() {
//     try {
//         const response = await fetch('/lia-space/postos'); // Endpoint para obter postos
//         const postos = await response.json();

//         const postoSelect = document.getElementById('posto-select');
//         postos.forEach(posto => {
//             if(posto.space_code != null){
//                 const option = document.createElement('option');
//                 option.value = posto.id;
//                 option.textContent = "Posto " + posto.space_code;
//                 postoSelect.appendChild(option);
//             }
//         });
//     } catch (error) {
//         console.error('Erro ao carregar postos:', error);
//     }
// }

async function carregarPostos() {
    try {
        const response = await fetch('/lia-space/postos'); // Endpoint para obter postos
        const postos = await response.json();

        const postoSelect = document.getElementById('posto-select');
        const spaceCodes = new Set();  // Conjunto para garantir que cada código de posto seja único

        postos.forEach(posto => {
            if (posto.space_code != null && !spaceCodes.has(posto.space_code)) {
                const option = document.createElement('option');
                option.value = posto.space_code;
                option.textContent = "Posto " + posto.space_code;
                postoSelect.appendChild(option);
                
                // Adiciona o código do posto ao conjunto para evitar duplicações
                spaceCodes.add(posto.space_code);
            }
        });
    } catch (error) {
        console.error('Erro ao carregar postos:', error);
    }
}

function inicializarCalendario() {
    const calendarEl = document.getElementById('calendar');
    const postoSelect = document.getElementById('posto-select');

    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        themeSystem: 'standard',
        contentHeight: 'auto',
        locale: 'pt',
        buttonText: {
            today: 'Hoje',
        },
        dayHeaderClassNames: function () {
            return ['custom-header'];
        },
        events: async function (info, successCallback, failureCallback) {
            try {
                const postoId = postoSelect.value; 
                const response = await fetch(`/lia-space/reservas${postoId ? `?id=${postoId}` : ''}`);
                const data = await response.json();

                // Formatar os eventos para o FullCalendar
                const events = data.map(reserva => {
                    return {
                        title: reserva.title, // Nome da reserva
                        bolseiro: reserva.bolseiro,
                        email: reserva.email,
                        start: reserva.start,  // Data de início
                        end: reserva.end,      // Data de término
                        extendedProps: {
                            users: reserva.users,      // Informações do bolseiro
                            equipamentos: reserva.equipamentos, // Equipamentos associados
                        },
                        backgroundColor: reserva.backgroundColor, // Cor de fundo gerada
                        borderColor: reserva.borderColor, // Cor da borda gerada
                    };
                });

                successCallback(events); // Passa os eventos para o FullCalendar
            } catch (error) {
                console.error('Erro ao carregar eventos:', error);
                failureCallback(error);
                alert('Erro ao carregar reservas.');
            }
        },
        eventClick: function (info) {
            // Preencher o modal com os dados da reserva
            const reserva = info.event.extendedProps;

            document.getElementById('modalBolseiro').innerText = reserva.bolseiro || 'Sem bolseiro';
            document.getElementById('modalEmail').innerText = reserva.email || 'N/A';

            function formatDateTimePT(date) {
                if (!date) return 'N/A';
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0'); // Meses começam em 0
                const day = String(date.getDate()).padStart(2, '0');
                const hours = String(date.getHours()).padStart(2, '0');
                const minutes = String(date.getMinutes()).padStart(2, '0');
                // Caso queira segundos, pode adicioná-los:
                // const seconds = String(date.getSeconds()).padStart(2, '0');
                return `${year}/${month}/${day} ${hours}:${minutes}`;
            }

            document.getElementById('modalStartDate').innerText = formatDateTimePT(info.event.start);
            document.getElementById('modalEndDate').innerText = formatDateTimePT(info.event.end);


            // document.getElementById('modalStartDate').innerText = info.event.start.toLocaleString() || 'N/A';
            // document.getElementById('modalEndDate').innerText = info.event.end ? info.event.end.toLocaleString() : 'N/A';

            // const equipamentosList = document.getElementById('modalEquipamentos');
            // equipamentosList.innerHTML = '';
            // if (reserva.equipamentos && reserva.equipamentos.length) {
            //     reserva.equipamentos.forEach(equip => {
            //         const li = document.createElement('li');
            //         li.textContent = equip;
            //         equipamentosList.appendChild(li);
            //     });
            // } else {
            //     equipamentosList.innerHTML = '<li>Sem equipamentos</li>';
            // }

            // Abrir o modal
            var myModal = new bootstrap.Modal(document.getElementById('reservationModal'));
            myModal.show();
        },
    });

    // Recarrega o calendário ao mudar o posto
    postoSelect.addEventListener('change', function () {
        calendar.refetchEvents();
    });

    calendar.render();
}


document.addEventListener('DOMContentLoaded', function () {
    carregarPostos();
    inicializarCalendario();
});

</script>

<div class="page-title">
    <nav class="breadcrumbs">
        <div class="container d-flex justify-content-between align-items-center">
            <ol class="d-flex mb-0">
                <li><a href="/"><i class="bi bi-house"></i></a></li>
                <li><a href="/lia-space">Espaço LIA</a></li>
                <li><a class="current" href="#">Calendário</a></li>
            </ol>
        </div>
    </nav>
</div>
<br>
<div class="container-fluid">
    <div class="row">
        <div class="post-select">
            <label for="posto-select" style="font-size: 14px; font-weight: bold; color: #333;">Selecione um Posto:</label>
            <select id="posto-select">
                <option value="">Todos os Postos</option> <!-- Opção para mostrar tudo -->
            </select>
        </div>
    </div>
    <br>
    <div id="calendar"></div>

    
    <!-- Modal -->
    <div class="modal fade" id="reservationModal" tabindex="-1" aria-labelledby="reservationModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="reservationModalLabel">Detalhes da Reserva</h5>
                    <!-- <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button> -->
                </div>
                <div class="modal-body">
                    <p><strong>Bolseiro:</strong> <span id="modalBolseiro"></span></p>
                    <p><strong>Email:</strong> <span id="modalEmail"></span></p>
                    <p><strong>Data de Início:</strong> <span id="modalStartDate"></span></p>
                    <p><strong>Data de Término:</strong> <span id="modalEndDate"></span></p>
                    <!-- <p><strong>Equipamentos:</strong></p>
                    <ul id="modalEquipamentos"></ul> -->
                </div>
                <!-- <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                </div> -->
            </div>
        </div>
    </div>

</div>
@endsection