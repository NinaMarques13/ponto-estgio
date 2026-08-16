import re

with open('tests/Feature/EstagiariosTest.php', 'r') as f:
    content = f.read()

# Fix assertDatabaseHas
def replace_db_keys(match):
    block = match.group(0)
    block = block.replace("'nome'", "'nm_estagiarios'")
    block = block.replace("'setor'", "'nm_setor'")
    block = block.replace("'telefone'", "'nr_telefone'")
    block = block.replace("'email'", "'nm_email'")
    return block

content = re.sub(r'assertDatabaseHas\(\'estagiarios\',\s*\[.*?\]\);', replace_db_keys, content, flags=re.DOTALL)

# Fix factory()->create
content = re.sub(r'Estagiario::factory\(\)->create\(\[.*?\]\);', replace_db_keys, content, flags=re.DOTALL)

with open('tests/Feature/EstagiariosTest.php', 'w') as f:
    f.write(content)

