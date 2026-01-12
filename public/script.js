$(document).ready(function() {

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

    
    $('#cpf').on('keyup', function() {
        
        $(this).val(aplicarMascaraCPF($(this).val()));
    });
    
  
    
    
    $('.registro-link').on('click', function() { 
        
        
        $('.registro-link').removeClass('active');
        $(this).addClass('active');
        
        let acao = $(this).text().includes('Entrada') ? 'Entrada' : 'Saída';
        console.log("Ação selecionada:", acao);
    });

    
    $('#registrarBtn').on('click', function() {
        
        
        let cpfBruto = $('#cpf').val().trim();
        let cpf = limparCPF(cpfBruto);
        
        
        let acaoSelecionada = $('.registro-link.active').text().includes('Entrada') ? 'Entrada' : 'Saída';

       
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
        

       
        console.log(`Registrando ${acaoSelecionada} para o CPF: ${cpf}`);
        
        
        setTimeout(() => {
             alert(`Ponto de ${acaoSelecionada} para o CPF ${cpf} SIMULADO com sucesso!`);
             $('#cpf').val(''); 
        }, 300);
        
        
    });
    
    
    $('.camera-icon').on('click', function() {
        alert("Ação de Leitura de CPF/QR Code ativada (Simulação)");
    });

    $(".nav-link").on("click", function () {
        $(".nav-link").removeClass("active");
        $(this).addClass("active");
        let type = $(this).attr("id");
        $(".conteudo-aba").hide();
        $(".conteudo-aba." + type).fadeIn();
    });
    $(document).ready(function () {

<<<<<<< HEAD
        $('#openQrCadastro').on('click', function () {
    
            let urlCadastro = "https://ponto-estagio.pm.pr.gov.br/views/principal/cadastro";
    
            $('#qrcodeCadastro').html("");
    
            new QRCode(document.getElementById("qrcodeCadastro"), {
                text: urlCadastro,
                width: 220,
                height: 220
            });
    
            const modal = new bootstrap.Modal(document.getElementById('qrModalCadastro'));
            modal.show();
        });
    
    });
    
=======
    // Carregar o número de estagiários presentes no dia
        $.ajax({
            url: "/relatorio-estagiarios",
            type: "GET",
            success: function (response) {
                $("#contador-presentes").text(response.total);
            },
        });
        $.ajax({
            url: "/relatorio-registros",
            type: "GET",
            success: function (response) {
                $("#registros-dia").text(response.total);
            },
        });
        $.ajax({
            url: "/relatorio-recesso",
            type: "GET",
            success: function (response) {
                $("#recesso-dia").text(response.total);
            },
        });
        $.ajax({
            url: "/relatorio-atestados",
            type: "GET",
            success: function (response) {
                $("#atestados-dia").text(response.total);
            },
        });
        $.ajax({
            url: "/relatorio-folgas",
            type: "GET",
            success: function (response) {
                $("#folgas-dia").text(response.total);
            },
        });
        $.ajax({
            url: "/relatorio-dispensas",
            type: "GET",
            success: function (response) {
                $("#dispensas-dia").text(response.total);
            },
        });
        $.ajax({
            url: "/relatorio-faltas",
            type: "GET",
            success: function (response) {
                $("#faltas-dia").text(response.total);
            },
        });
        carregarSelectEstagiarios();
        carregarListaEstagiarios();
        $("#filtro-motivo, #filtro-estagiario").change(function () {
            carregarListaEstagiarios();
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
                    '<option value="">Erro ao carregar</option>'
                );
            },
        });
    }
    function carregarListaEstagiarios() {
        let motivoVal = $("#filtro-motivo").val() || "";
        let estagiarioVal = $("#filtro-estagiario").val() || "";
        if (!$.isNumeric(estagiarioVal)) {
        estagiarioVal = ""; 
        }
        console.log("Filtrando por:", { motivo: motivoVal, id: estagiarioVal });
        $.ajax({
            url: "/lista-estagiarios",
            type: "GET",
            data: {
                motivo: motivoVal,
                estagiario_id: estagiarioVal,
            },
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
                    });
                }
                $("#tabela-estagiarios-corpo").html(html);
            },
            error: function (err) {
                console.error("Erro ao carregar lista:", err);
                $("#tabela-estagiarios-corpo").html(
                    '<tr><td colspan="8" class="text-danger">Erro ao carregar dados.</td></tr>'
                );
            },
        });
    }
<<<<<<< HEAD
>>>>>>> 53e5f22 (elementos com consulta ajax atualizados)
});
=======
});
>>>>>>> 1d7662b (lista principal da tela de exportação funcional)
