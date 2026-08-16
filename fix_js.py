import os

filepath = 'public/js/script.js'
with open(filepath, 'r') as f:
    content = f.read()

target = 'const html5QrCode = new Html5Qrcode("reader");'
replacement = '''if (typeof Html5Qrcode !== 'undefined' && document.getElementById("reader")) {
            const html5QrCode = new Html5Qrcode("reader");'''

content = content.replace(target, replacement)

target2 = '''        $("#cameraModal").on("hidden.bs.modal", function () {
            html5QrCode.stop().catch((err) => console.log("Erro ao parar", err));
        });
    });'''
replacement2 = '''        $("#cameraModal").on("hidden.bs.modal", function () {
            html5QrCode.stop().catch((err) => console.log("Erro ao parar", err));
        });
        }
    });'''

content = content.replace(target2, replacement2)

with open(filepath, 'w') as f:
    f.write(content)

