$(document).ready(function () {
    // Função auxiliar para limpar o CPF, removendo todos os caracteres não-dígitos
    const limparCPF = (cpf) => {
        return cpf.replace(/[^\d]/g, "");
    };

    
    const aplicarMascaraCPF = (valor) => {
        valor = limparCPF(valor); 
        valor = valor.replace(/(\d{3})(\d)/, "$1.$2");
        valor = valor.replace(/(\d{3})(\d)/, "$1.$2");
        valor = valor.replace(/(\d{3})(\d{1,2})$/, "$1-$2");
        
        if (valor.length > 14) {
            valor = valor.substring(0, 14);
        }
        return valor;
    };

    // 0. Aplicar Máscara ao Input do CPF
    $("#cpf").on("keyup", function () {
        // Aplica a máscara a cada tecla digitada
        $(this).val(aplicarMascaraCPF($(this).val()));
    });

    // -------------------------------------------

    // 1. Alternar seleção Entrada/Saída
    $(".registro-link").on("click", function (e) {
        e.preventDefault();

        $(".registro-link").removeClass("active");
        $(this).addClass("active");

        let acao = $(this).text().includes("Entrada") ? "Entrada" : "Saída";
        console.log("Ação selecionada:", acao);
    });

    // 2. Lógica do botão REGISTRAR
    $("#registrarBtn").on("click", function () {
        // Pega o valor do CPF (com máscara) e limpa (apenas números)
        let cpfBruto = $("#cpf").val().trim();
        let cpf = limparCPF(cpfBruto);

        // Pega a ação selecionada (Entrada ou Saída)
        let acaoSelecionada = $(".registro-link.active")
            .text()
            .includes("Entrada")
            ? "Entrada"
            : "Saída";

       
        if (cpf === "") {
            alert("Por favor, digite o CPF.");
            $("#cpf").focus();
            return;
        }

        if (cpf.length !== 11) {
            alert("O CPF deve conter exatamente 11 dígitos.");
            $("#cpf").focus();
            return;
        }
        

        // // Simulação do processamento de registro
        // console.log(`Registrando ${acaoSelecionada} para o CPF: ${cpf}`);

        // // Simulação de sucesso
        // setTimeout(() => {
        //      alert(`Ponto de ${acaoSelecionada} para o CPF ${cpf} SIMULADO com sucesso!`);
        //      $('#cpf').val(''); // Limpa o campo
        // }, 300);

        /*
        // Lógica REAL de AJAX para enviar os dados para o servidor:
        // Exemplo:
        // $.ajax({
        //     url: 'seu_endpoint_de_registro.php', 
        //     method: 'POST',
        //     data: { 
        //         cpf: cpf, // Envia o CPF limpo (somente dígitos)
        //         acao: acaoSelecionada 
        //     },
        //     ...
        // });
        */
    });

    // 3. Ação para o ícone da câmera (simulação)
    $(".camera-icon").on("click", function () {
        alert("Ação de Leitura de CPF/QR Code ativada (Simulação)");
    });

    $(".nav-link").on("click", function () {
        $(".nav-link").removeClass("active");
        $(this).addClass("active");
        let type = $(this).attr("id");
        $(".conteudo-aba").hide();
        $(".conteudo-aba." + type).fadeIn();
    });
    carregarSelectEstagiarios();
    carregarListaEstagiarios();
    cardEstagiarios();
    cardRegistros();
    cardRecessos();
    cardAtestados();
    cardFolgas();
    cardDispensas();
    cardFaltas();
    $(
        "#filtro-motivo, #filtro-estagiario, #data-ano, #data-completa, #data-mes",
    ).change(function () {
        console.log($(this).val());
        carregarListaEstagiarios();
        cardEstagiarios();
        cardRegistros();
        cardRecessos();
        cardAtestados();
        cardFolgas();
        cardDispensas();
        cardFaltas();
    });
    function carregarSelectEstagiarios() {
        $.ajax({
            url: "/pesquisar-estagiarios",
            type: "GET",
            success: function (resposta) {
                let html = '<option value="">Todos</option>';
                const lista = resposta.data || [];
                lista.forEach(function (item) {
                    const nome = item.nm_estagiarios
                        ? item.nm_estagiarios.toUpperCase()
                        : "SEM NOME";
                    html += `<option value="${item.id}">${nome}</option>`;
                });
                $("#filtro-estagiario").html(html);
            },
            error: function (err) {
                console.error("Erro ao carregar nomes:", err);
                $("#filtro-estagiario").html(
                    '<option value="">Erro ao carregar</option>',
                );
            },
        });
    }
    function carregarListaEstagiarios() {
        let dataVal = $("#data-completa").val() || "";
        let motivoVal = $("#filtro-motivo").val() || "";
        let estagiarioVal = $("#filtro-estagiario").val() || "";
        let anoVal = $("#data-ano").val() || "";
        let MesVal = $("#data-mes").val() || "";
        let token = $('input[name="_token"]').val();
        let payload = { _token: token };
        if (anoVal && anoVal !== "") {
            payload.ano = anoVal;
        }
        if (MesVal && MesVal !== "") {
            payload.mes = MesVal;
        }
        if (motivoVal && motivoVal !== "") {
            payload.motivo = motivoVal;
        }
        if (estagiarioVal && estagiarioVal !== "") {
            payload.estagiario_id = estagiarioVal;
        }
        if (dataVal && dataVal !== "") {
            payload.data = dataVal;
        }
        $.ajax({
            url: "/lista-estagiarios",
            type: "GET",
            data: payload,
            success: function (lista) {
                let html = "";
                const dadosReais = lista.data || [];
                if (dadosReais.length === 0) {
                    html =
                        '<tr><td colspan="8" class="text-center">Nenhum registro encontrado.</td></tr>';
                } else {
                    dadosReais.forEach(function (item) {
                        const nomeLimpo = item.nome
                            ? item.nome.toUpperCase()
                            : "---";
                        const matriculaLimpa = item.matricula
                            ? item.matricula
                            : "---";
                        const horaEntrada = item.entrada || "";
                        const horaSaida = item.saida || "";
                        const totalHoras = item.total_horas || "";
                        const motivoRegistro = item.motivo || "";
                        const setorEstagiario = item.setor || "---";
                        const dataDia = item.data || "";
                        const observacao = item.ds_observacao || "---";
                        html += `
                    <tr>
                        <td>
                            ${dataDia}
                        </td>
                        <td>
                            ${horaEntrada}
                        </td>
                        <td>
                            ${horaSaida}
                        </td>
                        <td>${totalHoras}</td>
                        <td>${matriculaLimpa}</td>
                        <td>${nomeLimpo}</td>
                        <td>${motivoRegistro}</td>
                        <td>${setorEstagiario}</td>
                        <td>${observacao}</td>
                        <td><button class="btn btn-sm btn-primary btn-editar" 
                        data-item='${JSON.stringify(item)}'>Editar</button>
                        </td>
                    </tr>
                    `;
                    });
                }
                $("#tabela-estagiarios-corpo").html(html);
            },
            error: function (err) {
                console.error("Erro ao carregar lista:", err);
                $("#tabela-estagiarios-corpo").html(
                    '<tr><td colspan="8" class="text-danger">Erro ao carregar dados.</td></tr>',
                );
            },
        });
    }
    function cardEstagiarios() {
        let dataVal = $("#data-completa").val() || "";
        let motivoVal = $("#filtro-motivo").val() || "";
        let estagiarioVal = $("#filtro-estagiario").val() || "";
        let anoVal = $("#data-ano").val() || "";
        let MesVal = $("#data-mes").val() || "";
        let token = $('input[name="_token"]').val();
        let payload = { _token: token };
        if (anoVal && anoVal !== "") {
            payload.ano = anoVal;
        }
        if (MesVal && MesVal !== "") {
            payload.mes = MesVal;
        }
        if (motivoVal && motivoVal !== "") {
            payload.motivo = motivoVal;
        }
        if (estagiarioVal && estagiarioVal !== "") {
            payload.estagiario_id = estagiarioVal;
        }
        if (dataVal && dataVal !== "") {
            payload.data = dataVal;
        }
        $.ajax({
            url: "/relatorio-estagiarios",
            type: "GET",
            data: payload,
            success: function (response) {
                $("#contador-presentes").text(response.total);
            },
        });
    }
    function cardRegistros() {
        let dataVal = $("#data-completa").val() || "";
        let motivoVal = $("#filtro-motivo").val() || "";
        let estagiarioVal = $("#filtro-estagiario").val() || "";
        let anoVal = $("#data-ano").val() || "";
        let MesVal = $("#data-mes").val() || "";
        let token = $('input[name="_token"]').val();
        let payload = { _token: token };
        if (anoVal && anoVal !== "") {
            payload.ano = anoVal;
        }
        if (MesVal && MesVal !== "") {
            payload.mes = MesVal;
        }
        if (motivoVal && motivoVal !== "") {
            payload.motivo = motivoVal;
        }
        if (estagiarioVal && estagiarioVal !== "") {
            payload.estagiario_id = estagiarioVal;
        }
        if (dataVal && dataVal !== "") {
            payload.data = dataVal;
        }
        $.ajax({
            url: "/relatorio-registros",
            type: "GET",
            data: payload,
            success: function (response) {
                $("#registros-dia").text(response.total);
            },
        });
    }
    function cardRecessos() {
        let dataVal = $("#data-completa").val() || "";
        let motivoVal = $("#filtro-motivo").val() || "";
        let estagiarioVal = $("#filtro-estagiario").val() || "";
        let anoVal = $("#data-ano").val() || "";
        let MesVal = $("#data-mes").val() || "";
        let token = $('input[name="_token"]').val();
        let payload = { _token: token };
        if (anoVal && anoVal !== "") {
            payload.ano = anoVal;
        }
        if (MesVal && MesVal !== "") {
            payload.mes = MesVal;
        }
        if (motivoVal && motivoVal !== "") {
            payload.motivo = motivoVal;
        }
        if (estagiarioVal && estagiarioVal !== "") {
            payload.estagiario_id = estagiarioVal;
        }
        if (dataVal && dataVal !== "") {
            payload.data = dataVal;
        }
        $.ajax({
            url: "/relatorio-recessos",
            type: "GET",
            data: payload,
            success: function (response) {
                $("#recesso-dia").text(response.total);
            },
        });
    }
    function cardAtestados() {
        let dataVal = $("#data-completa").val() || "";
        let motivoVal = $("#filtro-motivo").val() || "";
        let estagiarioVal = $("#filtro-estagiario").val() || "";
        let anoVal = $("#data-ano").val() || "";
        let MesVal = $("#data-mes").val() || "";
        let token = $('input[name="_token"]').val();
        let payload = { _token: token };
        if (anoVal && anoVal !== "") {
            payload.ano = anoVal;
        }
        if (MesVal && MesVal !== "") {
            payload.mes = MesVal;
        }
        if (motivoVal && motivoVal !== "") {
            payload.motivo = motivoVal;
        }
        if (estagiarioVal && estagiarioVal !== "") {
            payload.estagiario_id = estagiarioVal;
        }
        if (dataVal && dataVal !== "") {
            payload.data = dataVal;
        }
        $.ajax({
            url: "/relatorio-atestados",
            type: "GET",
            success: function (response) {
                $("#atestados-dia").text(response.total);
            },
        });
    }
    function cardFolgas() {
        let dataVal = $("#data-completa").val() || "";
        let motivoVal = $("#filtro-motivo").val() || "";
        let estagiarioVal = $("#filtro-estagiario").val() || "";
        let anoVal = $("#data-ano").val() || "";
        let MesVal = $("#data-mes").val() || "";
        let token = $('input[name="_token"]').val();
        let payload = { _token: token };
        if (anoVal && anoVal !== "") {
            payload.ano = anoVal;
        }
        if (MesVal && MesVal !== "") {
            payload.mes = MesVal;
        }
        if (motivoVal && motivoVal !== "") {
            payload.motivo = motivoVal;
        }
        if (estagiarioVal && estagiarioVal !== "") {
            payload.estagiario_id = estagiarioVal;
        }
        if (dataVal && dataVal !== "") {
            payload.data = dataVal;
        }
        $.ajax({
            url: "/relatorio-folgas",
            type: "GET",
            success: function (response) {
                $("#folgas-dia").text(response.total);
            },
        });
    }
    function cardDispensas() {
        let dataVal = $("#data-completa").val() || "";
        let motivoVal = $("#filtro-motivo").val() || "";
        let estagiarioVal = $("#filtro-estagiario").val() || "";
        let anoVal = $("#data-ano").val() || "";
        let MesVal = $("#data-mes").val() || "";
        let token = $('input[name="_token"]').val();
        let payload = { _token: token };
        if (anoVal && anoVal !== "") {
            payload.ano = anoVal;
        }
        if (MesVal && MesVal !== "") {
            payload.mes = MesVal;
        }
        if (motivoVal && motivoVal !== "") {
            payload.motivo = motivoVal;
        }
        if (estagiarioVal && estagiarioVal !== "") {
            payload.estagiario_id = estagiarioVal;
        }
        if (dataVal && dataVal !== "") {
            payload.data = dataVal;
        }
        $.ajax({
            url: "/relatorio-dispensas",
            type: "GET",
            success: function (response) {
                $("#dispensas-dia").text(response.total);
            },
        });
    }
    function cardFaltas() {
        let dataVal = $("#data-completa").val() || "";
        let motivoVal = $("#filtro-motivo").val() || "";
        let estagiarioVal = $("#filtro-estagiario").val() || "";
        let anoVal = $("#data-ano").val() || "";
        let MesVal = $("#data-mes").val() || "";
        let token = $('input[name="_token"]').val();
        let payload = { _token: token };
        if (anoVal && anoVal !== "") {
            payload.ano = anoVal;
        }
        if (MesVal && MesVal !== "") {
            payload.mes = MesVal;
        }
        if (motivoVal && motivoVal !== "") {
            payload.motivo = motivoVal;
        }
        if (estagiarioVal && estagiarioVal !== "") {
            payload.estagiario_id = estagiarioVal;
        }
        if (dataVal && dataVal !== "") {
            payload.data = dataVal;
        }
        $.ajax({
            url: "/relatorio-faltas",
            type: "GET",
            success: function (response) {
                $("#faltas-dia").text(response.total);
            },
        });
    }
    $(document).on("click", ".btn-editar", function () {
        const item = $(this).data("item");
        let dataFormatada = "";
        if (item.data && item.data.includes("/")) {
            const partes = item.data.split("/");
            dataFormatada = `${partes[2]}-${partes[1]}-${partes[0]}`;
        }
        console.log(item);
        $("#edit-id").val(item.id || "");
        $("#edit-data").val(dataFormatada || "");
        $("#edit-nome").val(item.nome || "");
        $("#edit-matricula").val(item.matricula || "");
        $("#edit-entrada").val(item.entrada.substring(0, 5) || "");
        $("#edit-saida").val(item.saida.substring(0, 5) || "");
        $("#edit-total-horas").val(item.total_horas || "");
        $("#edit-motivo").val(item.motivo || "");
        $("#edit-setor").val(item.setor || "");
        $("#edit-obs").val(item.ds_observacao || "");
        $('input[name="_token"]').val();
        const modal = new bootstrap.Modal(
            document.getElementById("modalEditarEstagiario"),
        );
        modal.show();
    });
    $(document).on("click", "#btn-salvar", function () {
        salvarEstagiario();
    });
    function salvarEstagiario() {
        let payload = {
            id: $("#edit-id").val(),
            data: $("#edit-data").val(),
            entrada: $("#edit-entrada").val(),
            saida: $("#edit-saida").val(),
            matricula: $("#edit-matricula").val(),
            nome: $("#edit-nome").val(),
            motivo: $("#edit-motivo").val(),
            setor: $("#edit-setor").val(),
            observacao: $("#edit-obs").val(),
            _token: $('input[name="_token"]').val(),
            _method: "PUT",
        };
        console.log("Payload sendo enviado:", payload);
        if (!payload.id) {
            alert("Erro: ID do registro não encontrado.");
            return;
        }
        $.ajax({
            url: "/atualizar-estagiarios/" + payload.id,
            type: "post",
            data: payload,
            success: function (response) {
                console.log(response);
                location.reload();
            },
            error: function (xhr) {
                console.error(xhr.responseText);
                alert("Erro ao salvar. Verifique o console.");
            },
        });
    }
});
