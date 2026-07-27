# Zadania rekrutacyjne — Full Stack Developer (Laravel + Vue.js)

Realizacja zestawu zadań z `docs/zadania_rekrutacyjne_fullstack_v2.docx`:
Zadania 1–4 (obowiązkowe) oraz 5a/5b (bonus) — wszystkie zaimplementowane.

Stack: Laravel 13 (PHP 8.3), Sanctum (SPA/cookie auth), Vue 3, Bootstrap 5,
Vite, SQLite (dev), PHPUnit.

## Szybki start

```bash
composer install
npm install

cp .env.example .env   # jeśli .env nie istnieje
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed

npm run build           # lub: npm run dev (w osobnym terminalu, dla HMR)
php artisan serve
```

Aplikacja: http://127.0.0.1:8000/ (przekierowuje na `/notes`).
Konto testowe utworzone przez seeder: **test@example.com / password**.

Uruchomienie testów:

```bash
php artisan test
```

Przykłady modułu kolejki z Zadania 3 (czysty JS, niezależny od Laravel):

```bash
cd task-queue
node examples.js
```

Aby zobaczyć realny efekt Zadania 5b (e-mail po utworzeniu notatki), po
utworzeniu notatki w UI odpal worker kolejki (w osobnym terminalu — domyślnie
`QUEUE_CONNECTION=database`):

```bash
php artisan queue:work
```

E-mail (z `MAIL_MAILER=log`) wyląduje w `storage/logs/laravel.log`.

## Struktura zadań i gdzie ich szukać

| Zadanie | Zakres | Lokalizacja |
|---|---|---|
| 1 | Sanctum auth, REST API `notes`, walidacja, paginacja, Policy, testy | `app/Http/Controllers/Api`, `app/Http/Requests`, `app/Policies`, `routes/api.php`, `routes/web.php` (auth), `tests/Feature/NoteApiTest.php`, `tests/Feature/AuthApiTest.php` |
| 2 | Repository + Service layer, limit 100 notatek/user | `app/Repositories`, `app/Services/NoteService.php` |
| 3 | `TaskQueue` — kolejka async z priorytetami | `task-queue/TaskQueue.js`, `task-queue/examples.js` |
| 4 | Widget Vue 3 (`NoteManager` + `NoteForm`) w Blade | `resources/js/components`, `resources/views/notes.blade.php` |
| 5a | Powiadomienia (backend + `NotificationBell.vue`) | `app/Http/Controllers/Api/NotificationController.php`, `resources/js/components/NotificationBell.vue`, `tests/Feature/NotificationApiTest.php` |
| 5b | `NoteCreated` event → kolejkowany listener → Mailable | `app/Events/NoteCreated.php`, `app/Listeners/SendNoteCreatedEmail.php`, `app/Mail/NoteCreatedMail.php` |

## Decyzje projektowe i uzasadnienia

**Uwierzytelnianie: Sanctum w trybie SPA (cookie + CSRF), nie tokeny Bearer.**
Widget Vue jest osadzony w tej samej domenie co backend (widok Blade), więc
zamiast tokenów użyto sesji + ciasteczka `XSRF-TOKEN` — dokładnie tak, jak
sugerował wymóg `withCredentials`/`withXSRFToken` w Zadaniu 4. Trasy
`register`/`login`/`logout`/`me` celowo leżą w `routes/web.php` (middleware
`web`), a nie w `routes/api.php` — dzięki temu sesja i CSRF zawsze działają,
niezależnie od nagłówka `Origin`/`Referer` (który dla `api.php` decyduje,
czy Sanctum potraktuje żądanie jako „stateful”). Endpointy zasobów
(`/api/notes`, `/api/notifications`) zostały w `api.php`, chronione
`auth:sanctum`.

**Warstwa autoryzacji: Policy + repozytorium zawężone do właściciela.**
`NotePolicy` (view/update/delete) jest egzekwowana w kontrolerze / w
`UpdateNoteRequest::authorize()`, a `EloquentNoteRepository` dodatkowo
zawęża każde zapytanie do `user_id` zalogowanego użytkownika. To zamierzone
podwójne zabezpieczenie: Policy daje poprawny kod HTTP (403) przy próbie
dostępu do cudzej notatki (testowane w `NoteApiTest`), a repozytorium
gwarantuje izolację danych nawet gdyby ktoś pominął warstwę autoryzacji
wyżej.

**Limit 100 notatek na użytkownika** żyje w `NoteService`, a nie w
repozytorium ani w Form Request — to reguła biznesowa, nie walidacja
formatu danych ani szczegół dostępu do danych, więc naturalnie należy do
warstwy serwisu.

**`NoteCreated` event jest emitowany w `NoteService::create()`**, a nie w
kontrolerze — zgodnie ze schematem z treści zadania 5b. Dzięki temu
notatka utworzona z dowolnego miejsca w aplikacji (np. przyszły import,
komenda artisan) również wywoła powiadomienie e-mail, bez duplikowania
logiki w kontrolerach.

**Dlaczego `SendNoteCreatedEmail` implementuje `ShouldQueue`, a nie działa
synchronicznie:** wysyłka e-maila to operacja I/O do zewnętrznego serwisu
(SMTP/API dostawcy poczty), która może trwać setki milisekund do kilku
sekund i może się nie powieść (timeout, przejściowa awaria dostawcy).
Wykonanie jej synchronicznie w cyklu żądanie-odpowiedź `POST /api/notes`
oznaczałoby, że użytkownik czeka na odpowiedź API dokładnie tyle, ile
trwa wysyłka maila, a awaria serwera pocztowego zwróciłaby błąd 500 mimo
że notatka została poprawnie zapisana. `ShouldQueue` przenosi tę pracę do
kolejki (`QUEUE_CONNECTION=database` w `.env` dla dev) — endpoint
odpowiada natychmiast po zapisaniu notatki, a wysyłka maila (wraz z
automatycznymi retry przy błędzie) dzieje się w tle, obsługiwana przez
`php artisan queue:work`.

**Bootstrap zamiast domyślnego Tailwinda ze szkieletu Laravela** — treść
zadania 4 i 5a jawnie odwołuje się do klas Bootstrapa (`card`, `btn`,
`bi-bell` itd.), więc scaffold Tailwind/Vite z `laravel/laravel` został
zdjęty na rzecz `bootstrap` + `bootstrap-icons` (paczki npm, bez CDN).

**Widget ma realną paginację (Prev/Next + numery stron)**, jeden do
jednego z paginacją API z Zadania 1 (15 na stronę) — `NoteManager.vue`
pobiera po jednej stronie na raz i renderuje kontrolki Bootstrapa
(`pagination`). Filtrowanie po tytule (computed property, bez
dodatkowego zapytania do API — wymóg Zadania 4) działa w obrębie
aktualnie wczytanej strony, nie całej kolekcji notatek.

**`config/sanctum.php` dodaje `Sanctum::currentRequestHost()` do listy
stateful domains.** Domyślna konfiguracja Sanctum na sztywno rozpoznaje
tylko `127.0.0.1:8000` jako "swój" frontend — każdy inny port lokalny
(np. `php artisan serve --port=8975`) powodował, że sesja logowania
działała (bo `routes/web.php` zawsze ma `StartSession`), ale
`/api/notes` i `/api/notifications` zwracały 401, bo Sanctum nie
rozpoznawał żądania jako pochodzącego z zaufanego frontendu. Ten wpis
ufa aktualnemu hostowi żądania na dowolnym porcie — właściwe rozwiązanie
do lokalnego developmentu z niestandardowym portem.

## Weryfikacja

- `php artisan test` — 17 testów PHPUnit (rejestracja/logowanie, CRUD
  notatek, izolacja danych między użytkownikami, walidacja 422, limit 100
  notatek, powiadomienia).
- `node task-queue/examples.js` — trzy scenariusze `TaskQueue`
  (priorytety + concurrency, odporność na błędy, dokładanie zadań w locie).
- Cały widget Vue (logowanie, CRUD notatek, toggle pin, dzwonek
  powiadomień z odczytem/„oznacz wszystkie") zweryfikowany end-to-end w
  headless Chromium.
