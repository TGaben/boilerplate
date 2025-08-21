# Gyakori Problémák és Megoldások

Ez a dokumentum a Laravel Boilerplate használata során felmerülő leggyakoribb problémákat és azok megoldásait tartalmazza.

## 🚀 Quick Start & Setup Problémák

### Setup Script Hibák

**Problem**: `./scripts/quick-start.sh: Permission denied`
```bash
# Megoldás: Script végrehajtási jogok beállítása
chmod +x scripts/quick-start.sh
chmod +x scripts/quality-check.sh
```

**Problem**: Docker daemon nem fut
```bash
# Ellenőrzés
docker --version
docker info

# Ubuntu/Debian indítás
sudo systemctl start docker
sudo systemctl enable docker

# Windows/Mac - Docker Desktop indítása
```

**Problem**: Port már használatban (80, 3306, stb.)
```bash
# Portok ellenőrzése
sudo netstat -tulpn | grep :80
sudo netstat -tulpn | grep :3306

# Sail portok módosítása
# .env fájlban:
APP_PORT=8080
FORWARD_DB_PORT=3307
FORWARD_REDIS_PORT=6380
```

### Environment Beállítási Problémák

**Problem**: `.env` fájl nem található
```bash
# Megoldás: Environment sablon másolása
php artisan boilerplate:env copy development

# Vagy manuálisan
cp .env.example .env
php artisan key:generate
```

**Problem**: APP_KEY hiányzik vagy hibás
```bash
# Új kulcs generálása
php artisan key:generate

# Ellenőrzés
php artisan boilerplate:env validate
```

**Problem**: Database connection hibák
```bash
# MySQL indítása Sail-ben
./vendor/bin/sail up -d mysql

# Connection tesztelése
php artisan boilerplate:env check

# Manual connection test
mysql -h 127.0.0.1 -P 3306 -u sail -p
```

## 🗄️ Adatbázis Problémák

### Migration Hibák

**Problem**: `Migration table not found`
```bash
# Migration tábla létrehozása
php artisan migrate:install

# Összes migration futtatása
php artisan migrate

# Sail környezetben
./vendor/bin/sail artisan migrate
```

**Problem**: `Table already exists` hiba
```bash
# Migration állapot ellenőrzése
php artisan migrate:status

# Specific migration rollback
php artisan migrate:rollback --step=1

# Teljes rollback (VESZÉLYES!)
php artisan migrate:reset
php artisan migrate
```

**Problem**: Foreign key constraint hibák
```bash
# Foreign key ellenőrzések kikapcsolása (MySQL)
SET FOREIGN_KEY_CHECKS=0;

# Vagy migration-ben:
Schema::disableForeignKeyConstraints();
// ... your migration code ...
Schema::enableForeignKeyConstraints();
```

### Seeder Problémák

**Problem**: `Class 'RoleSeeder' not found`
```bash
# Composer autoload frissítése
composer dump-autoload

# Seeder futtatása
php artisan db:seed --class=RolesAndPermissionsSeeder
```

**Problem**: Duplicate entry hibák seeder-nél
```bash
# updateOrCreate használata a seeder-ben
Role::updateOrCreate(
    ['name' => 'admin'],
    ['guard_name' => 'web']
);

# Vagy firstOrCreate
Permission::firstOrCreate(['name' => 'users.read']);
```

## 🔒 Authentication & Permission Hibák

### Spatie Permission Problémák

**Problem**: `Permission does not exist`
```bash
# Jogosultságok szinkronizálása
php artisan boilerplate:setup-permissions

# Cache törlése
php artisan permission:cache-reset

# Adott permission létrehozása
php artisan tinker
> Spatie\Permission\Models\Permission::create(['name' => 'users.read']);
```

**Problem**: `User does not have the right permission`
```bash
# User jogosultságok ellenőrzése
php artisan tinker
> $user = App\Models\User::find(1);
> $user->permissions;
> $user->roles;

# Jogosultság hozzáadása
> $user->givePermissionTo('users.read');

# Szerepkör hozzáadása
> $user->assignRole('admin');
```

**Problem**: Admin panel hozzáférés megtagadva
```bash
# Admin user létrehozása
php artisan make:boilerplate-role admin --permissions="*"

# User admin szerepkör hozzárendelése
$user = User::where('email', 'admin@admin.com')->first();
$user->assignRole('admin');
```

### Session Problémák

**Problem**: `Session store not set on request`
```bash
# Session driver ellenőrzése (.env)
SESSION_DRIVER=file

# Session táblák esetén
php artisan session:table
php artisan migrate

# Cache törlése
php artisan cache:clear
php artisan config:clear
```

**Problem**: CSRF token mismatch
```bash
# Meta tag ellenőrzése (layout.blade.php)
<meta name="csrf-token" content="{{ csrf_token() }}">

# Session lifetime ellenőrzése (.env)
SESSION_LIFETIME=120

# Axios CSRF setup
window.axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
```

## 🎨 Frontend & Asset Problémák

### Node.js & NPM Hibák

**Problem**: `npm command not found`
```bash
# Node.js telepítése
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt-get install -y nodejs

# Version ellenőrzése
node --version
npm --version
```

**Problem**: `npm ERR! peer deps` figyelmeztetések
```bash
# Legacy peer deps használata
npm install --legacy-peer-deps

# Vagy yarn használata
npm install -g yarn
yarn install
```

**Problem**: `Module not found` Vite hibák
```bash
# Node modules újratelepítése
rm -rf node_modules package-lock.json
npm install

# Vite cache törlése
rm -rf .vite

# Dev server újraindítása
npm run dev
```

### Tailwind CSS Problémák

**Problem**: Tailwind class-ek nem működnek
```bash
# Tailwind config ellenőrzése
cat tailwind.config.js

# Content paths ellenőrzése
content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
    "./vendor/filament/**/*.blade.php",
],

# CSS build
npm run build
```

**Problem**: Dark mode nem váltogat
```bash
# Tailwind config dark mode ellenőrzése
module.exports = {
  darkMode: 'class', // vagy 'media'
  // ...
}

# JavaScript dark mode toggle ellenőrzése
```

## 🔧 Filament Admin Panel Problémák

### Filament Installation Hibák

**Problem**: Filament admin panel 404
```bash
# Filament telepítés ellenőrzése
php artisan filament:install --panels

# Panel konfiguráció ellenőrzése
# app/Providers/Filament/AdminPanelProvider.php

# Route cache törlése
php artisan route:clear
```

**Problem**: Filament resource nem jelenik meg
```bash
# Resource regisztrálás ellenőrzése
php artisan filament:make-resource User --generate

# Navigation permissions ellenőrzése
public static function shouldRegisterNavigation(): bool
{
    return auth()->user()->can('users.read');
}
```

**Problem**: Form validation hibák
```bash
# Request validation rules ellenőrzése
# Make sure form field names match model fillable

# Custom validation rules
protected function rules(): array
{
    return [
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email,' . $this->user?->id,
    ];
}
```

### Filament Theme & Styling

**Problem**: Filament UI elromlott
```bash
# Filament assets publish
php artisan filament:assets

# Theme rebuild
npm run build

# Cache clear
php artisan filament:cache-components
```

## ⚡ Performance Problémák

### Lassú Oldalletöltés

**Problem**: Adatbázis query-k lassúak
```bash
# Query log engedélyezése
DB::enableQueryLog();
// ... your code ...
dd(DB::getQueryLog());

# N+1 probléma ellenőrzése
# Use eager loading: ->with(['relation'])

# Database indexes ellenőrzése
php artisan migrate:status
```

**Problem**: Composer autoload lassú
```bash
# Optimized autoloader
composer dump-autoload --optimize

# Production optimization
composer install --no-dev --optimize-autoloader
```

**Problem**: Cache nem működik
```bash
# Redis connection ellenőrzése
redis-cli ping

# Cache driver ellenőrzése (.env)
CACHE_DRIVER=redis

# Cache clear and config cache
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Memory Limit Hibák

**Problem**: `Fatal error: Maximum execution time exceeded`
```bash
# PHP limits növelése (php.ini)
max_execution_time = 300
memory_limit = 512M

# Vagy .env-ben
php_value[max_execution_time] = 300
php_value[memory_limit] = 512M
```

**Problem**: `Allowed memory size exhausted`
```bash
# Memory limit növelése
ini_set('memory_limit', '512M');

# Large datasets chunking
User::chunk(1000, function ($users) {
    foreach ($users as $user) {
        // Process user
    }
});
```

## 🐛 Code Quality & Testing Hibák

### PHPStan Hibák

**Problem**: Mixed type errors
```bash
# Explicit type casting
$value = (string) $this->argument('name');
$count = (int) $this->option('count');

# Null-safe operations
$user?->name ?? 'Default';
```

**Problem**: Undefined method calls
```bash
# IDE Helper generálása
composer require --dev barryvdh/laravel-ide-helper
php artisan ide-helper:generate
php artisan ide-helper:models
```

### Laravel Pint Hibák

**Problem**: Coding style violations
```bash
# Automatic fixing
./vendor/bin/pint

# Specific files
./vendor/bin/pint app/Models/User.php

# Dry run (preview only)
./vendor/bin/pint --test
```

### Test Hibák

**Problem**: `Base table or view not found` teszteknél
```bash
# Test database ellenőrzése (.env.testing)
DB_CONNECTION=sqlite
DB_DATABASE=:memory:

# RefreshDatabase trait használata
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase
{
    use RefreshDatabase;
}
```

**Problem**: Factory hibák
```bash
# Factory létrehozása
php artisan make:factory UserFactory --model=User

# Factory használata tesztben
$user = User::factory()->create([
    'email' => 'test@example.com'
]);
```

## 📁 File Permissions & Storage

### Storage Problémák

**Problem**: Permission denied storage/
```bash
# Proper permissions
sudo chown -R www-data:www-data storage/
sudo chmod -R 775 storage/

# Vagy development-ben
sudo chmod -R 777 storage/
```

**Problem**: Symlink hibák
```bash
# Storage link létrehozása
php artisan storage:link

# Manual symlink (Linux/Mac)
ln -sf /path/to/laravel/storage/app/public /path/to/laravel/public/storage
```

**Problem**: File upload hibák
```bash
# PHP upload limits (php.ini)
upload_max_filesize = 20M
post_max_size = 20M
max_file_uploads = 20

# Laravel validation
'file' => 'required|file|max:20480', // 20MB in KB
```

## 🌐 Server & Deployment Hibák

### Web Server Konfiguráció

**Problem**: Apache .htaccess not working
```bash
# Enable mod_rewrite
sudo a2enmod rewrite
sudo systemctl restart apache2

# Virtual host konfiguráció
DocumentRoot /var/www/laravel/public

<Directory /var/www/laravel/public>
    AllowOverride All
</Directory>
```

**Problem**: Nginx 404 hibák
```bash
# Nginx konfiguráció
location / {
    try_files $uri $uri/ /index.php?$query_string;
}

location ~ \.php$ {
    fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
    fastcgi_index index.php;
    fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
    include fastcgi_params;
}
```

### Environment Problémák Production-ben

**Problem**: APP_DEBUG=true production-ben
```bash
# Production .env
APP_ENV=production
APP_DEBUG=false
LOG_LEVEL=error

# Config cache
php artisan config:cache
```

**Problem**: Queue jobs nem futnak
```bash
# Queue worker indítása
php artisan queue:work

# Supervisor konfiguráció (production)
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/laravel/artisan queue:work --sleep=3 --tries=3
directory=/var/www/laravel
user=www-data
numprocs=8
redirect_stderr=true
stdout_logfile=/var/www/laravel/storage/logs/worker.log
```

## 🔄 Cache & Config Hibák

### Config Cache Problémák

**Problem**: .env változások nem érvényesülnek
```bash
# Config cache törlése
php artisan config:clear

# Új cache létrehozása (production-ben)
php artisan config:cache
```

**Problem**: Route cache hibák
```bash
# Route cache törlése
php artisan route:clear

# Új route cache (production-ben)
php artisan route:cache
```

**Problem**: View cache problémák
```bash
# View cache törlése
php artisan view:clear

# Új view cache
php artisan view:cache
```

## 🆘 Emergency Recovery

### Teljes Rendszer Reset

Ha minden más hibázik:

```bash
# 1. Cache és config törlése
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# 2. Composer dependencies
composer install --no-dev --optimize-autoloader

# 3. NPM dependencies
rm -rf node_modules package-lock.json
npm install
npm run build

# 4. Database reset (VESZÉLYES!)
php artisan migrate:fresh --seed

# 5. Storage permissions
chmod -R 775 storage/
php artisan storage:link

# 6. Queue restart
php artisan queue:restart
```

### Backup Helyreállítás

```bash
# Database backup visszaállítása
mysql -u username -p database_name < backup.sql

# Files backup visszaállítása
rsync -av backup/storage/ storage/
rsync -av backup/public/ public/

# Permissions
chmod -R 775 storage/
```

## 📞 További Segítség

Ha a fenti megoldások nem segítenek:

1. **Laravel Log-ok ellenőrzése**: `storage/logs/laravel.log`
2. **Web szerver log-ok**: `/var/log/apache2/` vagy `/var/log/nginx/`
3. **PHP error log**: `tail -f /var/log/php8.2-fpm.log`
4. **Database log-ok**: MySQL slow query log
5. **Laravel Telescope**: Development debugging tool

### Debug Mode Engedélyezés

```bash
# .env
APP_DEBUG=true
LOG_LEVEL=debug

# Detailed error reporting
php artisan tinker
> dd(config('app.debug'));
```

### Hasznos Debug Parancsok

```bash
# System info
php artisan about

# Environment check
php artisan boilerplate:env check

# Performance check
php artisan optimize
php artisan optimize:clear
```


