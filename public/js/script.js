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
    if ($.fn.DataTable) {
        var tabelaCadastrados = $("#tabela-estagiarios-eventos").DataTable({
            processing: true,
            serverSide: true,
            ajax: "/estagiarios-eventos",
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

    // ====================================================
    // EVENTOS E OCORRÊNCIAS
    // ====================================================
    let currentEstagiarioId = null;
    let occurrencesList = [];

    // 1. Abrir Modal para Adicionar Evento
    $(document).on("click", ".btn-gerar-evento", function () {
        const idEstagiario = $(this).data("identificador");
        const nomeEstagiario = $(this).data("nome");

        $("#add-evento-estagiario-id").val(idEstagiario);
        $("#add-evento-estagiario-nome").val(nomeEstagiario);

        // Limpa campos anteriores
        $("#add-evento-data-inicio").val("");
        $("#add-evento-data-fim").val("");
        $("#add-evento-motivo").val("");
        $("#add-evento-observacao").val("");

        $("#add-evento-hora-entrada").val("").removeAttr("required");
        $("#add-evento-hora-saida").val("").removeAttr("required");
        $("#add-evento-is-abonado").prop("checked", false);

        $("#add-evento-secao-horarios").addClass("d-none");
        $("#add-evento-secao-abono").addClass("d-none");

        $("#add-evento-secao-conflitos").addClass("d-none");
        $("#lista-registros-conflito").empty();

        $("#modalAdicionarEvento").modal("show");
    });

    // Função para verificar se já existem registros no período
    function verificarRegistrosExistentes() {
        const idEstagiario = $("#add-evento-estagiario-id").val();
        const dataInicio = $("#add-evento-data-inicio").val();
        const dataFim = $("#add-evento-data-fim").val();

        if (idEstagiario && dataInicio && dataFim) {
            $.ajax({
                url: "/estagiarios/" + idEstagiario + "/verificar-periodo",
                type: "GET",
                data: {
                    inicio: dataInicio,
                    fim: dataFim
                },
                success: function (response) {
                    const listContainer = $("#lista-registros-conflito");
                    listContainer.empty();

                    if (response.success && response.registros.length > 0) {
                        $("#add-evento-secao-conflitos").removeClass("d-none");
                        response.registros.forEach(function (record) {
                            const li = `
                                <li class="list-group-item d-flex justify-content-between align-items-center py-1 bg-light">
                                    <span><strong>${record.data}</strong> - ${record.label}</span>
                                    <button type="button" class="btn btn-sm btn-link text-danger p-0 btn-excluir-conflito" data-id="${record.id}" style="text-decoration: none;">
                                        Excluir
                                    </button>
                                </li>
                            `;
                            listContainer.append(li);
                        });
                    } else {
                        $("#add-evento-secao-conflitos").addClass("d-none");
                    }
                },
                error: function (xhr) {
                    console.error("Erro ao verificar período:", xhr.responseText);
                }
            });
        } else {
            $("#add-evento-secao-conflitos").addClass("d-none");
            $("#lista-registros-conflito").empty();
        }
    }

    // Monitora alteração nas datas do modal de criação
    $("#add-evento-data-inicio, #add-evento-data-fim").on("change", function () {
        verificarRegistrosExistentes();
    });

    // Excluir registro conflitante de dentro do modal
    $(document).on("click", ".btn-excluir-conflito", function () {
        const idRegistro = $(this).data("id");
        if (confirm("Deseja realmente excluir este registro?")) {
            $.ajax({
                url: "/excluir-evento/" + idRegistro,
                type: "POST",
                data: {
                    _token: $('input[name="_token"]').val(),
                    _method: "DELETE"
                },
                success: function (response) {
                    alert(response.message);
                    verificarRegistrosExistentes();
                },
                error: function (xhr) {
                    console.error("Erro ao excluir registro conflitante:", xhr.responseText);
                    alert("Erro ao excluir o registro.");
                }
            });
        }
    });

    // Alternar campos com base no tipo do evento selecionado
    $("#add-evento-motivo").on("change", function () {
        const motivo = $(this).val();

        if (motivo === "correcao") {
            $("#add-evento-secao-horarios").removeClass("d-none");
            $("#add-evento-hora-entrada").removeAttr("required");
            $("#add-evento-hora-saida").removeAttr("required");

            $("#add-evento-secao-abono").addClass("d-none");
            $("#add-evento-is-abonado").prop("checked", false);
        } else if (motivo === "atestado" || motivo === "dispensa") {
            $("#add-evento-secao-abono").removeClass("d-none");

            $("#add-evento-secao-horarios").addClass("d-none");
            $("#add-evento-hora-entrada").removeAttr("required").val("");
            $("#add-evento-hora-saida").removeAttr("required").val("");
        } else {
            $("#add-evento-secao-horarios").addClass("d-none");
            $("#add-evento-hora-entrada").removeAttr("required").val("");
            $("#add-evento-hora-saida").removeAttr("required").val("");

            $("#add-evento-secao-abono").addClass("d-none");
            $("#add-evento-is-abonado").prop("checked", false);
        }
    });

    // 2. Enviar Formulário de Novo Evento
    $("#formAdicionarEvento").on("submit", function (e) {
        e.preventDefault();

        const motivo = $("#add-evento-motivo").val();
        if (motivo === "correcao") {
            const entrada = $("#add-evento-hora-entrada").val();
            const saida = $("#add-evento-hora-saida").val();
            if (!entrada && !saida) {
                alert("Por favor, preencha pelo menos o horário de entrada ou de saída para realizar a correção.");
                return;
            }
        }

        const payload = $(this).serialize();

        $.ajax({
            url: "/salvar-evento",
            type: "POST",
            data: payload,
            success: function (response) {
                alert(response.message);
                $("#modalAdicionarEvento").modal("hide");
            },
            error: function (xhr) {
                console.error(xhr.responseText);
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    let msg = "Erros de validação:\n";
                    for (const field in errors) {
                        msg += `- ${errors[field].join(", ")}\n`;
                    }
                    alert(msg);
                } else {
                    alert("Erro ao salvar ocorrência.");
                }
            }
        });
    });

    // Função para carregar ocorrências na tabela do modal de listagem
    function carregarOcorrencias(idEstagiario, resetFilter = true) {
        currentEstagiarioId = idEstagiario;
        if (resetFilter) {
            $("#filtro-tipo-ocorrencia").val("todos");
        }
        $("#selecionar-todas-ocorrencias").prop("checked", false);
        $("#btn-excluir-lote-ocorrencias").addClass("d-none");
        $("#count-excluir-lote").text("0");

        const incluirExcluidos = $("#chk-mostrar-excluidos").is(":checked") ? "true" : "false";

        $.ajax({
            url: "/estagiarios/" + idEstagiario + "/listar-eventos?incluir_excluidos=" + incluirExcluidos,
            type: "GET",
            success: function (response) {
                occurrencesList = response.eventos || [];
                renderizarOcorrenciasFiltradas();
            },
            error: function (xhr) {
                console.error(xhr.responseText);
                alert("Erro ao buscar ocorrências.");
            }
        });
    }

    function renderizarOcorrenciasFiltradas() {
        const filtro = $("#filtro-tipo-ocorrencia").val();
        const tbody = $("#corpo-tabela-ocorrencias");
        tbody.empty();

        let filtrados = occurrencesList;
        if (filtro !== "todos") {
            if (filtro === "correcao") {
                // Correções são "entrada" ou "saida"
                filtrados = occurrencesList.filter(e => e.tipo_bruto === "entrada" || e.tipo_bruto === "saida");
            } else {
                filtrados = occurrencesList.filter(e => e.tipo_bruto === filtro);
            }
        }

        if (filtrados.length === 0) {
            $("#tabela-ocorrencias-estagiario").addClass("d-none");
            $("#aviso-sem-ocorrencias").removeClass("d-none");
        } else {
            $("#tabela-ocorrencias-estagiario").removeClass("d-none");
            $("#aviso-sem-ocorrencias").addClass("d-none");

            filtrados.forEach(function (evento) {
                let badgeClass = "bg-secondary";
                if (evento.tipo_bruto === "entrada") badgeClass = "bg-primary";
                else if (evento.tipo_bruto === "saida") badgeClass = "bg-info text-dark";
                else if (evento.tipo_bruto === "falta") badgeClass = "bg-danger";
                else if (evento.tipo_bruto === "recesso") badgeClass = "bg-success";
                else if (evento.tipo_bruto === "atestado") badgeClass = "bg-warning text-dark";

                const isExcluido = evento.excluido;
                const rowStyle = isExcluido ? "opacity: 0.6; text-decoration: line-through;" : "";
                const checkboxHtml = isExcluido ? "" : `<input type="checkbox" class="form-check-input chk-ocorrencia" data-id="${evento.id}">`;
                const actionHtml = isExcluido
                    ? `<span class="badge bg-dark">Excluído</span>`
                    : `<button class="btn btn-sm btn-outline-danger btn-excluir-evento" data-id="${evento.id}" data-estagiario-id="${currentEstagiarioId}" title="Excluir Ocorrência">Excluir</button>`;

                const tr = `
                    <tr style="${rowStyle}">
                        <td class="text-center">${checkboxHtml}</td>
                        <td>${evento.data}</td>
                        <td><span class="badge ${badgeClass}">${evento.motivo}</span></td>
                        <td>${evento.observacao || ""}</td>
                        <td class="text-center">${actionHtml}</td>
                    </tr>
                `;
                tbody.append(tr);
            });
        }

        atualizarBotaoLote();
    }

    // Monitora alteração no filtro de tipo de ocorrência
    $("#filtro-tipo-ocorrencia").on("change", function () {
        $("#selecionar-todas-ocorrencias").prop("checked", false);
        renderizarOcorrenciasFiltradas();
    });

    // Monitora o toggle de exibir excluídos
    $("#chk-mostrar-excluidos").on("change", function () {
        if (currentEstagiarioId) {
            carregarOcorrencias(currentEstagiarioId, false); // false para não resetar o filtro atual
        }
    });

    // Toggle selecionar todas
    $(document).on("change", "#selecionar-todas-ocorrencias", function () {
        const isChecked = $(this).prop("checked");
        $(".chk-ocorrencia").prop("checked", isChecked);
        atualizarBotaoLote();
    });

    // Monitora cliques nos checkboxes individuais
    $(document).on("change", ".chk-ocorrencia", function () {
        if (!$(this).prop("checked")) {
            $("#selecionar-todas-ocorrencias").prop("checked", false);
        } else {
            const todosMarcados = $(".chk-ocorrencia:not(:checked)").length === 0;
            $("#selecionar-todas-ocorrencias").prop("checked", todosMarcados);
        }
        atualizarBotaoLote();
    });

    // Função para atualizar o botão de exclusão em lote
    function atualizarBotaoLote() {
        const selecionados = $(".chk-ocorrencia:checked").length;
        if (selecionados > 0) {
            $("#btn-excluir-lote-ocorrencias").removeClass("d-none");
            $("#count-excluir-lote").text(selecionados);
        } else {
            $("#btn-excluir-lote-ocorrencias").addClass("d-none");
            $("#count-excluir-lote").text("0");
            $("#selecionar-todas-ocorrencias").prop("checked", false);
        }
    }

    // Ação de exclusão em lote
    $(document).on("click", "#btn-excluir-lote-ocorrencias", function () {
        const ids = [];
        $(".chk-ocorrencia:checked").each(function () {
            ids.push($(this).data("id"));
        });

        if (ids.length === 0) return;

        if (confirm(`Tem certeza que deseja excluir as ${ids.length} ocorrências selecionadas?`)) {
            $.ajax({
                url: "/excluir-eventos-lote",
                type: "POST",
                data: {
                    _token: $('input[name="_token"]').val(),
                    ids: ids
                },
                success: function (response) {
                    alert(response.message);
                    carregarOcorrencias(currentEstagiarioId);
                },
                error: function (xhr) {
                    console.error("Erro ao excluir lote:", xhr.responseText);
                    alert("Erro ao excluir as ocorrências selecionadas.");
                }
            });
        }
    });

    // 3. Abrir Modal de Listar Ocorrências
    $(document).on("click", ".btn-listar-eventos", function () {
        const idEstagiario = $(this).data("identificador");
        const nomeEstagiario = $(this).data("nome");

        $("#listar-evento-estagiario-nome").text(nomeEstagiario);
        $("#chk-mostrar-excluidos").prop("checked", false);
        carregarOcorrencias(idEstagiario);
        $("#modalListarEventos").modal("show");
    });

    // 4. Excluir uma Ocorrência Específica
    $(document).on("click", ".btn-excluir-evento", function () {
        const idEvento = $(this).data("id");
        const idEstagiario = $(this).data("estagiario-id");

        if (confirm("Tem certeza que deseja excluir esta ocorrência?")) {
            $.ajax({
                url: "/excluir-evento/" + idEvento,
                type: "POST",
                data: {
                    _token: $('input[name="_token"]').val(),
                    _method: "DELETE"
                },
                success: function (response) {
                    alert(response.message);
                    carregarOcorrencias(idEstagiario);
                },
                error: function (xhr) {
                    console.error(xhr.responseText);
                    alert("Erro ao excluir ocorrência.");
                }
            });
        }
    });

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
    });
    $(
        "#data-mes, #data-ano, #filtro-motivo, #filtro-estagiario, #data-completa, #data-semana-inicio, #data-semana-fim"
    ).on("change", function () {
        console.log("Filtro alterado: " + $(this).val());
        if (typeof tabelaPontos !== "undefined") {
            tabelaPontos.ajax.reload();
        }
    });
    tabelaPontos = $("#myTable").DataTable({
        processing: true,
        serverSide: false,
        searching: true,
        dom: "<'row mb-3'<'col-sm-12 col-md-4 d-flex align-items-center'l><'col-sm-12 col-md-4 d-flex justify-content-center align-items-center'B><'col-sm-12 col-md-4 d-flex align-items-center justify-content-end'f>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row mt-3'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        buttons: [
            {
                extend: 'excelHtml5',
                text: '<i class="fa-solid fa-file-excel me-1"></i> Excel',
                className: 'btn btn-success btn-sm me-2',
                title: 'Relatório de Estagiários',
                exportOptions: { columns: ':visible' }
            },
            {
                extend: 'pdfHtml5',
                text: '<i class="fa-solid fa-file-pdf me-1"></i> PDF',
                className: 'btn btn-danger btn-sm ',
                title: 'Relatório de Estagiários',
                orientation: 'landscape',
                pageSize: 'A4',
                exportOptions: { columns: ':visible' }
            }
        ],
        ajax: {
            url: "/lista-estagiarios",
            data: function (d) {
                d._token = $('input[name="_token"]').val();
                d.ano = $("#data-ano").val() || "";
                d.mes = $("#data-mes").val() || "";
                d.motivo = $("#filtro-motivo").val() || "";
                d.estagiario_id = $("#filtro-estagiario").val() || "";
                d.data = $("#data-completa").val() || "";
                d.inicioSemana = $("#data-semana-inicio").val() || "";
                d.fimSemana = $("#data-semana-fim").val() || "";
            },
        },
        "order": [[5, "asc"]],
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
        ],
        language: {
            url: "",
            lengthMenu: "Exibir _MENU_ por página",
            search: "",
            searchPlaceholder: "Pesquisar Estagiário",
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
            inicio: $("#edit-data-inicio").val(),
            fim: $("#edit-data-fim").val(),
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
                if (response.success) {
                    console.log(response);
                    // alert("Success! Now reloading...");
                    // window.location.reload();
                } else {
                    alert("Erro reportado pelo servidor: " + response.message);
                }
            },
            error: function (xhr) {
                console.error(xhr.responseText);
                alert("Erro ao salvar. Verifique o console.");
            },
        });
    }

    $(document).ready(function () {
        $("#openQrCadastro").on("click", function () {
            let urlCadastro =
                "https://ponto-estagio.pm.pr.gov.br/views/principal/cadastro";

            $("#qrcodeCadastro").html("");

            new QRCode(document.getElementById("qrcodeCadastro"), {
                text: urlCadastro,
                width: 220,
                height: 220,
            });

            const modal = new bootstrap.Modal(
                document.getElementById("qrModalCadastro"),
            );
            modal.show();
        });
    });

    const qrModal = document.getElementById("qrModalCadastro");
    if (qrModal) {
        qrModal.addEventListener("show.bs.modal", function (event) {
            const button = event.relatedTarget;
            if (!button) return;
            
            const table = $("#tabela-estagiarios-cadastrados").DataTable();
            const tr = $(button).closest("tr");
            const data = table.row(tr).data();

            if (data && data.nr_matricula) {
                // Extrai apenas os números do CPF para um QR Code limpo
                const cpfLimpo = data.nr_matricula.replace(/\D/g, "");
                
                $("#qrcodeCadastro").html("");
                new QRCode(document.getElementById("qrcodeCadastro"), {
                    text: cpfLimpo,
                    width: 220,
                    height: 220,
                });
            } else {
                alert("Não foi possível encontrar a matrícula (CPF) deste estagiário.");
            }
        });
    }

    const modalEditar = document.getElementById("modalEditarEstagiario");
    if (modalEditar) {
        modalEditar.addEventListener("show.bs.modal", function (event) {
            const button = event.relatedTarget;
            if (!button) return;

            const idParaEditar = $(button).data("identificador");
            const table = $("#tabela-estagiarios-cadastrados").DataTable();
            const tr = $(button).closest("tr");
            const data = table.row(tr).data();

            console.log("DADOS DISPONÍVEIS NA LINHA:", data);

            if (idParaEditar && data) {
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
        });
    }

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
    $(document).ready(function () {
        // Inicializa o leitor apenas uma vez
        const html5QrCode = new Html5Qrcode("reader");
        $("#btn-abrir-camera").on("click", function () {
            html5QrCode
                .start(
                    { facingMode: "environment" },
                    { fps: 10, qrbox: 250 },
                    (decodedText) => {
                        html5QrCode.stop().then(() => {
                            // O QR Code possui apenas números. Aplicamos a formatação que o DB espera.
                            let cpfLidoLimpo = decodedText.replace(/\D/g, "");
                            let cpfFormatado = cpfLidoLimpo.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, "$1.$2.$3-$4");

                            $.ajax({
                                url: "/processar-qrcode",
                                type: "POST",
                                data: {
                                    _token: $('input[name="_token"]').val(),
                                    cpf: cpfFormatado,
                                },
                                success: function (xhr) {
                                    console.log("Sucesso: ", xhr);
                                    // Preenche automaticamente o campo de CPF no formulário principal formatado
                                    $("#cpf").val(cpfFormatado);
                                    
                                    // Toca um feedback visual
                                    $("#cpf").addClass("is-valid");
                                    setTimeout(() => $("#cpf").removeClass("is-valid"), 3000);
                                    
                                    alert("Sucesso!\n" + xhr.data + "\n\nO CPF foi preenchido. Escolha 'Entrada' ou 'Saída' e clique em REGISTRAR.");
                                },
                                error: function (xhr) {
                                    console.error("Erro no processamento", xhr);
                                    alert("Erro: O QR Code lido (" + cpfFormatado + ") não corresponde a um estagiário ativo.");
                                },
                            });
                        });
                    },
                )
                .catch((err) => {
                    // <--- O .catch correto fica aqui
                    console.error("Erro na câmera: ", err);
                    alert("A câmera não pôde ser iniciada. Verifique as permissões de segurança do navegador.");
                });
        });
    });
});
