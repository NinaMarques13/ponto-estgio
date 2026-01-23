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
    
});