# Produkciós Telepítési Útmutató

Ez az útmutató a Laravel Vállalati Boilerplate produkciós környezetekbe való telepítését fedezi le.

## 📋 Tartalomjegyzék

### ⚡ Gyors Telepítés (Ajánlott)
- [Quick Start Script Produkciós Telepítés](#quick-start-script-produkciós-telepítés)
- [Automatizált CI/CD Deploy](#automatizált-cicd-deploy)

### 🔧 Manuális Telepítés
- [Előfeltételek](#előfeltételek)
- [Szerver Követelmények](#szerver-követelmények)
- [Környezet Beállítás](#környezet-beállítás)
- [Docker Telepítés](#docker-telepítés)
- [Hagyományos Szerver Telepítés](#hagyományos-szerver-telepítés)
- [Adatbázis Beállítás](#adatbázis-beállítás)
- [SSL Konfiguráció](#ssl-konfiguráció)
- [Teljesítmény Optimalizáció](#teljesítmény-optimalizáció)
- [Monitorozás és Naplózás](#monitorozás-és-naplózás)
- [Biztonsági Ellenőrzőlista](#biztonsági-ellenőrzőlista)
- [Biztonsági Mentési Stratégia](#biztonsági-mentési-stratégia)
- [CI/CD Pipeline](#cicd-pipeline)

## ⚡ Quick Start Script Produkciós Telepítés

### Egyparancs Produkciós Setup

A Quick Start Script támogatja a biztonságos produkciós telepítést automatizált biztonsági ellenőrzésekkel:

```bash
# Klónozás és produkciós setup
git clone https://github.com/TGaben/boilerplate.git myapp-production
cd myapp-production

# Produkciós környezet beállítása
./scripts/quick-start.sh --env=production \
  --domain=myapp.com \
  --skip-interactive \
  --skip-tests
```

### Mit Csinál Automatikusan (Production)?

✅ **Biztonsági Validáció**
- REPLACE_WITH_* értékek ellenőrzése
- Erős jelszavak validálása
- HTTPS kényszerítés beállítása
- Session és cache biztonság

✅ **Teljesítmény Optimalizáció**
- Production cache beállítások
- Asset optimalizáció
- Database connection pooling
- Redis konfiguráció

✅ **Production Dependencies**
- Optimalizált Composer install
- Production NPM build
- Asset minification és compression

✅ **Biztonsági Konfigurációk**
- Secure headers beállítása
- CSRF protection
- Session encryption
- Environment variable validation

### Production Deploy Workflow

```bash
# 1. Szerver előkészítése
sudo apt update && sudo apt install -y docker.io docker-compose git

# 2. SSL tanúsítvány (Let's Encrypt)
sudo apt install certbot
sudo certbot certonly --standalone -d myapp.com

# 3. Projekt telepítése
git clone https://github.com/myorg/myapp.git /var/www/myapp
cd /var/www/myapp

# 4. Production setup script
./scripts/quick-start.sh --env=production \
  --domain=myapp.com \
  --skip-interactive

# 5. Nginx/Apache beállítása (lásd alább)

# 6. Firewall és biztonság
sudo ufw allow 80,443/tcp
sudo ufw enable
```

### Docker Production Deploy

```bash
# Production Docker Compose override
cat > docker-compose.prod.yml << EOF
version: '3.8'
services:
  laravel.test:
    environment:
      - APP_ENV=production
    volumes:
      - ./storage:/var/www/html/storage
      - ./bootstrap/cache:/var/www/html/bootstrap/cache
    restart: unless-stopped
  mysql:
    restart: unless-stopped
    volumes:
      - mysql_data:/var/lib/mysql
  redis:
    restart: unless-stopped
volumes:
  mysql_data:
EOF

# Start production containers
docker-compose -f docker-compose.yml -f docker-compose.prod.yml up -d
```

### Zero-Downtime Deployment

```bash
#!/bin/bash
# deploy.sh script

set -e

echo "🚀 Starting zero-downtime deployment..."

# 1. Backup current version
sudo cp -r /var/www/myapp /var/www/myapp.backup.$(date +%s)

# 2. Pull latest changes
cd /var/www/myapp
git pull origin main

# 3. Quick setup with production optimizations
./scripts/quick-start.sh --env=production --skip-interactive --force

# 4. Run migrations safely
php artisan migrate --force

# 5. Reload application
php artisan config:cache
php artisan route:cache
php artisan view:cache
sudo systemctl reload nginx

echo "✅ Deployment complete!"
```

### Production Monitoring Integration

```bash
# Health check endpoint
./scripts/quick-start.sh --env=production --domain=myapp.com

# Automatikus health check
curl -f https://myapp.com/health || exit 1

# Performance benchmark
time ./scripts/quick-start.sh --check-only
```

### Troubleshooting Production Issues

**1. Gyors diagnózis:**
```bash
# Rendszer állapot
./scripts/quick-start.sh --env=production --check-only

# Environment validáció
php artisan boilerplate:env validate --target-env=production

# Log ellenőrzés
tail -f storage/logs/laravel.log
```

**2. Rollback process:**
```bash
# Ha valami elromlik, gyors rollback
sudo mv /var/www/myapp.backup.TIMESTAMP /var/www/myapp
sudo systemctl reload nginx
```

---

## Automatizált CI/CD Deploy

### GitHub Actions Production Deploy

```yaml
# .github/workflows/deploy-production.yml
name: Production Deploy

on:
  push:
    tags:
      - 'v*'

jobs:
  deploy:
    runs-on: ubuntu-latest
    
    steps:
    - uses: actions/checkout@v4
    
    - name: Deploy to production
      uses: appleboy/ssh-action@v0.1.7
      with:
        host: ${{ secrets.PROD_HOST }}
        username: ${{ secrets.PROD_USER }}
        key: ${{ secrets.PROD_SSH_KEY }}
        script: |
          cd /var/www/myapp
          git pull origin main
          ./scripts/quick-start.sh --env=production --skip-interactive --force
          sudo systemctl reload nginx
```

### GitLab CI Production Deploy

```yaml
# .gitlab-ci.yml
deploy_production:
  stage: deploy
  only:
    - tags
  script:
    - echo "Deploying to production..."
    - ssh $PROD_USER@$PROD_HOST "cd /var/www/myapp && git pull && ./scripts/quick-start.sh --env=production --skip-interactive"
```

---

## 🚀 Előfeltételek

### Szükséges Szoftverek
- **PHP 8.3+** szükséges kiterjesztésekkel
- **Composer** (legfrissebb verzió)
- **Node.js 18+** és **NPM**
- **MySQL 8.0+** vagy **PostgreSQL 13+**
- **Redis** (gyorsítótárazás és session-ökhöz)
- **Webszerver** (Nginx vagy Apache)

### Szükséges PHP Kiterjesztések
```bash
# Ubuntu/Debian
sudo apt install php8.3-cli php8.3-fpm php8.3-mysql php8.3-redis \
    php8.3-xml php8.3-mbstring php8.3-curl php8.3-zip \
    php8.3-gd php8.3-bcmath php8.3-intl
```

## 🖥️ Szerver Követelmények

### Minimális Követelmények
- **CPU:** 2 mag
- **RAM:** 2GB (4GB ajánlott)
- **Tárhely:** 20GB SSD
- **Hálózat:** 100 Mbps

### Produkciós Ajánlás
- **CPU:** 4+ mag
- **RAM:** 8GB+
- **Tárhely:** 50GB+ NVMe SSD
- **Hálózat:** 1 Gbps
- **Load Balancer** (több példány esetén)

## 🔧 Környezet Beállítás

### 1. Produkciós Felhasználó Létrehozása

```bash
# Telepítési felhasználó létrehozása
sudo adduser deploy
sudo usermod -aG www-data deploy
sudo usermod -aG sudo deploy

# Váltás a deploy felhasználóra
su - deploy
```

### 2. Könyvtár Struktúra Beállítása

```bash
# Alkalmazás könyvtárak létrehozása
sudo mkdir -p /var/www/laravel-app
sudo chown deploy:www-data /var/www/laravel-app
sudo chmod 755 /var/www/laravel-app

# További könyvtárak létrehozása
mkdir -p /var/www/laravel-app/{releases,storage,shared}
```

### 3. Repository Klónozása

```bash
cd /var/www/laravel-app
git clone https://github.com/TGaben/boilerplate.git current
cd current
```

## 🐳 Docker Telepítés

### 1. Docker Compose Produkciós Beállítás

Hozd létre a `docker-compose.prod.yml` fájlt:

```yaml
version: '3.8'

services:
  app:
    build:
      context: .
      dockerfile: Dockerfile.production
    container_name: laravel_app
    restart: unless-stopped
    environment:
      - APP_ENV=production
      - APP_DEBUG=false
    volumes:
      - storage_data:/var/www/storage
      - ./public:/var/www/public
    networks:
      - laravel_network
    depends_on:
      - mysql
      - redis

  nginx:
    image: nginx:alpine
    container_name: laravel_nginx
    restart: unless-stopped
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./docker/nginx/prod.conf:/etc/nginx/conf.d/default.conf
      - ./public:/var/www/public:ro
      - ./storage/app/public:/var/www/storage/app/public:ro
      - ./ssl:/etc/nginx/ssl:ro
    networks:
      - laravel_network
    depends_on:
      - app

  mysql:
    image: mysql:8.0
    container_name: laravel_mysql
    restart: unless-stopped
    environment:
      MYSQL_DATABASE: ${DB_DATABASE}
      MYSQL_USER: ${DB_USERNAME}
      MYSQL_PASSWORD: ${DB_PASSWORD}
      MYSQL_ROOT_PASSWORD: ${DB_ROOT_PASSWORD}
    volumes:
      - mysql_data:/var/lib/mysql
      - ./docker/mysql/my.cnf:/etc/mysql/conf.d/my.cnf
    ports:
      - "3306:3306"
    networks:
      - laravel_network

  redis:
    image: redis:7-alpine
    container_name: laravel_redis
    restart: unless-stopped
    volumes:
      - redis_data:/data
    networks:
      - laravel_network
    command: redis-server --appendonly yes

volumes:
  mysql_data:
  redis_data:
  storage_data:

networks:
  laravel_network:
    driver: bridge
```

### 2. Produkciós Dockerfile

Hozd létre a `Dockerfile.production` fájlt:

```dockerfile
FROM php:8.3-fpm-alpine

# Rendszer függőségek telepítése
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    oniguruma-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    nginx \
    supervisor

# PHP kiterjesztések telepítése
RUN docker-php-ext-configure zip
RUN docker-php-ext-install \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    intl

# Redis kiterjesztés telepítése
RUN pecl install redis && docker-php-ext-enable redis

# Composer telepítése
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Munkakönyvtár beállítása
WORKDIR /var/www

# Composer fájlok másolása
COPY composer.json composer.lock ./

# PHP függőségek telepítése
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Alkalmazás kód másolása
COPY . .

# Jogosultságok beállítása
RUN chown -R www-data:www-data /var/www
RUN chmod -R 755 /var/www/storage /var/www/bootstrap/cache

# Node függőségek telepítése és asset build
COPY package*.json ./
RUN npm ci --only=production
RUN npm run build

# Laravel optimalizációk
RUN php artisan config:cache
RUN php artisan route:cache
RUN php artisan view:cache

# Supervisor konfiguráció másolása
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

EXPOSE 9000

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
```

### 3. Docker-rel Való Telepítés

```bash
# Konténerek build és indítása
docker-compose -f docker-compose.prod.yml up -d --build

# Migrációk futtatása
docker-compose -f docker-compose.prod.yml exec app php artisan migrate --force

# Kezdeti adatok seedelése
docker-compose -f docker-compose.prod.yml exec app php artisan db:seed --force
```

## 🏗️ Hagyományos Szerver Telepítés

### 1. Függőségek Telepítése

```bash
cd /var/www/laravel-app/current

# PHP függőségek telepítése
composer install --no-dev --optimize-autoloader

# Node függőségek telepítése
npm ci --only=production

# Produkciós asset-ek build-je
npm run build
```

### 2. Környezet Konfigurálása

```bash
# Környezeti fájl másolása
cp .env.example .env

# Alkalmazás kulcs generálása
php artisan key:generate

# Környezeti változók konfigurálása
vim .env
```

### 3. Jogosultságok Beállítása

```bash
# Megfelelő tulajdonos beállítása
sudo chown -R deploy:www-data /var/www/laravel-app
sudo chmod -R 755 /var/www/laravel-app

# Storage jogosultságok beállítása
sudo chmod -R 775 storage bootstrap/cache
sudo chown -R www-data:www-data storage bootstrap/cache
```

### 4. Webszerver Konfigurálása

#### Nginx Konfiguráció

Hozd létre a `/etc/nginx/sites-available/laravel-app` fájlt:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name yourdomain.com www.yourdomain.com;
    root /var/www/laravel-app/current/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    # Biztonsági header-ök
    add_header X-XSS-Protection "1; mode=block";
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains";
    add_header Referrer-Policy "strict-origin-when-cross-origin";

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        
        # Biztonság
        fastcgi_hide_header X-Powered-By;
        fastcgi_read_timeout 300;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Statikus asset-ek gyorsítótárazása
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        add_header Vary Accept-Encoding;
    }

    # Gzip tömörítés
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css text/xml text/javascript application/javascript application/xml+rss application/json;
}
```

Oldal engedélyezése:

```bash
sudo ln -s /etc/nginx/sites-available/laravel-app /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

## 🗄️ Adatbázis Beállítás

### MySQL Konfiguráció

```bash
# Csatlakozás MySQL-hez
mysql -u root -p

# Adatbázis és felhasználó létrehozása
CREATE DATABASE laravel_app CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'laravel_user'@'localhost' IDENTIFIED BY 'biztonságos_jelszó';
GRANT ALL PRIVILEGES ON laravel_app.* TO 'laravel_user'@'localhost';
FLUSH PRIVILEGES;
```

### Migrációk Futtatása

```bash
# Adatbázis migrációk futtatása
php artisan migrate --force

# Kezdeti adatok seedelése
php artisan db:seed --force
```

## 🔒 SSL Konfiguráció

### Let's Encrypt Használata (Certbot)

```bash
# Certbot telepítése
sudo apt install certbot python3-certbot-nginx

# SSL tanúsítvány megszerzése
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com

# Automatikus megújítás tesztelése
sudo certbot renew --dry-run
```

### Manuális SSL Konfiguráció

Nginx konfiguráció frissítése:

```nginx
server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name yourdomain.com www.yourdomain.com;

    ssl_certificate /path/to/certificate.crt;
    ssl_certificate_key /path/to/private.key;
    
    # SSL konfiguráció
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-RSA-AES128-GCM-SHA256:ECDHE-RSA-AES256-GCM-SHA384;
    ssl_prefer_server_ciphers off;
    
    # SSL session gyorsítótárazás
    ssl_session_cache shared:SSL:10m;
    ssl_session_timeout 10m;
    
    # OCSP stapling
    ssl_stapling on;
    ssl_stapling_verify on;
    
    # Konfiguráció többi része...
}

# HTTP átirányítása HTTPS-re
server {
    listen 80;
    listen [::]:80;
    server_name yourdomain.com www.yourdomain.com;
    return 301 https://$server_name$request_uri;
}
```

## ⚡ Teljesítmény Optimalizáció

### 1. Laravel Optimalizációk

```bash
# Konfiguráció gyorsítótárazása
php artisan config:cache

# Route-ok gyorsítótárazása
php artisan route:cache

# View-k gyorsítótárazása
php artisan view:cache

# Autoloader optimalizálása
composer dump-autoload --optimize
```

### 2. PHP-FPM Konfiguráció

Szerkeszd a `/etc/php/8.3/fpm/pool.d/www.conf` fájlt:

```ini
; Folyamat kezelés
pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35
pm.max_requests = 1000

; Teljesítmény
request_slowlog_timeout = 10s
slowlog = /var/log/php8.3-fpm-slow.log
```

### 3. MySQL Hangolás

Szerkeszd a `/etc/mysql/mysql.conf.d/mysqld.cnf` fájlt:

```ini
[mysqld]
# InnoDB beállítások
innodb_buffer_pool_size = 1G
innodb_log_file_size = 256M
innodb_file_per_table = 1
innodb_flush_method = O_DIRECT

# Query cache
query_cache_type = 1
query_cache_size = 32M

# Kapcsolat beállítások
max_connections = 200
wait_timeout = 300
```

### 4. Redis Konfiguráció

Szerkeszd a `/etc/redis/redis.conf` fájlt:

```ini
# Memória kezelés
maxmemory 256mb
maxmemory-policy allkeys-lru

# Perzisztencia
save 900 1
save 300 10
save 60 10000

# Hálózat
tcp-keepalive 300
```

## 📊 Monitorozás és Naplózás

### 1. Napló Konfiguráció

Frissítsd a `config/logging.php` fájlt:

```php
'channels' => [
    'production' => [
        'driver' => 'daily',
        'path' => storage_path('logs/laravel.log'),
        'level' => 'error',
        'days' => 30,
    ],
],
```

### 2. Rendszer Monitorozás

Monitorozó eszközök telepítése:

```bash
# Rendszer monitorozás telepítése
sudo apt install htop iotop nethogs

# Napló monitorozás telepítése
sudo apt install logwatch
```

### 3. Alkalmazás Monitorozás

Fontos integrációk:
- **Sentry** - Hibakövető
- **New Relic** - Alkalmazás teljesítmény monitorozás
- **DataDog** - Infrastruktúra monitorozás
- **Uptime Kuma** - Üzemidő monitorozás

## 🔐 Biztonsági Ellenőrzőlista

### Szerver Biztonság

- [ ] Root SSH bejelentkezés letiltása
- [ ] SSH kulcsok használata jelszavak helyett
- [ ] Tűzfal konfigurálása (UFW/iptables)
- [ ] Fail2ban telepítése
- [ ] Rendszer naprakészen tartása
- [ ] Nem szabványos SSH port használata

```bash
# Alapvető tűzfal beállítás
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow ssh
sudo ufw allow 'Nginx Full'
sudo ufw enable
```

### Alkalmazás Biztonság

- [ ] HTTPS használata mindenhol
- [ ] Biztonságos környezeti változók beállítása
- [ ] CORS megfelelő konfigurálása
- [ ] Rate limiting engedélyezése
- [ ] Biztonságos header-ök használata
- [ ] Rendszeres biztonsági frissítések

### Laravel Biztonsági Konfiguráció

Frissítsd a `.env` fájlt:

```env
# Biztonsági beállítások
APP_DEBUG=false
APP_ENV=production

# Session biztonság
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=strict

# Adatbázis
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
```

## 💾 Biztonsági Mentési Stratégia

### 1. Adatbázis Biztonsági Mentések

Biztonsági mentési script létrehozása `/home/deploy/backup-db.sh`:

```bash
#!/bin/bash

BACKUP_DIR="/home/deploy/backups"
DATE=$(date +%Y%m%d_%H%M%S)
DB_NAME="laravel_app"
DB_USER="laravel_user"
DB_PASS="biztonságos_jelszó"

# Backup könyvtár létrehozása
mkdir -p $BACKUP_DIR

# Adatbázis backup létrehozása
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME > $BACKUP_DIR/db_backup_$DATE.sql

# Backup tömörítése
gzip $BACKUP_DIR/db_backup_$DATE.sql

# 30 napnál régebbi backup-ok törlése
find $BACKUP_DIR -name "db_backup_*.sql.gz" -mtime +30 -delete

echo "Adatbázis backup befejezve: db_backup_$DATE.sql.gz"
```

Futtathatóvá tétel és cron-hoz adás:

```bash
chmod +x /home/deploy/backup-db.sh

# Crontab-hoz adás (naponta hajnali 2-kor)
crontab -e
0 2 * * * /home/deploy/backup-db.sh
```

### 2. Fájl Biztonsági Mentések

```bash
# Alkalmazás fájlok backup-ja
tar -czf /home/deploy/backups/app_backup_$(date +%Y%m%d).tar.gz /var/www/laravel-app/current

# Storage fájlok backup-ja
tar -czf /home/deploy/backups/storage_backup_$(date +%Y%m%d).tar.gz /var/www/laravel-app/current/storage
```

## 🔄 CI/CD Pipeline

### GitHub Actions Telepítés

Hozd létre a `.github/workflows/deploy.yml` fájlt:

```yaml
name: Telepítés Produkcióba

on:
  push:
    branches: [main]

jobs:
  deploy:
    runs-on: ubuntu-latest
    
    steps:
    - uses: actions/checkout@v4
    
    - name: PHP beállítása
      uses: shivammathur/setup-php@v2
      with:
        php-version: '8.3'
        
    - name: Függőségek telepítése
      run: composer install --no-dev --optimize-autoloader
      
    - name: Asset-ek build-je
      run: |
        npm ci --only=production
        npm run build
        
    - name: Telepítés szerverre
      uses: appleboy/ssh-action@v0.1.5
      with:
        host: ${{ secrets.HOST }}
        username: ${{ secrets.USERNAME }}
        key: ${{ secrets.PRIVATE_KEY }}
        script: |
          cd /var/www/laravel-app/current
          git pull origin main
          composer install --no-dev --optimize-autoloader
          npm ci --only=production
          npm run build
          php artisan migrate --force
          php artisan config:cache
          php artisan route:cache
          php artisan view:cache
          sudo systemctl reload php8.3-fpm
          sudo systemctl reload nginx
```

### Nulla Leállás Telepítés

Telepítési script létrehozása:

```bash
#!/bin/bash

REPO_URL="https://github.com/TGaben/boilerplate.git"
APP_DIR="/var/www/laravel-app"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)

# Új release könyvtár létrehozása
mkdir -p $APP_DIR/releases/$TIMESTAMP

# Repository klónozása
git clone $REPO_URL $APP_DIR/releases/$TIMESTAMP

# Release könyvtárba lépés
cd $APP_DIR/releases/$TIMESTAMP

# Függőségek telepítése
composer install --no-dev --optimize-autoloader
npm ci --only=production
npm run build

# Megosztott storage linkelése
ln -nfs $APP_DIR/shared/storage/app/public storage/app/public
ln -nfs $APP_DIR/shared/.env .env

# Laravel parancsok futtatása
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Symlink frissítése
ln -nfs $APP_DIR/releases/$TIMESTAMP $APP_DIR/current

# Szolgáltatások újratöltése
sudo systemctl reload php8.3-fpm
sudo systemctl reload nginx

# Régi release-ek tisztítása (utolsó 5 megtartása)
cd $APP_DIR/releases && ls -t | tail -n +6 | xargs rm -rf

echo "Telepítés sikeresen befejezve!"
```

---

Ez a telepítési útmutató egy biztonságos, teljesítményes és karbantartható produkciós környezetet biztosít a Laravel Vállalati Boilerplate-hez.
