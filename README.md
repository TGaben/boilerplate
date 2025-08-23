# 🚀 Laravel 12 Vállalati Boilerplate

<p align="center">
<a href="https://github.com/TGaben/boilerplate/actions"><img src="https://github.com/TGaben/boilerplate/workflows/Laravel%20CI/CD%20Pipeline/badge.svg" alt="CI/CD Pipeline"></a>
<img src="https://img.shields.io/badge/Laravel-12-FF2D20?style=flat&logo=laravel" alt="Laravel 12">
<img src="https://img.shields.io/badge/PHP-8.3-777BB4?style=flat&logo=php" alt="PHP 8.3">
<img src="https://img.shields.io/badge/Tests-161%20passed-brightgreen" alt="Tesztek">
<img src="https://img.shields.io/badge/PHPStan-Max%20Level-brightgreen" alt="PHPStan">
</p>

**Egy production-ready Laravel boilerplate, amely heteket spórol meg a fejlesztési időből.** Az ötlettől a telepítésig 2 perc alatt eljuthatsz vállalati szintű alapokkal, átfogó admin panellel és automatizált minőségbiztosítási kapukkal.

## ✨ Miért pont ez a Boilerplate?

Ne építsd újra ugyanazt az infrastruktúrát minden Laravel projekthez. Ez a boilerplate biztosítja:

- **🎯 2 perces telepítés** - Klónozd, futtass egyetlen parancsot, kezdj el funkciókkal foglalkozni
- **🛡️ Vállalati biztonság** - Szerepkör-alapú jogosultságok, tevékenység naplózás, CSRF védelem  
- **⚡ Zéró-konfig admin** - Teljes Filament admin panel felhasználó/szerepkör kezeléssel
- **🔧 Minőségi kapuk** - Automatizált tesztelés, kódstílus (Pint), statikus elemzés (PHPStan)
- **🐳 Docker-first** - Konzisztens fejlesztői környezet Laravel Sail-lel
- **📱 Modern frontend** - TailwindCSS 3.4 sötét móddal és UI komponensekkel

## 🎁 Mit Tartalmaz

### 🎛️ **Admin Panel (Filament PHP 3.x)**
- Felhasználókezelés CRUD műveletekkel
- Szerepkör és jogosultság kezelés (Spatie Laravel Permission)
- Tevékenység naplózás audit nyomvonalakhoz
- Dashboard statisztikákkal és betekintésekkel
- Többnyelvű támogatás (HU/EN)

### 🔐 **Hitelesítés és Jogosultságkezelés**
- Szerepkör-alapú hozzáférés-szabályozás (Admin/Felhasználó szerepkörök)
- Jogosultság-alapú funkció hozzáférés
- Tevékenység naplózás minden felhasználói művelethez
- Biztonságos jelszókezelés hash-eléssel

### 🎨 **Frontend Alapok**
- TailwindCSS 3.4 egyedi design rendszerrel
- Sötét/világos mód rendszer preferencia felismeréssel
- Reszponzív UI komponens könyvtár
- Konfigurálható demo komponensek (könnyen ki/bekapcsolható)

### 🧪 **Minőségbiztosítás**
- **161 átfogó teszt** (Unit, Feature, Integration)
- **Laravel Pint** - PSR-12 kódstílus kikényszerítés
- **PHPStan Max Level** - Legszigorúbb statikus kódelemzés
- **GitHub Actions CI/CD** - Automatizált minőségi kapuk

### 🐳 **Fejlesztői Környezet**
- **Laravel Sail** - Docker-alapú fejlesztés
- **MySQL 8** (fejlesztés) + **SQLite** (tesztelés)
- **Mailpit** email teszteléshez
- **Vite** asset fordításhoz

## 🚀 Gyors Kezdés (2 perc!) ⚡

### Előfeltételek
- **Docker** és **Docker Compose**
- **Git**

### Egyparancs Setup 🎯
```bash
git clone https://github.com/TGaben/boilerplate.git sajat-projekt
cd sajat-projekt && ./scripts/quick-start.sh
```

**Ennyi!** A script automatikusan:
- ✅ Ellenőrzi a függőségeket
- ✅ Beállítja a környezeti konfigurációt  
- ✅ Telepíti a dependencies-eket
- ✅ Indítja a Docker környezetet
- ✅ Inicializálja az adatbázist
- ✅ Építi az asset-eket
- ✅ Validálja a telepítést

### Alternatív Környezetek
```bash
# Production környezet
./scripts/quick-start.sh --env=production --domain=myapp.com

# Testing környezet  
./scripts/quick-start.sh --env=testing

# CI/CD környezet
./scripts/quick-start.sh --env=ci --skip-interactive

# Utility opciók
./scripts/quick-start.sh --check-only    # Dependency ellenőrzés
./scripts/quick-start.sh --force         # Újratelepítés kényszerítése
./scripts/quick-start.sh --skip-tests    # Gyorsabb setup (tesztek nélkül)
```

**🎉 Kész!** Az alkalmazásod fut a következő címeken:
- **Publikus oldal:** http://localhost  
- **Admin panel:** http://localhost/admin

## 🔑 Admin Hozzáférés

**Alapértelmezett admin adatok:**
- **Email:** `admin@example.com`
- **Jelszó:** `password`

> 🛡️ **Fontos:** Változtasd meg ezeket az adatokat azonnal production környezetben!

## 🧪 Tesztek és Minőségbiztosítás

### ⚡ **Quality Check Script (Ajánlott)**

A legegyszerűbb és legbiztonságosabb módja a minőségbiztosítási ellenőrzéseknek:

```bash
# Teljes minőségbiztosítási ellenőrzés (tesztek + kódstílus + statikus elemzés)
./scripts/quality-check.sh

# Csak kódminőség ellenőrzés, tesztek kihagyása
./scripts/quality-check.sh --skip-tests

# Csak kódstílus javítás
./scripts/quality-check.sh --fix-only

# Súgó megtekintése
./scripts/quality-check.sh --help
```

> 💡 **Tipp:** Használd ezt a scriptet minden commit előtt, hogy elkerüld a CI/CD pipeline hibákat!

### 🔧 **Manuális Parancsok**

```bash
# Minden teszt futtatása (161 teszt)
./vendor/bin/sail artisan test

# Coverage-zel
./vendor/bin/sail artisan test --coverage

# Kódstílus ellenőrzés
./vendor/bin/sail composer lint

# Kódstílus automatikus javítás
./vendor/bin/sail pint

# Statikus elemzés (PHPStan Max Level)
./vendor/bin/sail composer stan
```

## 🛠️ Technológiai Stack

| Komponens | Technológia | Cél |
|-----------|------------|-----|
| **Keretrendszer** | Laravel 12 + PHP 8.3 | Modern, robosztus backend |
| **Admin Panel** | Filament PHP 3.x | Teljes körű admin felület |
| **Jogosultságok** | Spatie Laravel Permission | Szerepkör-alapú hozzáférés-szabályozás |
| **Frontend** | TailwindCSS 3.4 | Utility-first stílus |
| **Adatbázis** | MySQL 8 / SQLite | Megbízható adattárolás |
| **Tesztelés** | PHPUnit | Átfogó teszt lefedettség |
| **Minőség** | Pint + PHPStan | Kódstílus + statikus elemzés |
| **Fejlesztés** | Laravel Sail | Docker-alapú környezet |
| **CI/CD** | GitHub Actions | Automatizált telepítési pipeline |

## 📚 Dokumentáció

### 🏗️ **Core Komponensek (Minden projektben használható)**
- [🔐 Authentication & Permissions](docs/core/authentication.md) - Felhasználó- és jogosultságkezelés
- [🎛️ Admin Panel (Filament)](docs/core/admin-panel.md) - Admin felület használata és testreszabása
- [🌍 Environment Setup](docs/core/environment-setup.md) - Környezeti konfigurációk és sablonok
- [🧪 Testing Foundation](docs/core/testing.md) - Tesztelési alapok és helper-ek

### 🍳 **Recipes (Opcionális funkciók igény szerint)**
- [🔌 API Development](docs/recipes/api-development.md) - RESTful API Laravel Sanctum-mal
- [💾 File Upload System](docs/recipes/file-uploads.md) - Fájlfeltöltés képoptimalizálással
- [🏗️ Multi-Tenancy](docs/recipes/multi-tenancy.md) - SaaS multi-tenant architektúra

### 🚀 **Deployment & Troubleshooting**
- [🏗️ Architecture Overview](docs/deployment/architecture.md) - Rendszerterv és technológiai döntések
- [📖 Deployment Guide](docs/deployment.md) - Produkciós telepítési útmutató
- [🔧 Troubleshooting](docs/troubleshooting/common-issues.md) - Gyakori problémák és megoldások

### 📋 **Teljes Navigáció**
- [📚 Documentation Index](docs/README.md) - Teljes dokumentációs áttekintés minden funkcióval

### 👥 **Közreműködőknek**
- [Közreműködési Irányelvek](CONTRIBUTING.md) - Hogyan járulj hozzá ehhez a projekthez

## 🎯 Felhasználási Területek

Ez a boilerplate tökéletes a következő esetekhez:

- **SaaS alkalmazások** - Multi-tenant szerepkör kezeléssel
- **Admin dashboardok** - Adatkezelés audit nyomvonalakkal  
- **Tartalomkezelés** - Felhasználó által generált tartalom moderálással
- **E-commerce backend** - Termék/rendelés kezelő rendszerek
- **Vállalati eszközök** - Belső üzleti alkalmazások

## 🔄 Ütemterv

- [ ] **API Alapok** - Laravel Sanctum hitelesítés
- [ ] **Multi-tenancy** - Adatbázis-per-bérlő architektúra
- [ ] **Queue Kezelés** - Háttérfolyamat feldolgozás UI
- [ ] **Fájl Kezelés** - Média könyvtár S3 integrációval
- [ ] **Értesítések** - Valós idejű értesítési rendszer

## 🤝 Közreműködés

Szívesen fogadunk közreműködéseket! Kérlek olvasd el a [Közreműködési Irányelveinket](CONTRIBUTING.hu.md) a részletekért.

### 🚀 **Gyors Közreműködési Beállítás**
```bash
git clone https://github.com/TGaben/boilerplate.git
cd boilerplate
./vendor/bin/sail up -d
./vendor/bin/sail artisan test  # Győződj meg róla, hogy minden teszt sikeres
```

## 📄 Licenc

Ez a projekt nyílt forráskódú szoftver, amely az [MIT licenc](https://opensource.org/licenses/MIT) alatt áll.

## 🙏 Köszönetnyilvánítás

Ezekkel a fantasztikus technológiákkal épült:
- [Laravel](https://laravel.com) - A PHP keretrendszer webes kézműveseknek
- [Filament](https://filamentphp.com) - Gyönyörű admin panelek Laravel-hez
- [TailwindCSS](https://tailwindcss.com) - Utility-first CSS keretrendszer
- [Spatie Csomagok](https://spatie.be/open-source) - Magas minőségű PHP csomagok

---

<p align="center">
<strong>⭐ Csillagozd meg ezt a repo-t, ha segített valami fantasztikus építésében!</strong><br>
<em>Kérdések? Nyiss egy issue-t vagy kezdj egy beszélgetést.</em>
</p>
