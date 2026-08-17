import os

filepath = 'public/js/script.js'
with open(filepath, 'r') as f:
    content = f.read()

target = '''                .catch((err) => {
                    // <--- O .catch correto fica aqui
                    console.error("Erro na câmera: ", err);
                    alert("A câmera não pôde ser iniciada. Verifique as permissões de segurança do navegador.");
                });
        });
    });
});'''

replacement = '''                .catch((err) => {
                    console.error("Erro na câmera: ", err);
                    alert("A câmera não pôde ser iniciada. Verifique as permissões de segurança do navegador.");
                });
        });

        $("#cameraModal").on("hidden.bs.modal", function () {
            html5QrCode.stop().catch((err) => console.log("Erro ao parar", err));
        });
        }
    });
});'''

content = content.replace(target, replacement)

with open(filepath, 'w') as f:
    f.write(content)

