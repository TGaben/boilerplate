# Environment Setup & Templates

Ez a dokumentum a Laravel Boilerplate environment template rendszerének használatát és a konfigurációs lehetőségeket mutatja be.

## 🌍 Áttekintés

A boilerplate beépített environment template rendszert tartalmaz:
- **Előre konfigurált sablonok** különböző környezetekhez
- **Automatikus validáció** a szükséges változók ellenőrzésére
- **Biztonsági ellenőrzések** production környezetek számára
- **Quick Start integráció** az automatikus setup-hoz

## 📁 Environment Sablonok

### Elérhető Sablonok

```
templates/environments/
├── env.development     # Helyi fejlesztés
├── env.testing        # Automatizált tesztelés  
├── env.production     # Éles környezet
└── env.ci             # CI/CD pipeline
```

### Sablon Jellemzők

**Development** (`env.development`)
- Debug mode bekapcsolva
- SQLite adatbázis (gyors setup)
- Verbose logging
- Cache kikapcsolva
- Email capture (log driver)

**Testing** (`env.testing`)
- In-memory SQLite (:memory:)
- Gyors teszt végrehajtás
- Minimal logging
- Queue sync driver
- Session array driver

**Production** (`env.production`)
- Debug mode kikapcsolva
- MySQL/PostgreSQL adatbázis
- Optimalizált cache
- Queue worker
- Email SMTP
- SSL kényszerítés

**CI/CD** (`env.ci`)
- MySQL service-szel
- GitHub Actions kompatibilis
- Artifact generation
- Test coverage reporting

## 🛠️ Environment Management

### Artisan Command

```bash
# Összes sablon listázása
php artisan boilerplate:env list

# Sablon tartalmának megtekintése
php artisan boilerplate:env show development

# Sablon másolása
php artisan boilerplate:env copy development

# Jelenlegi környezet validálása
php artisan boilerplate:env validate

# Környezet állapotának ellenőrzése
php artisan boilerplate:env check
```

### Interaktív Használat

```bash
# Development környezet beállítása
php artisan boilerplate:env copy development

# Biztonsági mentés készítése
php artisan boilerplate:env copy production --backup

# Felülírás megerősítés nélkül
php artisan boilerplate:env copy testing --force
```

## ⚙️ Konfiguráció Validálás

### Automatikus Ellenőrzések

A rendszer automatikusan ellenőrzi:

**Kötelező Változók**
- `APP_NAME`
- `APP_KEY` 
- `APP_URL`
- `DB_CONNECTION`
- `DB_DATABASE`

**Környezet-specifikus Változók**
```php
// Production környezetben kötelező
'production' => [
    'APP_DEBUG' => 'false',
    'APP_ENV' => 'production',
    'LOG_LEVEL' => 'warning',
    'CACHE_DRIVER' => '!array',
    'SESSION_DRIVER' => '!array',
]
```

**Tiltott Értékek**
```php
'forbidden_values' => [
    'APP_KEY' => ['base64:REPLACE_WITH_32_CHARACTER_SECRET_KEY'],
    'DB_PASSWORD' => ['', 'password', '123456'],
    'MAIL_PASSWORD' => ['', 'password'],
]
```

### Validációs Eredmény

```bash
$ php artisan boilerplate:env validate

✅ ENVIRONMENT VALIDATION RESULTS
┌─────────────────────────┬─────────┬─────────────────────────────┐
│ Check                   │ Status  │ Message                     │
├─────────────────────────┼─────────┼─────────────────────────────┤
│ Required Variables      │ ✅ PASS │ All required vars present   │
│ APP_KEY Security        │ ✅ PASS │ Strong application key      │
│ Database Connection     │ ✅ PASS │ Connection successful       │
│ Cache Connection        │ ⚠️ WARN  │ Using array driver         │
│ Production Settings     │ ✅ PASS │ Debug mode disabled         │
└─────────────────────────┴─────────┴─────────────────────────────┘

📊 SUMMARY: 4 passed, 1 warning, 0 errors
```

## 🔧 Egyedi Environment Létrehozása

### Új Sablon Készítése

```bash
# 1. Másold egy meglévő sablont
cp templates/environments/env.development templates/environments/env.staging

# 2. Szerkeszd a változókat
# 3. Add hozzá a konfigurációhoz
```

### Sablon Regisztrálása

```php
// config/environment.php
'templates' => [
    'available' => [
        'staging' => [
            'name' => 'Staging',
            'description' => 'Pre-production testing environment',
            'file' => 'env.staging'
        ],
    ],
],
```

### Egyedi Validációs Szabályok

```php
// config/environment.php
'validation' => [
    'required_by_env' => [
        'staging' => [
            'STAGING_API_KEY',
            'STAGING_DATABASE_URL',
        ],
    ],
],
```

## 🚀 Quick Start Integráció

### Automatikus Environment Setup

```bash
# Quick Start Script környezet kiválasztással
./scripts/quick-start.sh --env=production

# Script létrehozza a .env fájlt a megfelelő sablonból
# Majd futtatja a validálást
```

### Script Konfigurációk

```bash
# Development (alapértelmezett)
./scripts/quick-start.sh

# Testing környezet
./scripts/quick-start.sh --env=testing

# Production biztonságos móddal
./scripts/quick-start.sh --env=production --domain=myapp.com
```

## 🔐 Biztonsági Szempontok

### Production Checklist

A production environment automatikusan ellenőrzi:

✅ **Application Security**
- `APP_DEBUG=false`
- `APP_ENV=production`
- Erős `APP_KEY` generálva

✅ **Database Security**
- Nem default jelszavak
- SSL kapcsolat ajánlott
- Backup stratégia

✅ **Session & Cache**
- Biztonságos session driver
- Persistent cache (Redis/Memcached)
- HTTPS cookie beállítások

✅ **Logging & Monitoring**
- Appropriate log level
- Error tracking (Sentry)
- Performance monitoring

### Security Warnings

```bash
⚠️ SECURITY WARNINGS:
├── APP_KEY is still default value
├── Database password is weak
├── HTTPS not enforced
└── No error tracking configured

🔧 Run: php artisan boilerplate:env validate --fix-suggestions
```

## 📊 Environment Health Check

### System Connectivity

```bash
$ php artisan boilerplate:env check

🔍 ENVIRONMENT HEALTH CHECK
┌─────────────────────────┬─────────┬─────────────────────────────┐
│ Service                 │ Status  │ Details                     │
├─────────────────────────┼─────────┼─────────────────────────────┤
│ Database Connection     │ ✅ OK   │ MySQL 8.0.32 (15ms)        │
│ Cache Connection        │ ✅ OK   │ Redis 6.2.6 (2ms)          │
│ Queue Connection        │ ✅ OK   │ Redis queue (1ms)           │
│ Mail Configuration      │ ✅ OK   │ SMTP configured             │
│ Storage Permissions     │ ✅ OK   │ All writable                │
│ External APIs           │ ⚠️ WARN │ Some services slow          │
└─────────────────────────┴─────────┴─────────────────────────────┘

💡 OPTIMIZATION SUGGESTIONS:
├── Enable Redis cache for better performance
├── Configure a dedicated queue worker
└── Set up automated backup schedule
```

## 🧪 Testing Environments

### Test-specific Configuration

```env
# env.testing
APP_ENV=testing
DB_CONNECTION=sqlite
DB_DATABASE=:memory:
QUEUE_CONNECTION=sync
SESSION_DRIVER=array
CACHE_DRIVER=array
MAIL_MAILER=array
```

### Parallel Testing

```env
# env.testing (parallel)
DB_CONNECTION=sqlite
DB_DATABASE=database/testing_{$PARALLEL_PROCESS}.sqlite
CACHE_PREFIX=test_{$PARALLEL_PROCESS}
```

### CI/CD Specific

```env
# env.ci
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=boilerplate_ci
DB_USERNAME=root
DB_PASSWORD=root

# GitHub Actions specific
CACHE_DRIVER=array
SESSION_DRIVER=array
```

## 🔄 Environment Migration

### Upgrade Environment

```bash
# Backup current environment
cp .env .env.backup

# Update to new template
php artisan boilerplate:env copy production --backup

# Merge custom variables
php artisan boilerplate:env merge .env.backup
```

### Environment Diff

```bash
# Compare current vs template
php artisan boilerplate:env diff production

# Shows:
# + Added variables
# - Removed variables  
# ~ Changed values
# ! Security concerns
```

## 💡 Best Practices

### Development

1. **Egyedi adatbázis** minden fejlesztőnek
2. **Local mail capture** (MailHog, Mailtrap)
3. **Debug toolbar** engedélyezése
4. **Cache disable** fejlesztés alatt

### Production

1. **Environment validation** deploy előtt
2. **Backup készítése** minden változtatás előtt
3. **Monitoring** beállítása
4. **SSL** kényszerítése
5. **Rate limiting** beállítása

### CI/CD

1. **Parallel testing** támogatása
2. **Artifact generation** 
3. **Environment secrets** kezelése
4. **Deploy hooks** konfigurálása

## 📚 További Olvasnivaló

- [Laravel Environment Configuration](https://laravel.com/docs/configuration#environment-configuration)
- [Laravel Deployment](https://laravel.com/docs/deployment)
- [Environment Security Best Practices](https://owasp.org/www-project-cheat-sheets/cheatsheets/Laravel_Cheat_Sheet.html)


