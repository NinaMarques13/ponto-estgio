import os

replacements = {
    "namespace App\\Models;": "namespace App\\Domains\\Admins\\Models;",
    "namespace App\\Http\\Controllers\\Admin;": "namespace App\\Domains\\Admins\\Controllers;",
    "App\\Models\\Admin": "App\\Domains\\Admins\\Models\\Admin",
    "App\\Http\\Controllers\\Admin\\LoginController": "App\\Domains\\Admins\\Controllers\\LoginController"
}

files_to_check = [
    "app/Domains/Admins/Models/Admin.php",
    "app/Domains/Admins/Controllers/LoginController.php",
    "routes/web.php",
    "config/auth.php",
    "database/seeders/AdminSeeder.php",
    "app/Http/Middleware/CheckAdminLevel.php"
]

for filepath in files_to_check:
    if os.path.exists(filepath):
        with open(filepath, 'r') as file:
            content = file.read()
        
        new_content = content
        for old, new in replacements.items():
            new_content = new_content.replace(old, new)
            
        if new_content != content:
            with open(filepath, 'w') as file:
                file.write(new_content)
            print(f'Updated {filepath}')

