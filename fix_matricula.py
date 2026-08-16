import re

with open('tests/Feature/EstagiariosTest.php', 'r') as f:
    content = f.read()

content = content.replace("'matricula'", "'cpf'")

with open('tests/Feature/EstagiariosTest.php', 'w') as f:
    f.write(content)

