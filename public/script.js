$(document).ready(function () {
    // Função auxiliar para limpar o CPF, removendo todos os caracteres não-dígitos
    const limparCPF = (cpf) => {
        return cpf.replace(/[^\d]/g, "");
    };

    // 🛠️ NOVIDADE: Função para aplicar a máscara 000.000.000-00
    const aplicarMascaraCPF = (valor) => {
        valor = limparCPF(valor); // Garante que só há números
        valor = valor.replace(/(\d{3})(\d)/, "$1.$2");
        valor = valor.replace(/(\d{3})(\d)/, "$1.$2");
        valor = valor.replace(/(\d{3})(\d{1,2})$/, "$1-$2");
        // Limita o valor final a 14 caracteres (11 dígitos + 3 caracteres da máscara)
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

        // --- VALIDAÇÃO BÁSICA ---
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
        // --- FIM DA VALIDAÇÃO BÁSICA ---

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

    // Carregar o número de estagiários presentes no dia
    $(document).ready(function () {
        $.ajax({
            url: "/relatorio-estagiarios",
            type: "GET",
            success: function (response) {
                $("#contador-presentes").text(response.total);
            },
        });
    });
    $(document).ready(function () {
        $.ajax({
            url: "/relatorio-registros",
            type: "GET",
            success: function (response) {
                $("#registros-dia").text(response.total);
            },
        });
    });
    $(document).ready(function () {
        $.ajax({
            url: "/relatorio-recesso",
            type: "GET",
            success: function (response) {
                $("#recesso-dia").text(response.total);
            },
        });
    });
    $(document).ready(function () {
        $.ajax({
            url: "/relatorio-atestados",
            type: "GET",
            success: function (response) {
                $("#atestados-dia").text(response.total);
            },
        });
    });
    $(document).ready(function () {
        $.ajax({
            url: "/relatorio-folgas",
            type: "GET",
            success: function (response) {
                $("#folgas-dia").text(response.total);
            },
        });
    });
    $(document).ready(function () {
        $.ajax({
            url: "/relatorio-dispensas",
            type: "GET",
            success: function (response) {
                $("#dispensas-dia").text(response.total);
            },
        });
    });
    $(document).ready(function () {
        $.ajax({
            url: "/relatorio-faltas",
            type: "GET",
            success: function (response) {
                $("#faltas-dia").text(response.total);
            },
        });
    });
    $(document).ready(function () {
        carregarListaEstagiarios();
    });
    function carregarListaEstagiarios() {
        $.ajax({
            url: "/lista-estagiarios",
            type: "GET",
            success: function (lista) {
                let html = "";
                const dadosReais = lista.data || [];
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
                    const dataDia = item.data || '';
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
                    </tr>
                    `;
                    $("#tabela-estagiarios-corpo").html(html);
                });
            },
        });
    }
});
