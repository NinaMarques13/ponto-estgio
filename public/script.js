$(document).ready(function() {

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
    $('#cpf').on('keyup', function() {
        // Aplica a máscara a cada tecla digitada
        $(this).val(aplicarMascaraCPF($(this).val()));
    });
    
    // -------------------------------------------
    
    // 1. Alternar seleção Entrada/Saída
    $('.registro-link').on('click', function(e) {
        e.preventDefault();
        
        $('.registro-link').removeClass('active');
        $(this).addClass('active');
        
        let acao = $(this).text().includes('Entrada') ? 'Entrada' : 'Saída';
        console.log("Ação selecionada:", acao);
    });

    // 2. Lógica do botão REGISTRAR
    $('#registrarBtn').on('click', function() {
        
        // Pega o valor do CPF (com máscara) e limpa (apenas números)
        let cpfBruto = $('#cpf').val().trim();
        let cpf = limparCPF(cpfBruto);
        
        // Pega a ação selecionada (Entrada ou Saída)
        let acaoSelecionada = $('.registro-link.active').text().includes('Entrada') ? 'Entrada' : 'Saída';

        // --- VALIDAÇÃO BÁSICA ---
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
    $('.camera-icon').on('click', function() {
        alert("Ação de Leitura de CPF/QR Code ativada (Simulação)");
    });

});