# Documentation Search System

Ez a dokumentum magyarázza a Laravel Boilerplate beépített dokumentációs keresőrendszerét, amely Laravel Scout és Meilisearch technológiákat használ.

## 🔍 Áttekintés

A Documentation Search System egy teljes körű megoldás markdown dokumentumok indexeléséhez, kereséséhez és megjelenítéséhez. A rendszer automatikusan feldolgozza a `/docs` mappa tartalmát, és lehetővé teszi a valós idejű keresést egy modern, reszponzív felületen.

### Főbb jellemzők:
- **Full-text keresés** - Laravel Scout + Meilisearch
- **Valós idejű UI** - Livewire komponens billentyűzet navigációval
- **Markdown támogatás** - GitHub Flavored Markdown (GFM) táblázatok
- **Tartalomkiemelés** - Keresési kifejezések kiemelése
- **Dark mode támogatás** - Teljes sötét mód kompatibilitás
- **Automatikus szinkronizáció** - Quality check script integráció

## 🏗️ Architektúra

### Komponensek:
```
app/
├── Console/Commands/DocsImport.php      # Artisan parancs
├── Http/Controllers/DocsController.php  # Routing és logika
├── Livewire/DocSearch.php              # Keresőkomponens
└── Models/Documentation.php             # Eloquent modell

resources/views/
├── docs/
│   ├── index.blade.php                 # Főoldal keresővel
│   ├── category.blade.php              # Kategória lista
│   └── show.blade.php                  # Dokumentum megjelenítő
└── livewire/doc-search.blade.php       # Keresőkomponens UI

config/
├── scout.php                           # Laravel Scout konfig
└── docs.php                            # Dokumentáció beállítások
```

## ⚙️ Telepítés és Konfiguráció

### 1. Meilisearch Szolgáltatás

**Docker környezetben (Laravel Sail):**
```bash
# docker-compose.yml már tartalmazza a Meilisearch szolgáltatást
./vendor/bin/sail up -d meilisearch
```

**Manuális telepítés:**
```bash
# Ubuntu/Debian
curl -L https://install.meilisearch.com | sh
./meilisearch --master-key="your-master-key"
```

### 2. Laravel Scout Konfiguráció

**config/scout.php:**
```php
'driver' => env('SCOUT_DRIVER', 'meilisearch'),
'prefix' => '', // Üres prefix ajánlott
'meilisearch' => [
    'host' => env('MEILISEARCH_HOST', 'http://localhost:7700'),
    'key' => env('MEILISEARCH_KEY', null),
],
```

**.env beállítások:**
```bash
SCOUT_DRIVER=meilisearch
MEILISEARCH_HOST=http://localhost:7700
MEILISEARCH_KEY=your-master-key
```

### 3. Adatbázis Migration

```bash
php artisan migrate
```

A `documentations` tábla automatikusan létrejön a következő mezőkkel:
- `title` - Dokumentum címe
- `content` - HTML tartalom
- `category` - Kategória név
- `slug` - URL slug
- `url` - Teljes URL elérési út
- `meta` - JSON metaadatok

## 📝 Használat

### Dokumentumok Importálása

**Teljes import:**
```bash
php artisan docs:import --fresh
```

**Növekményes import:**
```bash
php artisan docs:import
```

**Quality check script (automatikus):**
```bash
./scripts/quality-check.sh
# vagy skip dokumentáció:
./scripts/quality-check.sh --skip-docs
```

### Keresés API

**Programatikus keresés:**
```php
use App\Models\Documentation;

// Egyszerű keresés
$results = Documentation::search('authentication')->get();

// Korlátozott keresés
$results = Documentation::search('API')->limit(10)->get();

// Kategória szűrés
$results = Documentation::search('testing')
    ->where('category', 'core')
    ->get();
```

**HTTP API:**
```bash
# GET /docs/search
curl "http://localhost:8080/docs/search?q=authentication&limit=5"

# JSON Response:
{
    "data": [
        {
            "title": "User Authentication & Permission System",
            "content": "...",
            "category": "core",
            "url": "/docs/core/authentication"
        }
    ],
    "total": 1
}
```

### Frontend Integráció

**Livewire komponens beágyazás:**
```blade
<!-- resources/views/docs/index.blade.php -->
@livewire('doc-search')
```

**Keresőbar billentyűzet navigáció:**
- **↑/↓** - Eredmények közötti navigáció
- **Enter** - Kiválasztott dokumentum megnyitása
- **Escape** - Eredmények bezárása

## 🎨 Testreszabás

### Markdown Renderelés

**Támogatott elemek:**
- Címsorok (H1-H6)
- Listák (rendezett és rendezetlen)
- Táblázatok (GFM kompatibilis)
- Kódblokkok szintaxis kiemelés nélkül
- Idézetek (blockquote)
- Linkek és képek

**CSS testreszabás:**
```css
/* resources/views/docs/show.blade.php */
.documentation-content {
    /* Alapértelmezett stílusok */
}

.documentation-content h1 { /* H1 stílus */ }
.documentation-content table { /* Táblázat stílus */ }
.documentation-content code { /* Kód stílus */ }
```

### Keresési UI Testreszabás

**Livewire komponens:**
```php
// app/Livewire/DocSearch.php
class DocSearch extends Component
{
    public string $query = '';
    public bool $showResults = false;
    public int $selectedIndex = -1;
    
    // Testreszabható metódusok...
}
```

**Blade template:**
```blade
<!-- resources/views/livewire/doc-search.blade.php -->
<div class="relative">
    <!-- Keresőmező testreszabás -->
    <!-- Eredmények dropdown testreszabás -->
</div>
```

## 📊 Teljesítmény Optimalizálás

### Meilisearch Beállítások

**Index konfigurálás:**
```php
// app/Models/Documentation.php
public function toSearchableArray(): array
{
    return [
        'title' => $this->title,
        'content' => strip_tags($this->content), // HTML tagek eltávolítása
        'category' => $this->category,
    ];
}

public function getScoutKey(): mixed
{
    return $this->id; // Egyszerű numerikus kulcs
}
```

**Bulk Import Optimalizálás:**
```bash
# Nagy mennyiségű dokumentum esetén
php artisan docs:import --fresh --chunk=100
```

### Laravel Cache

**Keresési eredmények cache-elése:**
```php
use Illuminate\Support\Facades\Cache;

$results = Cache::remember("docs_search_{$query}", 300, function () use ($query) {
    return Documentation::search($query)->limit(10)->get();
});
```

## 🧪 Tesztelés

### Funkcionális Tesztek

**Artisan parancs teszt:**
```bash
php artisan test --filter=DocsImportCommandTest
```

**Livewire komponens teszt:**
```bash
php artisan test --filter=DocSearchTest
```

**Controller teszt:**
```bash
php artisan test --filter=DocsControllerTest
```

**Teljes teszt suite:**
```bash
./scripts/quality-check.sh --skip-docs
```

### Manuális Tesztelés

**1. Dokumentáció elérhetőség:**
```bash
curl http://localhost:8080/docs
```

**2. Keresés funkcionalitás:**
```bash
curl "http://localhost:8080/docs/search?q=test"
```

**3. Meilisearch health check:**
```bash
curl http://localhost:7700/health
```

## 🔧 Hibaelhárítás

### Gyakori Problémák

**1. Meilisearch kapcsolódási hiba:**
```bash
# Ellenőrzés:
curl http://localhost:7700/health

# Újraindítás:
./vendor/bin/sail restart meilisearch
```

**2. Üres keresési eredmények:**
```bash
# Index újraépítése:
php artisan docs:import --fresh

# Scout index törlése:
php artisan scout:flush "App\Models\Documentation"
php artisan scout:import "App\Models\Documentation"
```

**3. Scout prefix problémák:**
```php
// config/scout.php - győződj meg róla:
'prefix' => '', // NE legyen space vagy spec. karakter!
```

**4. Markdown táblázatok nem jelennek meg:**
```bash
# Ellenőrizd a CommonMark extensions-t:
composer show league/commonmark
# Frissítés szükséges lehet
```

### Debug Információk

**Meilisearch státusz:**
```bash
# Index lista
curl http://localhost:7700/indexes

# Dokumentumok száma
curl http://localhost:7700/indexes/documentations/stats
```

**Laravel Debug:**
```php
// Tinker-ben:
php artisan tinker
> Documentation::search('test')->raw();
> app('scout')->engine()->search(new App\Models\Documentation, 'test');
```

## 📚 API Referencia

### DocsController Endpoints

**GET /docs**
- Dokumentációs főoldal keresővel

**GET /docs/search**
- Query params: `q` (keresési kifejezés), `limit` (eredmények száma)
- Response: JSON with data array

**GET /docs/{category}**
- Kategória alapú dokumentumok listája

**GET /docs/{category}/{slug}**
- Egyedi dokumentum megjelenítése
- Query param: `highlight` (keresési kifejezés kiemeléshez)

### Artisan Commands

**docs:import**
- `--fresh`: Teljes újraindexelés
- `--chunk`: Bulk import méret (alapértelmezés: 50)

**scout:flush/import**
- Scout specifikus index kezelés

## 🚀 Fejlesztői Jegyzetek

### Kiterjeszthetőség

**Új markdown extensionök:**
```php
// app/Console/Commands/DocsImport.php
private function setupMarkdownConverter(): void
{
    $environment = new Environment();
    $environment->addExtension(new CommonMarkCoreExtension());
    $environment->addExtension(new GithubFlavoredMarkdownExtension());
    // ÚJ EXTENSION ITT:
    $environment->addExtension(new YourCustomExtension());
    
    $this->markdownConverter = new MarkdownConverter($environment);
}
```

**Új keresési mezők:**
```php
// app/Models/Documentation.php
public function toSearchableArray(): array
{
    return [
        'title' => $this->title,
        'content' => strip_tags($this->content),
        'category' => $this->category,
        'tags' => $this->meta['tags'] ?? [], // ÚJ MEZŐ
    ];
}
```

**Custom Livewire események:**
```php
// app/Livewire/DocSearch.php
public function selectResult(string $url): void
{
    $this->dispatch('doc-selected', url: $url); // Custom event
    $highlightParam = $this->query ? '?highlight=' . urlencode($this->query) : '';
    $this->redirect($url . $highlightParam);
}
```

### Jövőbeli Fejlesztések

**Tervezett funkciók:**
- [ ] **Szinonimatár** - Keresési szinonimák
- [ ] **Kategóriaszűrők** - UI-ban elérhető szűrők  
- [ ] **Bookmark rendszer** - Kedvenc dokumentumok
- [ ] **Verziózás** - Dokumentumok verziókövetése
- [ ] **Analytics** - Keresési statisztikák
- [ ] **PDF export** - Dokumentumok PDF-be exportálása

## 📄 Licenc és Közreműködés

Ez a funkció a Laravel Boilerplate része, MIT licenc alatt. Közreműködés üdvözölt a [CONTRIBUTING.md](../../CONTRIBUTING.md) irányelvei szerint.

### Hasznos linkek:
- [Laravel Scout dokumentáció](https://laravel.com/docs/scout)
- [Meilisearch dokumentáció](https://docs.meilisearch.com/)
- [CommonMark dokumentáció](https://commonmark.thephpleague.com/)
- [Livewire dokumentáció](https://livewire.laravel.com/)
