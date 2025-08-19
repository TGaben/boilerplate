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

### Gyors Beállítás

```bash
# 1. Fork-old és klónozd a repository-t
git clone https://github.com/TE_FELHASZNALONEVED/boilerplate.git
cd boilerplate

# 2. Telepítsd a függőségeket
composer install
npm install

# 3. Állítsd be a környezetet
cp .env.example .env

# 4. Indítsd el a fejlesztői környezetet
./vendor/bin/sail up -d

# 5. Inicializáld az adatbázist
./vendor/bin/sail artisan migrate --seed

# 6. Build-eld az asset-eket
./vendor/bin/sail npm run dev

# 7. Futtasd a teszteket, hogy minden működik
./vendor/bin/sail artisan test
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

**PHPStan Level 5**-öt használunk statikus elemzéshez:

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

### Beküldés Előtt

**Minden hozzájárulásnak át kell mennie ezeken a minőségi kapukon:**

1. **Tesztek**: Minden teszt sikeres kell legyen
   ```bash
   ./vendor/bin/sail artisan test
   ```

2. **Kódstílus**: PSR-12-t kell követnie
   ```bash
   ./vendor/bin/sail composer lint
   ```

3. **Statikus Elemzés**: PHPStan Level 5-ön át kell mennie
   ```bash
   ./vendor/bin/sail composer stan
   ```

4. **Nem Breaking Changes**: Hacsak nem beszéltük meg kifejezetten

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
