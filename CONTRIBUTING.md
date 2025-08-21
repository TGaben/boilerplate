# Közreműködés a Laravel Vállalati Boilerplate-hez

Köszönjük az érdeklődést a projekthez való hozzájárulás iránt! Minden közreműködést szívesen fogadunk, és nagyra értékeljük a segítséget a boilerplate továbbfejlesztésében.

## 📋 Tartalomjegyzék

- [Magatartási Kódex](#magatartási-kódex)
- [Kezdés](#kezdés)
- [Fejlesztői Beállítás](#fejlesztői-beállítás)
- [Változtatások Végrehajtása](#változtatások-végrehajtása)
- [Minőségi Szabványok](#minőségi-szabványok)
- [Változtatások Beküldése](#változtatások-beküldése)
- [Issue Irányelvek](#issue-irányelvek)
- [Funkcióigények](#funkcióigények)

## 🤝 Magatartási Kódex

Ez a projekt egy magatartási kódexet követ. A részvétellel elvárjuk, hogy betartsd ezt a kódexet. Kérlek jelentsd a nem elfogadható viselkedést a projekt karbantartóinak.

### Szabványaink

- Legyél tiszteletteljes és befogadó
- Fókuszálj építő kritikákra
- Segíts másoknak tanulni és fejlődni
- Tartsd fenn a szakmai környezetet

## 🚀 Kezdés

### Előfeltételek

- **PHP 8.3+**
- **Composer**
- **Node.js & NPM**
- **Docker & Docker Compose**
- **Git**

### ⚡ Egyparancs Setup (Ajánlott)

```bash
# 1. Fork-old és klónozd a repository-t
git clone https://github.com/TE_FELHASZNALONEVED/boilerplate.git
cd boilerplate

# 2. Quick Start Script (minden mást elvégez)
./scripts/quick-start.sh
```

**Ennyi!** A script automatikusan elvégzi az összes setup lépést.

### 🔧 Manuális Setup (Speciális Esetek)

Ha valami specifikus konfigurációra van szükséged:

```bash
# Dependency ellenőrzés
./scripts/quick-start.sh --check-only

# Specifikus környezet
./scripts/quick-start.sh --env=testing

# Kényszerített újratelepítés
./scripts/quick-start.sh --force

# Részletes manuális lépések...
composer install
npm install
./vendor/bin/sail artisan boilerplate:env copy development
./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate --seed
./vendor/bin/sail npm run dev
```

## 🛠️ Fejlesztői Beállítás

### Ajánlott IDE Beállítás

- **PhpStorm** vagy **VS Code** PHP kiterjesztésekkel
- **PHP Intelephense** kód intelligenciához
- **Laravel Extension Pack** VS Code felhasználóknak

### Kódstílus Konfiguráció

A projekt **Laravel Pint**-et használ kódformázáshoz:

```bash
# Kódstílus ellenőrzés
./vendor/bin/sail composer lint

# Kódstílus automatikus javítás
./vendor/bin/sail composer cs-fix
```

### Statikus Elemzés

**PHPStan Max Level**-t használunk a legszigorúbb típusbiztonság érdekében:

```bash
# Statikus elemzés futtatása
./vendor/bin/sail composer stan
```

## 🔄 Változtatások Végrehajtása

### Branching Stratégia

1. **Hozz létre egy feature branch-et** a `main`-ből:
   ```bash
   git checkout -b feature/funkcio-neved
   ```

2. **Használj leíró branch neveket:**
   - `feature/add-user-export`
   - `bugfix/fix-login-redirect`
   - `docs/update-installation-guide`

### Commit Üzenetek

Kövesd a **Conventional Commits** specifikációt:

```
type(scope): leírás

# Példák:
feat(admin): felhasználó export funkció hozzáadása
fix(auth): bejelentkezési átirányítási hiba javítása
docs(readme): telepítési instrukciók frissítése
test(user): átfogó felhasználói CRUD tesztek hozzáadása
refactor(permission): szerepkör ellenőrzési logika optimalizálása
```

**Típusok:**
- `feat` - Új funkció
- `fix` - Hibajavítás
- `docs` - Dokumentáció változtatások
- `test` - Tesztek hozzáadása vagy frissítése
- `refactor` - Kód refaktorálás
- `style` - Kódstílus változtatások (formázás)
- `perf` - Teljesítmény javítások
- `chore` - Karbantartási feladatok

## ✅ Minőségi Szabványok

### ⚡ **Quality Check Script (Ajánlott Módszer)**

A legegyszerűbb és legbiztonságosabb módja a minőségbiztosítási ellenőrzéseknek:

```bash
# Teljes minőségbiztosítási ellenőrzés - FÜR MINDEN COMMIT ELŐTT!
./scripts/quality-check.sh

# Csak kódminőség ellenőrzés, tesztek kihagyása (gyorsabb)
./scripts/quality-check.sh --skip-tests

# Csak kódstílus javítás
./scripts/quality-check.sh --fix-only
```

> 🎯 **Best Practice:** Futtasd minden commit előtt, hogy elkerüld a CI/CD pipeline hibákat!

### 🔧 **Manuális Ellenőrzések**

**Minden hozzájárulásnak át kell mennie ezeken a minőségi kapukon:**

1. **Tesztek**: Minden teszt sikeres kell legyen
   ```bash
   ./vendor/bin/sail artisan test
   ```

2. **Kódstílus**: PSR-12-t kell követnie
   ```bash
   ./vendor/bin/sail composer lint      # Ellenőrzés
   ./vendor/bin/sail pint               # Automatikus javítás
   ```

3. **Statikus Elemzés**: PHPStan Max Level-en át kell mennie
   ```bash
   ./vendor/bin/sail composer stan
   ```

4. **Nem Breaking Changes**: Hacsak nem beszéltük meg kifejezetten

### 📋 **Ajánlott Munkafolyamat**

```bash
# 1. Fejlesztés/implementáció
# ... kód írása ...

# 2. Quality check futtatása
./scripts/quality-check.sh

# 3. Ha minden zöld, akkor commit & push
git add .
git commit -m "feat: új funkció hozzáadása"
git push origin feature/branch-nev

# 💡 Tipp: Új projekt esetén használd a quick-start scriptet:
# ./scripts/quick-start.sh
```

### 🚀 **Scaffolding Parancsok (ÚJ!)**

**Gyorsítsd fel a fejlesztést az új scaffolding parancsokkal:**

```bash
# Teljes CRUD rendszer egyetlen paranccsal
./vendor/bin/sail artisan make:boilerplate-resource Product

# Új szerepkör alapértelmezett jogosultságokkal  
./vendor/bin/sail artisan make:boilerplate-role editor

# Jogosultságok szinkronizálása
./vendor/bin/sail artisan boilerplate:setup-permissions

# Teljes fresh install fejlesztéshez
./vendor/bin/sail artisan boilerplate:fresh-install --seed
```

**📖 Részletes dokumentáció**: [Útmutató Gyűjtemény](docs/how-to-guides.md#🚀-boilerplate-scaffolding-parancsok-új)

### Tesztek Írása

- **Írj teszteket** új funkciókhoz
- **Frissítsd a teszteket** meglévő funkcionalitás módosításakor
- **Teszt lefedettség** nem csökkenhet
- **Használj leíró teszt neveket**:
  ```php
  /** @test */
  public function admin_can_create_user_with_valid_data(): void
  {
      // Teszt implementáció
  }
  ```

### Dokumentáció

- Frissítsd a releváns dokumentációt új funkciókhoz
- Adj **kód példákat** ahol hasznos
- Tartsd **naprakészen** a dokumentációt a kód változásokkal

## 📤 Változtatások Beküldése

### Pull Request Folyamat

1. **Győződj meg róla, hogy a branch-ed naprakész:**
   ```bash
   git fetch origin
   git rebase origin/main
   ```

2. **Futtasd le az összes minőségi ellenőrzést:**
   ```bash
   # Ajánlott: Quality check script használata
   ./scripts/quality-check.sh
   
   # Vagy manuálisan:
   ./vendor/bin/sail artisan test
   ./vendor/bin/sail composer lint
   ./vendor/bin/sail composer stan
   ```

3. **Push-old a branch-ed:**
   ```bash
   git push origin feature/funkcio-neved
   ```

4. **Hozz létre egy Pull Request-et** a következőkkel:
   - **Világos cím** a változtatás leírásával
   - **Részletes leírás** a mit és miért kérdésekre
   - **Link a kapcsolódó issue-khoz** (ha van)
   - **Képernyőképek** UI változtatásokhoz

### Pull Request Sablon

```markdown
## Leírás
Rövid leírás a változtatásokról

## Változtatás Típusa
- [ ] Hibajavítás
- [ ] Új funkció
- [ ] Dokumentáció frissítés
- [ ] Refaktorálás
- [ ] Teljesítmény javítás

## Tesztelés
- [ ] Minden teszt sikeres
- [ ] Új tesztek hozzáadva (ha szükséges)
- [ ] Manuális tesztelés elvégezve

## Minőségi Ellenőrzések
- [ ] Kódstílus (Pint) ✅
- [ ] Statikus elemzés (PHPStan) ✅
- [ ] Nincsenek breaking change-ek

## Képernyőképek (ha szükséges)
```

## 🐛 Issue Irányelvek

### Hibajelentések

**Hibajelentés létrehozása előtt:**
1. **Keress a meglévő issue-k között** duplikátumok elkerülésére
2. **Reprodukáld a hibát** minimális lépésekkel
3. **Teszteld a legfrissebb verzióval**

**Add meg a hibajelentésben:**
```markdown
**Hiba Leírása:**
A probléma világos leírása

**Reprodukálási Lépések:**
1. Első lépés
2. Második lépés
3. Harmadik lépés

**Várt Viselkedés:**
Mi kellene történjen

**Tényleges Viselkedés:**
Mi történik valójában

**Környezet:**
- PHP verzió: 8.3.x
- Laravel verzió: 12.x
- Böngésző (ha szükséges): Chrome 120
- OS: Ubuntu 22.04

**További Kontextus:**
Bármilyen más releváns információ
```

### Funkcióigények

**Funkció igénylése előtt:**
1. **Ellenőrizd, hogy már létezik-e** vagy tervezve van
2. **Fontold meg, hogy illeszkedik-e** a projekt hatókörébe
3. **Gondolj az implementáció** komplexitására

**Add meg a funkcióigényben:**
```markdown
**Funkció Leírása:**
A javasolt funkció világos leírása

**Használati Eset:**
Miért van szükség erre a funkcióra?

**Javasolt Megoldás:**
Hogyan kellene ezt implementálni?

**Fontolt Alternatívák:**
Bármilyen alternatív megközelítés?

**További Kontextus:**
Mockup-ok, példák vagy kapcsolódó funkciók
```

## 🏷️ Címkék és Mérföldkövek

A következő címkéket használjuk:

- `bug` - Valami nem működik
- `enhancement` - Új funkció vagy igény
- `documentation` - Dokumentáció javítások
- `good first issue` - Jó kezdőknek
- `help wanted` - Extra figyelem szükséges
- `question` - További információ kérése
- `wontfix` - Ezen nem fogunk dolgozni

## 🎯 Közreműködési Területek

Hogyan tudsz hozzájárulni? Fontold meg ezeket a területeket:

### 🌟 **Nagy Hatású**
- **Teszt lefedettség javítás**
- **Teljesítmény optimalizációk**
- **Biztonsági fejlesztések**
- **Dokumentáció javítások**

### 🔧 **Funkció Hozzáadások**
- **API hitelesítés** (Laravel Sanctum)
- **Haladó jogosultságok** (egyedi szerepkörök)
- **Többnyelvű támogatás** (további nyelvek)
- **Import/Export funkcionalitás**

### 🐛 **Hibajavítások**
- **Cross-browser kompatibilitás**
- **Mobil reszponzivitás**
- **Edge case kezelés**

### 📖 **Dokumentáció**
- **Tutorial fejlesztések**
- **Kód példák**
- **Video útmutatók**
- **Migrációs útmutatók**

## 💬 Segítség Kérése

**Segítségre van szükséged az induláshoz?**

- **GitHub Discussions** - Kérdezz és kérj segítséget
- **Issues** - Jelentsd a hibákat vagy kérj funkciókat
- **Kód Kommentek** - Jól dokumentált kódbázis

## 🎉 Elismerés

A közreműködők:
- **Fel lesznek sorolva a release-ekben** a hozzájárulásaikkal
- **Említve lesznek a dokumentációban** ahol megfelelő
- **Meghívást kapnak** a karbantartói csapatba (rendszeres közreműködők esetén)

---

**Köszönjük, hogy hozzájárulsz a Laravel Vállalati Boilerplate-hez!** 🚀

A hozzájárulásod segít jobbá tenni ezt a projektet mindenki számára a Laravel közösségben.
