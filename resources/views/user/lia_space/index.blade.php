@extends('index')

@section('content')
<link href="/css/custom.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/pikaday/pikaday.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/pikaday/css/pikaday.css">

<div class="page-title">
    <nav class="breadcrumbs">
        <div class="container d-flex justify-content-between align-items-center">
            <ol class="d-flex mb-0">
                <li><a href="/"><i class="bi bi-house"></i></a></li>
                <li><a class="current" href="#">Espaço LIA</a></li>
            </ol>
            <div>
                <!-- <a href="#" onclick="checkAvailability()"><span>Verificar Disponibilidade</span></a> -->
                <a href="/lia-space/callendar"><span>Verificar Disponibilidade</span></a>
            </div>
            <!-- <div class="ml-2">
                <input type="date" class="form-control" name="start_date" id="start_date">
            </div>
            <div class="ml-2">
                <input type="date" class="form-control" name="end_date" id="end_date">
            </div> -->
        </div>
    </nav>
</div>
<br>
<div class="container-fluid">
    <div class="row">
        <div class="col-12 col-sm-6">
            <div id="space-info" style="display: none;">
                <div class="row">
                    <div class="col-6">
                        <h4 id="space_title"></h4>
                    </div>
                </div>
                <ul class="list-group">
                    <li class="list-group-item">
                        <label for="bolseiro">Bolseiro:</label>
                        <p id="bolseiro"></p>
                        <p id="tempo"></p>
                    </li>
                    <li class="list-group-item">
                        <label for="description">Descriçao:</label>
                        <p id="description"></p>
                    </li>
                    <li class="list-group-item">
                        <label for="price">Custo de Reserva:</label>
                        <p id="price"></p>
                    </li>
                    <!-- <li class="list-group-item">
                        <div id="availability"></div>
                    </li> -->
                </ul>
                <br>
                <div>
                    <button onclick="abrirModalSpecs()" class="button_equip">Ver Detalhes PC</button>
                    <div id="modalSpecs" class="modal">
                        <div class="modal-content">
                            <span class="fechar" onclick="fecharModalSpecs()">&times;</span>
                            <h3>Detalhes de Computador do Posto</h3>
                            <ul class="list-group">
                                <li id="pc" class="list-group-item"></li>
                                <li id="teclado" class="list-group-item"></li>
                                <li id="rato" class="list-group-item"></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <br>
                <div>
                    <button onclick="abrirModal()" class="button_equip">Ver Equipamentos</button>
                    <div id="modalEquipamentos" class="modal">
                        <div class="modal-content">
                            <span class="fechar" onclick="fecharModal()">&times;</span>
                            <h3>Equipamento do Posto de Trabalho</h3>
                            <ul id="itens" class="list-group">
                        </div>
                    </div>
                </div>

                <!-- <h4 class="content-header">Equipamento do Posto de Trabalho</h4> -->
                <!-- <ul id="itens" class="list-group"> -->
                </ul>
                <br>
            </div>
            <div id="inactive-space" style="display: none;">
                <h4>Posto de Trabalho Inativo!</h4>
            </div>
        </div>
        <div class="col-12 col-sm-6">
            <div class="three-container" id="threeContainer">
                <!-- O Three.js vai renderizar aqui -->
            </div>
            <p></p>
        </div>
    </div>
</div>

<style>
    input[type="date"] {
        border: none;
        background-color: #2c2c2c;
        /* Dark background color */
        color: #a1a1a1;
        /* Placeholder text color */
    }

    input[type="date"]:focus {
        background-color: #2c2c2c;
        /* Dark background color */
        color: #a1a1a1;
        /* Placeholder text color */
    }

    input[type="date"]::-webkit-calendar-picker-indicator {
        filter: invert(1);
    }

    /* Para o modelo da sala */
    canvas {
        /* position: block; */
        /* top: 0;
        left: 0; */
        width: 100%;
        height: 100%;
    }
    #threeContainer {
        position: block;
        width: 40vw;
        height: 70vh;
        overflow: hidden;
    }

        /* Para o modelo da sala */
    /* body { margin: 0; overflow: hidden; } */
    canvas {
        /* position: block; */
        /* top: 0;
        left: 0; */
        width: 100%;
        height: 100%;
    }
    #threeContainer {
        position: block;
        width: 35vw;
        height: 70vh;
        overflow: hidden;
    }

    /* Estilos básicos do modal */
    .modal {
    display: none; /* Oculto por padrão */
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0, 0, 0, 0.4); /* Fundo escurecido */
    animation: fadeIn 0.3s; /* Animação para aparecer */
    }

    /* Conteúdo do modal */
    .modal-content {
    background-color: #fff;
    margin: 10% auto;
    padding: 20px;
    border-radius: 8px;
    width: 50%;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    transform: scale(0.9); /* Efeito de "zoom out" inicial */
    animation: zoomIn 0.3s forwards; /* Animação para aumentar */
    }

    /* Botão de fechar */
    .fechar {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
    }

    .fechar:hover {
    color: #000;
    }

    /* Lista de equipamentos dentro do modal */
    .modal-content ul {
    list-style: none;
    padding: 0;
    }

    .modal-content li {
    padding: 10px;
    border-bottom: 1px solid #ddd;
    }

    .modal-content li:last-child {
    border-bottom: none;
    }

    .modal-content li:hover {
    background-color: #f9f9f9;
    }

    /* Animação de fade-in para o fundo */
    @keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
    }

    /* Animação de zoom-in para o conteúdo */
    @keyframes zoomIn {
    from {
        transform: scale(0.9);
        opacity: 0;
    }
    to {
        transform: scale(1);
        opacity: 1;
    }
    }

    /* Animação de fade-out para fechar o modal */
    @keyframes fadeOut {
    from {
        opacity: 1;
    }
    to {
        opacity: 0;
    }
    }

    /* Animação de zoom-out para fechar o conteúdo */
    @keyframes zoomOut {
    from {
        transform: scale(1);
        opacity: 1;
    }
    to {
        transform: scale(0.9);
        opacity: 0;
    }
    }

    .button_equip{
        width: 100%;
        position: relative;
        display: block;
        padding: .75rem 1.25rem;
        background-color: #fff;
        border: 2px solid rgba(0,0,0,.125);
    }
</style>
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/three@0.128/examples/js/controls/OrbitControls.js"></script>
<script>
        function abrirModal() {
            const modal = document.getElementById("modalEquipamentos");
            modal.style.display = "block"; // Mostra o modal
            modal.classList.remove("fadeOut"); // Remove animação de saída, se existir
            modal.classList.add("fadeIn"); // Adiciona animação de entrada
        }

        function fecharModal() {
            const modal = document.getElementById("modalEquipamentos");
            modal.classList.remove("fadeIn"); // Remove animação de entrada
            modal.classList.add("fadeOut"); // Adiciona animação de saída
            modal.style.display = "none";
        }

        // Fecha o modal ao clicar fora dele
        window.onclick = function (event) {
            const modal = document.getElementById("modalEquipamentos");
            if (event.target === modal) {
                fecharModal();
            }
        };

        function abrirModalSpecs() {
            const modal = document.getElementById("modalSpecs");
            modal.style.display = "block"; // Mostra o modal
            modal.classList.remove("fadeOut"); // Remove animação de saída, se existir
            modal.classList.add("fadeIn"); // Adiciona animação de entrada
        }

        function fecharModalSpecs() {
            const modal = document.getElementById("modalSpecs");
            modal.classList.remove("fadeIn"); // Remove animação de entrada
            modal.classList.add("fadeOut"); // Adiciona animação de saída
            modal.style.display = "none";
        }

        // Fecha o modal ao clicar fora dele
        window.onclick = function (event) {
            const modal = document.getElementById("modalSpecs");
            if (event.target === modal) {
                fecharModalSpecs();
            }
        };

        document.addEventListener("DOMContentLoaded", function() {
            const container = document.getElementById("threeContainer");
            let isModalOpen = false; // Estado para verificar se o modal está aberto
            // Setup da cena
            const scene = new THREE.Scene();
            scene.background = new THREE.Color(0xffffff);
        
            // Configuração da câmara
            const camera = new THREE.PerspectiveCamera(60, $(container).width() / $(container).height(), 0.1, 1000);
            camera.position.set(0, 13, 7); // Ajusta a posição da câmera (x, y, z)
            camera.lookAt(new THREE.Vector3(0, 0, 0));
        
            // Configuração do renderizador
            const renderer = new THREE.WebGLRenderer({ antialias: true });
            renderer.setSize($(container).width(), $(container).height());
            // document.body.appendChild(renderer.domElement);
            container.appendChild(renderer.domElement);
        
            // Controlos de órbita
            const controls = new THREE.OrbitControls(camera, renderer.domElement);
            controls.enableDamping = true;
            controls.dampingFactor = 0.1;
            controls.enableZoom = true;

            // Limita o zoom
            controls.minDistance = 5; // Distância mínima da câmara em relação à cena
            controls.maxDistance = 20; // Distância máxima da câmara em relação à cena

            // Limita a rotação vertical (para evitar que a câmara fique de cabeça para baixo ou esconda a sala)
            controls.maxPolarAngle = Math.PI / 2.2; // Limite superior (90 graus = Math.PI / 2)
            controls.minPolarAngle = 0; // Limite inferior (horizontal)

            // Limita a área de rotação horizontal
            controls.maxAzimuthAngle = Math.PI / 2;
            controls.minAzimuthAngle = -Math.PI / 2;

            // Definir os limites de pan (posição do "target")
            const panLimits = {
                minX: -5, // Limite esquerdo
                maxX: 5,  // Limite direito
                minZ: -5, // Limite traseiro
                maxZ: 5,  // Limite frontal
                minY: 1,  // Limite inferior no eixo Y (altura mínima)
                maxY: 10  // Limite superior no eixo Y (altura máxima)
            };

            // Função para aplicar os limites
            function aplicarLimitesPan() {
                const target = controls.target;

                // Limitar o movimento no eixo X
                target.x = Math.max(panLimits.minX, Math.min(panLimits.maxX, target.x));

                // Limitar o movimento no eixo Z
                target.z = Math.max(panLimits.minZ, Math.min(panLimits.maxZ, target.z));

                // Limitar o movimento no eixo Y
                target.y = Math.max(panLimits.minY, Math.min(panLimits.maxY, target.y));
            }

            // Verificar e corrigir o movimento do "target"
            controls.addEventListener('change', aplicarLimitesPan);

            // Atualizar os controlos inicialmente
            aplicarLimitesPan();
        
            // Chão da sala
            const floorGeometry = new THREE.PlaneGeometry(10, 10);
            const floorMaterial = new THREE.MeshBasicMaterial({ color: 0xaaaaaa, side: THREE.DoubleSide });
            const floor = new THREE.Mesh(floorGeometry, floorMaterial);
            floor.rotation.x = Math.PI / 2;
            scene.add(floor);
        
            // Paredes
            const wallMaterial = new THREE.MeshBasicMaterial({ color: 0x000000 });
            const wallThickness = 0.2;
        
            const walls = [
                new THREE.BoxGeometry(10, 2, wallThickness), // Parede superior
                new THREE.BoxGeometry(10, 2, wallThickness), // Parede inferior
                new THREE.BoxGeometry(wallThickness, 2, 10), // Parede esquerda
                new THREE.BoxGeometry(wallThickness, 2, 10), // Parede direita
            ];
        
            walls.forEach((geometry, index) => {
                const wall = new THREE.Mesh(geometry, wallMaterial);
                switch (index) {
                    case 0: wall.position.set(0, 1.01, -5); break;
                    case 1: wall.position.set(0, 1.01, 5); break;
                    case 2: wall.position.set(-5, 1.01, 0); break;
                    case 3: wall.position.set(5, 1.01, 0); break;
                }
                scene.add(wall);
            });
        
            // Porta
            const doorGeometry = new THREE.BoxGeometry(0.21, 1.5, 1);
            const doorMaterial = new THREE.MeshBasicMaterial({ color: 0x895129 });
            const door = new THREE.Mesh(doorGeometry, doorMaterial);
            door.position.set(-5, 0.78, 4);
            scene.add(door);
        
            // Postos de trabalho (workstations)
            const workstationGeometry = new THREE.BoxGeometry(1, 0.5, 1);
            //const workstationMaterial = new THREE.MeshBasicMaterial({ color: 0x00ff00 });
        
            // Array para armazenar os postos de trabalho
            const postos = [];
            const workstationPositions = [
                [-4.5, 0.3, 2], [-4.5, 0.3, -0.5], [-4.5, 0.3, -3], [-2, 0.3, -4.5], [1, 0.3, -4.5],
                [4.5, 0.3, 2], [4.5, 0.3, -0.5], [4.5, 0.3, -3],
            ];
        
            workstationPositions.forEach((pos, index) => {
                const material = new THREE.MeshBasicMaterial({ color: 0x065294 }); // Material único para cada posto
                const workstation = new THREE.Mesh(workstationGeometry, material);
                workstation.position.set(pos[0], pos[1], pos[2]);
                workstation.userData = { id: index + 1 };
                scene.add(workstation);
                postos.push(workstation);  // Adiciona ao array postos
            });
        
            // Mesa central
            function createTable(position) {
                const tableGeometry = new THREE.BoxGeometry(3.5, 0.5, 5);
                const tableMaterial = new THREE.MeshStandardMaterial({ color: 0x777777 });
                const table = new THREE.Mesh(tableGeometry, tableMaterial);
                table.position.set(position.x, 0.4, position.z);
                scene.add(table);

                // Define o ponto de rotação para coincidir com a table
                controls.target.set(table.position.x, table.position.y, table.position.z);
                controls.update(); // Atualiza os controlos
            }
            createTable({ x: 0, z: 0 });  // Mesa central
        
            // Loop de renderização
            function animate() {
                requestAnimationFrame(animate);
                if (!isModalOpen) {
                    controls.update(); // Apenas atualiza os controles se o modal estiver fechado
                }
                renderer.render(scene, camera);
            }
        
            animate();
        
            // Ajuste para redimensionar a janela
            window.addEventListener('resize', () => {
                renderer.setSize($(container).width(), $(container).height());
                camera.aspect = $(container).width() / $(container).height();
                camera.updateProjectionMatrix();
            });
        
            // Configuração do Raycaster
            const raycaster = new THREE.Raycaster();
            const mouse = new THREE.Vector2();
        
            // Evento de clique para mostrar informações
            function onDocumentClick(event) {
                event.preventDefault();

                const rect = container.getBoundingClientRect();
        
                // Calcula a posição do mouse na tela
                mouse.x = ((event.clientX - rect.left) / rect.width) * 2 - 1;
                mouse.y = -((event.clientY - rect.top) / rect.height) * 2 + 1;
        
                // Atualiza o Raycaster com base na posição do mouse
                raycaster.setFromCamera(mouse, camera);
        
                // Verifica as interseções com os objetos no array postos
                const intersects = raycaster.intersectObjects(postos);
        
                if (intersects.length > 0 ) {
                    console.log('Posto de trabalho clicado!');
                    mostrarInformacoes(intersects[0].object);
                } else {
                    console.log('Nenhum posto clicado.');
                }
            }   

            // Evento de clique para mostrar informações (apenas dentro do container Three.js)
            container.addEventListener('click', onDocumentClick);
        

            let postoSelecionado = null; // Variável global para guardar o posto selecionado
        
            function mostrarInformacoes(objeto) {
                const postoID = objeto.userData.id;
                let bolseiro = "Sem bolseiro atribuído.";
                let startDate = "N/A";
                let endDate = "N/A";

                // Atualizar o visual do posto clicado
                if (postoSelecionado) {
                    postoSelecionado.material.color.set(0x065294); // Reverte a cor do posto anterior para verde
                }
                objeto.material.color.set(0x009885); // Altera a cor do posto clicado para vermelho
                postoSelecionado = objeto; // Atualiza o posto selecionado

                 // Chamada para buscar informações do bolseiro
                 fetch(`/admin/lia-space/bolseiro/${postoID}`, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => {
                        if (!response.ok) throw new Error('Falha ao carregar dados do bolseiro.');
                        return response.json();
                    })
                    .then(data => {
                        if (data && data.bolseiro.length > 0) {
                            const reserva = data.bolseiro[0]; // Considerando que pode haver apenas um bolseiro ativo por reserva
                            bolseiro = reserva.bolseiro || "Sem bolseiro atribuído.";
                            startDate = reserva.data_inicio || "N/A";
                            endDate = reserva.data_fim || "N/A";
                        } else {
                            console.log("Sem bolseiro atribuído.");
                        }
                        // Fazer a chamada à API para buscar as informações
                        return fetch(`lia-space`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({
                                spaceID: postoID
                            })
                        });
                    })
                    .then(response => {
                        if (!response.ok) throw new Error('Falha ao carregar dados do espaço.');
                        return response.json();
                    })
                        .then(data => {
                            if (data.space == null) {
                                // Caso o espaço não exista
                                mostrarEspacoInativo();
                            } else {
                                mostrarEspacoAtivo(data, bolseiro, startDate, endDate)
                        
                    }

                   // Exibir o modal de informações
                   abrirModalInformacoes();
                })
                // .catch(error => {
                //         console.error("Erro ao buscar informações:", error);
                // });
            }

             // Função para exibir espaços ativos com dados
             function mostrarEspacoAtivo(data, bolseiro, startDate, endDate) {
                    // Exibir informações do posto
                    document.getElementById('inactive-space').style.display = 'none';
                    document.getElementById('space-info').style.display = 'block';
                    // Atualizar campos com os dados do espaço
                    document.getElementById('space_title').innerText = `Posto de Trabalho ${data.space.space_code}`;
                    document.getElementById('description').innerText = data.space.description || 'Sem descrição';
                    document.getElementById('pc').innerText = data.space.pc || 'Sem pc';
                    document.getElementById('teclado').innerText = data.space.teclado || 'Sem teclado';
                    document.getElementById('rato').innerText = data.space.rato || 'Sem rato';
                    document.getElementById('price').innerText = `${numberFormat(data.space.cost, 2, ',', '.')} € / dia`;

                    // Atualizar campos adicionais
                    const bolseiroElement = document.getElementById('bolseiro'); // Evitar confusão de nomes
                    const tempo = document.getElementById('tempo');
                    if (bolseiroElement) bolseiroElement.innerText = bolseiro;
                    if (tempo) tempo.innerText = `Tempo de Uso: ${startDate} - ${endDate}`;

                    // Exibindo equipamentos
                    const itensList = document.getElementById('itens');
                    itensList.innerHTML = '';
                    if (data.itens && data.itens.length > 0) {
                        data.itens.forEach(item => {
                            const markup = `<li class="list-group-item">${item.description}</li>`;
                            itensList.insertAdjacentHTML('beforeend', markup);
                        });
                    } else {
                        itensList.innerHTML = '<li class="list-group-item">Sem equipamentos associados.</li>';
                    }
            }
            
            // Função para exibir espaços inativos
            function mostrarEspacoInativo() {
                document.getElementById('space-info').style.display = 'none';
                document.getElementById('inactive-space').style.display = 'block';
            }

            // Função para abrir o modal de informações
            function abrirModalInformacoes() {
                // document.getElementById("infoModal").style.display = "block";
                isModalOpen = true; // Define o modal como aberto
            }

            function closeModal() {
                document.getElementById("infoModal").style.display = "none";
        
                // Voltar a cor original do posto selecionado
                if (postoSelecionado) {
                    postoSelecionado.material.color.set(0x00ff00); // Cor verde original
                    postoSelecionado = null; // Limpar a referência ao posto selecionado
                }
        
                isModalOpen = false; // Modal fechado
                //controls.enabled = true; // Reativa os controles de órbita
            }
        
        
            // Função para mostrar modais de erro
            function mostrarModalErro(mensagem) {
                document.getElementById("modalTitle").innerText = "Erro";
                document.getElementById("modalContent").innerText = mensagem;
                document.getElementById("infoModal").style.display = "block";
                //controls.enabled = true; // Reativa os controles de órbita
            }
        });

</script>
<script>
    start_date.min = new Date().toISOString().split("T")[0];
    end_date.min = new Date().toISOString().split("T")[0];
    var currentSpace;
    var picker; // Variável global para armazenar a instância do Pikaday

    var ptDate = {
        previousMonth: 'Mês Anterior',
        nextMonth: 'Próximo Mês',
        months: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'],
        weekdays: ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'],
        weekdaysShort: ['Dom', '2ª', '3ª', '4ª', '5ª', '6ª', 'Sab']
    };

    function parseDate(dateStr) {
        var parts = dateStr.split('/');
        return new Date(parts[2], parts[1] - 1, parts[0]);
    }

    function updatePikaday(parsedReservas) {
        // Seleciona todos os botões do Pikaday
        var days = document.querySelectorAll('.pika-button');

        // Itera sobre cada botão para verificar se está dentro de algum intervalo de reserva
        days.forEach(function(day) {
            var year = day.getAttribute('data-pika-year');
            var month = day.getAttribute('data-pika-month');
            var dayNum = day.getAttribute('data-pika-day');
            var dayDate = new Date(year, month, dayNum);

            var isHighlighted = false;

            // Verifica se o dia está dentro de algum intervalo de reserva
            parsedReservas.forEach(function(reserva) {
                var start = reserva.start;
                var end = reserva.end;

                if (dayDate >= start && dayDate <= end) {
                    isHighlighted = true;
                }
            });

            // Adiciona ou remove a classe 'has-event' com base na verificação
            if (isHighlighted) {
                day.classList.add('has-event');
            } else {
                day.classList.remove('has-event');
            }
        });
    }

    // Função para inicializar o Pikaday
    function initializePikaday(parsedReservas) {
        if (picker) {
            picker.destroy(); // Destruir a instância anterior do Pikaday se existir
        }

        picker = new Pikaday({
            field: document.getElementById('datepicker'),
            i18n: ptDate,
            bound: false,
            minDate: new Date(),
            onDraw: function() {
                // Atualizar Pikaday com as reservas sempre que o calendário for desenhado
                updatePikaday(parsedReservas);
                // Define o z-index do container do Pikaday
                document.querySelector('.pika-single').style.zIndex = '1';
            }
        });

        // Atualizar Pikaday com as reservas após a inicialização
        updatePikaday(parsedReservas);
    }

    function numberFormat(number, decimals, decPoint, thousandsSep) {
        number = number.toFixed(decimals);

        var nstr = number.toString();
        var x = nstr.split('.');
        var x1 = x[0];
        var x2 = x.length > 1 ? decPoint + x[1] : '';

        var rgx = /(\d+)(\d{3})/;

        while (rgx.test(x1)) {
            x1 = x1.replace(rgx, '$1' + thousandsSep + '$2');
        }

        return x1 + x2;
    }

    function highlightArea(id) {
        // Encontra a área correspondente ao ID recebido
        var area = document.querySelector('area[onclick="showModal(' + id + ')"]');

        if (!area) {
            console.error("Área com ID " + id + " não encontrada.");
            return;
        }

        // Oculta todas as camadas de destaque existentes
        var highlightLayers = document.getElementsByClassName('highlight-layer');
        for (var i = 0; i < highlightLayers.length; i++) {
            highlightLayers[i].style.display = 'none';
        }

        // Obtém as coordenadas da área clicada
        var coords = area.coords.split(',');
        var left = parseInt(coords[0]);
        var top = parseInt(coords[1]);
        var right = parseInt(coords[2]);
        var bottom = parseInt(coords[3]);

        // Calcula o tamanho personalizado do bloco de destaque
        var width = right - left;
        var height = bottom - top;

        // Ajuste para personalizar o tamanho do bloco de destaque como desejado
        width *= 1.5; // Exemplo: aumentar a largura em 50%
        height *= 1.5; // Exemplo: aumentar a altura em 50%

        // Define as dimensões e posição da camada de destaque
        var highlightLayer = document.getElementById('highlight-layer');
        highlightLayer.style.display = 'block';
        highlightLayer.style.top = (top - 10) + 'px';
        highlightLayer.style.left = (left - 9) + 'px';
        highlightLayer.style.width = width + 'px';
        highlightLayer.style.height = height + 'px';
    }

    function showModal(id) {
        currentSpace = id;

        highlightArea(id); // Chama a função highlightArea com o ID recebido

        $.ajax({
            url: "lia-space",
            type: "POST",
            data: {
                "_token": "{{ csrf_token() }}",
                spaceID: id
            },
            success: function(data) {
                if (data.space == null) {
                    $('#space-info').hide();
                    $('#inactive-space').show();
                } else {
                    $('#inactive-space').hide();
                    $('#space-info').show();
                    $('#space_title').text('Posto de Trabalho ' + data.space.space_code)
                    $('#description').text(data.space.description)
                    $('#price').text(numberFormat(data.space.cost, 2, ',', '.') + ' € / dia')

                    // Exibindo os itens associados ao espaço
                    $('#itens').empty();
                    data.itens.forEach(item => {
                        var markup = '<li class="list-group-item">' + item.description + '</li>';
                        $('#itens').append(markup);
                    });

                    // Exibindo a disponibilidade das datas de reserva
                    $('#availability').empty();
                    if (data.reservas.length > 0) {
                        var availabilityMarkup = '';
                        availabilityMarkup += '<div class="container d-flex justify-content-center align-items-center text-center flex-column" id="calendar">';
                        availabilityMarkup += '<div id="datepicker"></div>';
                        availabilityMarkup += '</div>';
                        $('#availability').html(availabilityMarkup);
                        // Atualizar Pikaday com as novas reservas
                        var reservas = data.reservas;
                        var parsedReservas = reservas.map(function(reserva) {
                            return {
                                start: parseDate(reserva.start_date),
                                end: parseDate(reserva.end_date)
                            };
                        });
                        // Inicializar Pikaday com as novas reservas
                        initializePikaday(parsedReservas);
                    } else {
                        $('#availability').text('Nenhuma reserva agendada.');
                    }
                }
            }
        });
    }

    function checkAvailability() {

        if ($('#start_date').val() > $('#end_date').val()) {
            console.log("erro");
            Swal.fire({
                title: 'Erro!',
                text: "As datas escolhidas não são válidas!",
                cancelButtonText: 'Cancelar'
            });
            return;
        }

        $.ajax({
            url: "lia-space/availability",
            type: "POST",
            data: {
                "_token": "{{ csrf_token() }}",
                spaceID: currentSpace,
                start_date: $('#start_date').val(),
                end_date: $('#end_date').val()
            },
            success: function(data) {
                if (data.available == true) {
                    Swal.fire({
                        title: 'Posto disponível!',
                        text: "Pretende reservar?",
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Sim, reservar',
                        cancelButtonText: 'Não'
                    }).then((result) => {
                        if (result.value) {
                            window.location.replace("/lia-space/reserve?spaceID=" + currentSpace + "&start_date=" + $('#start_date').val() + "&end_date=" + $('#end_date').val());
                        }
                    })
                } else {
                    Swal.fire({
                        title: 'Posto indisponível!',
                        text: "Posto encontra-se ocupado para as datas selecionadas!"
                    });
                }
            },
            error: function(data) {
                console.log(data.reponse);
            }
        })
    }

    window.onload = function() {
        var ImageMap = function(map, img) {
                var n,
                    areas = map.getElementsByTagName('area'),
                    len = areas.length,
                    coords = [],
                    previousWidth = 561;
                for (n = 0; n < len; n++) {
                    coords[n] = areas[n].coords.split(',');
                }
                this.resize = function() {
                    var n, m, clen,
                        x = img.offsetWidth / previousWidth;
                    for (n = 0; n < len; n++) {
                        clen = coords[n].length;
                        for (m = 0; m < clen; m++) {
                            coords[n][m] *= x;
                        }
                        areas[n].coords = coords[n].join(',');
                    }
                    previousWidth = document.body.clientWidth;
                    return true;
                };
                window.onresize = this.resize;
            },
            imageMap = new ImageMap(document.getElementById('map'), document.getElementById('img'));
        imageMap.resize();
        return;
    }

    function myFunction() {
        var ImageMap = function(map, img) {
                var n,
                    areas = map.getElementsByTagName('area'),
                    len = areas.length,
                    coords = [],
                    previousWidth = 561;
                for (n = 0; n < len; n++) {
                    coords[n] = areas[n].coords.split(',');
                }
                this.resize = function() {
                    var n, m, clen,
                        x = img.offsetWidth / previousWidth;
                    for (n = 0; n < len; n++) {
                        clen = coords[n].length;
                        for (m = 0; m < clen; m++) {
                            coords[n][m] *= x;
                        }
                        areas[n].coords = coords[n].join(',');
                    }
                    previousWidth = document.body.clientWidth;
                    return true;
                };
                window.onresize = this.resize;
            },
            imageMap = new ImageMap(document.getElementById('map'), document.getElementById('img'));
        imageMap.resize();
        return;
    }

    $(document).ready(function() {
        $('[data-toggle="popover"]').popover();
    });
</script>
@endsection