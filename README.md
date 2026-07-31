<p align="center">
  <span style="display:inline-grid;place-items:center;width:56px;height:56px;border-radius:12px;background:#2563eb;color:#fff;font-weight:bold;font-size:24px;">E</span>
</p>

<h1 align="center">EUROFOND OS</h1>

## O projekte

EUROFOND OS je interný nástroj pre správu eurofondových projektov — sleduje termíny, dokumenty, rozhodnutia a stav gate items naprieč projektovým portfóliom. Cieľom je nahradiť roztrieštenú komunikáciu a tabuľky jednotným prehľadom, ktorý tímu ukáže, čo treba urobiť, kedy a kým.

## Rýchly štart

```bash
composer install
npm install
npm run build
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
php artisan serve
```

Ak máte projekt zaparkovaný cez [Laravel Valet](https://laravel.com/docs/valet), aplikácia beží na adrese:

```
http://eurofond-os.test
```

V opačnom prípade použite adresu, ktorú vypíše `php artisan serve` (predvolene `http://127.0.0.1:8000`).

## Demo prihlásenie

Po spustení `php artisan migrate:fresh --seed` sa vytvorí demo účet:

- **E-mail:** `denis@eurofond.test`
- **Heslo:** `password`

## Testy

```bash
./vendor/bin/pest
```
