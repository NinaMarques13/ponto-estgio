$(document).ready(function () {
    if ($.fn.DataTable) {
        var tabelaCadastrados = $("#tabela-estagiarios-cadastrados").DataTable({
            processing: true,
            serverSide: true,
            ajax: "/estagiarios-cadastrados",
            columns: [
                { data: "id", name: "id", visible: false },
                { data: "nm_estagiarios", name: "nm_estagiarios" },
                { data: "nr_matricula", name: "nr_matricula" },
                { data: "nm_setor", name: "nm_setor" },
                { data: "nr_telefone", name: "nr_telefone" },
                { data: "nm_email", name: "nm_email" },
                {
                    data: "action",
                    name: "action",
                    orderable: false,
                    searchable: false,
                },
            ],
            language: {
                url: "https://cdn.datatables.net/plug-ins/1.13.4/i18n/pt-BR.json",
            },
        });
    } else {
        console.error("O plugin DataTable não foi carregado corretamente.");
    }

    // var tabelaEstagiarios = $('#tabela-estagiarios-cadastro').DataTable({
    //     processing: true,
    //     serverSide: true,
    //     ajax: {
    //         url: "/estagiarios-cadastrados",
    //         data: function(d) {
    //             d.ano = $("#data-ano").val();
    //             d.mes = $("#data-mes").val();
    //         }
    //     },
    //     columns: [
    //         {data: 'nm_estagiarios', name: 'nm_estagiarios'},
    //         {data: 'nr_cpf', name: 'nrcpf'},
    //         {data: 'nm_setor', name:'nm_setor'},
    //         {data: 'nr_telefone', name:'nr_telefone'},
    //         {data: 'nm_email', name: 'nm_email'},
    //         {
    //             data: 'action',
    //             name: 'action',
    //             orderable: false,
    //             serchable: false,
    //             className: "text-center"
    //         }
    //     ],
    //     language: {
    //         url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/pt-BR.json"
    //     }
    // });
    // $("#data-ano, #data-mes").change(function() {
    //     tabelaEstagiarios.ajax.reload();
    // })

    // $(document).ready(function () {
    //     // Função auxiliar para limpar o CPF, removendo todos os caracteres não-dígitos
    //     const limparCPF = (cpf) => {
    //         return cpf.replace(/[^\d]/g, "");
    //     };

    //     const aplicarMascaraCPF = (valor) => {
    //         valor = limparCPF(valor);
    //         valor = valor.replace(/(\d{3})(\d)/, "$1.$2");
    //         valor = valor.replace(/(\d{3})(\d)/, "$1.$2");
    //         valor = valor.replace(/(\d{3})(\d{1,2})$/, "$1-$2");

    //         if (valor.length > 14) {
    //             valor = valor.substring(0, 14);
    //         }
    //         return valor;
    //     };

    //     // 0. Aplicar Máscara ao Input do CPF
    //     $("#cpf").on("keyup", function () {
    //         // Aplica a máscara a cada tecla digitada
    //         $(this).val(aplicarMascaraCPF($(this).val()));
    //     });

    //     // -------------------------------------------

    //     // 1. Alternar seleção Entrada/Saída
    //     $(".registro-link").on("click", function (e) {
    //         e.preventDefault();

    //         $(".registro-link").removeClass("active");
    //         $(this).addClass("active");

    //         let acao = $(this).text().includes("Entrada") ? "Entrada" : "Saída";
    //         console.log("Ação selecionada:", acao);
    //     });

    //     // 2. Lógica do botão REGISTRAR
    //     $("#registrarBtn").on("click", function () {
    //         // Pega o valor do CPF (com máscara) e limpa (apenas números)
    //         let cpfBruto = $("#cpf").val().trim();
    //         let cpf = limparCPF(cpfBruto);

    //         // Pega a ação selecionada (Entrada ou Saída)
    //         let acaoSelecionada = $(".registro-link.active")
    //             .text()
    //             .includes("Entrada")
    //             ? "Entrada"
    //             : "Saída";

    //         if (cpf === "") {
    //             alert("Por favor, digite o CPF.");
    //             $("#cpf").focus();
    //             return;
    //         }

    //         if (cpf.length !== 11) {
    //             alert("O CPF deve conter exatamente 11 dígitos.");
    //             $("#cpf").focus();
    //             return;
    //         }

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
    // });

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
    // carregarListaEstagiarios();
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
                        <td><button class="btn btn-sm btn-bd-primary btn-editar" 
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

    $(document).on("click", ".btn-gerar-qr", function () {
        const cpfdEstagiario = $(this).attr("data-identificador") || "0";

        const dadoParaOQr = cpfdEstagiario;

        const container = document.getElementById("qrcodeCadastro");
        container.innerHTML = "";

        new QRCode(container, {
            text: dadoParaOQr,
            width: 220,
            height: 220,
        });
    });

    var tabelaCadastrados = $("#tabela-estagiarios-listagem").DataTable({
        processing: true,
        serverSide: true,
        ajax: "/estagiariosCadastrados",
        columns: [
            { data: "id", name: "id" },
            { data: "nm_estagiarios", name: "nm_estagiarios" },
            { data: "nr_matricula", name: "nr_matricula" },
            { data: "nm_setor", name: "nm_setor" },
            { data: "nr_telefone", name: "nr_telefone" },
            {
                data: "action",
                name: "action",
                orderable: false,
                serchable: false,
            },
        ],
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/pt-BR.json",
        },
    });
    $("#formAdicionarEstagiario").on("submit", function (e) {
        e.preventDefault();
        let token = $('input[name="_token"]').val();

        let payload = {
            _token: token,
            nm_estagiarios: $("#nome_cadastro").val(),
            nr_matricula: $("#cpf_cadastro").val(),
            nm_setor: $("#setor_cadastro").val(),
            nr_telefone: $("#telefone_cadastro").val(),
            nm_email: $("#email_cadastro").val(),
        };
        console.log(payload);

        $.ajax({
            url: "/cadastrar-estagiario",
            type: "POST",
            headers: {
                "X-CSRF-TOKEN": $('input[name="_token"]').val(),
                Accept: "application/json",
            },
            data: payload,
            success: function (e) {
                alert("Cadastro concluído!");
                $("#modalAdicionarEstagiario").modal("hide");
                location.reload();
            },
            error: function (xhr) {
                console.error(xhr.responseText);
                alert("Erro ao cadastrar.");
            },
        });
    });
});

// $('#formAdicionarEstagiario').on('submit', function (e) {
//     e.preventDefault();

//     const dados = {
//         nome: $('#nome_cadastro').val().toUpperCase(),
//         cpf: $('#cpf_cadastro').val(),
//         setor: $('#setor_cadastro').val().toUpperCase(),
//         telefone: $('#telefone_cadastro').val() || "---",
//         email: $('#email_cadastro').val() || "---"
//     };

//     const tr = document.createElement('tr');

//     const criarColuna = (texto) => {
//         const td = document.createElement('td');
//         td.textContent = texto;
//         return td;
//     };

//     tr.appendChild(criarColuna(dados.nome));
//     tr.appendChild(criarColuna(dados.cpf));
//     tr.appendChild(criarColuna(dados.setor));
//     tr.appendChild(criarColuna(dados.telefone));
//     tr.appendChild(criarColuna(dados.email));

//     const tdAcoes = document.createElement('td');
//     tdAcoes.className = "text-center";

//     const divGroup = document.createElement('div');
//     divGroup.className = "d-flex justify-content-center gap-2";

//     const criarBotao = (classes, icone) => {
//         const btn = document.createElement('button');
//         btn.className = `btn btn-sm border-0 ${classes}`;

//         const i = document.createElement('i');
//         i.className = `bi bi-${icone}`;

//         btn.appendChild(i);
//         return btn;
//     };

//     const btnQr = criarBotao('btn-purple text-white', 'qr-code');
//     btnQr.setAttribute('data-bs-toggle', 'modal');
//     btnQr.setAttribute('data-bs-target', '#qrModalCadastro');
//     btnQr.setAttribute('data-identificador', dados.cpf);
//     btnQr.classList.add('btn-gerar-qr');

//     const btnEdit = criarBotao('btn-primary btn-editar-estagiario', 'pencil-square');
//     const btnDel = criarBotao('btn-danger btn-excluir-estagiario', 'trash');

//     divGroup.appendChild(btnQr);
//     divGroup.appendChild(btnEdit);
//     divGroup.appendChild(btnDel);
//     tdAcoes.appendChild(divGroup);
//     tr.appendChild(tdAcoes);

//     const corpoTabela = $("#tabela-estagiarios");

//     if (corpoTabela.find('tr').first().find('td').first().text().trim() === "") {
//         corpoTabela.empty();
//     }

//     corpoTabela.append(tr);

//     bootstrap.Modal.getInstance(document.getElementById('modalAdicionarEstagiario')).hide();
//     this.reset();

//     alert("Estagiário adicionado com sucesso!");
// });

$("#tabela-estagiarios-cadastrados").on(
    "click",
    ".btn-editar-estagiario",
    function (e) {
        e.preventDefault();

        const idParaEditar =
            $(this).attr("data-identificador") || $(this).data("identificador");
        const table = $("#tabela-estagiarios-cadastrados").DataTable();
        const tr = $(this).closest("tr");
        const data = table.row(tr).data();

        console.log("DADOS DISPONÍVEIS NA LINHA:", data);

        if (idParaEditar) {
            $("#id_estagiario_editar").val(idParaEditar);

            $("#nome_editar").val(data.nm_estagiarios || "");
            $("#cpf_editar").val(data.nr_matricula || "");
            $("#setor_editar").val(data.nm_setor || "");
            $("#telefone_editar").val(data.nr_telefone || "");
            $("#email_editar").val(data.nm_email || "");

        } else {
            alert(
                "Erro: O DataTable não forneceu um ID válido para esta linha.",
            );
            console.error("Objeto data não possui ID:", data);
        }
    },
);

$("#formEditarEstagiario").on("submit", function (e) {
    e.preventDefault();

    const inputId = document.getElementById("id_estagiario_editar");
    let idLimpo = inputId.value.toString().replace(/\D/g, "");

    console.log("ID RECUPERADO DO INPUT:", inputId.value);
    console.log("DEBUG - VALOR ENVIADO:", idLimpo);

    if (!idLimpo) {
        alert("Erro: ID não identificado.");
        return;
    }

    const payload = {
        _token: $('input[name="_token"]').val(),
        _method: "PUT",
        nome: $("#nome_editar").val(),
        cpf: $("#cpf_editar").val(),
        setor: $("#setor_editar").val(),
        telefone: $("#telefone_editar").val(),
        email: $("#email_editar").val(),
    };

    $.ajax({
        url: "/atualizar-cadastro/" + idLimpo,
        type: "POST",
        data: payload,
        dataType: "json",
        success: function (response) {
            alert("Sucesso: Estagiário atualizado!");
            location.reload();
        },
        error: function (xhr) {
            let erroMsg = "Erro desconhecido no servidor.";

            if (xhr.responseJSON && xhr.responseJSON.message) {
                erroMsg = xhr.responseJSON.message;
            } else if (xhr.responseText) {
                try {
                    const resp = JSON.parse(xhr.responseText);
                    erroMsg = resp.message || erroMsg;
                } catch (e) {
                    erroMsg = "Erro crítico (500) no servidor.";
                }
            }

            alert("Falha ao salvar: " + erroMsg);
            console.error("Detalhes do erro:", xhr);
        },
    });
});

$("#cpf_cadastro").on("input", function () {
    let valor = $(this).val().replace(/\D/g, "");

    if (valor.length > 11) {
        valor = valor.substring(0, 11);
    }

    $(this).val(valor);
});

$("#telefone_cadastro").on("input", function () {
    let valor = $(this).val().replace(/\D/g, "");

    if (valor.length > 11) {
        valor = valor.substring(0, 11);
    }

    $(this).val(valor);
});

$(document).on("click", ".btn-excluir-estagiario", function (e) {
    e.preventDefault();

    let idLimpo = $(this).data("identificador");

    console.log("ID para exclusão:", idLimpo);

    if (!idLimpo) {
        alert("Erro: Não foi possível recuperar o ID do estagiário.");
        return;
    }

    if (confirm("Tem certeza que deseja excluir este estagiário?")) {
        const payload = {
            _token: $('input[name="_token"]').val(),
            _method: "PUT",
        };

        $.ajax({
            url: "/desativar-estagiario/" + idLimpo,
            type: "POST",
            data: payload,

            success: function (response) {
                alert(response.message);

                if (typeof tabelaCadastrados !== "undefined") {
                    tabelaCadastrados.ajax.reload();
                } else {
                    location.reload();
                }
            },
            error: function (xhr) {
                console.error(xhr.responseText);
                alert("Erro ao excluir registro.");
            },
        });
    }
});

// $("#modalAdicionarEstagiario").on("hidden.bs.modal", function () {
//     $(this).find("form")[0].reset();
// });

// $("#buscaNome").on("keyup", function() {
//     let valor = $(this).val().toLowerCase();

//     $("#tabela-estagiarios tr").filter(function() {

//         let nomeEstagiario = $(this).find("td:first").text().toLowerCase();

//         $(this).toggle(nomeEstagiario.indexOf(valor) > -1);
//     });
// });

// $("#buscaCPF").on("keyup", function () {
//     let valor = limparCPF($(this).val());

//     $("#tabela-estagiarios tr").filter(function () {
//         let cpfTabela = limparCPF($(this).find("td:nth-child(2)").text());
//         $(this).toggle(cpfTabela.indexOf(valor) > -1);
//     });
// });
