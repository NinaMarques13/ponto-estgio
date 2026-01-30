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
                url: "",
                lengthMenu: "Exibir _MENU_ por página",
                search: "Pesquisar Estagiário",
                searchPlaceholder: "Digite a informação",
                zeroRecords: "Nenhum registro encontrado",
                info: "Mostrando página _PAGE_ de _PAGES_",
                infoEmpty: "Nenhum registro disponível",
                infoFiltered: "(filtrado de _MAX_ registros no total)",
            },
        });
    } else {
        console.error("O plugin DataTable não foi carregado corretamente.");
    }

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
    $(
        "#data-mes, #data-ano, #filtro-motivo, #filtro-estagiario, #data-completa",
    ).on("change", function () {
        console.log("Filtro alterado: " + $(this).val());
        tabelaPontos.draw();
    });
    tabelaPontos = $("#myTable").DataTable({
        processing: true,
        serverSide: true,
        searching: true,
        ajax: {
            url: "/lista-estagiarios",
            data: function (d) {
                d._token = $('input[name="_token"]').val();
                d.ano = $("#data-ano").val() || "";
                d.mes = $("#data-mes").val() || "";
                d.motivo = $("#filtro-motivo").val() || "";
                d.estagiario_id = $("#filtro-estagiario").val() || "";
                d.data = $("#data-completa").val() || "";
            },
        },
        columns: [
            { data: "data", name: "hr_registro" },
            {
                data: "entrada",
                name: "entrada",
                orderable: false,
                searchable: false,
            },
            {
                data: "saida",
                name: "saida",
                orderable: false,
                searchable: false,
            },
            {
                data: "total_horas",
                name: "total_horas",
                orderable: false,
                searchable: false,
            },
            {
                data: "matricula",
                name: "estagiario.nr_matricula",
                orderable: false,
                searchable: false,
            },
            {
                data: "nome",
                name: "estagiario.nm_estagiarios",
                orderable: true,
                searchable: true,
            },
            { data: "motivo", name: "ds_motivo" },
            { data: "setor", name: "estagiario.nm_setor" },
            {
                data: "observacao",
                name: "ds_observacao",
                orderable: false,
                searchable: false,
            },
            {
                data: "action",
                name: "action",
                orderable: false,
                searchable: false,
            },
        ],
        language: {
            url: "",
            lengthMenu: "Exibir _MENU_ por página",
            search: "Pesquisar Estagiário",
            searchPlaceholder: "Digite a informação",
            zeroRecords: "Nenhum registro encontrado",
            info: "Mostrando página _PAGE_ de _PAGES_",
            infoEmpty: "Nenhum registro disponível",
            infoFiltered: "(filtrado de _MAX_ registros no total)",
        },
        drawCallback: function (settings) {
            var json = this.api().ajax.json();
            if (json && json.cards) {
                $("#contador-presentes").text(json.cards.presentes);
                $("#registros-dia").text(json.cards.registros);
                $("#recesso-dia").text(json.cards.recessos);
                $("#atestados-dia").text(json.cards.atestados);
                $("#folgas-dia").text(json.cards.folgas);
                $("#dispensas-dia").text(json.cards.dispensa);
                $("#faltas-dia").text(json.cards.falta);
            }
        },
    });
    $(document).on("click", ".editar-btn", function () {
        let tr = $(this).closest("tr");
        let rowData = $("#myTable").DataTable().row(tr).data();
        console.log("Dados capturados da linha:", rowData);
        let dataFormatada = "";
        if (rowData.data && rowData.data.includes("/")) {
            const partes = rowData.data.split("/");
            dataFormatada = `${partes[2]}-${partes[1]}-${partes[0]}`;
        }
        $("#edit-id").val(rowData.estagiario_id || "");
        $("#edit-data").val(dataFormatada || "");
        $("#edit-nome").val(rowData.nome || "");
        $("#edit-matricula").val(rowData.matricula || "");
        $("#edit-entrada").val(rowData.entrada.substring(0, 5) || "");
        $("#edit-saida").val(rowData.saida.substring(0, 5) || "");
        $("#edit-total-horas").val(rowData.total_horas || "");
        $("#edit-motivo").val(rowData.motivo || "");
        $("#edit-setor").val(rowData.setor || "");
        $("#edit-obs").val(rowData.ds_observacao || "");
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
