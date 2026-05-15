@extends('adminlte::page')

@section('title', 'Espaço LIA')

@section('content')
<style>
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
    <br>
    <div class="row">
        <div class="col-12 col-sm-5">
            <div class="three-container" id="threeContainer">
                <!-- O Three.js vai renderizar aqui -->
            </div>
        </div>
        <div class="col-12 col-sm-7">
            <!-- Elemento de informações do espaço -->
            <div id="space-info" style="display: none;">
                <div class="row">
                    <div class="col-6">
                        <h4 id="space_title"></h4>
                    </div>
                    <div class="col-6">
                        <div class="float-sm-right">
                            <button id="give" class="btn btn-success" style="width: 140px;" onclick="giveSpace()">Atribuição Posto</button>
                            <button class="btn btn-primary" style="width: 140px;" onclick="editSpace()">Editar Posto</button>
                            <button class="btn btn-danger" style="width: 140px;" onclick="deleteSpace(event)">Apagar Posto</button>
                        </div>
                    </div>
                </div>
                <br>
                <ul class="list-group">
                    <li class="list-group-item">
                        <label for="description">Descrição:</label>
                        <p id="description"></p>
                    </li>
                    <li class="list-group-item">
                        <label for="bolseiro">Bolseiro:</label>
                        <p id="bolseiro"></p>
                        <p id="tempo"></p>
                    </li>
                    <li class="list-group-item">
                        <label for="lia_code">Código LIA:</label>
                        <p id="lia_code"></p>
                    </li>
                    <li class="list-group-item">
                        <label for="price">Custo de Reserva:</label>
                        <p id="price"></p>
                    </li>
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
            </div>

            <!-- Elemento de inatividade do espaço -->
            <div id="inactive-space" style="display: none;">
                <h4>Posto de Trabalho Inativo!</h4>
                <p>Não existem informações disponíveis para o posto selecionado.</p>
            </div>

            <!-- Botões de ação -->
            <div id="space-buttons" style="display: none;">
                <h4>Nenhuma Informação Encontrada</h4>
                <button onclick="createSpace()" class="btn btn-primary" style="width: 140px;">Criar Posto</button>
            </div>
        </div>

    </div>
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
            scene.background = new THREE.Color(0xf4f6f9);
        
            // Configuração da câmara
            const camera = new THREE.PerspectiveCamera(60, $(container).width() / $(container).height(), 0.1, 1000);
            camera.position.set(0, 12, 7); // Ajusta a posição da câmera (x, y, z)
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
            const workstationGeometry = new THREE.BoxGeometry(1, 0.6, 1);
            // const workstationMaterial = new THREE.MeshBasicMaterial({ color: 0x00ff00 });
        
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

            // Chamando a função para criar a mesa central
            createTable({ x: 0, z: 0 });
        
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
                    // console.log('Posto de trabalho clicado!');
                    mostrarInformacoes(intersects[0].object);
                } else {
                    // console.log('Nenhum posto clicado.');
                }
            }   

            // Evento de clique para mostrar informações (apenas dentro do container Three.js)
            container.addEventListener('click', onDocumentClick);
            // Adiciona o evento de clique ao document
            // document.addEventListener('click', onDocumentClick, false);
        
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
                const give = document.getElementById('give');

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
                            give.innerText = "Editar Atribuição";
                            give.onclick = function() {
                                loadSpace(); // Chama a função quando o evento for disparado
                            };
                        } else {
                            // console.log("Sem bolseiro atribuído.");
                            give.innerText = "Atribuir Posto";
                            give.onclick = function() {
                                giveSpace(); // Chama a função quando o evento for disparado
                            };
                        }

                        // Após obter o bolseiro, buscar informações do espaço
                        return fetch(`/admin/lia-space`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({ spaceID: postoID })
                        });
                    })
                    .then(response => {
                        if (!response.ok) throw new Error('Falha ao carregar dados do espaço.');
                        return response.json();
                    })
                    .then(data => {
                        if (!data || !data.space) {
                            // Caso o espaço não exista
                            mostrarEspacoInativo();
                        } else {
                            // Atualiza os campos com os dados do espaço
                            mostrarEspacoAtivo(data, bolseiro, startDate, endDate);
                        }

                        // Exibir o modal de informações
                        abrirModalInformacoes();
                    })
                    .catch(error => {
                        console.error("Erro ao buscar informações:", error);
                    });
            }

            // Função para exibir espaços inativos
            function mostrarEspacoInativo() {
                document.getElementById('space-info').style.display = 'none';
                document.getElementById('inactive-space').style.display = 'block';
            }

            // Função para exibir espaços ativos com dados
            function mostrarEspacoAtivo(data, bolseiro, startDate, endDate) {
                const spaceInfo = document.getElementById('space-info');
                const spaceButtons = document.getElementById('space-buttons');

                if (spaceInfo && spaceButtons) {
                    spaceButtons.style.display = 'none';
                    spaceInfo.style.display = 'block';

                    // Atualizar campos com os dados do espaço
                    document.getElementById('space_title').innerText = `Posto de Trabalho ${data.space.space_code}`;
                    document.getElementById('description').innerText = data.space.description || 'Sem descrição';
                    document.getElementById('pc').innerText = data.space.pc || 'Sem pc';
                    document.getElementById('teclado').innerText = data.space.teclado || 'Sem teclado';
                    document.getElementById('rato').innerText = data.space.rato || 'Sem rato';
                    document.getElementById('lia_code').innerText = data.space.lia_code || 'Sem código de LIA';
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
                            const markup = `<li class="list-group-item">${item.description} <span style="color:gray; font-size:0.8em;">${item.lia_code}</span></li>`;
                            itensList.insertAdjacentHTML('beforeend', markup);
                        });
                    } else {
                        itensList.innerHTML = '<li class="list-group-item">Sem equipamentos associados.</li>';
                    }
                }
            }

            function mostrarEspacoInativo() {
                const spaceInfo = document.getElementById('space-info');
                const spaceButtons = document.getElementById('space-buttons');

                if (spaceInfo && spaceButtons) {
                    spaceInfo.style.display = 'none';
                    spaceButtons.style.display = 'block';
                }
            }

            // Função para abrir o modal de informações
            function abrirModalInformacoes() {
                isModalOpen = true; // Define o modal como aberto
            }

            // Função para exibir mensagens de erro no modal
            function mostrarModalErro(mensagem) {
                alert(mensagem);
            }

            window.createSpace = createSpace;
            function createSpace(){
                window.location.replace('/admin/lia-space/create/' + postoSelecionado.userData.id);
            }
            
            window.editSpace = editSpace;
            function editSpace(){
                window.location.replace('/admin/lia-space/' + postoSelecionado.userData.id + '/edit');
            }
            
            window.giveSpace = giveSpace;
            function giveSpace(){
                window.location.replace('/admin/lia-space/' + postoSelecionado.userData.id + '/reserve');
            }

            window.loadSpace = loadSpace;
            function loadSpace(){
                window.location.replace('/admin/lia-space/' + postoSelecionado.userData.id + '/editbolseiro');
            }

            window.deleteSpace = deleteSpace;
            function deleteSpace(){
                Swal.fire({
                    title: 'Apagar posto ' + postoSelecionado.userData.id + '?',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sim',
                    cancelButtonText: 'Não'
                }).then((result) => {
                    if(result.value)  {
                        $.ajax({
                            url: '/admin/lia-space/' + postoSelecionado.userData.id,
                            type: 'DELETE',
                            data:{
                                "_token": "{{ csrf_token() }}",
                                "_method": 'DELETE'
                            },
                            success: function(){
                            const Toast = Swal.mixin({
                                    toast: true,
                                    position: 'top-end',
                                    showConfirmButton: false,
                                    timer: 3000,
                                    timerProgressBar: true
                                })
                                Toast.fire({
                                    icon: 'success',
                                    title: 'Apagar posto...'
                                });
                                setTimeout(() => window.location.reload(), 1500); // Recarrega após 1,5s

                                $('#space-buttons').hide();
                                $('#space-info').hide();
                            }
                        });
                    }
                });
            }
        });
</script>
    <script>
        var spaceID;
        var imgWidth;

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

        function showModal(id){
            spaceID = id;
            highlightArea(id); // Chama a função highlightArea com o ID recebido
            $.ajax({
                url: "/admin/lia-space",
                type: "POST",
                data: {
                    "_token": "{{ csrf_token() }}",
                    spaceID: id,
                    start_date: $('#start_date').val(),
                    end_date: $('#end_date').val()
                },
                success: function(data){
                    if(data.space == null){
                        $('#space-buttons').show();
                        $('#space-info').hide();
                    } else {
                        $('#space-buttons').hide();
                        $('#space-info').show();
                        $('#space_title').text('Posto de Trabalho ' + data.space.space_code)
                        $('#description').text(data.space.description)
                        $('#lia_code').text(data.space.lia_code)
                        $('#price').text(numberFormat(data.space.cost, 2, ',', '.') + ' € / dia')
                        $('#itens').empty();
                        data.itens.forEach(item => {
                            var markup = 
                                '<li class="list-group-item">' +   
                                    item.description
                                '</li>' ;
                            $('#itens').append(markup);
                        });
                    }
                }
            })
        }

        // function createSpace(){
        //     window.location.replace('/admin/lia-space/create/' + spaceID)
        // }

        // function editSpace(){
        //     window.location.replace('/admin/lia-space/' + spaceID + '/edit');
        // }

        // function deleteSpace(){
        //     Swal.fire({
        //         title: 'Apagar posto ' + spaceID + '?',
        //         showCancelButton: true,
        //         confirmButtonColor: '#3085d6',
        //         cancelButtonColor: '#d33',
        //         confirmButtonText: 'Sim',
        //         cancelButtonText: 'Não'
        //     }).then((result) => {
        //         if(result.value)  {
        //             $.ajax({
        //                 url: '/admin/lia-space/' + spaceID,
        //                 type: 'DELETE',
        //                 data:{
        //                     "_token": "{{ csrf_token() }}",
        //                     "_method": 'DELETE'
        //                 },
        //                 success: function(){
        //                 const Toast = Swal.mixin({
        //                         toast: true,
        //                         position: 'top-end',
        //                         showConfirmButton: false,
        //                         timer: 3000,
        //                         timerProgressBar: true
        //                     })
        //                     Toast.fire({
        //                         icon: 'success',
        //                         title: 'Posto apagado'
        //                     });

        //                     $('#space-buttons').hide();
        //                     $('#space-info').hide();
        //                 }
        //             });
        //         }
        //     });
        // }

        // window.onload = function () {
        //     var ImageMap = function (map, img) {
        //             var n,
        //                 areas = map.getElementsByTagName('area'),
        //                 len = areas.length,
        //                 coords = [],
        //                 previousWidth = 561;
        //             for (n = 0; n < len; n++) {
        //                 coords[n] = areas[n].coords.split(',');
        //             }
        //             this.resize = function () {
        //                 var n, m, clen,
        //                     x = img.offsetWidth / previousWidth;
        //                 for (n = 0; n < len; n++) {
        //                     clen = coords[n].length;
        //                     for (m = 0; m < clen; m++) {
        //                         coords[n][m] *= x;
        //                     }
        //                     areas[n].coords = coords[n].join(',');
        //                 }
        //                 previousWidth = document.body.clientWidth;
        //                 return true;
        //             };
        //             window.onresize = this.resize;
        //         },
        //         imageMap = new ImageMap(document.getElementById('map'), document.getElementById('img'));
        //     imageMap.resize();
        //     return;
        // }
    </script>

    <style>
        /* Estilo para a camada de destaque */
        .highlight-layer {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: black;
            opacity: 50%;
            border-radius: 10px;
            pointer-events: none; /* Garante que a camada não intercepte eventos de clique */
            display: none; /* Inicialmente oculta */
        }
    </style>
@endsection