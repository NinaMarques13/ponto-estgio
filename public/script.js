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
            $('#cpf').focus();
            return;
        }

        if (cpf.length !== 11) {
             alert("O CPF deve conter exatamente 11 dígitos.");
             $('#cpf').focus();
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
    $(document).ready(function() {
        $.ajax({
            url: '/relatorio-estagiarios',
            type: 'GET',
            success: function(response) {
                $('#contador-presentes').text(response.total);
            }
        });
    });
    $(document).ready(function() {
        $.ajax({
            url: '/relatorio-registros',
            type: 'GET',
            success: function(response) {
                $('#registros-dia').text(response.total);
            }
        });
    });
    $(document).ready(function() {
        $.ajax({
            url: '/relatorio-recesso',
            type: 'GET',
            success: function(response) {
                $('#recesso-dia').text(response.total);
            }
        });
    });
    $(document).ready(function() {
        $.ajax({
            url: '/relatorio-atestados',
            type: 'GET',
            success: function(response) {
                $('#atestados-dia').text(response.total);
            }
        });
    });
    $(document).ready(function() {
        $.ajax({
            url: '/relatorio-folgas',
            type: 'GET',
            success: function(response) {
                $('#folgas-dia').text(response.total);
            }
        });
    });
    $(document).ready(function() {
        $.ajax({
            url: '/relatorio-dispensas',
            type: 'GET',
            success: function(response) {
                $('#dispensas-dia').text(response.total);
            }
        });
    });
    $(document).ready(function() {
        $.ajax({
            url: '/relatorio-faltas',
            type: 'GET',
            success: function(response) {
                $('#faltas-dia').text(response.total);
            }
        });
    });
    $(document).ready(function() {
        carregarListaEstagiarios();
    });
    function carregarListaEstagiarios() {
        $.ajax({
            url: '/lista-estagiarios',
            type: 'get',
            success: function(lista) {
                let html = '';
                lista.forEach(function(item) {
                    html += `
                    <tr>
                        <td>
                            <input type="text" class="form-control" value="${item.data}" readonly>
                        </td>
                        <td>
                            <input type="text" class="form-control" value="${item.entrada}">
                        </td>
                        <td>
                            <input type="text" class="form-control" value="${item.saida}">
                        </td>
                        <td>${item.matricula}</td>
                        <td>${item.nome}</td>
                    </tr>
                    `;
                });
                $('#tabela-estagiarios-corpo').html(html);
            }
        });
    }
>>>>>>> 53e5f22 (elementos com consulta ajax atualizados)
});