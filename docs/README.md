# 📚 Laravel Boilerplate Dokumentáció

Üdvözölünk a Laravel Boilerplate átfogó dokumentációjában! Ez a dokumentáció moduláris struktúrában van szervezve, hogy könnyedén megtaláld a keresett információkat.

## 🧭 Dokumentáció Struktúra

### 🏗️ Core Komponensek

Az alapvető boilerplate komponensek dokumentációja. Ezek a funkciók minden projektben elérhetők és használhatók.

| Dokumentum | Leírás | Nehézség |
|------------|--------|----------|
| [🔐 Authentication & Permissions](core/authentication.md) | Felhasználókezelés, szerepkörök és jogosultságok | 🟢 |
| [🎛️ Admin Panel (Filament)](core/admin-panel.md) | Filament admin panel használata és testreszabása | 🟢 |
| [🌍 Environment Setup](core/environment-setup.md) | Environment sablonok és konfiguráció kezelése | 🟡 |
| [🧪 Testing Foundation](core/testing.md) | Tesztelési alapok és helper-ek | 🟡 |

### 🍳 Recipes (Opcionális Funkciók)

Külön telepíthető funkciók és bővítmények a boilerplate-hez. Mindegyik recipe önálló és igény szerint alkalmazható.

#### Backend Recipes

| Recipe | Leírás | Nehézség | Időigény |
|---------|--------|----------|----------|
| [🔌 API Development](recipes/api-development.md) | RESTful API Laravel Sanctum-mal | 🟢 Easy | 2-3 óra |
| [🏗️ Multi-Tenancy](recipes/multi-tenancy.md) | SaaS multi-tenant architektúra | 🔴 Advanced | 1-2 nap |
| [⚡ Performance & Caching](recipes/performance-optimization.md) | Redis cache, optimization | 🟡 Medium | 3-4 óra |
| [🔒 Advanced Permissions](recipes/advanced-permissions.md) | Komplex jogosultságkezelés | 🔴 Advanced | 4-6 óra |

#### Storage & Media Recipes

| Recipe | Leírás | Nehézség | Időigény |
|---------|--------|----------|----------|
| [💾 File Upload System](recipes/file-uploads.md) | Fájlfeltöltés képoptimalizálással | 🟡 Medium | 3-4 óra |
| [📁 Media Library](recipes/media-library.md) | Komplett média kezelés | 🟡 Medium | 2-3 óra |
| [☁️ Cloud Storage](recipes/cloud-storage.md) | AWS S3, DigitalOcean Spaces | 🟡 Medium | 2-3 óra |

#### Communication Recipes

| Recipe | Leírás | Nehézség | Időigény |
|---------|--------|----------|----------|
| [📧 Email Templates](recipes/email-templates.md) | Testreszabható email rendszer | 🟡 Medium | 2-3 óra |
| [🔔 Notifications](recipes/notifications.md) | Push, SMS, email értesítések | 🟡 Medium | 3-4 óra |
| [⚡ WebSocket & Real-time](recipes/websocket-realtime.md) | Real-time kommunikáció | 🔴 Advanced | 5-6 óra |

#### Analytics & Reporting Recipes

| Recipe | Leírás | Nehézség | Időigény |
|---------|--------|----------|----------|
| [📊 Reporting Dashboard](recipes/reporting-dashboard.md) | Chart.js alapú dashboard | 🟡 Medium | 4-5 óra |
| [📈 Analytics Integration](recipes/analytics.md) | Google Analytics, tracking | 🟢 Easy | 1-2 óra |
| [🎯 A/B Testing](recipes/ab-testing.md) | Feature flag és A/B tesztek | 🟡 Medium | 3-4 óra |

### 🚀 Deployment & Infrastructure

Produkciós telepítési útmutatók és infrastruktúra beállítások.

| Dokumentum | Leírás | Célközönség |
|------------|--------|-------------|
| [🏗️ Architecture Overview](deployment/architecture.md) | Technológiai döntések és architektúra | Fejlesztők |
| [🐳 Docker Deployment](deployment/docker.md) | Docker-alapú telepítés | DevOps |
| [🖥️ Traditional Server](deployment/traditional-server.md) | Apache/Nginx telepítés | Sys Admin |
| [☁️ Cloud Platforms](deployment/cloud-platforms.md) | AWS, DigitalOcean, Vultr | DevOps |
| [⚙️ CI/CD Pipeline](deployment/cicd.md) | GitHub Actions, GitLab CI | DevOps |

### 🆘 Troubleshooting & Support

Hibaelhárítás és problémamegoldás.

| Dokumentum | Leírás | Mikor használd |
|------------|--------|----------------|
| [🔧 Common Issues](troubleshooting/common-issues.md) | Gyakori problémák és megoldások | Setup hibák |
| [⚡ Performance Debugging](troubleshooting/performance-debugging.md) | Teljesítmény problémák | Lassú alkalmazás |
| [🌍 Environment Problems](troubleshooting/environment-problems.md) | Konfiguráció és környezet hibák | .env problémák |
| [🔍 Quality Check Guide](troubleshooting/quality-check.md) | Code quality eszközök használata | Code review |

## 🚀 Gyors Navigáció

### 🎯 Szerepkör Alapú Útmutatók

**Új Fejlesztő** 🆕
1. [Quick Start](../README.md#gyors-kezdés) - 2 perces setup
2. [Authentication](core/authentication.md) - Jogosultságrendszer megértése
3. [Admin Panel](core/admin-panel.md) - Filament alapok
4. [Testing](core/testing.md) - Tesztelési alapok

**Frontend Fejlesztő** 🎨
1. [Admin Panel Testreszabás](core/admin-panel.md#tema-és-megjelenés)
2. [File Upload UI](recipes/file-uploads.md#frontend-components)
3. [Real-time Features](recipes/websocket-realtime.md)
4. [Performance Optimalization](recipes/performance-optimization.md)

**Backend Fejlesztő** 🔧
1. [API Development](recipes/api-development.md)
2. [Advanced Permissions](recipes/advanced-permissions.md)
3. [Multi-Tenancy](recipes/multi-tenancy.md)
4. [Performance & Caching](recipes/performance-optimization.md)

**DevOps Engineer** 🚀
1. [Docker Deployment](deployment/docker.md)
2. [CI/CD Pipeline](deployment/cicd.md)
3. [Cloud Platforms](deployment/cloud-platforms.md)
4. [Performance Debugging](troubleshooting/performance-debugging.md)

**Project Manager** 📋
1. [Architecture Overview](deployment/architecture.md)
2. [Recipe Planning](#recipe-tervezés)
3. [Deployment Options](deployment/)
4. [Common Issues](troubleshooting/common-issues.md)

### 🔍 Témakör Alapú Keresés

**Security & Authentication** 🔒
- [Core Authentication](core/authentication.md)
- [Advanced Permissions](recipes/advanced-permissions.md)
- [Multi-Tenancy Security](recipes/multi-tenancy.md#biztonsági-szempontok)

**Performance & Scaling** ⚡
- [Caching Strategies](recipes/performance-optimization.md)
- [Database Optimization](troubleshooting/performance-debugging.md)
- [CDN Integration](recipes/cloud-storage.md)

**File & Media Management** 📁
- [File Uploads](recipes/file-uploads.md)
- [Media Library](recipes/media-library.md)
- [Cloud Storage](recipes/cloud-storage.md)

**API & Integration** 🔌
- [RESTful API](recipes/api-development.md)
- [WebSocket](recipes/websocket-realtime.md)
- [Third-party APIs](recipes/analytics.md)

## 🍳 Recipe Kezelés

### Recipe Telepítése

```bash
# Elérhető recipes listázása
php artisan boilerplate:recipes list

# Interaktív böngészés
php artisan boilerplate:recipes browse

# Konkrét recipe telepítése
php artisan boilerplate:recipes install api-development
```

### Recipe Tervezés

A recipe-k telepítése előtt fontos megtervezni, hogy mely funkciókat szeretnéd használni:

1. **Projekt igények felmérése** - Milyen funkciókat igényel a projekt?
2. **Függőségek ellenőrzése** - Mely recipe-k függnek egymástól?
3. **Implementációs sorrend** - Milyen sorrendben telepítsed a recipe-ket?
4. **Tesztelési stratégia** - Hogyan teszteled az egyes recipe-ket?

#### Ajánlott Recipe Kombinációk

**SaaS Alkalmazás**
```bash
php artisan boilerplate:recipes install multi-tenancy
php artisan boilerplate:recipes install api-development
php artisan boilerplate:recipes install file-uploads
php artisan boilerplate:recipes install email-templates
```

**E-commerce Platform**
```bash
php artisan boilerplate:recipes install file-uploads
php artisan boilerplate:recipes install email-templates
php artisan boilerplate:recipes install performance-optimization
php artisan boilerplate:recipes install analytics
```

**CMS/Admin Panel**
```bash
php artisan boilerplate:recipes install advanced-permissions
php artisan boilerplate:recipes install media-library
php artisan boilerplate:recipes install notifications
```

## 📝 Közreműködés

Ha hibát találsz a dokumentációban vagy javítási javaslatod van:

1. **Issues** - Nyiss issue-t a GitHub repository-ban
2. **Pull Request** - Küldd be a javításaidat
3. **Discussion** - Beszéljük meg új recipe ötleteket

### Dokumentáció Írási Útmutató

1. **Konzisztens struktúra** használata
2. **Gyakorlati példák** mellékjelése
3. **Troubleshooting szekció** minden recipe-nél
4. **Kód példák** kommentálása
5. **További olvasnivalók** linkje

## 🎯 Következő Lépések

1. **Quick Start** - Ha még nem tetted meg: [Gyors Kezdés](../README.md#gyors-kezdés)
2. **Core ismeretek** - Tanulmányozd a [Core komponenseket](#core-komponensek)
3. **Recipe kiválasztás** - Válaszd ki a projektedhez szükséges [Recipe-ket](#recipes-opcionális-funkciók)
4. **Deployment tervezés** - Tervezd meg a [produkciós telepítést](#deployment--infrastructure)

---

> 💡 **Tipp**: Használd a `Ctrl+F` (vagy `Cmd+F`) billentyűkombinációt konkrét kifejezések kereséséhez a dokumentációban.

> 📧 **Support**: Ha nem találod a keresett információt, keresd fel a [troubleshooting szekciót](#troubleshooting--support) vagy nyiss issue-t a GitHub repository-ban.


