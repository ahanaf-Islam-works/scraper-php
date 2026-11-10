# Laravel Practical Guide: Reading and Changing a Real API Project

This guide teaches you to **read, trace and modify** an existing Laravel API. Every example comes from the
Indeed/Airwork scraping project (scraping jobs, applicant ingestion, exports, scheduling).

It assumes you already know JS/TS and backend basics. It does not teach PHP from zero. It covers only the
PHP and Laravel pieces you will actually meet in this codebase.

> Docs links point to the latest Laravel docs (`laravel.com/docs/<topic>` redirects to the current version).
> Check the version in `composer.json` (`"laravel/framework"`) if something differs.

> **Implementation exercises:** start with
> [Part 1: Questions](#part-1-questions) (Enums/Types → Data Model) at the end of this file.

---

## Table of Contents

0. [Your first CRUD API (start here)](#0-your-first-crud-api-start-here) — [https://laravel.com/docs/routing](https://laravel.com/docs/routing)
1. [The big picture: one request, end to end](#1-the-big-picture-one-request-end-to-end) — [https://laravel.com/framework/docs/12.x/lifecycle](https://laravel.com/framework/docs/12.x/lifecycle)
2. [Project layout: where things live](#2-project-layout-where-things-live) — [https://laravel.com/framework/docs/12.x/structure](https://laravel.com/framework/docs/12.x/structure)
3. [PHP syntax you must recognise](#3-php-syntax-you-must-recognise) — [https://www.php.net/manual/en/language.types.declarations.php](https://www.php.net/manual/en/language.types.declarations.php)
4. [Routing](#4-routing) — [https://laravel.com/framework/docs/12.x/routing](https://laravel.com/framework/docs/12.x/routing)
5. [Middleware](#5-middleware) — [https://laravel.com/framework/docs/12.x/middleware](https://laravel.com/framework/docs/12.x/middleware)
6. [Controllers](#6-controllers) — [https://laravel.com/framework/docs/12.x/controllers](https://laravel.com/framework/docs/12.x/controllers)
7. [Form Requests: validation and authorization](#7-form-requests-validation-and-authorization) — [https://laravel.com/framework/docs/12.x/validation](https://laravel.com/framework/docs/12.x/validation)
8. [Service container, dependency injection, providers](#8-service-container-dependency-injection-providers) — [https://laravel.com/framework/docs/12.x/container](https://laravel.com/framework/docs/12.x/container)
9. [Facades](#9-facades) — [https://laravel.com/framework/docs/12.x/facades](https://laravel.com/framework/docs/12.x/facades)
10. [Services (your business logic layer)](#10-services-your-business-logic-layer) — [https://laravel.com/framework/docs/12.x/providers](https://laravel.com/framework/docs/12.x/providers)
11. [Enums](#11-enums) — [https://www.php.net/manual/en/language.enumerations.php](https://www.php.net/manual/en/language.enumerations.php)
12. [Eloquent models](#12-eloquent-models) — [https://laravel.com/framework/docs/12.x/eloquent](https://laravel.com/framework/docs/12.x/eloquent)
13. [Relationships and eager loading](#13-relationships-and-eager-loading) — [https://laravel.com/framework/docs/12.x/eloquent-relationships](https://laravel.com/framework/docs/12.x/eloquent-relationships)
14. [Migrations](#14-migrations) — [https://laravel.com/framework/docs/12.x/migrations](https://laravel.com/framework/docs/12.x/migrations)
15. [API Resources](#15-api-resources) — [https://laravel.com/framework/docs/12.x/eloquent-resources](https://laravel.com/framework/docs/12.x/eloquent-resources)
16. [Jobs and queues](#16-jobs-and-queues) — [https://laravel.com/framework/docs/12.x/queues](https://laravel.com/framework/docs/12.x/queues)
17. [Events and listeners](#17-events-and-listeners) — [https://laravel.com/framework/docs/12.x/events](https://laravel.com/framework/docs/12.x/events)
18. [Console commands and the scheduler](#18-console-commands-and-the-scheduler) — [https://laravel.com/framework/docs/12.x/artisan](https://laravel.com/framework/docs/12.x/artisan)
19. [Config and environment](#19-config-and-environment) — [https://laravel.com/framework/docs/12.x/configuration](https://laravel.com/framework/docs/12.x/configuration)
20. [Testing](#20-testing) — [https://laravel.com/framework/docs/12.x/testing](https://laravel.com/framework/docs/12.x/testing)
21. [Filament (admin UI) in one page](#21-filament-admin-ui-in-one-page) — [https://filamentphp.com/docs](https://filamentphp.com/docs)
22. [Walkthrough: tracing the scraper callback](#22-walkthrough-tracing-the-scraper-callback) — [https://laravel.com/framework/docs/12.x/lifecycle](https://laravel.com/framework/docs/12.x/lifecycle)
23. [Walkthrough: making a real change (image-key resolver)](#23-walkthrough-making-a-real-change-image-key-resolver) — [https://laravel.com/framework/docs/12.x/container](https://laravel.com/framework/docs/12.x/container)
24. [Debugging toolbox](#24-debugging-toolbox) — [https://laravel.com/framework/docs/12.x/artisan#tinker](https://laravel.com/framework/docs/12.x/artisan#tinker)
25. [Common gotchas](#25-common-gotchas) — [https://laravel.com/framework/docs/12.x/errors](https://laravel.com/framework/docs/12.x/errors)
26. [Study plan and exercises](#26-study-plan-and-exercises) — [https://laravel.com/framework/docs/12.x/testing](https://laravel.com/framework/docs/12.x/testing)
27. [Documentation index](#27-documentation-index) — [https://laravel.com/framework/docs/12.x/documentation](https://laravel.com/framework/docs/12.x/documentation)

---

## 0. Your first CRUD API (start here)

This is the smallest useful Laravel API you can build. It exercises the whole path:

```
HTTP request → route → FormRequest → controller → service → model → database → JSON response
```

You will build two endpoints:

| Method | URL                     | What it does                                                  |
| ------ | ----------------------- | ------------------------------------------------------------- |
| `GET`  | `/api/messages/welcome` | Returns a friendly message with an emoji added by the service |
| `POST` | `/api/messages`         | Validates `message`, saves it, and returns the saved row      |

"CRUD" means **C**reate, **R**ead, **U**pdate, **D**elete. This first exercise covers **Create** and a small
**Read**. The exercises at the end show how to finish the rest.

An API has no Blade view. Its "view" is the **JSON response** returned by the controller. So this is still MVC,
but the V is JSON instead of HTML.

### 0.1 Make sure API routes are enabled

In a fresh Laravel 11+ app, `routes/api.php` may not exist. Either run:

```bash
php artisan install:api
```

or create `routes/api.php` yourself and make sure `bootstrap/app.php` loads it:

```php
->withRouting(
    web: __DIR__.'/../routes/web.php',
    api: __DIR__.'/../routes/api.php',
    commands: __DIR__.'/../routes/console.php',
    health: '/up',
)
```

Routes in `routes/api.php` automatically get the `/api` prefix. That is why the controller routes below are
written as `messages`, but the real URLs are `/api/messages` and `/api/messages/welcome`.

### 0.2 Generate the files

```bash
php artisan make:model Message -m
php artisan make:request StoreMessageRequest
php artisan make:class Services/MessageService
php artisan make:controller Api/MessageController
```

What each file is:

| File                  | MVC role   | Job                                            |
| --------------------- | ---------- | ---------------------------------------------- |
| `Message`             | Model      | reads and writes the `messages` table          |
| `MessageService`      | Service    | business logic: welcome text and persistence   |
| `MessageController`   | Controller | receives HTTP, calls the service, returns JSON |
| `StoreMessageRequest` | Request    | validates input before the controller runs     |
| migration             | Schema     | creates the `messages` table                   |

### 0.3 Migration: create the table

Open the new `database/migrations/*_create_messages_table.php` and replace `up()` with:

```php
public function up(): void
{
    Schema::create('messages', function (Blueprint $table): void {
        $table->id();
        $table->string('message');
        $table->timestamps();
    });
}
```

`id` and `message` are your two data columns. `timestamps()` adds `created_at` and `updated_at`, which Eloquent
maintains automatically.

Run the migration:

```bash
php artisan migrate
```

### 0.4 Model: talk to the table

`app/Models/Message.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = ['message'];
}
```

- The class name `Message` maps to the table `messages` automatically.
- `$fillable` is the mass-assignment allow-list. `create(['message' => ...])` works because `message` is listed.
  Sending an unlisted column would fail instead of silently writing it.

### 0.5 Service: keep business logic out of the controller

`app/Services/MessageService.php`:

```php
<?php

namespace App\Services;

use App\Models\Message;

class MessageService
{
    public function welcomeMessage(): string
    {
        return $this->withEmoji('you learned how to create an api yeah!!!!!');
    }

    public function create(string $message): Message
    {
        return Message::query()->create([
            'message' => $message,
        ]);
    }

    private function withEmoji(string $message): string
    {
        return $message.' 🎉';
    }
}
```

Why a service instead of putting this in the controller?

| Controller                          | Service                                            |
| ----------------------------------- | -------------------------------------------------- |
| HTTP only: request in, response out | Business rules: welcome decoration and persistence |
| Does not call Eloquent directly     | Can be called from a command, job, or test         |

If the greeting or the create rules change tomorrow, only `MessageService` changes. The controller stays the same.
The POST endpoint stores exactly what the user sent; only the welcome endpoint decorates its text.

### 0.6 Form Request: validate before the controller runs

`app/Http/Requests/StoreMessageRequest.php`:

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'message' => ['required', 'string', 'max:255'],
        ];
    }
}
```

- `authorize()` returns `true` because this tiny endpoint is public. Returning `false` would produce `403`.
- `rules()` says: the request must contain `message`, it must be a string, and it can be at most 255 characters.
- On failure Laravel throws a validation exception and returns `422` JSON. The controller never runs.
- `$request->validated()` in the controller returns only the keys that passed validation.

### 0.7 Controller: the HTTP entry point

`app/Http/Controllers/Api/MessageController.php`:

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMessageRequest;
use App\Services\MessageService;
use Illuminate\Http\JsonResponse;

class MessageController extends Controller
{
    public function __construct(private MessageService $service) {}

    public function welcome(): JsonResponse
    {
        return response()->json([
            'message' => $this->service->welcomeMessage(),
        ]);
    }

    public function store(StoreMessageRequest $request): JsonResponse
    {
        $message = $this->service->createMessage($request->validated('message'));

        return response()->json([
            'message' => 'Message saved',
            'data' => [
                'id' => $message->id,
                'message' => $message->message,
            ],
        ], 201);
    }
}
```

What Laravel does behind the scenes:

1. The container reads the constructor type hint and injects `MessageService` automatically.
2. For `store()`, Laravel sees `StoreMessageRequest`, builds it, runs `authorize()`, then `rules()`.
3. If validation passes, the action runs. If it fails, Laravel returns `422` and the action is skipped.
4. `response()->json(..., 201)` sends JSON with the `201 Created` status.

Return only the fields the client needs (`id`, `message`) instead of the whole model. That keeps the response
stable if you add internal columns later.

### 0.8 Routes: connect URLs to controller methods

`routes/api.php`:

```php
<?php

use App\Http\Controllers\Api\MessageController;
use Illuminate\Support\Facades\Route;

Route::get('messages/welcome', [MessageController::class, 'welcome']);
Route::post('messages', [MessageController::class, 'store']);
```

- `[MessageController::class, 'welcome']` means "call the `welcome` method on this controller".
- The `/api` prefix comes from `bootstrap/app.php`, so you do not repeat it here.
- The real URLs are `GET /api/messages/welcome` and `POST /api/messages`.

### 0.9 Try it

```bash
php artisan migrate
php artisan route:list --path=api/messages
php artisan serve
```

In another terminal:

```bash
BASE=http://127.0.0.1:8000

# Read: welcome message + emoji from the service
curl "$BASE/api/messages/welcome"

# Create: validate, save to the database, return 201
curl -i -X POST "$BASE/api/messages" \
  -H 'Accept: application/json' \
  -H 'Content-Type: application/json' \
  -d '{"message":"I created my first row"}'

# Validation failure: message is missing, so Laravel returns 422
curl -i -X POST "$BASE/api/messages" \
  -H 'Accept: application/json' \
  -H 'Content-Type: application/json' \
  -d '{}'
```

Expected responses:

```json
{ "message": "you learned how to create an api yeah!!!!! 🎉" }
```

```json
{
    "message": "Message saved",
    "data": {
        "id": 1,
        "message": "I created my first row"
    }
}
```

```json
{
    "message": "The message field is required.",
    "errors": {
        "message": ["The message field is required."]
    }
}
```

> If you have already applied the admin-key middleware from [section 5](#5-middleware) to API routes, add
> `-H 'X-Admin-Key: your-key'` to every request. Otherwise those routes return `401` before reaching the controller.

Check that the row really exists:

```bash
php artisan tinker --execute 'App\Models\Message::query()->latest("id")->first();'
```

### 0.10 Trace the two requests

```
GET /api/messages/welcome
  Route → MessageController@welcome
        → MessageService::welcomeMessage()
        → response()->json([...]) → 200
```

```
POST /api/messages
  Route → StoreMessageRequest (authorize + validate)
        → MessageController@store
        → MessageService::create()
        → Message model → INSERT INTO messages
        → response()->json([...], 201)
```

This is the same flow used by the big scraping project later in this guide. The names get bigger, but the path
is identical.

### 0.11 Finish CRUD

So far you have **C**reate and a small **R**ead. Add the rest without breaking the rule from section 0.5:
the controller stays thin and the database work lives in `MessageService`. The path is still
`route → FormRequest → controller → service → model → database → JSON`.

Try each step yourself first, then compare with the code below.

#### 1. Add the read, update, and delete methods to the service

Add these methods to `app/Services/MessageService.php` (and import `Illuminate\Database\Eloquent\Collection`):

```php
/** @return Collection<int, Message> */
public function all(): Collection
{
    return Message::query()->latest('id')->get();
}

public function update(Message $message, string $messageText): Message
{
    $message->update(['message' => $messageText]);

    return $message;
}

public function delete(Message $message): void
{
    $message->delete();
}
```

#### 2. Add the matching controller actions

Add these methods to `MessageController` (and import `App\Models\Message` and `Illuminate\Http\Response`):

```php
public function index(): JsonResponse
{
    return response()->json([
        'data' => $this->service->all(),
    ]);
}

public function show(Message $message): JsonResponse
{
    return response()->json([
        'data' => $message,
    ]);
}

public function update(StoreMessageRequest $request, Message $message): JsonResponse
{
    $message = $this->service->update($message, $request->validated('message'));

    return response()->json([
        'message' => 'Message updated',
        'data' => $message,
    ]);
}

public function destroy(Message $message): Response
{
    $this->service->delete($message);

    return response()->noContent();   // 204
}
```

`show()`, `update()`, and `destroy()` rely on route-model binding: Laravel sees `Message $message` and the
`{message}` route parameter, loads that row, and returns `404` if it does not exist. The names must match.
We reuse `StoreMessageRequest` for the update because the validation rule is identical; in a bigger app you
would create an `UpdateMessageRequest`.

#### 3. Register the routes (order matters)

`routes/api.php`:

```php
<?php

use App\Http\Controllers\Api\MessageController;
use Illuminate\Support\Facades\Route;

// Keep "welcome" BEFORE "{message}" or the wildcard will swallow it.
Route::get('messages/welcome', [MessageController::class, 'welcome']);

Route::get('messages', [MessageController::class, 'index']);
Route::post('messages', [MessageController::class, 'store']);
Route::get('messages/{message}', [MessageController::class, 'show']);
Route::match(['put', 'patch'], 'messages/{message}', [MessageController::class, 'update']);
Route::delete('messages/{message}', [MessageController::class, 'destroy']);
```

Laravel matches routes top to bottom. If `messages/{message}` were declared first, a request to
`GET /api/messages/welcome` would be read as "show the message whose id is `welcome`" and return `404`.
That is the same trap as `/status` before `/{scrapingJob}` in [section 4](#4-routing).

`Route::match(['put', 'patch'], ...)` registers **one** route that answers both verbs, so
`php artisan route:list --path=api/messages` shows six entries: welcome, index, store, show, update, destroy.

#### 4. Try the new endpoints

```bash
BASE=http://127.0.0.1:8000

# Read all
curl "$BASE/api/messages"

# Read one
curl "$BASE/api/messages/1"

# Update (PUT and PATCH both hit the same route)
curl -i -X PATCH "$BASE/api/messages/1" \
  -H 'Accept: application/json' \
  -H 'Content-Type: application/json' \
  -d '{"message":"I updated my first row"}'

# Delete -> 204 with an empty body
curl -i -X DELETE "$BASE/api/messages/1"

# The row is gone -> 404
curl -i -H 'Accept: application/json' "$BASE/api/messages/1"
```

Then move on to [section 1](#1-the-big-picture-one-request-end-to-end) and read the same concepts applied to a
real project.

---

## 1. The big picture: one request, end to end

```
HTTP request
   │
   ▼
public/index.php              ← the only file the web server runs
   │
   ▼
bootstrap/app.php             ← builds the app: routes, global middleware, scheduler, exceptions
   │
   ▼
Global middleware             ← EnsureJsonIsValid, SetLocaleFromHeader, AssignRequestId
   │
   ▼
Router                        ← matches URL + method to a route in routes/*.php
   │
   ▼
Route middleware              ← e.g. 'protected' (authentication)
   │
   ▼
Route-model binding           ← {scrapingJob} → ScrapingJob row (404 if missing)
   │
   ▼
FormRequest                   ← authorize() then rules(); fails → 403 / 422 and the controller never runs
   │
   ▼
Controller method             ← thin: calls a service
   │
   ▼
Service                       ← business logic; uses Models, Jobs, Cache, Log
   │
   ▼
Model (Eloquent) → Database
   │
   ▼
API Resource → JSON response  ← travels back out through middleware
```

Slow work leaves this path through a queue:

```
Service → SyncIngestedApplicationJob::dispatch(...) → queue (Redis/SQS/DB) → queue worker → Job::handle()
```

Scheduled work enters from the side:

```
cron every minute → php artisan schedule:run → scraping:dispatch-companies → ScrapingJobService
```

**Mental model:** Laravel is a pipeline of small, single-purpose classes. A _container_ creates them and
injects what they need. You mostly write the small classes and let Laravel call them.

Docs: [Request lifecycle](https://laravel.com/docs/lifecycle) · [Structure](https://laravel.com/docs/structure)

---

## 2. Project layout: where things live

```
app/
  Console/Commands/     Artisan commands (ScrapingDispatchCompanies)
  Enums/                PHP enums (ScrapingSpiderEnum, ...)
  Events/               Event classes (EmailReceived)
  Listeners/            Reactions to events (DispatchAirWorkScrapingForCompany)
  Filament/             Admin panel resources
  Http/
    Controllers/Api/    HTTP entry points
    Middleware/         Custom middleware
    Requests/           FormRequests (validation + authorization)
    Resources/          JSON serializers
  Jobs/                 Queued background work
  Models/               Eloquent models
  Providers/            AppServiceProvider (container wiring)
  Services/             Business logic (plain classes)
bootstrap/app.php       Routing, middleware, schedule, exception config
config/                 *.php config files (read env vars here only)
database/
  migrations/           Schema versions
  factories/            Fake data for tests
routes/
  api.php               Includes routes/api/*.php
  console.php           Closure-based artisan commands
tests/Feature, tests/Unit
.env                    Per-environment values (never committed)
```

Rule of thumb for "where do I look?":

| I want to find...             | Look in                                |
| ----------------------------- | -------------------------------------- |
| What URL runs what code       | `routes/api.php`, `routes/api/*.php`   |
| What gets validated           | `app/Http/Requests/`                   |
| The real logic                | `app/Services/`                        |
| Table columns and relations   | `app/Models/` + `database/migrations/` |
| JSON response shape           | `app/Http/Resources/`                  |
| What runs daily               | `bootstrap/app.php` (schedule section) |
| How interfaces map to classes | `app/Providers/AppServiceProvider.php` |

---

### 1. Set `.env` in your Laravel project

The compose file reads the database name, user and password from this same `.env`:

```dotenv
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5433
DB_DATABASE=scrape_tracker
DB_USERNAME=postgres
DB_PASSWORD=secret
```

- `DB_HOST=127.0.0.1` is used because Laravel runs on your computer and Postgres is reachable on a port of your computer.
- `DB_PORT=5433` matches the left side of `"5433:5432"` in the compose file.

### 2. Start Postgres

```bash
docker compose up -d
docker compose ps        # postgres-db should say "running"
```

### 3. Connect Laravel to it

```bash
php artisan config:clear
php artisan db:show      # prints the connection details if it works
php artisan migrate
```

If `db:show` fails with `could not find driver`, your PHP is missing the Postgres extension. Check with `php -m | grep pgsql`. You need `pdo_pgsql` (on Ubuntu: `sudo apt install php-pgsql`).

### 4. Look inside the database (optional)

```bash
docker exec -it postgres-db psql -U postgres -d scrape_tracker
```

Use the username and database name from your `.env`. Inside `psql`:

```sql
\dt                      -- list tables
SELECT * FROM users;
\q                       -- quit
```

### Common errors

| Error                            | Cause                                                                    |
| -------------------------------- | ------------------------------------------------------------------------ |
| `Connection refused`             | Container not running (`docker compose up -d`) or wrong `DB_PORT`        |
| `password authentication failed` | `.env` password differs from the one the database was first created with |
| `database ... does not exist`    | `DB_DATABASE` changed after first start                                  |

The password and database name are set only the first time the volume is created. If you change them later, reset with `docker compose down -v` (this deletes the data) and run `docker compose up -d` again.

Files are in /home/claude:

- docker-compose.yml

---

## 3. PHP syntax you must recognise

### 3.1 The symbols cheat-sheet

| Symbol     | Meaning                                       | Example                  | JS/TS cousin           |
| ---------- | --------------------------------------------- | ------------------------ | ---------------------- |
| `$x`       | variable (always has `$`)                     | `$job`                   | `job`                  |
| `->`       | instance property/method                      | `$job->status`           | `job.status`           |
| `?->`      | null-safe access                              | `$job->company?->name`   | `job.company?.name`    |
| `::`       | static member, constant, enum case, `::class` | `ScrapingJob::find(1)`   | `Class.staticMethod()` |
| `=>`       | array key → value; also `match` arms          | `['a' => 1]`             | `{ a: 1 }`             |
| `??`       | null coalescing                               | `$data['records'] ?? []` | `??`                   |
| `\`        | namespace separator                           | `App\Models\Job`         | `/` in paths           |
| `...`      | spread / variadic                             | `fn (...$args) => ...`   | `...args`              |
| `.`        | string concatenation                          | `'Hello ' . $name`       | `+`                    |
| `[]`       | array literal                                 | `[1, 2]`, `['k' => 'v']` | `[]` and `{}`          |
| `fn () =>` | arrow function                                | `fn ($n) => $n * 2`      | `n => n * 2`           |
| `use`      | **three different things** (see below)        |                          |                        |

The keyword `use` means different things depending on position:

```php
use App\Models\Job;                   // 1. at top of file: import a class

class Foo {
    use HasFactory;                   // 2. inside a class: mix in a trait

    public function bar() {
        $fn = function () use ($x) {  // 3. after closure params: capture outer variable
            return $x;
        };
    }
}
```

### 3.2 Namespaces and autoloading

```php
namespace App\Services\Scraping;      // this file lives in app/Services/Scraping/

use App\Models\ScrapingJob;           // import, like `import { ScrapingJob } from ...`

class ScrapingJobService {}           // full name: App\Services\Scraping\ScrapingJobService
```

`composer.json` maps the `App\` prefix to the `app/` folder (PSR-4). Composer's autoloader loads the file when
the class is first used. There is no bundler and no manual `require`.

`ClassName::class` returns the fully-qualified class name as a string. Laravel uses it everywhere as an ID:

```php
ScrapingJob::class   // "App\Models\ScrapingJob"
```

Docs: [Namespaces](https://www.php.net/manual/en/language.namespaces.php) ·
[`::class`](https://www.php.net/manual/en/language.oop5.basic.php#language.oop5.basic.class.class)

### 3.3 Arrays: PHP's list and map in one type

```php
$ids    = [1, 2, 3];                                  // list
$record = ['name' => '村上', 'date' => '2026-10-25'];  // map (associative array)

$record['name'];                // access
$record['missing'] ?? 'n/a';    // safe access with default
$ids[] = 4;                     // push
```

Laravel adds **Collections** (`collect($array)`) with chainable `map`, `filter`, `pluck`, etc.:

```php
$titles = $jobs->filter(fn ($j) => $j->is_active)->pluck('title');
```

Docs: [Arrays](https://www.php.net/manual/en/language.types.array.php) ·
[Collections](https://laravel.com/docs/collections)

### 3.4 Functions, closures, arrow functions

```php
// classic closure: must list captured variables with use()
$double = function (int $n) use ($factor): int {
    return $n * $factor;
};

// arrow function: auto-captures outer variables (by value), single expression, returns it
$double = fn (int $n): int => $n * $factor;
```

You see these constantly as callbacks:

```php
$jobs->filter(fn (Job $job): bool => blank($job->indeed_image_keys))
```

Docs: [Arrow functions](https://www.php.net/manual/en/functions.arrow.php) ·
[Anonymous functions](https://www.php.net/manual/en/functions.anonymous.php)

### 3.5 Types

```php
public function handleComplete(string $jobId, array $applications, string $status): ScrapingJob
```

| Syntax                                | Meaning            |
| ------------------------------------- | ------------------ |
| `string` `int` `float` `bool` `array` | scalar/array types |
| `?string`                             | `string` or `null` |
| `A\|B`                                | union type         |
| `void`                                | returns nothing    |
| `mixed`                               | anything           |
| `static`/`self`                       | the class itself   |

PHP types are checked at **runtime** and cannot say "array of strings". That is why the project uses PHPDoc for
static analysis:

```php
/**
 * @param  array<int, array<string, mixed>>  $records   // list of maps
 */
```

Docs: [Type declarations](https://www.php.net/manual/en/language.types.declarations.php)

### 3.6 Classes, visibility, constructor promotion

```php
class ScrapingJobCompleteController extends Controller
{
    public function __construct(
        private ScrapingJobService $service,
    ) {}
}
```

That is shorthand. The long form is:

```php
class ScrapingJobCompleteController extends Controller
{
    private ScrapingJobService $service;

    public function __construct(ScrapingJobService $service)
    {
        $this->service = $service;
    }
}
```

Line by line:

- `private` in the parameter list → declare a private property **and** assign it. That's _constructor promotion_.
- `$this->service` → inside the class, access own members.
- `{}` empty body → nothing else to do.
- `readonly` (e.g. `private readonly Foo $foo`) → cannot be reassigned after construction.

Visibility: `public` (anyone), `protected` (class + subclasses), `private` (class only).

Static members use `::` and need no instance:

```php
class MathHelper {
    public static function square(int $n): int { return $n * $n; }
}
MathHelper::square(4);   // 16
```

Docs: [Constructor promotion](https://www.php.net/manual/en/language.oop5.decon.php#language.oop5.decon.constructor.promotion) ·
[Visibility](https://www.php.net/manual/en/language.oop5.visibility.php) ·
[Static](https://www.php.net/manual/en/language.oop5.static.php)

### 3.7 Inheritance, interfaces, traits

```php
class IndeedJobApplication extends Model     // inheritance: gets all Model features
{
    use HasFactory;                          // trait: copies methods in (a mixin)
}

class IndeedRawStore implements ProviderRawStoreInterface   // interface: contract
{
    public function provider(): string { return 'indeed'; }
}
```

- **`extends`** = "is a" (one parent only).
- **`implements`** = "promises to have these methods" (many allowed). Lets code depend on the _contract_, not the
  concrete class. This is how the project swaps `IndeedRawStore` / `AirworkRawStore` behind one interface.
- **`use Trait`** = copy-paste reusable methods into the class.

Docs: [Interfaces](https://www.php.net/manual/en/language.oop5.interfaces.php) ·
[Traits](https://www.php.net/manual/en/language.oop5.traits.php)

### 3.8 Invokable classes and anonymous classes

```php
public function __invoke(ScrapingJobCompleteRequest $request, string $spider): JsonResponse
```

`__invoke` lets an object be called like a function. Laravel uses it for single-action controllers
(`[ScrapingJobCompleteController::class]` with no method name).

Migrations return an **anonymous class** (a class with no name):

```php
return new class extends Migration {
    public function up(): void { /* ... */ }
};
```

Docs: [`__invoke`](https://www.php.net/manual/en/language.oop5.magic.php#object.invoke) ·
[Anonymous classes](https://www.php.net/manual/en/language.oop5.anonymous.php)

### 3.9 Magic methods (the secret behind a lot of Laravel)

PHP calls these automatically when you access something that doesn't exist:

| Method           | Triggered by               | Laravel uses it for                              |
| ---------------- | -------------------------- | ------------------------------------------------ |
| `__get($name)`   | `$model->something`        | model attributes and relationships               |
| `__call(...)`    | `$obj->undefinedMethod()`  | forwarding calls (query builder, facades)        |
| `__callStatic()` | `Class::undefinedMethod()` | **facades** (`Log::info()`) and `Model::where()` |

This is why `ScrapingJob::where(...)` works even though `where` isn't defined on `ScrapingJob`: `__callStatic`
forwards it to a query builder. See [Facades](#9-facades).

Docs: [Overloading / magic methods](https://www.php.net/manual/en/language.oop5.overloading.php)

### 3.10 Named arguments

```php
$result = $this->service->handleComplete(
    jobId: $validated['job_id'],
    applications: $validated['records'] ?? [],
    status: $validated['status'],
);
```

`name: value` passes arguments by parameter name. Order no longer matters and it reads like documentation.

Docs: [Named arguments](https://www.php.net/manual/en/functions.arguments.php#functions.named-arguments)

### 3.11 Enums

```php
enum ScrapingSpiderEnum: string
{
    case IndeedJobDetails      = 'indeed-job-details';
    case IndeedJobApplications = 'indeed-job-applications';
    case AirregiJobs           = 'airregi-jobs';
}

ScrapingSpiderEnum::IndeedJobDetails;                 // the case (an object)
ScrapingSpiderEnum::IndeedJobDetails->value;          // 'indeed-job-details'
ScrapingSpiderEnum::from('airregi-jobs');             // case, or throws ValueError
ScrapingSpiderEnum::tryFrom('nope');                  // case, or null
ScrapingSpiderEnum::cases();                          // array of all cases
```

Plain-PHP equivalent before enums existed (worse: any string is accepted):

```php
class ScrapingSpider {
    const INDEED_JOB_DETAILS = 'indeed-job-details';
}
```

Enums can have methods, which is how `$spider->isIndeed()` can exist:

```php
public function isIndeed(): bool
{
    return in_array($this, [self::IndeedJobDetails, self::IndeedJobApplications], true);
}
```

Docs: [Enumerations](https://www.php.net/manual/en/language.enumerations.php)

### 3.12 `match` vs `switch`

```php
$message = match ($spider) {
    ScrapingSpiderEnum::IndeedJobDetails      => 'Starting Indeed job details scraper...',
    ScrapingSpiderEnum::IndeedJobApplications => 'Starting Indeed applicant scraper...',
    ScrapingSpiderEnum::AirregiJobs           => 'Starting Airwork jobs scraper...',
};
```

Equivalent `switch` (more code, loose `==`, needs `break`):

```php
switch ($spider) {
    case ScrapingSpiderEnum::IndeedJobDetails:
        $message = '...'; break;
    // ...
}
```

`match` is an **expression** (returns a value), uses strict `===`, and throws `UnhandledMatchError` if no arm
matches (so you find bugs early).

Docs: [match](https://www.php.net/manual/en/control-structures.match.php)

### 3.13 Null handling

```php
$job->company?->company_name;        // null if $job->company is null (no error)
$validated['records'] ?? [];         // use [] if key missing or null
$value ??= 'default';                // assign only if null
```

Without them you would write `isset($job->company) ? $job->company->company_name : null`.

Docs: [Null coalescing](https://www.php.net/manual/en/language.operators.comparison.php#language.operators.comparison.coalesce) ·
[Nullsafe](https://www.php.net/manual/en/language.oop5.basic.php#language.oop5.basic.nullsafe)

### 3.14 Exceptions

```php
try {
    $this->ingestService->ingest($record);
} catch (Throwable $e) {
    Log::error('Ingest failed', ['error' => $e->getMessage()]);
    // continue with the next record
} finally {
    // always runs
}

throw new RuntimeException('Missing credentials');
```

`Throwable` catches both `Exception` and `Error`. Services catch it inside loops so one bad record doesn't stop
the batch.

Docs: [Exceptions](https://www.php.net/manual/en/language.exceptions.php)

### 3.15 PHP is synchronous

There is no `async/await`. A request runs top to bottom and the client waits. Anything slow (exports, restores,
syncing hundreds of records) is pushed to a **queued job** ([section 16](#16-jobs-and-queues)).

---

## 4. Routing

A route maps _HTTP method + URL_ to code.

```php
// routes/api/admin.php
Route::middleware('protected')->group(function () {
    Route::get('/admin/scraping-jobs', [ScrapingJobController::class, 'index']);
    Route::get('/admin/scraping-jobs/status', [ScrapingJobController::class, 'status']);
    Route::get('/admin/scraping-jobs/{scrapingJob}', [ScrapingJobController::class, 'show']);
    Route::post('/admin/scraping-jobs', [ScrapingJobController::class, 'store']);
    Route::post('/admin/scraping-jobs/{scrapingJob}/retry', [ScrapingJobController::class, 'retry']);
});
```

Reading it:

- `Route::get(...)` is a facade call ([section 9](#9-facades)).
- `[ScrapingJobController::class, 'index']` is a PHP array that means "call method `index` on this class".
- `{scrapingJob}` is a URL parameter.
- `->group(function () { ... })` applies `protected` middleware to every route inside.
- **Order matters:** `/status` is declared before `/{scrapingJob}`, otherwise `{scrapingJob}` would swallow
  the word `status`.
- `routes/api.php` routes get the `/api` prefix automatically, so the real URL is `/api/admin/scraping-jobs`.

Route with a dynamic spider parameter (callback route):

```php
Route::post('/scraping-jobs/{spider}/complete', ScrapingJobCompleteController::class);
```

A bare class (no array) means an **invokable controller**.

### Route-model binding (behind the scenes)

```php
Route::get('/admin/scraping-jobs/{scrapingJob}', [ScrapingJobController::class, 'show']);

public function show(ScrapingJob $scrapingJob): JsonResponse { ... }
```

Laravel's `SubstituteBindings` middleware sees that the route parameter name (`scrapingJob`) matches the
type-hinted argument (`ScrapingJob`), runs `ScrapingJob::findOrFail($idFromUrl)`, and passes the model in. A
missing row becomes `404`. The names must match exactly.

Docs: [Routing](https://laravel.com/docs/routing) · [Route model binding](https://laravel.com/docs/routing#route-model-binding)

**Inspect routes:** `php artisan route:list --path=admin/scraping`

---

## 5 Middleware

Middleware wraps the request. Conceptually it is a set of nested functions:

```php
// plain PHP idea of a pipeline
$response = $auth(function ($request) use ($controller) {
    return $controller($request);
});
```

A middleware class:

```php
class EnsureCompanyIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->company?->isActive()) {
            abort(403);                 // stop here; controller never runs
        }

        return $next($request);         // continue inward
    }
}
```

Line by line:

- `Closure $next` is "the rest of the pipeline". Calling it passes the request on.
- Code **before** `$next($request)` runs on the way in. Code **after** it (capture the return value) runs on
  the way out.
- `abort(403)` throws an HTTP exception, which Laravel renders as a response.

Where it is registered (Laravel 11+/12): `bootstrap/app.php`

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias(['protected' => /* ... */]);   // name used in routes
    $middleware->append(AssignRequestId::class);      // global
})
```

In this project: `EnsureJsonIsValid`, `SetLocaleFromHeader`, `AssignRequestId` (global), `protected` (route
group for admin).

Docs: [Middleware](https://laravel.com/docs/middleware)

---

## 6. Controllers

A controller should be **thin**: take the request, call a service, return a response.

```php
class ScrapingJobCompleteController extends Controller
{
    public function __construct(
        private ScrapingJobService $service,
    ) {}

    public function __invoke(
        ScrapingJobCompleteRequest $request,
        string $spider,
    ): JsonResponse {
        $validated = $request->validated();

        $result = $this->service->handleComplete(
            jobId: $validated['job_id'],
            applications: $validated['records'] ?? [],
            status: $validated['status'],
        );

        return response()->json([
            'message' => 'Scraping job updated',
            'data' => ScrapingJobResource::make($result),
        ]);
    }
}
```

What Laravel does behind the scenes:

1. The router resolves the controller through the **container**, so `ScrapingJobService` is injected into the
   constructor automatically ([section 8](#8-service-container-dependency-injection-providers)).
2. For the action method, Laravel inspects each parameter:
    - A `FormRequest` type → builds it, runs `authorize()` and `rules()` first.
    - A model type matching a route param → route-model binding.
    - A scalar like `string $spider` → filled from the URL segment `{spider}` by name.
3. The returned value is converted to an HTTP response. `JsonResponse` is sent as-is. If you return an array
   or a Resource, Laravel turns it into JSON for you.

Response helpers: `response()->json($data, 201)`, `response()->noContent()`, `abort(404)`.

Common status codes in this project: `200` ok, `201` created, `403` forbidden (authorize false), `404` not
found, `409` conflict (job already running), `422` validation/credential failure.

A controller with several actions (`index`, `show`, `store`, `status`) is a **resource-style controller**.
One action → `__invoke`.

Docs: [Controllers](https://laravel.com/docs/controllers) · [Responses](https://laravel.com/docs/responses)

---

## 7. Form Requests: validation and authorization

```php
class TriggerScrapingJobRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;                       // false → 403
    }

    public function rules(): array
    {
        return [
            'company_id' => ['required', 'integer', 'exists:companies,id'],
            'spider'     => ['sometimes', 'string', Rule::enum(ScrapingSpiderEnum::class)],
        ];
    }
}
```

Rule meanings:

- `required` must be present. `sometimes` validate only if present.
- `exists:companies,id` checks the DB: a row in `companies` with this `id`.
- `Rule::enum(...)` value must be a valid backing value of the enum.
- `records.*.row_hash` → `*` applies the rule to every item of `records`.

Behind the scenes: when the container builds a `FormRequest` for the controller method, it calls
`validateResolved()`. That runs `authorize()` then `rules()`. On failure it **throws**:

- `AuthorizationException` → `403`
- `ValidationException` → `422` with `{ "message": "...", "errors": { "field": ["..."] } }`

The controller body never runs. (For API clients send `Accept: application/json`, otherwise failed validation
may redirect instead of returning JSON.)

Custom authorization from this project: the scraper callback is verified by an HMAC signature, not a user
login:

```php
public function authorize(): bool
{
    $scrapingJob = ScrapingJob::find($this->input('job_id'));

    return $scrapingJob !== null
        && $this->verifyHmac($scrapingJob->callback_secret);
}
```

Read validated data only through `$request->validated()` (only keys that passed validation), not
`$request->all()`.

Other useful reads: `$request->input('key')`, `$request->query('page')`, `$request->enum('spider', Enum::class)`,
`$request->user()`.

Docs: [Validation](https://laravel.com/docs/validation) · [Form Requests](https://laravel.com/docs/validation#form-request-validation) ·
[Available rules](https://laravel.com/docs/validation#available-validation-rules)

---

## 8. Service container, dependency injection, providers

### 8.1 The problem it solves

Plain PHP, building objects by hand:

```php
$heater = new WaterHeater();
$maker  = new CoffeeMaker($heater);
$service = new ScrapingJobService(new IngestApplicationService(new ProviderRegistry(/* ... */)));
```

That gets unmanageable. The **service container** does it for you by reading constructor type hints
(reflection).

A toy container to show the idea:

```php
function make(string $class) {
    $ctor = (new ReflectionClass($class))->getConstructor();
    if (! $ctor) return new $class;

    $args = array_map(
        fn ($param) => make($param->getType()->getName()),   // build each dependency recursively
        $ctor->getParameters()
    );

    return new $class(...$args);                              // spread array into arguments
}
```

Laravel's container is this idea plus caching, interface binding, and config.

### 8.2 Automatic resolution

```php
class WaterHeater {}
class CoffeeMaker { public function __construct(public WaterHeater $heater) {} }

$maker = app()->make(CoffeeMaker::class);   // heater built and injected
```

Controllers, jobs (`handle()`), listeners, middleware, console commands (`handle()`) and Form Requests are all
created by the container, so **type-hinting in their constructors or handle methods just works**.

### 8.3 `bind`, `singleton`, `tag`, contextual binding

```php
// New instance every time
$this->app->bind(CoffeeMaker::class, fn ($app) => new CoffeeMaker($app->make(WaterHeater::class)));

// One shared instance
$this->app->singleton(S3Service::class);

// Interface → implementation (the container can't guess which one)
$this->app->bind(NotifierInterface::class, EmailNotifier::class);

// Different implementation per consumer
$this->app->when(OfficeCoffeeMaker::class)
    ->needs(WaterHeaterInterface::class)
    ->give(ExpressHeater::class);
```

### 8.4 The registry pattern used in this project

```php
// app/Providers/AppServiceProvider.php
public function register(): void
{
    $this->app->tag(
        [AirworkRawStore::class, IndeedRawStore::class],
        ProviderRawStoreInterface::class,
    );

    $this->app->singleton(ProviderRegistry::class, fn ($app): ProviderRegistry => new ProviderRegistry(
        rawStores: $app->tagged(ProviderRawStoreInterface::class),
        syncHandlers: $app->tagged(ProviderSyncHandlerInterface::class),
    ));
}
```

Step by step:

1. `tag([...], Interface::class)` puts several classes under one label.
2. `$app->tagged(Interface::class)` later returns **all** of them (built through the container).
3. `ProviderRegistry` receives the list and indexes each by its `provider()` string (`'airwork'`, `'indeed'`).
4. Consumers write `$registry->rawStore('indeed')` and never reference `IndeedRawStore` directly.

**Why:** adding a third provider means one new class plus one entry in `tag([...])`. No `if/else` chain anywhere
else. This is the _Open/Closed principle_ in practice.

### 8.5 Service providers

- `register()`: only bind things into the container. Don't use other services yet.
- `boot()`: runs after all providers registered. Use for observers, macros, event hooks.

Providers are listed in `bootstrap/providers.php`. For small apps, `AppServiceProvider` is enough.

Docs: [Service container](https://laravel.com/docs/container) · [Service providers](https://laravel.com/docs/providers)

---

## 9. Facades

```php
Log::info('Scraping job completed');
Cache::lock('scraping-job:dispatch:3:indeed-job-details', 300);
DB::transaction(fn () => $this->settle());
Artisan::call('migrate', ['--force' => true]);
```

`Log::info(...)` looks like a static call but isn't. Behind the scenes:

```php
abstract class Facade
{
    public static function __callStatic($method, $args)
    {
        $instance = app(static::getFacadeAccessor());   // e.g. 'log' → the real logger from the container
        return $instance->$method(...$args);            // forward the call
    }
}

class Log extends Facade
{
    protected static function getFacadeAccessor() { return 'log'; }
}
```

So `Log::info('x')` = "get the logger from the container, then call `->info('x')`". Using the dedicated
injected object is equivalent:

```php
public function __construct(private LoggerInterface $logger) {}
// ...
$this->logger->info('x');
```

**When to use which in this project:**

- Facades for framework services (`Log`, `Cache`, `DB`, `Route`, `Schema`, `Artisan`, `Storage`).
- Constructor injection for **your own** classes (services), because the dependencies are visible and easy to
  mock in tests.

Docs: [Facades](https://laravel.com/docs/facades)

### Cache locks (used by dispatch)

```php
$lock = Cache::lock("scraping-job:dispatch:{$companyId}:{$spider->value}", $seconds);

if (! $lock->get()) {
    // someone else holds it → return 409
}

try {
    // create ScrapingJob row, send to SQS
} finally {
    // lock deliberately kept until the job finishes in this project; otherwise: $lock->release();
}
```

`"...{$companyId}..."` is string interpolation: double-quoted strings substitute `$vars`; single-quoted
strings do not.

Docs: [Atomic locks](https://laravel.com/docs/cache#atomic-locks)

---

## 10. Services (your business logic layer)

A service is a **plain PHP class**. It extends nothing and lives in `app/Services`.

```php
class ScrapingJobService
{
    public function __construct(
        private IngestApplicationService $ingestService,
    ) {}

    public function handleComplete(string $jobId, array $applications, string $status): ScrapingJob
    {
        // loop records, call $this->ingestService, update the ScrapingJob row, dispatch follow-up jobs
    }
}
```

Why this layer exists:

| Controller               | Service                                 | Model                          |
| ------------------------ | --------------------------------------- | ------------------------------ |
| Parse input, return JSON | Decide _what happens_ and in what order | Read/write one table           |
| HTTP only                | No HTTP knowledge                       | Relations, casts, small scopes |

A service can be called from a controller, a queued job, an artisan command, or a test, with no HTTP request
involved. That's why `ScrapingJobService::dispatchAll()` is used both by `POST /admin/scraping-jobs/trigger-all`
and by the daily command.

Services in this project:

| Service                      | Job                                                     |
| ---------------------------- | ------------------------------------------------------- |
| `ScrapingJobService`         | dispatch, complete, retry, lock handling                |
| `IngestApplicationService`   | hash a record, route to a provider raw store            |
| `IndeedRawStore`             | provider-specific upsert into `indeed_job_applications` |
| `JobExportService`           | Indeed/Airwork export validation + download             |
| `AirworkJobExportRunService` | async export runs and history                           |

---

## 11. Enums

Where enums appear in the code:

```php
// 1. Validation
'spider' => ['sometimes', 'string', Rule::enum(ScrapingSpiderEnum::class)],

// 2. Reading from a request
$spider = $request->enum('spider', ScrapingSpiderEnum::class) ?? ScrapingSpiderEnum::AirregiJobs;

// 3. Model cast (DB string ⇄ enum object)
'job_seeker_sync_status' => JobSeekerSyncStatusEnum::class,

// 4. Resource output
'status' => $this->status?->value,

// 5. Cron command argument
'scraping:dispatch-companies --spider='.ScrapingSpiderEnum::IndeedJobDetails->value
```

The pattern: **inside PHP use the enum object, at the edges (DB, JSON, CLI) use `->value`.** `from()` converts
a string back to the object.

Typical bug: comparing a cast enum to a string.

```php
$job->status === 'pending';                       // always false when status is cast to an enum
$job->status === ScrapingJobStatusEnum::Pending;  // correct
```

Docs: [Enum casting](https://laravel.com/docs/eloquent-mutators#enum-casting) · [Rule::enum](https://laravel.com/docs/validation#rule-enum)

---

## 12. Eloquent models

A model = one table + a query builder + behaviour.

```php
class IndeedJobApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'external_application_id',
        'user_unique_id',
        'row_hash',
        'application_record',
    ];

    protected function casts(): array
    {
        return [
            'application_record'     => 'array',
            'job_seeker_sync_status' => JobSeekerSyncStatusEnum::class,
            'last_otp_received_at'   => 'datetime',
        ];
    }
}
```

### 12.1 Conventions (zero config)

| Model                  | Table                     | Primary key |
| ---------------------- | ------------------------- | ----------- |
| `IndeedJobApplication` | `indeed_job_applications` | `id`        |
| `ScrapingJob`          | `scraping_jobs`           | `id`        |
| `Company`              | `companies`               | `id`        |

Class name → snake_case plural. Override with `protected $table = '...'` if needed.
Foreign keys default to `<singular_table>_id` (`company_id`).

### 12.2 `$fillable` (mass assignment protection)

`create([...])` and `update([...])` only accept attributes listed in `$fillable`; others throw an error or are
ignored. This stops a user sending `is_admin=1` in the body. Setting a property directly is always allowed:
`$model->status = 'x'; $model->save();`

### 12.3 Casts

| Cast                     | DB value → PHP value        |
| ------------------------ | --------------------------- |
| `'array'` (JSON column)  | JSON string → PHP array     |
| `'datetime'`             | timestamp → `Carbon` object |
| `SomeEnum::class`        | string → enum case          |
| `'boolean'`, `'integer'` | as named                    |

So `data_get($indeedJob->raw_payload, 'record.indeed_image_keys')` can walk a JSON column because the cast made
it an array.

### 12.4 Querying

```php
ScrapingJob::query()->find($id);                     // model or null
ScrapingJob::query()->findOrFail($id);               // model or 404
User::query()->where('email', $email)->first();      // model or null

ScrapingJob::query()
    ->where('company_id', $companyId)
    ->whereIn('status', ['pending', 'processing'])
    ->latest()                                        // order by created_at desc
    ->get();                                          // Collection of models
```

Key idea: `where`, `orderBy`, `with` etc. **build** a query (nothing runs). `get()`, `first()`, `find()`,
`paginate()`, `count()`, `exists()` **execute** it.

Writing:

```php
$app = IndeedJobApplication::query()->create([...]);   // INSERT
$app->update(['row_hash' => $newHash]);                // UPDATE (fillable applies)
$app->save();                                          // UPDATE after changing properties

Model::query()->updateOrCreate(['id' => $id], ['name' => $name]);
Model::query()->firstOrCreate(['email' => $email], ['name' => $name]);
```

### 12.5 Scopes

```php
public function scopeActive($query)
{
    return $query->whereIn('status', ['pending', 'processing']);
}

ScrapingJob::query()->active()->latest()->first();   // "scope" prefix dropped, lowerCamel
```

### 12.6 Transactions

```php
DB::transaction(function () {
    // several writes; if an exception is thrown, all are rolled back
});
```

Docs: [Eloquent](https://laravel.com/docs/eloquent) · [Query builder](https://laravel.com/docs/queries) ·
[Mutators & casting](https://laravel.com/docs/eloquent-mutators) · [Database transactions](https://laravel.com/docs/database#database-transactions)

---

## 13. Relationships and eager loading

Relationships are **methods that return a relation object**.

```php
class IndeedJobApplication extends Model
{
    public function jobSeekerUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'job_seeker_user_id');
    }
}

class Company extends Model
{
    public function indeedCredentials(): HasOne
    {
        return $this->hasOne(CompanyIndeedCredential::class);
    }

    public function jobs(): HasMany
    {
        return $this->hasMany(Job::class);
    }
}
```

| Relation        | Foreign key lives on... | Example here                              |
| --------------- | ----------------------- | ----------------------------------------- |
| `belongsTo`     | **this** table          | application → user (`job_seeker_user_id`) |
| `hasOne`        | the other table         | company → Indeed credentials              |
| `hasMany`       | the other table         | company → jobs                            |
| `belongsToMany` | a pivot table           | jobs ⇄ categories                         |

### Two ways to access (and why `()` matters)

```php
$company->indeedCredentials;      // no parentheses → __get → runs query → model (or null), cached on the object
$company->indeedCredentials();    // parentheses → returns the relation object so you can keep building a query
$company->jobs()->whereIn('id', $ids)->get();
```

### N+1 and `with()`

```php
// BAD: 1 query for jobs + 1 query per job for company
foreach (Job::all() as $job) { echo $job->company->company_name; }

// GOOD: 2 queries total
$jobs = Job::query()->with('company')->get();
```

`JobExportService::relations()` returns a long list for `with()` for exactly this reason: an export touches
thousands of rows. Prefer `with([...])` whenever you loop and read a relation.

Note on the resolver in section 23: it reads `$job->externalListings`. That's only free because the export
already eager-loaded it. Outside the export flow it would trigger a lazy query.

Docs: [Relationships](https://laravel.com/docs/eloquent-relationships) · [Eager loading](https://laravel.com/docs/eloquent-relationships#eager-loading)

---

## 14. Migrations

Migrations are version control for the schema.

```php
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('indeed_job_applications', function (Blueprint $table): void {
            $table->id();
            $table->string('external_application_id')->unique();
            $table->string('user_unique_id');
            $table->char('row_hash', 64);
            $table->jsonb('application_record');
            $table->foreignId('job_seeker_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('user_unique_id');
            $table->index('row_hash');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('indeed_job_applications');
    }
};
```

Notes:

- File names start with a timestamp, so Laravel runs them in order. A `migrations` table records which ran.
- `->nullable()`, `->unique()`, `->constrained()` are **chained modifiers** on the column definition.
- `->foreignId('job_seeker_user_id')->constrained('users')` creates a bigint column plus a foreign key to
  `users.id`. `->nullOnDelete()` sets it to NULL if the user is deleted.
- **Never edit a migration that has run in shared environments.** Create a new one that alters the table.

```bash
docker exec indeed-seo-api php artisan make:migration add_x_to_indeed_jobs_table
docker exec indeed-seo-api php artisan migrate
docker exec indeed-seo-api php artisan migrate:status
docker exec indeed-seo-api php artisan migrate:rollback
# DANGER: drops every table, local/test only
docker exec indeed-seo-api php artisan migrate:fresh
```

Docs: [Migrations](https://laravel.com/docs/migrations) · [Column types](https://laravel.com/docs/migrations#available-column-types)

---

## 15. API Resources

A Resource decides the JSON shape of a model.

```php
class ScrapingJobResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'company_id'   => $this->company_id,
            'spider'       => $this->spider,
            'status'       => $this->status?->value,
            'company_name' => $this->whenLoaded('company', fn () => $this->company->company_name),
            'created_at'   => $this->created_at?->toISOString(),
        ];
    }
}
```

- `$this->id` looks like it's the resource, but `JsonResource` forwards property access to the wrapped model.
- `whenLoaded('company', fn)`: includes the field only if the relation was eager-loaded, so a Resource never
  silently runs extra queries.
- `->value` converts enum to string, `->toISOString()` converts Carbon to a string.

```php
ScrapingJobResource::make($job);              // one
ScrapingJobResource::collection($jobs);       // many (also works with paginators)
```

Returning a Resource directly from a controller wraps it as `{ "data": { ... } }`. Inside
`response()->json([... 'data' => Resource::make($x)])` the project builds the wrapper itself.

**Why not return the model?** A model serialises every column (internal flags, secrets such as
`callback_secret`). A Resource is an explicit contract with the frontend.

Docs: [API resources](https://laravel.com/docs/eloquent-resources)

---

## 16. Jobs and queues

```php
class SyncIngestedApplicationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;

    public function __construct(
        public string $provider,
        public string $externalApplicationId,
    ) {}

    public function handle(ProviderRegistry $registry): void
    {
        $registry->syncHandler($this->provider)->sync($this->externalApplicationId);
    }
}

SyncIngestedApplicationJob::dispatch('indeed', $externalApplicationId);
```

What happens:

1. `dispatch(...)` (from the `Dispatchable` trait) creates the job object and **serializes** its public
   properties into a payload on the queue (Redis, DB, SQS...).
2. A worker process (`php artisan queue:work`) pops the payload, rebuilds the object, and the container calls
   `handle()`, injecting `ProviderRegistry`. Constructor args are data; `handle()` args are services.
3. If `handle()` throws, the job is retried up to `$tries`, then `failed()` runs and it goes to
   `failed_jobs`.

Rules of thumb:

- Pass **IDs and strings**, not big objects. (`SerializesModels` stores just a model's ID and reloads it.)
- Make jobs **idempotent**: running twice must still give a correct result.
- In tests `QUEUE_CONNECTION=sync` runs jobs immediately; use `Queue::fake()` to assert dispatch without running.
- Production needs a running worker (supervisor/systemd/container). No worker → jobs sit in the queue forever.

Project examples: `SyncIngestedApplicationJob`, `GenerateAirworkJobExportFile`, `DatabaseRestoreJob`.

Docs: [Queues](https://laravel.com/docs/queues)

---

## 17. Events and listeners

```php
class EmailReceived
{
    public function __construct(public EmailMessage $email) {}
}

class DispatchAirWorkScrapingForCompany implements ShouldQueue
{
    public function handle(EmailReceived $event): void
    {
        // find company by email sub-address → ScrapingJobService::dispatch(...)
    }
}

event(new EmailReceived($email));
// or
EmailReceived::dispatch($email);
```

- The `handle()` **type hint** tells Laravel which event the listener subscribes to (auto-discovery).
- The emitter doesn't know who listens. That's the point: decoupling.
- `implements ShouldQueue` → the listener runs on the queue worker, not inside the request.

Use events when "something happened" should trigger several independent reactions. Use a direct service call
when there is exactly one obvious next step.

Docs: [Events](https://laravel.com/docs/events)

---

## 18. Console commands and the scheduler

### 18.1 A command

```php
class ScrapingDispatchCompanies extends Command
{
    protected $signature = 'scraping:dispatch-companies {--spider=airregi}';
    protected $description = 'Dispatch scraping jobs for all eligible companies';

    public function handle(ScrapingJobService $service): int
    {
        $spider = ScrapingSpiderEnum::from($this->option('spider'));
        $count = $service->dispatchAll($spider)->count();

        $this->info("Dispatched {$count} jobs");
        return self::SUCCESS;
    }
}
```

- `$signature` defines the CLI name and args/options. `{--spider=airregi}` → `--spider=...` with a default.
- `handle()` is the entry point and can type-hint services (container injection again).
- Return `self::SUCCESS` (0) or `self::FAILURE` (1) as the exit code.

Run: `docker exec indeed-seo-api php artisan scraping:dispatch-companies --spider=indeed-job-details`

### 18.2 The scheduler

```php
// bootstrap/app.php
$schedule->command('scraping:dispatch-companies --spider='.ScrapingSpiderEnum::IndeedJobDetails->value)
    ->onOneServer()
    ->withoutOverlapping()
    ->dailyAt('07:15')
    ->timezone('Asia/Tokyo');
```

- Laravel does **not** run a daemon. The server has **one** cron entry, run every minute:
  `* * * * * php artisan schedule:run`. Each minute Laravel checks which tasks are due.
- `->onOneServer()` uses a cache lock so only one of several app servers runs it (needs a shared cache driver
  such as Redis).
- `->withoutOverlapping()` skips a run if the previous one is still running.
- `->timezone('Asia/Tokyo')` interprets `dailyAt` in JST regardless of server timezone.
- `'...'.ScrapingSpiderEnum::IndeedJobDetails->value` is string concatenation with `.`.

`php artisan schedule:list` shows all entries and their next run time.

### 18.3 Why "spider-aware company selection" matters

`dispatchAll()` previously did `Company::whereHas('airworkCredentials')`. Adding only a cron line for an Indeed
spider would send Indeed work to Airwork-only companies. The fix is a single selector that depends on the
spider:

```php
private function eligibleCompaniesQuery(ScrapingSpiderEnum $spider): Builder
{
    if ($spider->isIndeed()) {
        return Company::query()->where(function (Builder $query): void {
            $query
                ->whereHas('indeedCredentials', fn (Builder $q) => $q->whereNotNull('email'))
                ->orWhereNotNull('serial_number');
        });
    }

    return Company::query()->whereHas('airworkCredentials');
}
```

Key syntax:

- `whereHas('relation', fn)` → `WHERE EXISTS (select ... from related table ...)`.
- Grouping `where(function ($query) { ... })` → wraps the OR condition in parentheses:
  `WHERE (A OR B)`. Without it, an `AND` added later could change meaning.
- `fn (Builder $q) => $q->whereNotNull('email')` is a closure that receives the related query.

Docs: [Artisan console](https://laravel.com/docs/artisan) · [Task scheduling](https://laravel.com/docs/scheduling)

---

## 19. Config and environment

```php
// config/indeed.php
return [
    'email_template' => env('INDEED_EMAIL_TEMPLATE', 'hr.entry.office+{serial_number}@gmail.com'),
];

config('indeed.email_template');                                   // read
config('services.scraping.stale_timeout_minutes', 45);             // with default
```

- `.env` → read by `env()` **only inside `config/*.php`** → app reads via `config()`.
- Production runs `php artisan config:cache`, which freezes config. After that `env()` called anywhere else
  returns `null`. This is the most common "works locally, breaks in prod" bug.
- After changing `.env` locally: `php artisan config:clear`.

Other helpers you'll see:

```php
now()                              // Carbon "now"
now()->subMinutes(45)              // 45 minutes ago
collect($array)                    // Collection
str()->uuid()                      // UUID string
__('company.scraping_jobs.dispatched')   // translation lookup (lang files)
data_get($array, 'record.indeed_image_keys')   // safe nested access with dot notation
filled($v) / blank($v)             // not-empty / empty (treats null, '', whitespace, [] as blank)
```

`blank('0')` is false, `blank('')` is true, `blank(null)` is true.

Docs: [Configuration](https://laravel.com/docs/configuration) · [Helpers](https://laravel.com/docs/helpers) ·
[Localization](https://laravel.com/docs/localization)

---

## 20. Testing

```php
class ScrapingIndeedApplicationCompleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_ingests_records_and_dispatches_sync_job(): void
    {
        Queue::fake();

        $job = ScrapingJob::factory()->create([
            'spider' => ScrapingSpiderEnum::IndeedJobApplications,
        ]);

        $payload = [
            'job_id'  => $job->id,
            'status'  => 'success',
            'records' => [[ 'record' => ['a' => 1], 'row_hash' => 'abc', /* ... */ ]],
        ];

        $response = $this->postJson(
            '/api/scraping-jobs/indeed-job-applications/complete',
            $payload,
            ['X-HMAC-Signature' => $this->sign($payload, $job->callback_secret)],
        );

        $response->assertOk();
        $this->assertDatabaseHas('indeed_job_applications', ['row_hash' => 'abc']);
        Queue::assertPushed(SyncIngestedApplicationJob::class);
    }
}
```

- `RefreshDatabase` wraps each test in a transaction (or migrates fresh) so tests don't leak into each other.
- `Model::factory()->create([...])` inserts a row with fake data, overriding listed columns.
- `postJson`, `getJson` send JSON requests and give a `TestResponse` with `assertOk()`, `assertStatus(422)`,
  `assertJsonPath(...)`.
- `Queue::fake()` / `Event::fake()` / `Bus::fake()` stop real execution and let you assert dispatch.
- Method names start with `test_` (or use `#[Test]` attribute).

```bash
docker exec indeed-seo-api php artisan test --compact
docker exec indeed-seo-api php artisan test --compact --filter=IndeedJobExport
docker exec indeed-seo-api vendor/bin/pint --dirty         # format only changed files
```

Docs: [Testing](https://laravel.com/docs/testing) · [HTTP tests](https://laravel.com/docs/http-tests) ·
[Database testing](https://laravel.com/docs/database-testing) · [Factories](https://laravel.com/docs/eloquent-factories) ·
[Queue fake](https://laravel.com/docs/queues#testing)

---

## 21. Filament (admin UI) in one page

Filament builds admin CRUD screens in PHP (on Livewire). Only look at it when you touch the admin panel.

```php
class IndeedJobApplicationResource extends Resource
{
    protected static ?string $model = IndeedJobApplication::class;
    protected static ?string $navigationLabel = 'Indeed Job Applications';

    public static function table(Table $table): Table { return IndeedJobApplicationsTable::configure($table); }
    public static function form(Schema $schema): Schema { return IndeedJobApplicationForm::configure($schema); }

    public static function getPages(): array
    {
        return [
            'index' => ListIndeedJobApplications::route('/'),
            'view'  => ViewIndeedJobApplication::route('/{record}'),
            'edit'  => EditIndeedJobApplication::route('/{record}/edit'),
        ];
    }
}
```

- `protected static ?string $model` → a **static property** (shared by the class, read with `static::$model`).
- Columns: `TextColumn::make('user_unique_id')`; fields: `TextInput::make('email')`; actions: `Action::make(...)`.
  `::make()` is a **static factory** that returns a configurable object you chain on.

Location: `app/Filament/Resources/IndeedJobApplications/`

Docs: [Filament docs](https://filamentphp.com/docs)

---

## 22. Walkthrough: tracing the scraper callback

Scenario: the Python scraper finishes and calls
`POST /api/scraping-jobs/indeed-job-applications/complete`.

```
routes/api/scraping.php
   Route::post('/scraping-jobs/{spider}/complete', ScrapingJobCompleteController::class)
        │
        ▼
ScrapingJobCompleteRequest
   authorize(): ScrapingJob::find(job_id) → verifyHmac(callback_secret)   (fail → 403)
   rules(): job_id, status, records.*.record, records.*.row_hash          (fail → 422)
        │
        ▼
ScrapingJobCompleteController::__invoke()
   match($spider) → $this->service->handleComplete(...)
        │
        ▼
ScrapingJobService::handleComplete()
   foreach record: try { $this->ingestService->ingest(...) } catch (Throwable) { log, continue }
        │
        ▼
IngestApplicationService::ingest()
   $hash = hash of the record
   $store = $this->registry->rawStore('indeed')          ← registry pattern (section 8.4)
        │
        ▼
IndeedRawStore::upsert()
   find by external_application_id → created / updated / unchanged
        │
        ▼
IndeedJobApplication (Eloquent) → PostgreSQL (jsonb column cast to array)
        │
        ▼
if changed: SyncIngestedApplicationJob::dispatch('indeed', $externalId)   → queue
        │
        ▼
ScrapingJobResource::make($job) → JSON 200
```

How to read any feature like this, every time:

1. **Route** → which controller/action? (`php artisan route:list --path=...`)
2. **Request class** → what's required, who is allowed?
3. **Controller** → which service method?
4. **Service** → follow each `$this->xyz->method()` call (hover/ctrl-click in your IDE).
5. **Model** → casts, fillable, relations, scopes.
6. **Side effects** → `dispatch(...)`, `event(...)`, `Cache::`, `Log::`.
7. **Resource** → what the client finally sees.
8. **Test** → the quickest executable description of expected behaviour (`tests/Feature/...`).

---

## 23. Walkthrough: making a real change (image-key resolver)

Requirement: _exports should prefer scraped Indeed image keys, then fall back to the manually uploaded keys on
`jobs.indeed_image_keys`._

### Step 1: new class (single responsibility)

```php
// app/Services/Export/IndeedJobImageKeysResolver.php
class IndeedJobImageKeysResolver
{
    public function resolve(Job $job): ?string
    {
        return $this->fromScrapedIndeedJob($job)
            ?? $this->fromManualUpload($job);
    }

    private function fromScrapedIndeedJob(Job $job): ?string
    {
        $listing = $job->externalListings
            ->firstWhere('source', ExternalJobListingServiceEnum::Indeed);

        if ($listing === null) {
            return null;
        }

        $indeedJob = IndeedJob::latestForListing($listing->id);
        $keys = data_get($indeedJob?->raw_payload, 'record.indeed_image_keys');

        return is_string($keys) && trim($keys) !== '' ? trim($keys) : null;
    }

    private function fromManualUpload(Job $job): ?string
    {
        return blank($job->indeed_image_keys) ? null : trim((string) $job->indeed_image_keys);
    }
}
```

Syntax and design explained:

- `?string` return: a string, or `null` meaning "nothing found".
- `A() ?? B()`: PHP evaluates `B()` **only if** `A()` returned `null`. That one operator _is_ the priority
  rule: scraped first, manual second.
- `firstWhere('source', Enum::Indeed)` runs on an Eloquent **Collection** (the relation is already loaded in
  memory, so no query). Compare an enum case with strict equality to match the cast.
- `$indeedJob?->raw_payload`: null-safe, so a missing row gives `null` instead of an error.
- `data_get($array, 'record.indeed_image_keys')`: dot-path into nested arrays, `null` if any step is missing.
- `private` helpers: only `resolve()` is public API; the steps can change without affecting callers.

### Step 2: inject it where it's needed

```php
class IndeedJobExportFormatter
{
    public function __construct(
        private readonly IndeedJobImageKeysResolver $imageKeysResolver,
    ) {}

    public function resolveIndeedImageKeys(Job $job): string
    {
        return $this->imageKeysResolver->resolve($job) ?? '';
    }
}
```

No provider change needed: the container sees the type hint and builds `IndeedJobImageKeysResolver`
automatically (automatic resolution, section 8.2).

Use the **same** resolver in `JobExportService::validateIndeedImageKeys()`, so validation and the CSV can never
disagree.

### Step 3: test the four cases

1. scraped value present → wins
2. scraped row missing → manual
3. scraped value blank → manual
4. both blank → `null` (export rejected)

Then `php artisan test --compact --filter=IndeedJobImageKeysResolver` and `vendor/bin/pint --dirty`.

### Step 4: the manual button (frontend wiring only)

The backend already accepts any valid spider, so no route change is needed. The React button just passes
`spider="indeed-job-details"` and new translation keys (`indeedJobsTrigger`, `indeedJobsRunning`,
`indeedJobsTooltip`) in both `ja` and `en`. `ScrapingJobTriggerButton` already handles polling, toasts,
and 409/422 errors.

---

## 24. Debugging toolbox

```bash
# Where does this URL go?
docker exec indeed-seo-api php artisan route:list --path=scraping

# What's scheduled?
docker exec indeed-seo-api php artisan schedule:list

# Environment / versions / drivers
docker exec indeed-seo-api php artisan about

# Run PHP interactively against the real app (models, services, cache)
docker exec -it indeed-seo-api php artisan tinker

# Config problems
docker exec indeed-seo-api php artisan config:clear
docker exec indeed-seo-api php artisan cache:clear

# Queue
docker exec indeed-seo-api php artisan queue:work
docker exec indeed-seo-api php artisan queue:failed
docker exec indeed-seo-api php artisan queue:retry all

# Logs
docker exec indeed-seo-api tail -f storage/logs/laravel.log
```

In code: `dump($x)` prints and continues, `dd($x)` ("dump and die") prints and stops, `Log::debug('msg', [...])`
writes to the log.

Tinker examples:

```php
>>> App\Models\ScrapingJob::query()->latest()->first();
>>> App\Models\Company::find(3)->indeedCredentials;
>>> Cache::lock("scraping-job:dispatch:3:indeed-job-details")->forceRelease();
>>> data_get(App\Models\IndeedJob::latest('id')->first()?->raw_payload, 'record.indeed_image_keys');
```

Docs: [Tinker](https://laravel.com/docs/artisan#tinker) · [Logging](https://laravel.com/docs/logging) · [Error handling](https://laravel.com/docs/errors)

---

## 25. Common gotchas

| Symptom                                      | Likely cause                                                            |
| -------------------------------------------- | ----------------------------------------------------------------------- |
| `MassAssignmentException` / column not saved | attribute missing from `$fillable`                                      |
| Relation is `null` unexpectedly              | wrong foreign key name, or you forgot `with()` and the data isn't there |
| Page is slow with lots of rows               | N+1; add `->with('relation')`                                           |
| `$model->status === 'pending'` never true    | `status` is cast to an enum; compare to the enum case                   |
| Works locally, `null` config in production   | `env()` used outside `config/`; config is cached                        |
| Validation returns a redirect instead of 422 | client didn't send `Accept: application/json`                           |
| 404 on a route-model-bound route             | row missing, or parameter name ≠ type-hinted variable name              |
| Route never matches                          | declared after a wildcard route (`/{id}` before `/status`)              |
| Job dispatched but nothing happens           | no queue worker running, or `QUEUE_CONNECTION=sync` expectations differ |
| Cron job never fires                         | server has no `schedule:run` cron entry                                 |
| Dispatch returns 409 though nothing runs     | stale cache lock; wait for TTL or `forceRelease()` (see section 9)      |
| Indeed cron dispatched to wrong companies    | `dispatchAll()` not spider-aware (section 18.3)                         |
| Changing an old migration has no effect      | it already ran; write a new migration                                   |
| `Call to a member function x() on null`      | missing `?->` or the relation/row doesn't exist                         |
| `"text $var"` vs `'text $var'` confusion     | only double quotes interpolate                                          |

---

## 26. Study plan and exercises

Follow this order; each step uses files you'll really touch.

1. **PHP reading skills.** Section 3. Read `ScrapingSpiderEnum` and `IndeedRawStore`.
2. **Request path.** Sections 4-7. Read `routes/api/*.php`, `TriggerScrapingJobRequest`,
   `ScrapingJobController`, `ScrapingJobCompleteRequest`.
3. **Container and services.** Sections 8-10. Read `AppServiceProvider`, `ProviderRegistry`,
   `ScrapingJobService`, `IngestApplicationService`.
4. **Data.** Sections 12-14. Read `ScrapingJob`, `Company`, `IndeedJobApplication`, and the matching migrations.
5. **Output.** Section 15. Read `ScrapingJobResource`.
6. **Background work.** Sections 16-18. Read `SyncIngestedApplicationJob`, `ScrapingDispatchCompanies`,
   the schedule in `bootstrap/app.php`.
7. **Tests.** Section 20. Read `tests/Feature/ScrapingIndeedApplicationCompleteTest.php`, then write your own.

### Exercises (small → real)

1. In tinker, list the last 5 `ScrapingJob` rows with their company names. Do it once with an N+1 and once with
   `with('company')`; compare queries using `DB::enableQueryLog(); ...; DB::getQueryLog();`.
2. Add a `scopeFailed()` to `ScrapingJob` and use it in tinker.
3. Add a new optional filter `spider` to `GET /admin/scraping-jobs` (hint: `ScrapingJobController::index`
    - validate it with `Rule::enum`). Write a feature test.
4. Add the two Indeed cron entries (section 18) and verify them with `schedule:list`.
5. Implement the spider-aware company selector in **one** place (`ScrapingJobService`) and call it from the
   command too. Test that an Airwork-only company is _not_ returned for `indeed-job-details`.
6. Build `IndeedJobImageKeysResolver` with its four tests (section 23).
7. Add a new enum case and a `match` that handles it. Remove one arm and see `UnhandledMatchError`.

---

## 27. Documentation index

### Laravel

| Topic                | Link                                            |
| -------------------- | ----------------------------------------------- |
| Request lifecycle    | https://laravel.com/docs/lifecycle              |
| Directory structure  | https://laravel.com/docs/structure              |
| Routing              | https://laravel.com/docs/routing                |
| Middleware           | https://laravel.com/docs/middleware             |
| Controllers          | https://laravel.com/docs/controllers            |
| Requests / responses | https://laravel.com/docs/requests · /responses  |
| Validation           | https://laravel.com/docs/validation             |
| Service container    | https://laravel.com/docs/container              |
| Service providers    | https://laravel.com/docs/providers              |
| Facades              | https://laravel.com/docs/facades                |
| Eloquent             | https://laravel.com/docs/eloquent               |
| Relationships        | https://laravel.com/docs/eloquent-relationships |
| Mutators and casting | https://laravel.com/docs/eloquent-mutators      |
| API resources        | https://laravel.com/docs/eloquent-resources     |
| Query builder        | https://laravel.com/docs/queries                |
| Migrations           | https://laravel.com/docs/migrations             |
| Collections          | https://laravel.com/docs/collections            |
| Queues               | https://laravel.com/docs/queues                 |
| Events               | https://laravel.com/docs/events                 |
| Cache (locks)        | https://laravel.com/docs/cache                  |
| Artisan              | https://laravel.com/docs/artisan                |
| Task scheduling      | https://laravel.com/docs/scheduling             |
| Configuration        | https://laravel.com/docs/configuration          |
| Helpers              | https://laravel.com/docs/helpers                |
| Logging              | https://laravel.com/docs/logging                |
| Testing              | https://laravel.com/docs/testing                |
| Pint (formatter)     | https://laravel.com/docs/pint                   |

### PHP

| Concept                      | Link                                                                        |
| ---------------------------- | --------------------------------------------------------------------------- |
| Type declarations            | https://www.php.net/manual/en/language.types.declarations.php               |
| Arrays                       | https://www.php.net/manual/en/language.types.array.php                      |
| Null coalescing / comparison | https://www.php.net/manual/en/language.operators.comparison.php             |
| Closures / arrow functions   | https://www.php.net/manual/en/functions.anonymous.php · functions.arrow.php |
| Named arguments              | https://www.php.net/manual/en/functions.arguments.php                       |
| Classes and objects          | https://www.php.net/manual/en/language.oop5.php                             |
| Visibility                   | https://www.php.net/manual/en/language.oop5.visibility.php                  |
| Constructors / promotion     | https://www.php.net/manual/en/language.oop5.decon.php                       |
| Static members               | https://www.php.net/manual/en/language.oop5.static.php                      |
| Interfaces                   | https://www.php.net/manual/en/language.oop5.interfaces.php                  |
| Traits                       | https://www.php.net/manual/en/language.oop5.traits.php                      |
| Magic methods                | https://www.php.net/manual/en/language.oop5.magic.php                       |
| Anonymous classes            | https://www.php.net/manual/en/language.oop5.anonymous.php                   |
| Namespaces                   | https://www.php.net/manual/en/language.namespaces.php                       |
| Enums                        | https://www.php.net/manual/en/language.enumerations.php                     |
| `match`                      | https://www.php.net/manual/en/control-structures.match.php                  |
| Exceptions                   | https://www.php.net/manual/en/language.exceptions.php                       |
| Strings (interpolation)      | https://www.php.net/manual/en/language.types.string.php                     |

# Build a Demo Laravel API: "Scrape Tracker"

A hands-on tutorial. You will build a small Laravel API that mirrors the structure of the Indeed/Airwork
scraping project, then call it with `curl` and watch each Laravel concept do its job.

**You will not need Docker, SQS, or the Python scraper.** A queue table replaces SQS, SQLite replaces
PostgreSQL, and a small Artisan command plays the role of the scraper by sending a signed callback to your API.

> Companion to `laravel-practical-guide.md`. Each phase says which concept it demonstrates, so you can jump
> back to that section when something is unclear.

---

## Table of Contents

0. [What you will build](#0-what-you-will-build) — [https://laravel.com/framework/docs/12.x/introduction](https://laravel.com/framework/docs/12.x/introduction)
1. [Create the project](#q1-create-the-project-environment-and-config-file) — [https://laravel.com/framework/docs/12.x/installation](https://laravel.com/framework/docs/12.x/installation)
2. [Config, middleware, and JSON errors](#q2-admin-key-middleware-request-id-middleware-and-json-errors) — [https://laravel.com/framework/docs/12.x/configuration](https://laravel.com/framework/docs/12.x/configuration)
3. [Enums](#b-enums-and-exceptions) — [https://www.php.net/manual/en/language.enumerations.php](https://www.php.net/manual/en/language.enumerations.php)
4. [Database: migrations](#c-data-model) — [https://laravel.com/framework/docs/12.x/migrations](https://laravel.com/framework/docs/12.x/migrations)
5. [Models and factories](#c-data-model) — [https://laravel.com/framework/docs/12.x/eloquent](https://laravel.com/framework/docs/12.x/eloquent)
6. [Ingestion layer: interface, trait, registry](#d-ingestion-layer) — [https://laravel.com/framework/docs/12.x/container](https://laravel.com/framework/docs/12.x/container)
7. [The main service: `ScrapingJobService`](#f-core-service) — [https://laravel.com/framework/docs/12.x/providers](https://laravel.com/framework/docs/12.x/providers)
8. [Queue job, event, listener](#e-events) — [https://laravel.com/framework/docs/12.x/queues](https://laravel.com/framework/docs/12.x/queues)
9. [HTTP layer: requests, resources, controllers, routes](#g-http-layer) — [https://laravel.com/framework/docs/12.x/controllers](https://laravel.com/framework/docs/12.x/controllers)
10. [Commands and the scheduler](#h-console-and-scheduler) — [https://laravel.com/framework/docs/12.x/artisan](https://laravel.com/framework/docs/12.x/artisan)
11. [Run it and make API calls](#s29-end-to-end-demo-script) — [https://laravel.com/framework/docs/12.x/routing](https://laravel.com/framework/docs/12.x/routing)
12. [Write tests](#i-tests) — [https://laravel.com/framework/docs/12.x/testing](https://laravel.com/framework/docs/12.x/testing)
13. [Poke at the internals](#24-debugging-toolbox) — [https://laravel.com/framework/docs/12.x/artisan#tinker](https://laravel.com/framework/docs/12.x/artisan#tinker)
14. [Exercises](#26-study-plan-and-exercises) — [https://laravel.com/framework/docs/12.x/testing](https://laravel.com/framework/docs/12.x/testing)
15. [Troubleshooting](#25-common-gotchas) — [https://laravel.com/framework/docs/12.x/errors](https://laravel.com/framework/docs/12.x/errors)
16. [Concept map](#1-the-big-picture-one-request-end-to-end) — [https://laravel.com/framework/docs/12.x/lifecycle](https://laravel.com/framework/docs/12.x/lifecycle)

Items 13–16 point to the companion guide at the top of this file. The other links jump into the walkthrough below.

---

## 0. What you will build

### The story

Companies have job postings on external sites (Indeed, Airwork). Admins trigger a **scraping job** for a
company. A scraper (simulated here) does its work, then **calls back** with the scraped records. The API stores
them, and any new or changed record is synced by a **background job**. Every day a **scheduler** triggers
scraping for all eligible companies.

### Endpoints

| Method | URL                                            | Auth           | Purpose                                  |
| ------ | ---------------------------------------------- | -------------- | ---------------------------------------- |
| `GET`  | `/api/admin/companies`                         | `X-Admin-Key`  | List companies                           |
| `POST` | `/api/admin/companies`                         | `X-Admin-Key`  | Create company (+ optional Indeed email) |
| `GET`  | `/api/admin/scraping-jobs`                     | `X-Admin-Key`  | Paginated list with filters              |
| `GET`  | `/api/admin/scraping-jobs/status`              | `X-Admin-Key`  | Is a job running for company + spider?   |
| `GET`  | `/api/admin/scraping-jobs/{scrapingJob}`       | `X-Admin-Key`  | One job                                  |
| `POST` | `/api/admin/scraping-jobs`                     | `X-Admin-Key`  | Trigger a job (201 / 409 / 422)          |
| `POST` | `/api/admin/scraping-jobs/trigger-all`         | `X-Admin-Key`  | Trigger for every eligible company       |
| `POST` | `/api/admin/scraping-jobs/{scrapingJob}/retry` | `X-Admin-Key`  | Retry a failed job                       |
| `GET`  | `/api/admin/postings`                          | `X-Admin-Key`  | Scraped postings                         |
| `POST` | `/api/scraping-jobs/{spider}/complete`         | HMAC signature | Scraper callback (machine to machine)    |

### Architecture

```
curl / Postman
     │
     ▼
Routes ──► Middleware (request id, admin key) ──► FormRequest (validate / authorize)
                                                        │
                                                        ▼
                                                  Controller (thin)
                                                        │
                                                        ▼
                                              ScrapingJobService
                                    ┌───────────────┼────────────────┐
                                    ▼               ▼                ▼
                               Cache lock     Eloquent models   IngestPostingService
                                                                     │
                                                                     ▼
                                                  ProviderRegistry → IndeedRawStore / AirworkRawStore
                                                                     │
                                                                     ▼
                                                          SyncPostingJob (queue worker)
                                                          ScrapingJobCompleted event → listener
```

> **New to the ingestion layer?** Read [Ingestion in plain English](#ingestion-in-plain-english) and
> [Where the container layer fits](#where-the-container-layer-fits) before starting Q11.

### Final file tree (what you will create)

```
app/
  Console/Commands/ScrapingDispatchCompanies.php
  Console/Commands/SimulateScraper.php
  Enums/IngestOutcomeEnum.php
  Enums/ScrapingJobStatusEnum.php
  Enums/ScrapingSpiderEnum.php
  Events/ScrapingJobCompleted.php
  Exceptions/MissingCredentialsException.php
  Exceptions/ScrapingJobAlreadyRunningException.php
  Http/Controllers/Api/CompanyController.php
  Http/Controllers/Api/PostingController.php
  Http/Controllers/Api/ScrapingJobController.php
  Http/Controllers/Api/ScrapingJobCompleteController.php
  Http/Middleware/AssignRequestId.php
  Http/Middleware/EnsureAdminApiKey.php
  Http/Requests/ScrapingJobCompleteRequest.php
  Http/Requests/StoreCompanyRequest.php
  Http/Requests/TriggerScrapingJobRequest.php
  Http/Resources/CompanyResource.php
  Http/Resources/PostingResource.php
  Http/Resources/ScrapingJobResource.php
  Jobs/SyncPostingJob.php
  Listeners/LogScrapingJobResult.php
  Models/Company.php, CompanyIndeedCredential.php, ScrapingJob.php, ScrapedPosting.php
  Providers/AppServiceProvider.php
  Services/Scraping/ScrapingJobService.php
  Services/Ingestion/IngestPostingService.php
  Services/Ingestion/ProviderRegistry.php
  Services/Ingestion/ProviderRawStoreInterface.php
  Services/Ingestion/Concerns/UpsertsPostings.php
  Services/Ingestion/Providers/IndeedRawStore.php
  Services/Ingestion/Providers/AirworkRawStore.php
bootstrap/app.php
config/scraping.php
database/factories/CompanyFactory.php, ScrapingJobFactory.php
database/migrations/*  (4 tables)
database/seeders/DatabaseSeeder.php
routes/api.php, routes/console.php
tests/Feature/TriggerScrapingJobTest.php, ScrapingJobCompleteTest.php
```

# Scrape Tracker: Build It Question by Question

Build the whole Laravel API from the learning guide by asking the questions below **one at a time, in order**.
Each question is a concrete implementation task. Each solution gives the exact file path, the complete code,
and a short explanation of the PHP/Laravel ideas used.

**How to use this file**

1. Read a question in [Part 1](#part-1-questions).
2. Try it yourself (or ask me: _"Answer Q7"_) and compare with the matching solution in [Part 2](#part-2-solutions).
3. Use the **Learn** link to jump to the concept in your learning document.
4. Run the **Verify** step before moving on. Every later question depends on the earlier ones.

> **Link note:** `Learn` links point to `README.md#...` (your learning document, same folder). If you paste this
> content into the same file as the learning document, replace `README.md#` with `#`.

**Project in one paragraph:** Companies have postings on Indeed/Airwork. An admin triggers a _scraping job_; a
scraper (simulated by an Artisan command) calls back with records signed by an HMAC. The API upserts the records,
queues a sync job for new or changed rows, fires an event, and a scheduler triggers every eligible company daily.
An extra phase adds Indeed job applications with enum-cast sync status.

---

# Part 1: Questions

| Phase                    | Questions                                                                                       |
| ------------------------ | ----------------------------------------------------------------------------------------------- |
| A. Setup                 | [Q1](#q1) [Q2](#q2)                                                                             |
| B. Enums and exceptions  | [Q3](#q3) [Q4](#q4) [Q5](#q5) [Q6](#q6)                                                         |
| C. Data model            | [Q7](#q7) [Q8](#q8) [Q9](#q9) [Q10](#q10)                                                       |
| D. Ingestion layer       | [Q11](#q11) [Q12](#q12) [Q13](#q13) [Q14](#q14) [Q15](#q15)                                     |
| E. Events                | [Q16](#q16)                                                                                     |
| F. Core service          | [Q17](#q17)                                                                                     |
| G. HTTP layer            | [Q18](#q18) [Q19](#q19) [Q20](#q20) [Q21](#q21) [Q22](#q22) [Q23](#q23) [Q24](#q24) [Q25](#q25) |
| H. Console and scheduler | [Q26](#q26) [Q27](#q27) [Q28](#q28) [Q29](#q29)                                                 |
| I. Tests                 | [Q30](#q30) [Q31](#q31) [Q32](#q32) [Q33](#q33)                                                 |
| J. Applicants extension  | [Q34](#q34) [Q35](#q35) [Q36](#q36)                                                             |
| K. Hardening             | [Q37](#q37)                                                                                     |

## A. Setup

<a id="q1"></a>

### Q1. Create the project, environment, and config file

Create a new Laravel app named `scrape-tracker`, install API support, and configure `.env` for PGSQL with the
`database` queue and cache drivers. Add `config/scraping.php` exposing three values: the admin API key
(`SCRAPING_ADMIN_API_KEY`), the stale-job timeout in minutes (default 45), and the Indeed email template
(default `hr.entry.office+{serial_number}@example.com`). Application code must read these only via `config()`.

**Why it matters:** `config/scraping.php` is the single source for the admin key, stale timeout, and Indeed email template.
Routes, commands, jobs, and services all read these values through `config()`, so one wrong value can break auth or credentials.
Keeping configuration correct and cache-safe is what lets the same code run locally, in tests, and in production.

[Learn about Config](README.md#19-config-and-environment) · [View solution](#s1)

<a id="q2"></a>

### Q2. Admin-key middleware, request-id middleware, and JSON errors

Create `EnsureAdminApiKey` (reads header `X-Admin-Key`, compares in constant time with the configured key, returns
401 if missing or wrong, and denies everyone if no key is configured) and `AssignRequestId` (reuses the incoming
`X-Request-Id` or generates a UUID, adds it to log context, and echoes it on the response). Register the first as
alias `protected`, append the second to the `api` group, and force JSON rendering of all errors for `api/*` URLs
in `bootstrap/app.php`.

**Why it matters:** These files form the API's security and observability boundary.
The admin key blocks every admin route, while the request ID makes each log line traceable across a request.
JSON error handling keeps failures machine-readable instead of redirecting API clients.

[Learn about Middleware](README.md#5-middleware) · [View solution](#s2)

## B. Enums and exceptions

<a id="q3"></a>

### Q3. `ScrapingSpiderEnum`

Write a string-backed enum `App\Enums\ScrapingSpiderEnum` with cases `IndeedJobDetails = 'indeed-job-details'`,
`IndeedJobApplications = 'indeed-job-applications'`, `AirregiJobs = 'airregi-jobs'`, and three methods:
`isIndeed(): bool` (using `match`), `provider(): string` (`'indeed'` or `'airwork'`), and
`callbackPath(): string` (`/api/scraping-jobs/{value}/complete`).

**Why it matters:** `ScrapingSpiderEnum` is the single source of truth for spider names used in URLs, the database, validation, and scheduling.
A missing or misspelled case routes work to the wrong provider or makes callbacks fail.
Every dispatch, callback, and cron entry depends on these values matching exactly.

[Learn about Enums](README.md#11-enums) · [View solution](#s3)

<a id="q4"></a>

### Q4. `ScrapingJobStatusEnum`

Write `App\Enums\ScrapingJobStatusEnum` with cases `Pending`, `Processing`, `Completed`, `Failed`, `TimedOut`
(values `pending`, `processing`, `completed`, `failed`, `timed_out`). Add `isRetryable()` (true for `Failed` and
`TimedOut`) and `isTerminal()` (true for `Completed`, `Failed`, `TimedOut`), both exhaustive `match` expressions
with no `default` arm.

**Why it matters:** `ScrapingJobStatusEnum` defines the job lifecycle that the whole scraper relies on.
Retry, terminal-state checks, dashboards, and stale-job expiry all branch on these cases.
An incorrect value can leave jobs stuck, retryable when they should be finished, or invisible to the UI.

[Learn about match](README.md#312-match-vs-switch) · [View solution](#s4)

<a id="q5"></a>

### Q5. `IngestOutcomeEnum` and `JobSeekerSyncStatusEnum`

Write `IngestOutcomeEnum` (`Created`, `Updated`, `Unchanged`) and `JobSeekerSyncStatusEnum` (`Pending`, `Synced`,
`Failed`). The second needs `isRetryable()` (true only for `Failed`), `label()` (human-readable text per case), and
`static fromIngestOutcome(IngestOutcomeEnum $outcome): self` mapping `Created`/`Updated` to `Pending` and
`Unchanged` to `Synced`.

**Why it matters:** These enums tell the ingestion layer whether a record is new, changed, or unchanged, and whether it still needs syncing.
They prevent unnecessary queue work and drive status labels and retry decisions in the API.
The mapping methods keep that business meaning in one place instead of scattered string comparisons.

[Learn about Enums](README.md#11-enums) · [View solution](#s5)

<a id="q6"></a>

### Q6. Domain exceptions

Create `MissingCredentialsException` and `ScrapingJobAlreadyRunningException` in `App\Exceptions`, both extending
`RuntimeException` with empty bodies (the type is the information; controllers will map them to 422 and 409).

**Why it matters:** These typed exceptions separate expected business failures from real bugs.
Controllers can map missing credentials to 422 and an already-running job to 409 without inspecting messages.
That makes API responses predictable, logs clearer, and tests able to assert on exception types.

[Learn about Exceptions](README.md#314-exceptions) · [View solution](#s6)

## C. Data model

<a id="q7"></a>

### Q7. `Company` and `CompanyIndeedCredential` (migrations + models)

Create both models with migrations, in that order. `companies`: `id`, `name`, nullable unique `serial_number`,
boolean `airwork_enabled` default false, timestamps. `company_indeed_credentials`: `id`, unique foreign key
`company_id` (cascade on delete), `email`, timestamps. `Company` has a `hasOne` `indeedCredentials`, a `hasMany`
`scrapingJobs`, a boolean cast, and `resolveIndeedLoginEmail(): string` that returns the custom credential email,
else the template email built from `serial_number`, else throws `MissingCredentialsException`.
`CompanyIndeedCredential` belongs to `Company`.

**Why it matters:** `Company` and its credential row are the core tenant records for the entire project.
Every scraping job, eligibility query, and Indeed login resolution starts from a company and its credential or serial number.
If this model or relation is wrong, dispatch and callback flows fail for every company.

[Learn about Relationships](README.md#13-relationships-and-eager-loading) · [View solution](#s7)

<a id="q8"></a>

### Q8. `ScrapingJob` (migration + model)

Create `scraping_jobs`: `id`, `company_id` (FK, cascade), `spider` (string 64), `status` (string 32, default
`pending`), `callback_secret` (string 64), `retry_count` (unsigned int, default 0), nullable `error_message`,
four unsigned-int counters `records_created`, `records_updated`, `records_unchanged`, `records_failed` (default 0),
nullable `completed_at`, timestamps, and a composite index on `company_id, spider, status`. The model casts
`spider` and `status` to the enums and `completed_at` to datetime, hides `callback_secret`, has `company()` and
`postings()` relations, and an `active()` scope for `Pending` and `Processing`.

**Why it matters:** `ScrapingJob` is the central record that tracks one scraping run from dispatch to callback.
Its status, secret, counters, and timestamps power retries, stale-job expiry, dashboards, and audit logs.
A broken schema or cast here affects every admin endpoint and queued workflow.

[Learn about Eloquent models](README.md#12-eloquent-models) · [View solution](#s8)

<a id="q9"></a>

### Q9. `ScrapedPosting` (migration + model)

Create `scraped_postings`: `id`, nullable FK `scraping_job_id` (null on delete), `provider` (string 32),
`external_id` (string), `row_hash` (char 64), `payload` (json), nullable `synced_at`, timestamps, a composite
unique key on `provider, external_id`, and an index on `row_hash`. The model casts `payload` to array and
`synced_at` to datetime, and belongs to `ScrapingJob`. Then run all migrations.

**Why it matters:** `ScrapedPosting` is the raw landing table for everything the scraper sends back.
Its provider/external ID pair and row hash make ingestion idempotent and change detection possible.
Downstream sync jobs and admin browsing depend on this table being accurate.

[Learn about Migrations](README.md#14-migrations) · [View solution](#s9)

<a id="q10"></a>

### Q10. Factories and seeder

Fill `CompanyFactory` (fake name, no serial number, `airwork_enabled` false, plus an `airwork()` state) and
`ScrapingJobFactory` (creates its own company, spider `IndeedJobApplications`, status `Pending`, random 40-char
secret). Write `DatabaseSeeder` creating three companies: one Indeed company with a serial number, one with a
custom Indeed login email, and one Airwork-enabled company.

**Why it matters:** Factories and the seeder give every developer and test a reproducible starting dataset.
Factories isolate tests from real data, while the seeder creates one company for each eligibility path.
Bad seed data makes demos fail and hides bugs in dispatch, credentials, and filtering.

[Learn about Testing and factories](README.md#20-testing) · [View solution](#s10)

## D. Ingestion layer

### Ingestion in plain English

**Ingestion** means "taking data from the outside world into our system". The scraper sends raw job postings
from Indeed or Airwork; the ingestion layer decides what to do with each record:

1. Which provider sent it (`indeed` or `airwork`)?
2. Have we seen this posting before?
3. Is the content different from last time?
4. Should we save it and queue a background sync?

A useful analogy is a warehouse receiving desk:

| Warehouse         | In this project                      | Responsibility                                          |
| ----------------- | ------------------------------------ | ------------------------------------------------------- |
| Receiving desk    | `IngestPostingService`               | runs the whole delivery process                         |
| Parcel label      | `provider` + `external_id`           | uniquely identifies the posting                         |
| Unpacking station | `IndeedRawStore` / `AirworkRawStore` | handles one provider's records                          |
| Shared checklist  | `UpsertsPostings` trait              | create / unchanged / update rules used by every station |
| Fingerprint       | `row_hash` (SHA-256 of the record)   | detects whether the content changed                     |
| Staff directory   | `ProviderRegistry`                   | finds the right station by provider name                |
| Loading bay       | `SyncPostingJob`                     | moves new/changed records on in the background          |

### Where the container layer fits

"Container" here means Laravel's **service container** — the object factory that builds classes and their
dependencies for you. It is not Docker. You do not write `new IngestPostingService(new ProviderRegistry(...))`.
You ask the container for `IngestPostingService` and it builds the chain.

Two lookup tables are involved, and they solve different problems:

| Lookup table              | Key                                   | Value              | Used by                  |
| ------------------------- | ------------------------------------- | ------------------ | ------------------------ |
| Laravel service container | class / interface name                | constructed object | framework and `app(...)` |
| `ProviderRegistry`        | provider string (`indeed`, `airwork`) | raw store object   | `IngestPostingService`   |

`AppServiceProvider` connects them:

```php
$this->app->tag(
    [IndeedRawStore::class, AirworkRawStore::class],
    ProviderRawStoreInterface::class,
);

$this->app->singleton(ProviderRegistry::class, fn ($app): ProviderRegistry => new ProviderRegistry(
    $app->tagged(ProviderRawStoreInterface::class),
));
```

In words:

1. "These two classes implement `ProviderRawStoreInterface`."
2. "When someone asks for `ProviderRegistry`, build it from all implementations of that interface."
3. "Keep that registry as a singleton (one shared instance)."

The flow for one record looks like this:

```text
scraper callback
  │  provider = "indeed", external_id = "job-123", record = {...}
  ▼
IngestPostingService             ← orchestrator
  │  "registry, who handles indeed?"
  ▼
ProviderRegistry                 ← provider-name lookup
  │  returns IndeedRawStore
  ▼
IndeedRawStore                   ← provider adapter
  │  uses UpsertsPostings trait
  ▼
UpsertsPostings::upsertPosting() ← shared create/update logic
  │  compares provider + external_id + row_hash
  ▼
ScrapedPosting row               ← database
  │  Created or Updated (not Unchanged)
  ▼
SyncPostingJob                   ← queued background work
```

### A worked example

```php
$ingest = app(App\Services\Ingestion\IngestPostingService::class);

$entry = [
    'external_id' => 'job-123',
    'record' => ['title' => 'Backend developer', 'company' => 'Acme'],
];

$outcome = $ingest->ingest('indeed', $entry);
```

| Run | What changed                 | Database result                                   | Queue                       |
| --- | ---------------------------- | ------------------------------------------------- | --------------------------- |
| 1   | first time we see `job-123`  | row inserted, outcome `Created`                   | `SyncPostingJob` dispatched |
| 2   | same payload again           | row found, hashes match, outcome `Unchanged`      | nothing dispatched          |
| 3   | title changed in the payload | row updated, `synced_at` reset, outcome `Updated` | `SyncPostingJob` dispatched |

That is the whole layer: **one orchestrator, one lookup table, one adapter per provider, one shared upsert trait,
and one queued follow-up job.** The interface exists so the orchestrator never needs to know which provider it is
talking to; the registry exists so adding a provider does not require an `if` statement in the service.

<a id="q11"></a>

### Q11. `ProviderRawStoreInterface` and the `UpsertsPostings` trait

Create an interface with `provider(): string` and
`upsert(string $externalId, array $record, string $hash, ?int $scrapingJobId): array` (returning a tuple of
`ScrapedPosting` and `IngestOutcomeEnum`, documented with PHPDoc). Create a trait `UpsertsPostings` whose protected
`upsertPosting(...)` finds by `provider + external_id`, creates it (`Created`), reports `Unchanged` when the hash
matches, or updates it, resets `synced_at` to null, and reports `Updated`.

**Why it matters:** The interface and trait are the contract and shared engine of the ingestion layer.
Every provider store promises the same methods, while the trait keeps dedupe, update, and hash logic identical.
This is what lets the project add providers without copying fragile upsert code.

[Learn about interfaces and traits](README.md#37-inheritance-interfaces-traits) · [View solution](#s11)

<a id="q12"></a>

### Q12. `IndeedRawStore` and `AirworkRawStore`

Create two classes implementing the interface and using the trait. `IndeedRawStore::provider()` returns
`'indeed'`, `AirworkRawStore::provider()` returns `'airwork'`, and each `upsert()` delegates to `upsertPosting()`.

**Why it matters:** These two classes are the provider adapters that actually write scraped records.
They isolate Indeed and Airwork differences behind one interface, so the orchestrator stays provider-agnostic.
A defect in one store stops that provider's ingestion without breaking the other.

[Learn about the registry pattern](README.md#84-the-registry-pattern-used-in-this-project) · [View solution](#s12)

<a id="q13"></a>

### Q13. `ProviderRegistry` and container wiring

Write `ProviderRegistry` taking an `iterable` of raw stores, indexing them by `provider()`, and exposing
`rawStore(string $provider)` that throws `InvalidArgumentException` for unknown keys. In `AppServiceProvider`,
tag both stores under the interface and register the registry as a singleton built from the tagged stores.

**Why it matters:** `ProviderRegistry` turns a provider string from a callback into the correct store object.
The container wiring builds it once from tagged implementations, so adding a provider needs no edits in the service.
If this mapping is wrong, callbacks fail with “unknown provider” or write through the wrong adapter.

[Learn about the service container](README.md#8-service-container-dependency-injection-providers) · [View solution](#s13)

<a id="q14"></a>

### Q14. `SyncPostingJob`

Create a queued job taking `int $postingId`, with `$tries = 3`. `handle()` loads the posting, returns quietly if it
no longer exists (idempotent), sets `synced_at` to now, and logs the sync.

**Why it matters:** `SyncPostingJob` moves slower downstream work out of the callback request.
It keeps the API responsive and retries failed syncs up to three times.
Because it is idempotent, repeated or delayed runs cannot corrupt the synced data.

[Learn about Jobs and queues](README.md#16-jobs-and-queues) · [View solution](#s14)

<a id="q15"></a>

### Q15. `IngestPostingService`

Write `IngestPostingService::ingest(string $provider, array $entry, ?int $scrapingJobId = null): IngestOutcomeEnum`.
It hashes `$entry['record']` with SHA-256, asks the registry for the provider's store, upserts, destructures the
returned tuple, dispatches `SyncPostingJob` unless the outcome is `Unchanged`, and returns the outcome.

**Why it matters:** `IngestPostingService` is the single entry point for turning a callback record into a stored posting.
It hashes the payload, finds the right provider store, upserts, and decides whether to queue a sync.
Any bug here affects deduplication, change detection, and queue volume for every provider.

[Learn about Services](README.md#10-services-your-business-logic-layer) · [View solution](#s15)

## E. Events

<a id="q16"></a>

### Q16. `ScrapingJobCompleted` event and `LogScrapingJobResult` listener

Create the event (holds a `ScrapingJob`) and a queued listener that logs the job id, status, created count, and
updated count. Rely on listener auto-discovery (no manual registration).

**Why it matters:** The event and listener decouple “a job finished” from what should happen next.
The service only fires the event, while logging, notifications, or exports can subscribe later without changing it.
That keeps the core scraping flow stable as post-completion behaviour grows.

[Learn about Events](README.md#17-events-and-listeners) · [View solution](#s16)

## F. Core service

<a id="q17"></a>

### Q17. `ScrapingJobService`

Write `App\Services\Scraping\ScrapingJobService` with: `dispatch(Company, ScrapingSpiderEnum): ScrapingJob`
(builds credentials first, takes a 10-second cache lock per company and spider, throws
`ScrapingJobAlreadyRunningException` if the lock is busy or a job is already running, creates the job with a random
secret, logs the payload including `callback_url`, and always releases the lock); `runningJob(int, spider): ?ScrapingJob`
(active jobs newer than the stale timeout); `handleComplete(ScrapingJob, string $status, array $records)` (failure path or
per-record ingest with counts, one try/catch per record, then fires the event); `retry(ScrapingJob)`;
`dispatchAll(spider): Collection` (skips companies that throw); and `eligibleCompaniesQuery(spider): Builder`
(Indeed: custom email or serial number; Airwork: `airwork_enabled`). Credential building uses `match (true)`.

**Why it matters:** `ScrapingJobService` is the heart of the scraping workflow.
It prevents duplicate jobs with locks, selects eligible companies, creates jobs, ingests callbacks, and handles retries.
A bug here can double-scrape companies, lose records, or leave operators unable to recover failed runs.

[Learn about Facades and cache locks](README.md#cache-locks-used-by-dispatch) · [View solution](#s17)

## G. HTTP layer

<a id="q18"></a>

### Q18. `TriggerScrapingJobRequest` and `StoreCompanyRequest`

Trigger: `company_id` required integer that exists, `spider` optional and a valid enum value. Company: `name`
required (max 255), `serial_number` nullable, unique, max 64, `airwork_enabled` optional boolean, `indeed_email`
nullable email. Both authorize everyone (the route middleware guards access).

**Why it matters:** These Form Requests are the gatekeepers for admin-created jobs and companies.
They enforce enum values, existing company IDs, unique serial numbers, and email shape before anything touches the database.
Without them, invalid input reaches the service layer and produces inconsistent jobs or companies.

[Learn about Form Requests](README.md#7-form-requests-validation-and-authorization) · [View solution](#s18)

<a id="q19"></a>

### Q19. `ScrapingJobCompleteRequest` with HMAC authorization

Authorize by signature: load the job from `job_id` (memoised with `??=`, exposed via `scrapingJob()`), compute
`hash_hmac('sha256', rawBody, job.callback_secret)`, and compare in constant time with the `X-HMAC-Signature`
header. Rules: `job_id` required integer, `status` in `success|failed`, optional `records` array, each with required
string `external_id` (max 255) and required array `record`.

**Why it matters:** This request authenticates the machine-to-machine scraper callback.
It verifies the HMAC over the exact raw body before any record is trusted or stored.
Without it, anyone who guesses a job ID could inject fake postings into the system.

[Learn about authorization in requests](README.md#7-form-requests-validation-and-authorization) · [View solution](#s19)

<a id="q20"></a>

### Q20. API Resources

Create `ScrapingJobResource` (id, company id and name when loaded, spider and status values, retry count, error
message, nested record counters, `is_terminal`, ISO dates; never the secret), `CompanyResource` (including the
Indeed email when the relation is loaded), and `PostingResource`.

**Why it matters:** These resources define the stable JSON contract between the API and its clients.
They hide internal columns such as `callback_secret` and expose only the fields the admin UI needs.
`whenLoaded` also prevents hidden extra queries when a relation was not eager-loaded.

[Learn about API Resources](README.md#15-api-resources) · [View solution](#s20)

<a id="q21"></a>

### Q21. `CompanyController`

`index()` returns all companies with `indeedCredentials` eager-loaded. `store()` validates via the Form Request,
defaults `airwork_enabled` to false, creates the company and optional credentials inside one transaction, and
returns 201 with the resource.

**Why it matters:** `CompanyController` is the admin entry point for listing and creating companies.
Creation must write the company and optional Indeed credential atomically, or the two tables drift apart.
Eager loading keeps the company list fast as the number of tenants grows.

[Learn about Controllers](README.md#6-controllers) · [View solution](#s21)

<a id="q22"></a>

### Q22. `PostingController`

`index()` returns scraped postings, newest first, 15 per page, with an optional `provider` query filter.

**Why it matters:** `PostingController` gives operators visibility into the raw records the scraper stored.
Provider filtering and pagination keep that view usable when thousands of postings arrive.
It is often the first place a bad scrape or ingestion bug becomes visible.

[Learn about Eloquent queries](README.md#12-eloquent-models) · [View solution](#s22)

<a id="q23"></a>

### Q23. `ScrapingJobController`

Actions: `index` (filters `company_id`, `status`, `per_page` 1-100, eager-loaded company, paginated), `show`
(route-model binding), `status` (is a job running for company + spider), `store` (201, 409 when already running,
422 on missing credentials; default spider `AirregiJobs`), `triggerAll`, and `retry` (422 when not retryable,
201 on success).

**Why it matters:** `ScrapingJobController` is the main admin control surface for scraping.
It triggers jobs, reports running status, lists history, and retries failures.
Its exception mapping turns expected service failures into clear 409 or 422 responses instead of 500s.

[Learn about Controllers and binding](README.md#4-routing) · [View solution](#s23)

<a id="q24"></a>

### Q24. `ScrapingJobCompleteController` (invokable)

Resolve the `{spider}` URL segment to the enum (404 if unknown), abort with 422 when it does not match the job's
spider, call `handleComplete()` with named arguments, and return the job resource.

**Why it matters:** `ScrapingJobCompleteController` is the API endpoint the scraper calls when work finishes.
It resolves the spider, checks it matches the job, and hands the records to the ingestion service.
A mistake here can accept the wrong provider's payload or report results against the wrong job.

[Learn about invokable controllers](README.md#38-invokable-classes-and-anonymous-classes) · [View solution](#s24)

<a id="q25"></a>

### Q25. `routes/api.php`

Group all admin routes under `/admin` with the `protected` middleware. Declare `scraping-jobs/status` before
`scraping-jobs/{scrapingJob}`. Add the unprotected HMAC callback route `POST scraping-jobs/{spider}/complete`.

**Why it matters:** `routes/api.php` is the public contract for every client and the scraper.
It separates admin-key routes from the HMAC callback route and fixes the order of wildcard routes.
A wrong route or middleware assignment can expose admin actions or make callbacks unreachable.

[Learn about Routing](README.md#4-routing) · [View solution](#s25)

## H. Console and scheduler

<a id="q26"></a>

### Q26. `ScrapingDispatchCompanies` command

Signature `scraping:dispatch-companies {--spider=airregi-jobs}`. Reject unknown spiders with a failure exit code,
print the number of eligible companies, dispatch all, and print the dispatched count.

**Why it matters:** `ScrapingDispatchCompanies` is the cron entry point that starts scraping without a human.
It validates the spider and reuses the same eligibility query as the HTTP trigger, so the two cannot drift.
If this command fails, scheduled scraping silently stops and data goes stale.

[Learn about Artisan commands](README.md#18-console-commands-and-the-scheduler) · [View solution](#s26)

<a id="q27"></a>

### Q27. `SimulateScraper` command

Signature `scraper:simulate {job} {--fail} {--update}`. It builds three fake records, JSON-encodes the payload
once, signs that exact string with the job's secret, and POSTs it to the job's callback URL with Laravel's HTTP
client. `--fail` reports a failure; `--update` changes the first record's title.

**Why it matters:** `SimulateScraper` lets developers exercise the full callback flow without the real Python scraper.
It signs the exact JSON body and posts it to the API, matching the production security model.
That makes callback bugs reproducible and keeps demos independent of external infrastructure.

[Learn about the callback walkthrough](README.md#22-walkthrough-tracing-the-scraper-callback) · [View solution](#s27)

<a id="q28"></a>

### Q28. Daily schedule

In `routes/console.php`, schedule `scraping:dispatch-companies` for each of the three spiders: Airregi 06:30,
Indeed details 07:15, Indeed applications 07:30, all `Asia/Tokyo`, `onOneServer()` and `withoutOverlapping()`.

**Why it matters:** The schedule automates daily dispatch for all three spiders.
Timezone, single-server, and overlap settings prevent duplicate or missed runs in production.
Without it, companies are only scraped when someone manually triggers them.

[Learn about the Scheduler](README.md#18-console-commands-and-the-scheduler) · [View solution](#s28)

<a id="q29"></a>

### Q29. End-to-end demo script

Write `scripts/demo.sh`, a bash script that walks through the API with `curl`: missing key (401), list and create
companies, trigger (201), duplicate trigger (409), status, simulate callback, unchanged re-run, updated re-run, bad
signature (403), failure, retry (201), and retrying a completed job (422).

**Why it matters:** `scripts/demo.sh` is an executable acceptance test for the whole API.
It covers auth, validation, conflicts, callbacks, idempotency, retries, and error responses with real HTTP calls.
It doubles as onboarding documentation and a quick regression check after changes.

[Learn about the debugging toolbox](README.md#24-debugging-toolbox) · [View solution](#s29)

## I. Tests

<a id="q30"></a>

### Q30. `TriggerScrapingJobTest`

Test: admin key required, Indeed dispatch via serial-number fallback (201, no secret in JSON, row in DB), second
dispatch returns 409, Airwork spider without access returns 422, invalid spider fails validation.

**Why it matters:** `TriggerScrapingJobTest` protects the most important admin action: starting a scrape.
It proves the admin key is required, eligibility is enforced, and duplicate triggers return 409.
Regressions here would break the operator's primary workflow.

[Learn about Testing](README.md#20-testing) · [View solution](#s30)

<a id="q31"></a>

### Q31. `ScrapingJobCompleteTest`

Test with a signing helper that signs the raw body: bad signature gives 403 and stores nothing, a valid callback
stores records and pushes two `SyncPostingJob`s, resending the same data reports `unchanged` and pushes nothing new,
and a malformed record gives a validation error on `records.0.external_id`.

**Why it matters:** `ScrapingJobCompleteTest` protects the ingestion callback, where data enters the system.
It verifies signatures, record validation, idempotent re-sends, and sync dispatch counts.
A regression here risks accepting forged data or duplicating records.

[Learn about Testing](README.md#20-testing) · [View solution](#s31)

<a id="q32"></a>

### Q32. Eligibility and command tests

Test that `trigger-all` and the Artisan command only dispatch to eligible companies (Indeed: serial or custom
email; Airwork: `airwork_enabled`), that an unknown spider fails the command, and that an Airwork-only company is
never selected for an Indeed spider.

**Why it matters:** These tests ensure the right spider only selects the right companies.
They guard the shared eligibility query used by the HTTP trigger, the command, and the scheduler.
A mistake would scrape the wrong provider or waste jobs on ineligible companies.

[Learn about spider-aware selection](README.md#183-why-spider-aware-company-selection-matters) · [View solution](#s32)

<a id="q33"></a>

### Q33. Enum unit tests

Write a plain PHPUnit unit test (no database) covering `provider()`, `callbackPath()`, `tryFrom()` returning null,
the status helpers, and `fromIngestOutcome()`.

**Why it matters:** `EnumsTest` locks down enum values and business helper methods.
Those values are stored in the database and compared across validation, HTTP output, and retry logic.
Fast unit tests catch accidental renames before they corrupt data or break clients.

[Learn about Enums](README.md#11-enums) · [View solution](#s33)

## J. Applicants extension

<a id="q34"></a>

### Q34. `IndeedJobApplication` (migration, model, factory)

Generate model, migration, and factory together. Table: `external_application_id` (unique), `user_unique_id`
(indexed), `row_hash` (char 64, indexed), `application_record` (json), `job_seeker_sync_status` (string 32, default
`pending`, indexed), nullable `last_otp_received_at`, nullable FK `job_seeker_user_id` to `users` (null on delete),
timestamps. Model: fillable, casts (array, `JobSeekerSyncStatusEnum`, datetime), `jobSeekerUser()` relation,
`pendingSync()` and `forJobSeeker()` scopes. Factory: realistic definition plus `synced()` and `failed()` states.

**Why it matters:** This model and migration store scraped job applications for the applicants extension.
The unique external application ID prevents duplicate applicants, and the sync status drives retry behaviour.
The factory makes the whole feature testable without real applicant data.

[Learn about Eloquent models](README.md#12-eloquent-models) · [View solution](#s34)

<a id="q35"></a>

### Q35. `IndeedJobApplicationModelTest`

Four tests: casts (array, enum, Carbon), the `jobSeekerUser` relation, the `pendingSync()` scope, and the unique
`external_application_id` constraint throwing `QueryException`.

**Why it matters:** This test proves the application model's casts, relation, scope, and uniqueness constraint.
Those details decide whether the API returns arrays, enums, and related users correctly.
It catches schema or model drift before the applicants UI sees broken data.

[Learn about Database testing](README.md#20-testing) · [View solution](#s35)

<a id="q36"></a>

### Q36. Applications API (resource, controller, routes, test)

Add `GET /api/admin/applications` (filters `sync_status` via `Rule::enum`, `job_seeker_user_id`, `per_page`;
paginated) and `GET /api/admin/applications/{indeedJobApplication}`. The resource exposes the status value, its
label, and whether a sync retry is allowed. Provide the full updated `routes/api.php` and a feature test.

**Why it matters:** The applications API exposes applicant data to the admin UI with filters and pagination.
It returns status labels and retry flags so the frontend does not duplicate business rules.
It is the contract that makes the applicants extension usable by operators.

[Learn about API Resources](README.md#15-api-resources) · [View solution](#s36)

## K. Hardening

<a id="q37"></a>

### Q37. Expire stale jobs

Create `scraping:expire-stale`, which marks `Pending`/`Processing` jobs older than the stale timeout as `TimedOut`
(with an error message and `completed_at`) and prints the count. Schedule it every 15 minutes (one server, no
overlap). Provide the full updated `routes/console.php` and a test that also proves the expired job becomes
retryable.

**Why it matters:** `scraping:expire-stale` keeps job status honest when a scraper never calls back.
It marks old pending/processing jobs as timed out, which unblocks dispatch and enables retry.
Without it, dashboards show jobs running forever and operators cannot recover them.

[Learn about the Scheduler](README.md#18-console-commands-and-the-scheduler) · [View solution](#s37)

---

# Part 2: Solutions

Every solution lists the file path, complete code, the concepts used, and a **Verify** step.

---

## A. Setup

<a id="s1"></a>

### S1. Project, environment, and config

**Run:**

```bash
composer create-project laravel/laravel scrape-tracker
cd scrape-tracker
php artisan install:api      # answer "yes" if it offers to run migrations
```

**File:** `.env` (edit or add these lines; also copy the new keys into `.env.example`)

```dotenv
APP_URL=http://127.0.0.1:8000
DB_CONNECTION=sqlite
QUEUE_CONNECTION=database
CACHE_STORE=database

SCRAPING_ADMIN_API_KEY=secret-demo-key
SCRAPING_STALE_TIMEOUT_MINUTES=45
```

**File:** `config/scraping.php`

```php
<?php

return [
    'admin_api_key' => env('SCRAPING_ADMIN_API_KEY'),
    'stale_timeout_minutes' => (int) env('SCRAPING_STALE_TIMEOUT_MINUTES', 45),
    'indeed_email_template' => env('INDEED_EMAIL_TEMPLATE', 'hr.entry.office+{serial_number}@example.com'),
];
```

**Concepts**

- `env()` is called **only** in `config/*.php`; the app reads `config('scraping.admin_api_key')`. Production caches
  config, and `env()` elsewhere would return `null`.
- A config file is just a PHP file that returns an array.
- `(int)` casts the env string to an integer.

**Verify:** `php artisan tinker` then `config('scraping.stale_timeout_minutes')` gives `45`.

[Back to question](#q1) · [Learn about Config](README.md#19-config-and-environment)

---

<a id="s2"></a>

### S2. Middleware and JSON errors

**Run:**

```bash
php artisan make:middleware EnsureAdminApiKey
php artisan make:middleware AssignRequestId
```

**File:** `app/Http/Middleware/EnsureAdminApiKey.php`

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = config('scraping.admin_api_key');
        $given = (string) $request->header('X-Admin-Key');

        if (blank($expected) || ! hash_equals((string) $expected, $given)) {
            abort(401, 'Invalid admin key');
        }

        return $next($request);
    }
}
```

**File:** `app/Http/Middleware/AssignRequestId.php`

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class AssignRequestId
{
    public function handle(Request $request, Closure $next): Response
    {
        $id = $request->header('X-Request-Id') ?: (string) Str::uuid();

        Log::withContext(['request_id' => $id]);      // before: every log line carries the id

        $response = $next($request);                  // run the rest of the app

        $response->headers->set('X-Request-Id', $id); // after: echo the id back

        return $response;
    }
}
```

**File:** `bootstrap/app.php`

```php
<?php

use App\Http\Middleware\AssignRequestId;
use App\Http\Middleware\EnsureAdminApiKey;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias(['protected' => EnsureAdminApiKey::class]);
        $middleware->api(append: [AssignRequestId::class]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Always answer API errors as JSON, even without "Accept: application/json".
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request): bool => $request->is('api/*') || $request->expectsJson()
        );
    })
    ->create();
```

**Concepts**

- Middleware wraps the request: code before `$next($request)` runs inbound, code after runs outbound.
- `hash_equals()` compares in constant time (prevents timing attacks). Fail **closed** when no key is configured.
- `?:` (Elvis) uses the left value when truthy; `??` only checks `null`.
- `abort(401)` throws an HTTP exception; the controller never runs.
- `bootstrap/app.php` is where Laravel 11/12 registers routing, middleware, and exception behavior.

**Verify:** `php artisan serve`, then `curl -i http://127.0.0.1:8000/api/admin/companies` should show `X-Request-Id`
once routes exist (Q25). For now `php artisan about` should run without errors.

[Back to question](#q2) · [Learn about Middleware](README.md#5-middleware)

---

## B. Enums and exceptions

<a id="s3"></a>

### S3. `ScrapingSpiderEnum`

**File:** `app/Enums/ScrapingSpiderEnum.php`

```php
<?php

namespace App\Enums;

enum ScrapingSpiderEnum: string
{
    case IndeedJobDetails = 'indeed-job-details';
    case IndeedJobApplications = 'indeed-job-applications';
    case AirregiJobs = 'airregi-jobs';

    public function isIndeed(): bool
    {
        return match ($this) {
            self::IndeedJobDetails, self::IndeedJobApplications => true,
            self::AirregiJobs => false,
        };
    }

    public function provider(): string
    {
        return $this->isIndeed() ? 'indeed' : 'airwork';
    }

    public function callbackPath(): string
    {
        return "/api/scraping-jobs/{$this->value}/complete";
    }
}
```

**Concepts**

- A **backed enum** gives each case a string `->value`. Use the case object inside PHP and `->value` at the edges
  (DB, JSON, CLI).
- `match` is an expression with strict comparison; with no `default` it throws `UnhandledMatchError` if you add a
  case and forget an arm.
- Double-quoted strings interpolate `{$this->value}`.

**Verify (tinker):** `App\Enums\ScrapingSpiderEnum::from('indeed-job-details')->provider();` gives `"indeed"`.

[Back to question](#q3) · [Learn about Enums](README.md#11-enums)

---

<a id="s4"></a>

### S4. `ScrapingJobStatusEnum`

**File:** `app/Enums/ScrapingJobStatusEnum.php`

```php
<?php

namespace App\Enums;

enum ScrapingJobStatusEnum: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Completed = 'completed';
    case Failed = 'failed';
    case TimedOut = 'timed_out';

    public function isRetryable(): bool
    {
        return match ($this) {
            self::Failed, self::TimedOut => true,
            self::Pending, self::Processing, self::Completed => false,
        };
    }

    public function isTerminal(): bool
    {
        return match ($this) {
            self::Completed, self::Failed, self::TimedOut => true,
            self::Pending, self::Processing => false,
        };
    }
}
```

**Concepts**

- Two cases can share one `match` arm with a comma.
- Listing every case (no `default`) turns "I added a status and forgot to decide its behavior" into an immediate error.

**Verify:** `ScrapingJobStatusEnum::TimedOut->isRetryable()` gives `true`.

[Back to question](#q4) · [Learn about match](README.md#312-match-vs-switch)

---

<a id="s5"></a>

### S5. `IngestOutcomeEnum` and `JobSeekerSyncStatusEnum`

**File:** `app/Enums/IngestOutcomeEnum.php`

```php
<?php

namespace App\Enums;

enum IngestOutcomeEnum: string
{
    case Created = 'created';
    case Updated = 'updated';
    case Unchanged = 'unchanged';
}
```

**File:** `app/Enums/JobSeekerSyncStatusEnum.php`

```php
<?php

namespace App\Enums;

enum JobSeekerSyncStatusEnum: string
{
    case Pending = 'pending';
    case Synced = 'synced';
    case Failed = 'failed';

    public function isRetryable(): bool
    {
        return match ($this) {
            self::Failed => true,
            self::Pending, self::Synced => false,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Waiting to sync',
            self::Synced => 'Synced',
            self::Failed => 'Sync failed',
        };
    }

    public static function fromIngestOutcome(IngestOutcomeEnum $outcome): self
    {
        return match ($outcome) {
            IngestOutcomeEnum::Created, IngestOutcomeEnum::Updated => self::Pending,
            IngestOutcomeEnum::Unchanged => self::Synced,
        };
    }
}
```

**Concepts**

- Enums can have **static** methods (`::fromIngestOutcome`). They keep mapping logic in one place instead of
  scattered `if` statements.
- `self` as a return type means "a case of this enum".
- Both enums live in `App\Enums`, so no `use` import is needed between them.

**Verify:** `JobSeekerSyncStatusEnum::fromIngestOutcome(IngestOutcomeEnum::Unchanged)` gives `Synced`.

[Back to question](#q5) · [Learn about Enums](README.md#11-enums)

---

<a id="s6"></a>

### S6. Domain exceptions

**File:** `app/Exceptions/MissingCredentialsException.php`

```php
<?php

namespace App\Exceptions;

use RuntimeException;

class MissingCredentialsException extends RuntimeException {}
```

**File:** `app/Exceptions/ScrapingJobAlreadyRunningException.php`

```php
<?php

namespace App\Exceptions;

use RuntimeException;

class ScrapingJobAlreadyRunningException extends RuntimeException {}
```

**Concepts**

- Empty bodies are fine: the **type** carries the meaning, so callers can `catch` precisely.
- `RuntimeException` already provides `getMessage()`.

[Back to question](#q6) · [Learn about Exceptions](README.md#314-exceptions)

---

## C. Data model

<a id="s7"></a>

### S7. `Company` and `CompanyIndeedCredential`

**Run:**

```bash
php artisan make:model Company -mf
php artisan make:model CompanyIndeedCredential -m
```

**File:** `database/migrations/*_create_companies_table.php` (replace `up()`)

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('serial_number')->nullable()->unique();
            $table->boolean('airwork_enabled')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
```

**File:** `database/migrations/*_create_company_indeed_credentials_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_indeed_credentials', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('email');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_indeed_credentials');
    }
};
```

**File:** `app/Models/Company.php`

```php
<?php

namespace App\Models;

use App\Exceptions\MissingCredentialsException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Company extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'serial_number', 'airwork_enabled'];

    protected function casts(): array
    {
        return ['airwork_enabled' => 'boolean'];
    }

    public function indeedCredentials(): HasOne
    {
        return $this->hasOne(CompanyIndeedCredential::class);
    }

    public function scrapingJobs(): HasMany
    {
        return $this->hasMany(ScrapingJob::class);
    }

    public function resolveIndeedLoginEmail(): string
    {
        if (filled($this->indeedCredentials?->email)) {
            return $this->indeedCredentials->email;
        }

        if (filled($this->serial_number)) {
            return str_replace('{serial_number}', $this->serial_number, config('scraping.indeed_email_template'));
        }

        throw new MissingCredentialsException("Company {$this->id} has no Indeed login email");
    }
}
```

**File:** `app/Models/CompanyIndeedCredential.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyIndeedCredential extends Model
{
    protected $fillable = ['company_id', 'email'];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
```

**Concepts**

- Table names are inferred: `Company` becomes `companies`. Foreign keys default to `<singular>_id`.
- `->unique()` on the FK makes the relation `hasOne` in practice. The FK lives on the credentials table, so that
  side is `belongsTo`.
- `$this->indeedCredentials` (no parentheses) is the related row (or `null`); `?->` avoids an error on `null`.
- `$fillable` is the mass-assignment allow-list.

[Back to question](#q7) · [Learn about Relationships](README.md#13-relationships-and-eager-loading)

---

<a id="s8"></a>

### S8. `ScrapingJob`

**Run:**

```bash
php artisan make:model ScrapingJob -mf
```

**File:** `database/migrations/*_create_scraping_jobs_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scraping_jobs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('spider', 64);
            $table->string('status', 32)->default('pending');
            $table->string('callback_secret', 64);
            $table->unsignedInteger('retry_count')->default(0);
            $table->text('error_message')->nullable();
            $table->unsignedInteger('records_created')->default(0);
            $table->unsignedInteger('records_updated')->default(0);
            $table->unsignedInteger('records_unchanged')->default(0);
            $table->unsignedInteger('records_failed')->default(0);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'spider', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scraping_jobs');
    }
};
```

**File:** `app/Models/ScrapingJob.php`

```php
<?php

namespace App\Models;

use App\Enums\ScrapingJobStatusEnum;
use App\Enums\ScrapingSpiderEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ScrapingJob extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id', 'spider', 'status', 'callback_secret', 'retry_count', 'error_message',
        'records_created', 'records_updated', 'records_unchanged', 'records_failed', 'completed_at',
    ];

    protected $hidden = ['callback_secret'];

    protected function casts(): array
    {
        return [
            'spider' => ScrapingSpiderEnum::class,
            'status' => ScrapingJobStatusEnum::class,
            'completed_at' => 'datetime',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function postings(): HasMany
    {
        return $this->hasMany(ScrapedPosting::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', [
            ScrapingJobStatusEnum::Pending->value,
            ScrapingJobStatusEnum::Processing->value,
        ]);
    }
}
```

**Concepts**

- **Casts:** `$job->status` is an enum object, so compare with `ScrapingJobStatusEnum::Pending`, never `'pending'`.
- `$hidden` removes the secret from `toArray()`/JSON; it stays readable as `$job->callback_secret`.
- `scopeActive` becomes `->active()` (drop `scope`, lowercase the first letter).
- In query builders (`whereIn`) pass the enum's `->value` string.

[Back to question](#q8) · [Learn about Eloquent models](README.md#12-eloquent-models)

---

<a id="s9"></a>

### S9. `ScrapedPosting` and running migrations

**Run:**

```bash
php artisan make:model ScrapedPosting -m
```

**File:** `database/migrations/*_create_scraped_postings_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scraped_postings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('scraping_job_id')->nullable()->constrained()->nullOnDelete();
            $table->string('provider', 32);
            $table->string('external_id');
            $table->char('row_hash', 64);
            $table->json('payload');
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->unique(['provider', 'external_id']);
            $table->index('row_hash');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scraped_postings');
    }
};
```

**File:** `app/Models/ScrapedPosting.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScrapedPosting extends Model
{
    protected $fillable = [
        'scraping_job_id', 'provider', 'external_id', 'row_hash', 'payload', 'synced_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'synced_at' => 'datetime',
        ];
    }

    public function scrapingJob(): BelongsTo
    {
        return $this->belongsTo(ScrapingJob::class);
    }
}
```

**Run:**

```bash
php artisan migrate
php artisan migrate:status
```

**Concepts**

- Migrations run in filename-timestamp order, which is why parent tables are created first.
- `unique(['provider','external_id'])` is a composite key: same external ID allowed for different providers.
- `row_hash` is a fingerprint: same hash means "unchanged", so we can skip syncing.
- `'payload' => 'array'` lets PHP read/write arrays while the DB stores JSON text.
- Never edit a migration that already ran in a shared environment; add a new one.

**Verify:** `migrate:status` lists every migration as `Ran`.

[Back to question](#q9) · [Learn about Migrations](README.md#14-migrations)

---

<a id="s10"></a>

### S10. Factories and seeder

**File:** `database/factories/CompanyFactory.php`

```php
<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Company>
 */
class CompanyFactory extends Factory
{
    protected $model = Company::class;

    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'serial_number' => null,
            'airwork_enabled' => false,
        ];
    }

    public function airwork(): static
    {
        return $this->state(['airwork_enabled' => true]);
    }
}
```

**File:** `database/factories/ScrapingJobFactory.php`

```php
<?php

namespace Database\Factories;

use App\Enums\ScrapingJobStatusEnum;
use App\Enums\ScrapingSpiderEnum;
use App\Models\Company;
use App\Models\ScrapingJob;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ScrapingJob>
 */
class ScrapingJobFactory extends Factory
{
    protected $model = ScrapingJob::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'spider' => ScrapingSpiderEnum::IndeedJobApplications,
            'status' => ScrapingJobStatusEnum::Pending,
            'callback_secret' => Str::random(40),
        ];
    }
}
```

**File:** `database/seeders/DatabaseSeeder.php`

```php
<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Company::factory()->create(['name' => 'Acme (Indeed via serial)', 'serial_number' => 'S-001']);

        $globex = Company::factory()->create(['name' => 'Globex (custom Indeed login)']);
        $globex->indeedCredentials()->create(['email' => 'globex-hr@example.com']);

        Company::factory()->airwork()->create(['name' => 'Initech (Airwork)']);
    }
}
```

**Run:** `php artisan db:seed`

**Concepts**

- `'company_id' => Company::factory()` means "create a parent automatically unless I pass one".
- A factory **state** (`airwork()`) is a named variation: `Company::factory()->airwork()->create()`.
- `$globex->indeedCredentials()` (parentheses) returns the relation object; `create()` fills `company_id`.

**Verify:** `php artisan tinker` then `App\Models\Company::count()` gives `3`.

[Back to question](#q10) · [Learn about factories](README.md#20-testing)

---

## D. Ingestion layer

<a id="s11"></a>

### S11. Interface and trait

**File:** `app/Services/Ingestion/ProviderRawStoreInterface.php`

```php
<?php

namespace App\Services\Ingestion;

use App\Enums\IngestOutcomeEnum;
use App\Models\ScrapedPosting;

interface ProviderRawStoreInterface
{
    /** Provider key, e.g. "indeed". */
    public function provider(): string;

    /**
     * @param  array<string, mixed>  $record
     * @return array{0: ScrapedPosting, 1: IngestOutcomeEnum}
     */
    public function upsert(string $externalId, array $record, string $hash, ?int $scrapingJobId): array;
}
```

**File:** `app/Services/Ingestion/Concerns/UpsertsPostings.php`

```php
<?php

namespace App\Services\Ingestion\Concerns;

use App\Enums\IngestOutcomeEnum;
use App\Models\ScrapedPosting;

trait UpsertsPostings
{
    /**
     * @param  array<string, mixed>  $record
     * @return array{0: ScrapedPosting, 1: IngestOutcomeEnum}
     */
    protected function upsertPosting(
        string $provider,
        string $externalId,
        array $record,
        string $hash,
        ?int $scrapingJobId,
    ): array {
        $posting = ScrapedPosting::query()
            ->where('provider', $provider)
            ->where('external_id', $externalId)
            ->first();

        if ($posting === null) {
            $posting = ScrapedPosting::query()->create([
                'scraping_job_id' => $scrapingJobId,
                'provider' => $provider,
                'external_id' => $externalId,
                'row_hash' => $hash,
                'payload' => $record,
            ]);

            return [$posting, IngestOutcomeEnum::Created];
        }

        if ($posting->row_hash === $hash) {
            return [$posting, IngestOutcomeEnum::Unchanged];
        }

        $posting->update([
            'scraping_job_id' => $scrapingJobId,
            'row_hash' => $hash,
            'payload' => $record,
            'synced_at' => null,   // changed, so it needs syncing again
        ]);

        return [$posting, IngestOutcomeEnum::Updated];
    }
}
```

**Concepts**

- An **interface** is a contract; code depends on the contract, not a concrete store.
- A **trait** is copied into each class that `use`s it: shared logic without inheritance.
- PHP can't type "tuple of Model and Enum", so PHPDoc `array{0: ..., 1: ...}` documents it for static analysis.
- `where(...)->first()` returns the model or `null`.

[Back to question](#q11) · [Learn about interfaces and traits](README.md#37-inheritance-interfaces-traits)

---

<a id="s12"></a>

### S12. `IndeedRawStore` and `AirworkRawStore`

**File:** `app/Services/Ingestion/Providers/IndeedRawStore.php`

```php
<?php

namespace App\Services\Ingestion\Providers;

use App\Services\Ingestion\Concerns\UpsertsPostings;
use App\Services\Ingestion\ProviderRawStoreInterface;

class IndeedRawStore implements ProviderRawStoreInterface
{
    use UpsertsPostings;

    public function provider(): string
    {
        return 'indeed';
    }

    public function upsert(string $externalId, array $record, string $hash, ?int $scrapingJobId): array
    {
        return $this->upsertPosting($this->provider(), $externalId, $record, $hash, $scrapingJobId);
    }
}
```

**File:** `app/Services/Ingestion/Providers/AirworkRawStore.php`

```php
<?php

namespace App\Services\Ingestion\Providers;

use App\Services\Ingestion\Concerns\UpsertsPostings;
use App\Services\Ingestion\ProviderRawStoreInterface;

class AirworkRawStore implements ProviderRawStoreInterface
{
    use UpsertsPostings;

    public function provider(): string
    {
        return 'airwork';
    }

    public function upsert(string $externalId, array $record, string $hash, ?int $scrapingJobId): array
    {
        return $this->upsertPosting($this->provider(), $externalId, $record, $hash, $scrapingJobId);
    }
}
```

**Concepts**

- `implements` + `use` together: the interface promises the methods, the trait supplies shared behavior.
- In a real project each provider would have its own table and rules; this is where they would differ.

[Back to question](#q12) · [Learn about the registry pattern](README.md#84-the-registry-pattern-used-in-this-project)

---

<a id="s13"></a>

### S13. `ProviderRegistry` and container wiring

**File:** `app/Services/Ingestion/ProviderRegistry.php`

```php
<?php

namespace App\Services\Ingestion;

use InvalidArgumentException;

class ProviderRegistry
{
    /** @var array<string, ProviderRawStoreInterface> */
    private array $rawStores = [];

    /** @param iterable<ProviderRawStoreInterface> $rawStores */
    public function __construct(iterable $rawStores)
    {
        foreach ($rawStores as $store) {
            $this->rawStores[$store->provider()] = $store;
        }
    }

    public function rawStore(string $provider): ProviderRawStoreInterface
    {
        return $this->rawStores[$provider]
            ?? throw new InvalidArgumentException("Unknown provider [{$provider}]");
    }
}
```

**File:** `app/Providers/AppServiceProvider.php`

```php
<?php

namespace App\Providers;

use App\Services\Ingestion\ProviderRawStoreInterface;
use App\Services\Ingestion\ProviderRegistry;
use App\Services\Ingestion\Providers\AirworkRawStore;
use App\Services\Ingestion\Providers\IndeedRawStore;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->tag(
            [IndeedRawStore::class, AirworkRawStore::class],
            ProviderRawStoreInterface::class,
        );

        $this->app->singleton(ProviderRegistry::class, fn ($app): ProviderRegistry => new ProviderRegistry(
            $app->tagged(ProviderRawStoreInterface::class),
        ));
    }

    public function boot(): void
    {
        //
    }
}
```

**Concepts**

- `tag([...], Label::class)` groups classes; `tagged(Label::class)` later returns them all (built by the container).
- `singleton` builds the registry once and reuses it.
- `iterable` accepts arrays and generators (what `tagged()` returns).
- `?? throw ...`: `throw` is an expression in PHP 8, so it can sit on the right of `??`.
- Adding a provider later = one new class plus one entry in `tag([...])`.

**Verify (tinker):** `app(App\Services\Ingestion\ProviderRegistry::class)->rawStore('indeed');`

[Back to question](#q13) · [Learn about the container](README.md#8-service-container-dependency-injection-providers)

---

<a id="s14"></a>

### S14. `SyncPostingJob`

**Run:** `php artisan make:job SyncPostingJob`

**File:** `app/Jobs/SyncPostingJob.php`

```php
<?php

namespace App\Jobs;

use App\Models\ScrapedPosting;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SyncPostingJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public int $postingId) {}

    public function handle(): void
    {
        $posting = ScrapedPosting::query()->find($this->postingId);

        if ($posting === null) {
            return;   // deleted meanwhile: nothing to do (idempotent)
        }

        // Real project: copy raw data into platform tables. Demo: mark as synced.
        $posting->update(['synced_at' => now()]);

        Log::info('Posting synced', ['id' => $posting->id, 'external_id' => $posting->external_id]);
    }
}
```

**Concepts**

- `implements ShouldQueue` means "do not run inline; push to the queue".
- Pass **IDs**, not big objects, so the payload is small and data is read fresh.
- `dispatch()` serialises public properties into the `jobs` table; a worker (`php artisan queue:work`) rebuilds the
  object and calls `handle()`.
- Make jobs idempotent: running twice must still be correct.

[Back to question](#q14) · [Learn about Jobs and queues](README.md#16-jobs-and-queues)

---

<a id="s15"></a>

### S15. `IngestPostingService`

**File:** `app/Services/Ingestion/IngestPostingService.php`

```php
<?php

namespace App\Services\Ingestion;

use App\Enums\IngestOutcomeEnum;
use App\Jobs\SyncPostingJob;

class IngestPostingService
{
    public function __construct(private ProviderRegistry $registry) {}

    /**
     * @param  array{external_id: string, record: array<string, mixed>}  $entry
     */
    public function ingest(string $provider, array $entry, ?int $scrapingJobId = null): IngestOutcomeEnum
    {
        $hash = hash('sha256', json_encode($entry['record'], JSON_THROW_ON_ERROR));

        [$posting, $outcome] = $this->registry
            ->rawStore($provider)
            ->upsert($entry['external_id'], $entry['record'], $hash, $scrapingJobId);

        if ($outcome !== IngestOutcomeEnum::Unchanged) {
            SyncPostingJob::dispatch($posting->id);
        }

        return $outcome;
    }
}
```

**Concepts**

- Constructor promotion (`private ProviderRegistry $registry`) declares and assigns the property; the container
  injects the singleton.
- `[$posting, $outcome] = ...` is array destructuring of the tuple.
- `JSON_THROW_ON_ERROR` makes `json_encode` throw instead of returning `false`.
- Compare enums with `!==` against a case object.

[Back to question](#q15) · [Learn about Services](README.md#10-services-your-business-logic-layer)

---

## E. Events

<a id="s16"></a>

### S16. Event and listener

**Run:**

```bash
php artisan make:event ScrapingJobCompleted
php artisan make:listener LogScrapingJobResult --event=ScrapingJobCompleted --queued
```

**File:** `app/Events/ScrapingJobCompleted.php`

```php
<?php

namespace App\Events;

use App\Models\ScrapingJob;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ScrapingJobCompleted
{
    use Dispatchable, SerializesModels;

    public function __construct(public ScrapingJob $job) {}
}
```

**File:** `app/Listeners/LogScrapingJobResult.php`

```php
<?php

namespace App\Listeners;

use App\Events\ScrapingJobCompleted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class LogScrapingJobResult implements ShouldQueue
{
    public function handle(ScrapingJobCompleted $event): void
    {
        Log::info('Scraping job finished', [
            'job_id' => $event->job->id,
            'status' => $event->job->status->value,
            'created' => $event->job->records_created,
            'updated' => $event->job->records_updated,
        ]);
    }
}
```

**Concepts**

- Laravel auto-discovers listeners in `app/Listeners` and reads the **type hint** on `handle()` to know which event
  it subscribes to. The type hint is not optional.
- `SerializesModels` stores just the model ID in the queue and reloads it later.
- The emitter never knows who listens (decoupling). `implements ShouldQueue` runs the listener on the worker.

[Back to question](#q16) · [Learn about Events](README.md#17-events-and-listeners)

---

## F. Core service

<a id="s17"></a>

### S17. `ScrapingJobService`

**File:** `app/Services/Scraping/ScrapingJobService.php`

```php
<?php

namespace App\Services\Scraping;

use App\Enums\ScrapingJobStatusEnum;
use App\Enums\ScrapingSpiderEnum;
use App\Events\ScrapingJobCompleted;
use App\Exceptions\MissingCredentialsException;
use App\Exceptions\ScrapingJobAlreadyRunningException;
use App\Models\Company;
use App\Models\ScrapingJob;
use App\Services\Ingestion\IngestPostingService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class ScrapingJobService
{
    public function __construct(private IngestPostingService $ingest) {}

    /** Create a job and "send it to the scraper" (here: write the payload to the log). */
    public function dispatch(Company $company, ScrapingSpiderEnum $spider): ScrapingJob
    {
        $credentials = $this->buildCredentials($company, $spider);

        $lock = Cache::lock("scraping-job:dispatch:{$company->id}:{$spider->value}", 10);

        if (! $lock->get()) {
            throw new ScrapingJobAlreadyRunningException('Another dispatch is in progress, try again.');
        }

        try {
            if ($this->runningJob($company->id, $spider) !== null) {
                throw new ScrapingJobAlreadyRunningException(
                    "A {$spider->value} job is already running for company {$company->id}"
                );
            }

            $job = ScrapingJob::query()->create([
                'company_id' => $company->id,
                'spider' => $spider,
                'status' => ScrapingJobStatusEnum::Pending,
                'callback_secret' => Str::random(40),
            ]);

            Log::info('Scraping payload ready (production: sent to SQS)', [
                'job_id' => $job->id,
                'spider' => $spider->value,
                'credentials' => $credentials,
                'callback_url' => url($spider->callbackPath()),
            ]);

            return $job;
        } finally {
            $lock->release();
        }
    }

    public function runningJob(int $companyId, ScrapingSpiderEnum $spider): ?ScrapingJob
    {
        return ScrapingJob::query()
            ->where('company_id', $companyId)
            ->where('spider', $spider->value)
            ->active()
            ->where('created_at', '>=', now()->subMinutes(config('scraping.stale_timeout_minutes')))
            ->latest('id')
            ->first();
    }

    /**
     * Called by the scraper callback.
     *
     * @param  array<int, array{external_id: string, record: array<string, mixed>}>  $records
     */
    public function handleComplete(ScrapingJob $job, string $status, array $records): ScrapingJob
    {
        if ($status !== 'success') {
            $job->update([
                'status' => ScrapingJobStatusEnum::Failed,
                'error_message' => 'Scraper reported a failure',
                'completed_at' => now(),
            ]);

            ScrapingJobCompleted::dispatch($job);

            return $job->refresh();
        }

        $counts = ['created' => 0, 'updated' => 0, 'unchanged' => 0, 'failed' => 0];

        foreach ($records as $entry) {
            try {
                $outcome = $this->ingest->ingest($job->spider->provider(), $entry, $job->id);
                $counts[$outcome->value]++;
            } catch (Throwable $e) {
                $counts['failed']++;
                Log::error('Ingest failed for one record', [
                    'job_id' => $job->id,
                    'external_id' => $entry['external_id'] ?? null,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $job->update([
            'status' => ScrapingJobStatusEnum::Completed,
            'records_created' => $counts['created'],
            'records_updated' => $counts['updated'],
            'records_unchanged' => $counts['unchanged'],
            'records_failed' => $counts['failed'],
            'completed_at' => now(),
        ]);

        ScrapingJobCompleted::dispatch($job);

        return $job->refresh();
    }

    public function retry(ScrapingJob $failed): ScrapingJob
    {
        $new = $this->dispatch($failed->company, $failed->spider);
        $new->update(['retry_count' => $failed->retry_count + 1]);

        return $new;
    }

    /** @return Collection<int, ScrapingJob> */
    public function dispatchAll(ScrapingSpiderEnum $spider): Collection
    {
        return $this->eligibleCompaniesQuery($spider)
            ->get()
            ->map(function (Company $company) use ($spider): ?ScrapingJob {
                try {
                    return $this->dispatch($company, $spider);
                } catch (ScrapingJobAlreadyRunningException|MissingCredentialsException $e) {
                    Log::warning('Skipped company during dispatchAll', [
                        'company_id' => $company->id,
                        'reason' => $e->getMessage(),
                    ]);

                    return null;
                }
            })
            ->filter()
            ->values();
    }

    /** One place decides who is eligible for which spider (cron, command, and button cannot drift). */
    public function eligibleCompaniesQuery(ScrapingSpiderEnum $spider): Builder
    {
        if ($spider->isIndeed()) {
            return Company::query()->where(function (Builder $query): void {
                $query
                    ->whereHas('indeedCredentials', fn (Builder $q) => $q->whereNotNull('email'))
                    ->orWhereNotNull('serial_number');
            });
        }

        return Company::query()->where('airwork_enabled', true);
    }

    /** @return array<string, mixed> */
    private function buildCredentials(Company $company, ScrapingSpiderEnum $spider): array
    {
        return match (true) {
            $spider->isIndeed() => ['email' => $company->resolveIndeedLoginEmail()],
            $company->airwork_enabled => ['airwork' => true],
            default => throw new MissingCredentialsException("Company {$company->id} is not enabled for Airwork"),
        };
    }
}
```

**Concepts**

- **Lock + DB check:** `runningJob()` is a plain read, so two simultaneous requests could both see "nothing running".
  `Cache::lock(...)->get()` makes them take turns; `try/finally` always releases it.
- `match (true)` runs the first arm whose condition is true; `throw` is allowed in the `default` arm.
- `function (Company $c) use ($spider)` is a closure that must list captured outer variables with `use`.
- `catch (A|B $e)` is a multi-catch; one bad record is counted and skipped so it cannot abort the batch.
- `->filter()` with no argument drops falsy items (our `null`s); `->values()` re-indexes from 0.
- `eligibleCompaniesQuery` returns a **Builder** (nothing has run yet), so callers can `->count()` or `->get()`.
  `where(function ...)` wraps the OR in parentheses: `WHERE (A OR B)`.
- Stale jobs (older than the timeout) stop blocking a company forever.

[Back to question](#q17) · [Learn about Facades and cache locks](README.md#cache-locks-used-by-dispatch)

---

## G. HTTP layer

<a id="s18"></a>

### S18. `TriggerScrapingJobRequest` and `StoreCompanyRequest`

**Run:**

```bash
php artisan make:request TriggerScrapingJobRequest
php artisan make:request StoreCompanyRequest
```

**File:** `app/Http/Requests/TriggerScrapingJobRequest.php`

```php
<?php

namespace App\Http\Requests;

use App\Enums\ScrapingSpiderEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TriggerScrapingJobRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;   // access is already guarded by the 'protected' middleware
    }

    public function rules(): array
    {
        return [
            'company_id' => ['required', 'integer', 'exists:companies,id'],
            'spider' => ['sometimes', 'string', Rule::enum(ScrapingSpiderEnum::class)],
        ];
    }
}
```

**File:** `app/Http/Requests/StoreCompanyRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'serial_number' => ['nullable', 'string', 'max:64', 'unique:companies,serial_number'],
            'airwork_enabled' => ['sometimes', 'boolean'],
            'indeed_email' => ['nullable', 'email'],
        ];
    }
}
```

**Concepts**

- Order: the container builds the request, runs `authorize()` (false gives 403), then `rules()` (failure gives 422);
  the controller never runs on failure.
- `sometimes` validates only when the key is present; `exists:companies,id` checks the DB; `Rule::enum` accepts only
  valid backing values.
- Read data only via `$request->validated()`.

[Back to question](#q18) · [Learn about Form Requests](README.md#7-form-requests-validation-and-authorization)

---

<a id="s19"></a>

### S19. `ScrapingJobCompleteRequest` (HMAC)

**Run:** `php artisan make:request ScrapingJobCompleteRequest`

**File:** `app/Http/Requests/ScrapingJobCompleteRequest.php`

```php
<?php

namespace App\Http\Requests;

use App\Models\ScrapingJob;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ScrapingJobCompleteRequest extends FormRequest
{
    private ?ScrapingJob $scrapingJob = null;

    public function scrapingJob(): ?ScrapingJob
    {
        $id = $this->input('job_id');

        if (! is_numeric($id)) {
            return null;
        }

        return $this->scrapingJob ??= ScrapingJob::query()->find((int) $id);
    }

    public function authorize(): bool
    {
        $job = $this->scrapingJob();

        if ($job === null) {
            return false;
        }

        // The signature is computed over the RAW request body with a per-job secret.
        $expected = hash_hmac('sha256', $this->getContent(), $job->callback_secret);

        return hash_equals($expected, (string) $this->header('X-HMAC-Signature'));
    }

    public function rules(): array
    {
        return [
            'job_id' => ['required', 'integer'],
            'status' => ['required', Rule::in(['success', 'failed'])],
            'records' => ['sometimes', 'array'],
            'records.*.external_id' => ['required', 'string', 'max:255'],
            'records.*.record' => ['required', 'array'],
        ];
    }
}
```

**Concepts**

- The scraper is a machine, not a logged-in user. Both sides know a per-job secret; the scraper signs the body with
  `hash_hmac`, the server recomputes and compares. The secret is never sent.
- `$this->getContent()` is the exact raw body, so the signature covers exactly the bytes received.
- `??=` assigns only when the left side is `null` (memoises the DB lookup used by `authorize()` and the controller).
- `records.*.external_id` applies the rule to every item in `records`.

[Back to question](#q19) · [Learn about authorization in requests](README.md#7-form-requests-validation-and-authorization)

---

<a id="s20"></a>

### S20. API Resources

**Run:**

```bash
php artisan make:resource ScrapingJobResource
php artisan make:resource CompanyResource
php artisan make:resource PostingResource
```

**File:** `app/Http/Resources/ScrapingJobResource.php`

```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScrapingJobResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->company_id,
            'company_name' => $this->whenLoaded('company', fn () => $this->company->name),
            'spider' => $this->spider->value,
            'status' => $this->status->value,
            'is_terminal' => $this->status->isTerminal(),
            'retry_count' => $this->retry_count,
            'error_message' => $this->error_message,
            'records' => [
                'created' => $this->records_created,
                'updated' => $this->records_updated,
                'unchanged' => $this->records_unchanged,
                'failed' => $this->records_failed,
            ],
            'completed_at' => $this->completed_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
```

**File:** `app/Http/Resources/CompanyResource.php`

```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'serial_number' => $this->serial_number,
            'airwork_enabled' => $this->airwork_enabled,
            'indeed_email' => $this->whenLoaded('indeedCredentials', fn () => $this->indeedCredentials?->email),
        ];
    }
}
```

**File:** `app/Http/Resources/PostingResource.php`

```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'provider' => $this->provider,
            'external_id' => $this->external_id,
            'row_hash' => $this->row_hash,
            'payload' => $this->payload,
            'scraping_job_id' => $this->scraping_job_id,
            'synced_at' => $this->synced_at?->toISOString(),
        ];
    }
}
```

**Concepts**

- A Resource is an **allow-list**: `callback_secret` is deliberately absent.
- `$this->id` works because `JsonResource` forwards property access to the wrapped model.
- `whenLoaded('relation', fn)` includes the field only when the relation was eager-loaded (no hidden extra queries).
- `->value` turns an enum into a string; `?->toISOString()` handles a null date.

[Back to question](#q20) · [Learn about API Resources](README.md#15-api-resources)

---

<a id="s21"></a>

### S21. `CompanyController`

**Run:** `php artisan make:controller Api/CompanyController`

**File:** `app/Http/Controllers/Api/CompanyController.php`

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCompanyRequest;
use App\Http\Resources\CompanyResource;
use App\Models\Company;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class CompanyController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return CompanyResource::collection(
            Company::query()->with('indeedCredentials')->orderBy('id')->get()
        );
    }

    public function store(StoreCompanyRequest $request): JsonResponse
    {
        $data = $request->validated() + ['airwork_enabled' => false];

        $company = DB::transaction(function () use ($data) {
            $company = Company::query()->create(Arr::only($data, ['name', 'serial_number', 'airwork_enabled']));

            if (filled($data['indeed_email'] ?? null)) {
                $company->indeedCredentials()->create(['email' => $data['indeed_email']]);
            }

            return $company;
        });

        return CompanyResource::make($company->load('indeedCredentials'))
            ->response()
            ->setStatusCode(201);
    }
}
```

**Concepts**

- `array + array` keeps left keys and adds missing ones from the right: a default without overwriting user input.
- `DB::transaction(fn)` rolls back everything if the closure throws and returns the closure's value.
- `->with('indeedCredentials')` eager-loads to avoid N+1 queries.
- `->response()->setStatusCode(201)` turns a Resource into a `JsonResponse` whose status you can change.

[Back to question](#q21) · [Learn about Controllers](README.md#6-controllers)

---

<a id="s22"></a>

### S22. `PostingController`

**Run:** `php artisan make:controller Api/PostingController`

**File:** `app/Http/Controllers/Api/PostingController.php`

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostingResource;
use App\Models\ScrapedPosting;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PostingController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $postings = ScrapedPosting::query()
            ->when($request->query('provider'), fn (Builder $q, $provider) => $q->where('provider', $provider))
            ->latest('id')
            ->paginate(15);

        return PostingResource::collection($postings);
    }
}
```

**Concepts**

- `->when($value, fn)` applies the clause only if `$value` is truthy (replaces `if` blocks around query building).
- `paginate(15)` runs a count plus a limited query; the Resource collection adds `links` and `meta` automatically.

[Back to question](#q22) · [Learn about Eloquent queries](README.md#12-eloquent-models)

---

<a id="s23"></a>

### S23. `ScrapingJobController`

**Run:** `php artisan make:controller Api/ScrapingJobController`

**File:** `app/Http/Controllers/Api/ScrapingJobController.php`

```php
<?php

namespace App\Http\Controllers\Api;

use App\Enums\ScrapingJobStatusEnum;
use App\Enums\ScrapingSpiderEnum;
use App\Exceptions\MissingCredentialsException;
use App\Exceptions\ScrapingJobAlreadyRunningException;
use App\Http\Controllers\Controller;
use App\Http\Requests\TriggerScrapingJobRequest;
use App\Http\Resources\ScrapingJobResource;
use App\Models\Company;
use App\Models\ScrapingJob;
use App\Services\Scraping\ScrapingJobService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class ScrapingJobController extends Controller
{
    public function __construct(private ScrapingJobService $service) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->validate([
            'company_id' => ['sometimes', 'integer'],
            'status' => ['sometimes', Rule::enum(ScrapingJobStatusEnum::class)],
            'per_page' => ['sometimes', 'integer', 'between:1,100'],
        ]);

        $jobs = ScrapingJob::query()
            ->with('company')
            ->when($filters['company_id'] ?? null, fn (Builder $q, $id) => $q->where('company_id', $id))
            ->when($filters['status'] ?? null, fn (Builder $q, $status) => $q->where('status', $status))
            ->latest('id')
            ->paginate($filters['per_page'] ?? 15);

        return ScrapingJobResource::collection($jobs);
    }

    public function show(ScrapingJob $scrapingJob): ScrapingJobResource
    {
        return ScrapingJobResource::make($scrapingJob->load('company'));
    }

    public function status(Request $request): JsonResponse
    {
        $data = $request->validate([
            'company_id' => ['required', 'integer'],
            'spider' => ['required', Rule::enum(ScrapingSpiderEnum::class)],
        ]);

        $spider = ScrapingSpiderEnum::from($data['spider']);
        $running = $this->service->runningJob((int) $data['company_id'], $spider);

        return response()->json(['data' => [
            'company_id' => (int) $data['company_id'],
            'spider' => $spider->value,
            'is_running' => $running !== null,
        ]]);
    }

    public function store(TriggerScrapingJobRequest $request): JsonResponse
    {
        $company = Company::query()
            ->with('indeedCredentials')
            ->findOrFail($request->validated('company_id'));

        $spider = $request->enum('spider', ScrapingSpiderEnum::class)
            ?? ScrapingSpiderEnum::AirregiJobs;

        try {
            $job = $this->service->dispatch($company, $spider);
        } catch (ScrapingJobAlreadyRunningException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        } catch (MissingCredentialsException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'message' => 'Scraping job dispatched',
            'data' => ScrapingJobResource::make($job->load('company')),
        ], 201);
    }

    public function triggerAll(Request $request): JsonResponse
    {
        $request->validate(['spider' => ['sometimes', Rule::enum(ScrapingSpiderEnum::class)]]);

        $spider = $request->enum('spider', ScrapingSpiderEnum::class) ?? ScrapingSpiderEnum::AirregiJobs;
        $jobs = $this->service->dispatchAll($spider);

        return response()->json([
            'message' => "{$jobs->count()} job(s) dispatched",
            'data' => ScrapingJobResource::collection($jobs),
        ]);
    }

    public function retry(ScrapingJob $scrapingJob): JsonResponse
    {
        if (! $scrapingJob->status->isRetryable()) {
            return response()->json(['message' => 'Only failed or timed-out jobs can be retried'], 422);
        }

        try {
            $job = $this->service->retry($scrapingJob);
        } catch (ScrapingJobAlreadyRunningException|MissingCredentialsException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        }

        return response()->json(['data' => ScrapingJobResource::make($job)], 201);
    }
}
```

**Concepts**

- **Thin controller:** no locks, hashing, or Eloquent writes here; that is the service's job. Controllers map
  exceptions to HTTP status codes (409 conflict, 422 unprocessable).
- **Route-model binding:** `show(ScrapingJob $scrapingJob)` matches the `{scrapingJob}` URL segment by name and
  returns 404 if the row is missing.
- `$request->validate([...])` is inline validation (good for query-string filters); it throws the same 422.
- `$request->enum('spider', Enum::class)` returns a case or `null`.
- Returning a Resource wraps output in `{ "data": ... }`.

[Back to question](#q23) · [Learn about Controllers and binding](README.md#4-routing)

---

<a id="s24"></a>

### S24. `ScrapingJobCompleteController`

**Run:** `php artisan make:controller Api/ScrapingJobCompleteController --invokable`

**File:** `app/Http/Controllers/Api/ScrapingJobCompleteController.php`

```php
<?php

namespace App\Http\Controllers\Api;

use App\Enums\ScrapingSpiderEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\ScrapingJobCompleteRequest;
use App\Http\Resources\ScrapingJobResource;
use App\Services\Scraping\ScrapingJobService;
use Illuminate\Http\JsonResponse;

class ScrapingJobCompleteController extends Controller
{
    public function __construct(private ScrapingJobService $service) {}

    public function __invoke(ScrapingJobCompleteRequest $request, string $spider): JsonResponse
    {
        $spiderEnum = ScrapingSpiderEnum::tryFrom($spider) ?? abort(404, 'Unknown spider');

        $validated = $request->validated();
        $job = $request->scrapingJob();

        abort_if($job->spider !== $spiderEnum, 422, 'Spider does not match the job');

        $result = $this->service->handleComplete(
            job: $job,
            status: $validated['status'],
            records: $validated['records'] ?? [],
        );

        return response()->json([
            'message' => 'Scraping job updated',
            'data' => ScrapingJobResource::make($result),
        ]);
    }
}
```

**Concepts**

- `__invoke` makes the object callable, so the route needs no method name.
- `tryFrom($spider) ?? abort(404)`: `tryFrom` gives `null` for unknown values and `abort` throws.
- `abort_if(cond, status, msg)` is a one-line `if (cond) abort(...)`.
- The `$spider` argument is filled from the `{spider}` URL segment **by name**.
- Named arguments (`job:`, `status:`, `records:`) make the call self-documenting.

[Back to question](#q24) · [Learn about invokable controllers](README.md#38-invokable-classes-and-anonymous-classes)

---

<a id="s25"></a>

### S25. `routes/api.php`

**File:** `routes/api.php`

```php
<?php

use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\PostingController;
use App\Http\Controllers\Api\ScrapingJobCompleteController;
use App\Http\Controllers\Api\ScrapingJobController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->middleware('protected')->group(function () {
    Route::get('companies', [CompanyController::class, 'index']);
    Route::post('companies', [CompanyController::class, 'store']);

    Route::get('postings', [PostingController::class, 'index']);

    Route::get('scraping-jobs', [ScrapingJobController::class, 'index']);
    Route::get('scraping-jobs/status', [ScrapingJobController::class, 'status']);   // before {scrapingJob}!
    Route::get('scraping-jobs/{scrapingJob}', [ScrapingJobController::class, 'show']);
    Route::post('scraping-jobs', [ScrapingJobController::class, 'store']);
    Route::post('scraping-jobs/trigger-all', [ScrapingJobController::class, 'triggerAll']);
    Route::post('scraping-jobs/{scrapingJob}/retry', [ScrapingJobController::class, 'retry']);
});

// Machine-to-machine callback: protected by the HMAC signature, not by the admin key.
Route::post('scraping-jobs/{spider}/complete', ScrapingJobCompleteController::class);
```

**Concepts**

- `routes/api.php` routes get the `/api` prefix automatically.
- `[Controller::class, 'method']` means "call this method on this class"; a bare class means invokable.
- **Order matters:** `status` must come before `{scrapingJob}` or the wildcard swallows the word `status`.
- `->group(function () { ... })` applies the prefix and middleware to everything inside.

**Verify:** `php artisan route:list --path=api` lists 11 routes.

[Back to question](#q25) · [Learn about Routing](README.md#4-routing)

---

## H. Console and scheduler

<a id="s26"></a>

### S26. `ScrapingDispatchCompanies`

**Run:** `php artisan make:command ScrapingDispatchCompanies`

**File:** `app/Console/Commands/ScrapingDispatchCompanies.php`

```php
<?php

namespace App\Console\Commands;

use App\Enums\ScrapingSpiderEnum;
use App\Services\Scraping\ScrapingJobService;
use Illuminate\Console\Command;

class ScrapingDispatchCompanies extends Command
{
    protected $signature = 'scraping:dispatch-companies {--spider=airregi-jobs : Spider name}';

    protected $description = 'Dispatch scraping jobs for all eligible companies';

    public function handle(ScrapingJobService $service): int
    {
        $spider = ScrapingSpiderEnum::tryFrom((string) $this->option('spider'));

        if ($spider === null) {
            $this->error('Unknown spider. Valid: '.implode(', ', array_column(ScrapingSpiderEnum::cases(), 'value')));

            return self::FAILURE;
        }

        $eligible = $service->eligibleCompaniesQuery($spider)->count();
        $this->info("{$eligible} eligible company(ies) for {$spider->value}");

        $jobs = $service->dispatchAll($spider);
        $this->info("Dispatched {$jobs->count()} job(s)");

        return self::SUCCESS;
    }
}
```

**Concepts**

- `$signature` defines the CLI name and options; `{--spider=airregi-jobs}` is an option with a default.
- `handle(ScrapingJobService $service)` gets the service by method injection.
- `array_column(Enum::cases(), 'value')` reads the `value` property of each case.
- Return `self::SUCCESS` (0) or `self::FAILURE` (1); schedulers and CI read the exit code.
- Commands in `app/Console/Commands` register automatically.

**Verify:** `php artisan scraping:dispatch-companies --spider=indeed-job-details`

[Back to question](#q26) · [Learn about Artisan commands](README.md#18-console-commands-and-the-scheduler)

---

<a id="s27"></a>

### S27. `SimulateScraper`

**Run:** `php artisan make:command SimulateScraper`

**File:** `app/Console/Commands/SimulateScraper.php`

```php
<?php

namespace App\Console\Commands;

use App\Models\ScrapingJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SimulateScraper extends Command
{
    protected $signature = 'scraper:simulate
        {job : ScrapingJob id}
        {--fail : Report a failure}
        {--update : Change the first record so it is detected as updated}';

    protected $description = 'Act as the scraper: send a signed completion callback to the API';

    public function handle(): int
    {
        $job = ScrapingJob::query()->findOrFail($this->argument('job'));

        $records = [];
        foreach (range(1, 3) as $i) {
            $title = "Demo posting {$i}".($this->option('update') && $i === 1 ? ' (updated)' : '');
            $records[] = [
                'external_id' => "{$job->spider->value}-{$i}",
                'record' => ['title' => $title, 'location' => 'Tokyo', 'salary' => 1000 + $i * 100],
            ];
        }

        $payload = [
            'job_id' => $job->id,
            'status' => $this->option('fail') ? 'failed' : 'success',
            'records' => $this->option('fail') ? [] : $records,
        ];

        $body = json_encode($payload, JSON_THROW_ON_ERROR);
        $signature = hash_hmac('sha256', $body, $job->callback_secret);
        $url = rtrim(config('app.url'), '/').$job->spider->callbackPath();

        $response = Http::withHeaders(['X-HMAC-Signature' => $signature, 'Accept' => 'application/json'])
            ->withBody($body, 'application/json')
            ->post($url);

        $this->info("POST {$url} -> HTTP {$response->status()}");
        $this->line($response->body());

        return $response->successful() ? self::SUCCESS : self::FAILURE;
    }
}
```

**Concepts**

- `Http::...->post()` is Laravel's HTTP client; you are using Laravel as an **API client** too.
- The signature must cover the **exact bytes** sent, so we `json_encode` once, sign that string, and send the same
  string with `withBody`.
- `.` concatenates strings; `"{$job->spider->value}-{$i}"` interpolates an enum value.
- `$this->argument('job')` / `$this->option('fail')` read the CLI input defined in `$signature`.
- `APP_URL` must match the address of `php artisan serve` (set in Q1).

[Back to question](#q27) · [Learn about the callback flow](README.md#22-walkthrough-tracing-the-scraper-callback)

---

<a id="s28"></a>

### S28. Daily schedule

**File:** `routes/console.php`

```php
<?php

use App\Enums\ScrapingSpiderEnum;
use Illuminate\Support\Facades\Schedule;

Schedule::command('scraping:dispatch-companies --spider='.ScrapingSpiderEnum::AirregiJobs->value)
    ->onOneServer()
    ->withoutOverlapping()
    ->dailyAt('06:30')
    ->timezone('Asia/Tokyo');

Schedule::command('scraping:dispatch-companies --spider='.ScrapingSpiderEnum::IndeedJobDetails->value)
    ->onOneServer()
    ->withoutOverlapping()
    ->dailyAt('07:15')
    ->timezone('Asia/Tokyo');

Schedule::command('scraping:dispatch-companies --spider='.ScrapingSpiderEnum::IndeedJobApplications->value)
    ->onOneServer()
    ->withoutOverlapping()
    ->dailyAt('07:30')
    ->timezone('Asia/Tokyo');
```

**Concepts**

- Laravel runs no daemon: the server has **one** cron line, `* * * * * php artisan schedule:run`, and Laravel
  checks what is due each minute.
- `onOneServer()` uses a cache lock (needs a shared cache); `withoutOverlapping()` skips a run while the previous
  one is still going; `timezone()` interprets `dailyAt` in JST regardless of server time.
- `'...'.ScrapingSpiderEnum::AirregiJobs->value` concatenates a string with an enum value.

**Verify:** `php artisan schedule:list` shows three entries. For local dev, `php artisan schedule:work`.

[Back to question](#q28) · [Learn about the Scheduler](README.md#18-console-commands-and-the-scheduler)

---

<a id="s29"></a>

### S29. End-to-end demo script

**File:** `scripts/demo.sh`

```bash
#!/usr/bin/env bash
# Run from the project root after:  php artisan migrate:fresh --seed
# Needs: `php artisan serve` and `php artisan queue:work` running in other terminals.
set -euo pipefail

BASE="${BASE:-http://127.0.0.1:8000/api}"
KEY="${ADMIN_KEY:-secret-demo-key}"

step() { printf '\n\033[1m== %s ==\033[0m\n' "$1"; }

admin() {
  curl -s -w '\n-> HTTP %{http_code}\n' \
    -H "X-Admin-Key: $KEY" -H 'Accept: application/json' -H 'Content-Type: application/json' "$@"
}

trigger() { admin -X POST "$BASE/admin/scraping-jobs" -d "{\"company_id\":$1,\"spider\":\"$2\"}"; }

# Pull data.id out of a one-line JSON response.
data_id() { sed -n 's/.*"data":{"id":\([0-9]*\).*/\1/p'; }

step "1. Missing admin key -> 401"
curl -s -w '\n-> HTTP %{http_code}\n' -H 'Accept: application/json' "$BASE/admin/companies"

step "2. List companies (seeded)"
admin "$BASE/admin/companies"

step "3. Create a company -> 201"
admin -X POST "$BASE/admin/companies" \
  -d "{\"name\":\"Umbrella Corp\",\"serial_number\":\"S-$RANDOM\",\"indeed_email\":\"hr@umbrella.example\"}"

step "4. Trigger an Indeed job for company 1 -> 201"
OUT=$(trigger 1 indeed-job-details)
echo "$OUT"
JOB1=$(echo "$OUT" | data_id)

step "5. Trigger again immediately -> 409"
trigger 1 indeed-job-details

step "6. Is it running? -> is_running true"
admin "$BASE/admin/scraping-jobs/status?company_id=1&spider=indeed-job-details"

step "7. Scraper calls back (job $JOB1) -> created 3"
php artisan scraper:simulate "$JOB1"

step "8. Stored postings and the job"
admin "$BASE/admin/postings?provider=indeed"
admin "$BASE/admin/scraping-jobs/$JOB1"

step "9. Same data again -> unchanged 3"
JOB2=$(trigger 1 indeed-job-details | data_id)
php artisan scraper:simulate "$JOB2"

step "10. One record changed -> updated 1, unchanged 2"
JOB3=$(trigger 1 indeed-job-details | data_id)
php artisan scraper:simulate "$JOB3" --update

step "11. Wrong signature -> 403"
JOB4=$(trigger 1 indeed-job-details | data_id)
curl -s -w '\n-> HTTP %{http_code}\n' -X POST "$BASE/scraping-jobs/indeed-job-details/complete" \
  -H 'Accept: application/json' -H 'Content-Type: application/json' -H 'X-HMAC-Signature: wrong' \
  -d "{\"job_id\":$JOB4,\"status\":\"success\",\"records\":[]}"

step "12. Failure, then retry -> 201 (retry_count 1)"
php artisan scraper:simulate "$JOB4" --fail || true
admin -X POST "$BASE/admin/scraping-jobs/$JOB4/retry"

step "13. Retrying a completed job -> 422"
admin -X POST "$BASE/admin/scraping-jobs/$JOB1/retry"

step "14. Validation errors"
admin -X POST "$BASE/admin/scraping-jobs" -d '{"company_id":1,"spider":"nope"}'
admin -X POST "$BASE/admin/scraping-jobs" -d '{"company_id":9999}'
admin "$BASE/admin/scraping-jobs/9999"

printf '\nDone. Check the log (php artisan pail) for "Posting synced" and "Scraping job finished".\n'
```

**Run:**

```bash
mkdir -p scripts
php artisan migrate:fresh --seed
bash scripts/demo.sh
```

**Concepts**

- `curl -w '%{http_code}'` prints the status after each body; add `-i` to see headers such as `X-Request-Id`.
- `scraper:simulate` exits non-zero on a failed callback, hence `|| true` under `set -e`.
- Each step maps to a Laravel feature: 401 (middleware), 422 (FormRequest), 404 (route-model binding),
  403 (`authorize()`), 409 (lock + service), unchanged/updated (row hash), log lines (queue + event).

[Back to question](#q29) · [Learn about the debugging toolbox](README.md#24-debugging-toolbox)

---

## I. Tests

<a id="s30"></a>

### S30. `TriggerScrapingJobTest`

**Run:** `php artisan make:test TriggerScrapingJobTest`

**File:** `tests/Feature/TriggerScrapingJobTest.php`

```php
<?php

namespace Tests\Feature;

use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TriggerScrapingJobTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['scraping.admin_api_key' => 'test-key']);
    }

    private function headers(): array
    {
        return ['X-Admin-Key' => 'test-key'];
    }

    public function test_it_requires_the_admin_key(): void
    {
        $this->postJson('/api/admin/scraping-jobs', [])->assertUnauthorized();
    }

    public function test_it_dispatches_an_indeed_job_using_the_serial_number_fallback(): void
    {
        $company = Company::factory()->create(['serial_number' => 'S-100']);

        $this->postJson('/api/admin/scraping-jobs', [
            'company_id' => $company->id,
            'spider' => 'indeed-job-details',
        ], $this->headers())
            ->assertCreated()
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.spider', 'indeed-job-details')
            ->assertJsonMissingPath('data.callback_secret');

        $this->assertDatabaseHas('scraping_jobs', [
            'company_id' => $company->id,
            'spider' => 'indeed-job-details',
            'status' => 'pending',
        ]);
    }

    public function test_a_second_dispatch_for_the_same_company_and_spider_returns_409(): void
    {
        $company = Company::factory()->create(['serial_number' => 'S-100']);
        $payload = ['company_id' => $company->id, 'spider' => 'indeed-job-details'];

        $this->postJson('/api/admin/scraping-jobs', $payload, $this->headers())->assertCreated();
        $this->postJson('/api/admin/scraping-jobs', $payload, $this->headers())->assertStatus(409);
    }

    public function test_airwork_spider_without_airwork_access_returns_422(): void
    {
        $company = Company::factory()->create();   // airwork_enabled = false

        $this->postJson('/api/admin/scraping-jobs', ['company_id' => $company->id], $this->headers())
            ->assertUnprocessable();
    }

    public function test_an_invalid_spider_fails_validation(): void
    {
        $company = Company::factory()->create();

        $this->postJson('/api/admin/scraping-jobs', [
            'company_id' => $company->id,
            'spider' => 'nope',
        ], $this->headers())
            ->assertUnprocessable()
            ->assertJsonValidationErrors('spider');
    }
}
```

**Concepts**

- `RefreshDatabase` resets the DB between tests; `phpunit.xml` already uses in-memory SQLite, the `array` cache,
  and the `sync` queue.
- `postJson($uri, $data, $headers)` sends JSON and returns a `TestResponse` with `assertCreated()`,
  `assertStatus()`, `assertJsonPath()`, and `assertJsonValidationErrors()`.
- `config([...])` overrides config for one test.

**Run:** `php artisan test --filter=TriggerScrapingJobTest`

[Back to question](#q30) · [Learn about Testing](README.md#20-testing)

---

<a id="s31"></a>

### S31. `ScrapingJobCompleteTest`

**Run:** `php artisan make:test ScrapingJobCompleteTest`

**File:** `tests/Feature/ScrapingJobCompleteTest.php`

```php
<?php

namespace Tests\Feature;

use App\Jobs\SyncPostingJob;
use App\Models\ScrapingJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class ScrapingJobCompleteTest extends TestCase
{
    use RefreshDatabase;

    /** Sign the exact JSON bytes we send, like the real scraper does. */
    private function signedPost(ScrapingJob $job, array $payload, ?string $secret = null): TestResponse
    {
        $body = json_encode($payload);
        $signature = hash_hmac('sha256', $body, $secret ?? $job->callback_secret);

        return $this->call(
            'POST',
            "/api/scraping-jobs/{$job->spider->value}/complete",
            [], [], [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
                'HTTP_X_HMAC_SIGNATURE' => $signature,
            ],
            $body,
        );
    }

    private function payload(ScrapingJob $job): array
    {
        return [
            'job_id' => $job->id,
            'status' => 'success',
            'records' => [
                ['external_id' => 'a', 'record' => ['title' => 'One']],
                ['external_id' => 'b', 'record' => ['title' => 'Two']],
            ],
        ];
    }

    public function test_a_bad_signature_is_forbidden(): void
    {
        $job = ScrapingJob::factory()->create();

        $this->signedPost($job, $this->payload($job), 'wrong-secret')->assertForbidden();
        $this->assertDatabaseCount('scraped_postings', 0);
    }

    public function test_a_valid_callback_ingests_records_and_dispatches_sync_jobs(): void
    {
        Queue::fake();
        $job = ScrapingJob::factory()->create();

        $this->signedPost($job, $this->payload($job))
            ->assertOk()
            ->assertJsonPath('data.status', 'completed')
            ->assertJsonPath('data.records.created', 2);

        $this->assertDatabaseHas('scraped_postings', ['provider' => 'indeed', 'external_id' => 'a']);
        Queue::assertPushed(SyncPostingJob::class, 2);
    }

    public function test_sending_the_same_records_again_is_unchanged_and_not_resynced(): void
    {
        Queue::fake();
        $job = ScrapingJob::factory()->create();

        $this->signedPost($job, $this->payload($job))->assertOk();
        $this->signedPost($job, $this->payload($job))
            ->assertOk()
            ->assertJsonPath('data.records.unchanged', 2);

        Queue::assertPushed(SyncPostingJob::class, 2);   // still 2: nothing new was dispatched
    }

    public function test_validation_rejects_a_malformed_record(): void
    {
        $job = ScrapingJob::factory()->create();
        $payload = ['job_id' => $job->id, 'status' => 'success', 'records' => [['record' => []]]];

        $this->signedPost($job, $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('records.0.external_id');
    }
}
```

**Concepts**

- Why `$this->call()` and not `postJson()`? The signature covers the **raw bytes**, so you need full control over
  exactly what is sent.
- `Queue::fake()` stops real execution and lets you assert what was dispatched (`assertPushed(Class, times)`).
- Server variables in `call()` use the `HTTP_X_...` form for custom headers.

**Run:** `php artisan test --filter=ScrapingJobCompleteTest`

[Back to question](#q31) · [Learn about Testing](README.md#20-testing)

---

<a id="s32"></a>

### S32. Eligibility and command tests

**Run:** `php artisan make:test DispatchEligibilityTest`

**File:** `tests/Feature/DispatchEligibilityTest.php`

```php
<?php

namespace Tests\Feature;

use App\Enums\ScrapingSpiderEnum;
use App\Models\Company;
use App\Models\ScrapingJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DispatchEligibilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['scraping.admin_api_key' => 'test-key']);
    }

    /** @return array{serial: Company, custom: Company, airwork: Company, none: Company} */
    private function makeCompanies(): array
    {
        $serial = Company::factory()->create(['serial_number' => 'S-1']);

        $custom = Company::factory()->create();
        $custom->indeedCredentials()->create(['email' => 'custom@example.com']);

        $airwork = Company::factory()->airwork()->create();
        $none = Company::factory()->create();

        return compact('serial', 'custom', 'airwork', 'none');
    }

    public function test_trigger_all_for_an_indeed_spider_only_dispatches_to_indeed_companies(): void
    {
        $this->makeCompanies();

        $this->postJson(
            '/api/admin/scraping-jobs/trigger-all',
            ['spider' => 'indeed-job-details'],
            ['X-Admin-Key' => 'test-key'],
        )
            ->assertOk()
            ->assertJsonCount(2, 'data');

        $this->assertDatabaseCount('scraping_jobs', 2);
    }

    public function test_an_airwork_only_company_is_not_selected_for_an_indeed_spider(): void
    {
        $companies = $this->makeCompanies();

        $this->artisan('scraping:dispatch-companies', ['--spider' => 'indeed-job-details'])
            ->assertSuccessful();

        $this->assertDatabaseMissing('scraping_jobs', ['company_id' => $companies['airwork']->id]);
        $this->assertDatabaseHas('scraping_jobs', ['company_id' => $companies['serial']->id]);
        $this->assertDatabaseHas('scraping_jobs', ['company_id' => $companies['custom']->id]);
    }

    public function test_the_airwork_spider_only_selects_airwork_enabled_companies(): void
    {
        $companies = $this->makeCompanies();

        $this->artisan('scraping:dispatch-companies', ['--spider' => 'airregi-jobs'])
            ->assertSuccessful();

        $this->assertSame(1, ScrapingJob::query()->count());
        $this->assertDatabaseHas('scraping_jobs', [
            'company_id' => $companies['airwork']->id,
            'spider' => ScrapingSpiderEnum::AirregiJobs->value,
        ]);
    }

    public function test_an_unknown_spider_fails_the_command(): void
    {
        $this->artisan('scraping:dispatch-companies', ['--spider' => 'nope'])
            ->assertFailed();
    }
}
```

**Concepts**

- `$this->artisan('name', [options])` runs a command in tests; `assertSuccessful()` / `assertFailed()` check the
  exit code.
- `compact('a', 'b')` builds `['a' => $a, 'b' => $b]` from variable names.
- The same `eligibleCompaniesQuery()` backs the HTTP button, the command, and the cron, so one test guards all three.
- Passing an enum `->value` into `assertDatabaseHas` matches the stored string.

**Run:** `php artisan test --filter=DispatchEligibilityTest`

[Back to question](#q32) · [Learn about spider-aware selection](README.md#183-why-spider-aware-company-selection-matters)

---

<a id="s33"></a>

### S33. Enum unit tests

**Run:** `php artisan make:test EnumsTest --unit`

**File:** `tests/Unit/EnumsTest.php`

```php
<?php

namespace Tests\Unit;

use App\Enums\IngestOutcomeEnum;
use App\Enums\JobSeekerSyncStatusEnum;
use App\Enums\ScrapingJobStatusEnum;
use App\Enums\ScrapingSpiderEnum;
use PHPUnit\Framework\TestCase;

class EnumsTest extends TestCase
{
    public function test_spider_provider_mapping(): void
    {
        $this->assertSame('indeed', ScrapingSpiderEnum::from('indeed-job-applications')->provider());
        $this->assertSame('indeed', ScrapingSpiderEnum::IndeedJobDetails->provider());
        $this->assertSame('airwork', ScrapingSpiderEnum::AirregiJobs->provider());
        $this->assertTrue(ScrapingSpiderEnum::IndeedJobDetails->isIndeed());
        $this->assertFalse(ScrapingSpiderEnum::AirregiJobs->isIndeed());
    }

    public function test_spider_callback_path_and_unknown_values(): void
    {
        $this->assertSame(
            '/api/scraping-jobs/airregi-jobs/complete',
            ScrapingSpiderEnum::AirregiJobs->callbackPath(),
        );
        $this->assertNull(ScrapingSpiderEnum::tryFrom('nope'));
    }

    public function test_status_helpers(): void
    {
        $this->assertTrue(ScrapingJobStatusEnum::TimedOut->isRetryable());
        $this->assertTrue(ScrapingJobStatusEnum::Failed->isRetryable());
        $this->assertFalse(ScrapingJobStatusEnum::Completed->isRetryable());
        $this->assertTrue(ScrapingJobStatusEnum::Completed->isTerminal());
        $this->assertFalse(ScrapingJobStatusEnum::Processing->isTerminal());
    }

    public function test_sync_status_helpers_and_outcome_mapping(): void
    {
        $this->assertTrue(JobSeekerSyncStatusEnum::Failed->isRetryable());
        $this->assertFalse(JobSeekerSyncStatusEnum::Synced->isRetryable());
        $this->assertSame('Waiting to sync', JobSeekerSyncStatusEnum::Pending->label());

        $this->assertSame(
            JobSeekerSyncStatusEnum::Pending,
            JobSeekerSyncStatusEnum::fromIngestOutcome(IngestOutcomeEnum::Created),
        );
        $this->assertSame(
            JobSeekerSyncStatusEnum::Synced,
            JobSeekerSyncStatusEnum::fromIngestOutcome(IngestOutcomeEnum::Unchanged),
        );
    }
}
```

**Concepts**

- A **unit** test extends `PHPUnit\Framework\TestCase` (no app boot, no DB), so it runs in milliseconds.
- Enum cases are singletons, so `assertSame` compares them correctly.

**Run:** `php artisan test --filter=EnumsTest`

[Back to question](#q33) · [Learn about Enums](README.md#11-enums)

---

## J. Applicants extension

<a id="s34"></a>

### S34. `IndeedJobApplication`

**Run:**

```bash
php artisan make:model IndeedJobApplication -mf
```

**File:** `database/migrations/*_create_indeed_job_applications_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('indeed_job_applications', function (Blueprint $table): void {
            $table->id();
            $table->string('external_application_id')->unique();
            $table->string('user_unique_id');
            $table->char('row_hash', 64);
            $table->json('application_record');
            $table->string('job_seeker_sync_status', 32)->default('pending');
            $table->timestamp('last_otp_received_at')->nullable();
            $table->foreignId('job_seeker_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamps();

            $table->index('user_unique_id');
            $table->index('row_hash');
            $table->index('job_seeker_sync_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('indeed_job_applications');
    }
};
```

**File:** `app/Models/IndeedJobApplication.php`

```php
<?php

namespace App\Models;

use App\Enums\JobSeekerSyncStatusEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IndeedJobApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'external_application_id',
        'user_unique_id',
        'row_hash',
        'application_record',
        'job_seeker_sync_status',
        'last_otp_received_at',
        'job_seeker_user_id',
    ];

    protected function casts(): array
    {
        return [
            'application_record' => 'array',
            'job_seeker_sync_status' => JobSeekerSyncStatusEnum::class,
            'last_otp_received_at' => 'datetime',
        ];
    }

    public function jobSeekerUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'job_seeker_user_id');
    }

    public function scopePendingSync(Builder $query): Builder
    {
        return $query->where(
            'job_seeker_sync_status',
            JobSeekerSyncStatusEnum::Pending->value,
        );
    }

    public function scopeForJobSeeker(Builder $query, int $userId): Builder
    {
        return $query->where('job_seeker_user_id', $userId);
    }
}
```

**File:** `database/factories/IndeedJobApplicationFactory.php`

```php
<?php

namespace Database\Factories;

use App\Enums\JobSeekerSyncStatusEnum;
use App\Models\IndeedJobApplication;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<IndeedJobApplication>
 */
class IndeedJobApplicationFactory extends Factory
{
    protected $model = IndeedJobApplication::class;

    public function definition(): array
    {
        $record = [
            'title' => fake()->jobTitle(),
            'location' => fake()->city(),
            'answers' => [fake()->sentence()],
        ];

        return [
            'external_application_id' => fake()->unique()->uuid(),
            'user_unique_id' => fake()->uuid(),
            'row_hash' => hash('sha256', json_encode($record, JSON_THROW_ON_ERROR)),
            'application_record' => $record,
            'job_seeker_sync_status' => JobSeekerSyncStatusEnum::Pending,
            'last_otp_received_at' => null,
            'job_seeker_user_id' => null,
        ];
    }

    public function synced(): static
    {
        return $this->state(fn (): array => [
            'job_seeker_sync_status' => JobSeekerSyncStatusEnum::Synced,
            'last_otp_received_at' => now(),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (): array => [
            'job_seeker_sync_status' => JobSeekerSyncStatusEnum::Failed,
        ]);
    }
}
```

**Run:** `php artisan migrate`

**Concepts**

- `external_application_id` is the **idempotency key** (unique): the same scraped applicant never creates two rows.
- `row_hash` is indexed but **not** unique; it only detects changes.
- Use `json`, not `jsonb`, because the demo runs on SQLite (`jsonb` is Postgres-only).
- `nullOnDelete()` keeps the application if the platform user is deleted.
- `scopePendingSync` becomes `->pendingSync()`; the `forJobSeeker` scope takes an extra argument.
- Factory states make tests read like the domain: `IndeedJobApplication::factory()->synced()->create()`.

[Back to question](#q34) · [Learn about Eloquent models](README.md#12-eloquent-models)

---

<a id="s35"></a>

### S35. `IndeedJobApplicationModelTest`

**Run:** `php artisan make:test IndeedJobApplicationModelTest`

**File:** `tests/Feature/IndeedJobApplicationModelTest.php`

```php
<?php

namespace Tests\Feature;

use App\Enums\JobSeekerSyncStatusEnum;
use App\Models\IndeedJobApplication;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndeedJobApplicationModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_casts_the_json_enum_and_datetime_columns(): void
    {
        $application = IndeedJobApplication::factory()->create([
            'application_record' => ['title' => 'Backend Engineer', 'answers' => [1, 2]],
            'last_otp_received_at' => '2026-11-05 10:00:00',
        ]);

        $this->assertIsArray($application->application_record);
        $this->assertSame('Backend Engineer', $application->application_record['title']);
        $this->assertSame(JobSeekerSyncStatusEnum::Pending, $application->job_seeker_sync_status);
        $this->assertSame(
            '2026-11-05 10:00:00',
            $application->last_otp_received_at->toDateTimeString(),
        );
    }

    public function test_it_belongs_to_a_job_seeker_user(): void
    {
        $user = User::factory()->create();

        $application = IndeedJobApplication::factory()->create([
            'job_seeker_user_id' => $user->id,
        ]);

        $this->assertTrue($application->jobSeekerUser->is($user));
    }

    public function test_pending_sync_scope_returns_only_pending_rows(): void
    {
        IndeedJobApplication::factory()->create();
        IndeedJobApplication::factory()->synced()->create();
        IndeedJobApplication::factory()->failed()->create();

        $this->assertSame(1, IndeedJobApplication::query()->pendingSync()->count());
    }

    public function test_external_application_id_is_unique(): void
    {
        IndeedJobApplication::factory()->create([
            'external_application_id' => 'indeed-app-1',
        ]);

        $this->expectException(QueryException::class);

        IndeedJobApplication::factory()->create([
            'external_application_id' => 'indeed-app-1',
        ]);
    }
}
```

**Concepts**

- `$model->is($other)` compares two models by table and primary key.
- Call `expectException()` **before** the statement that should throw.
- A new test file named after what it proves keeps failures easy to locate.

**Run:** `php artisan test --filter=IndeedJobApplicationModelTest`

[Back to question](#q35) · [Learn about Database testing](README.md#20-testing)

---

<a id="s36"></a>

### S36. Applications API

**Run:**

```bash
php artisan make:resource IndeedJobApplicationResource
php artisan make:controller Api/IndeedJobApplicationController
php artisan make:test IndeedJobApplicationApiTest
```

**File:** `app/Http/Resources/IndeedJobApplicationResource.php`

```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IndeedJobApplicationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'external_application_id' => $this->external_application_id,
            'user_unique_id' => $this->user_unique_id,
            'row_hash' => $this->row_hash,
            'application_record' => $this->application_record,
            'sync_status' => $this->job_seeker_sync_status->value,
            'sync_status_label' => $this->job_seeker_sync_status->label(),
            'can_retry_sync' => $this->job_seeker_sync_status->isRetryable(),
            'last_otp_received_at' => $this->last_otp_received_at?->toISOString(),
            'job_seeker_user_id' => $this->job_seeker_user_id,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
```

**File:** `app/Http/Controllers/Api/IndeedJobApplicationController.php`

```php
<?php

namespace App\Http\Controllers\Api;

use App\Enums\JobSeekerSyncStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Resources\IndeedJobApplicationResource;
use App\Models\IndeedJobApplication;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class IndeedJobApplicationController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->validate([
            'sync_status' => ['sometimes', Rule::enum(JobSeekerSyncStatusEnum::class)],
            'job_seeker_user_id' => ['sometimes', 'integer'],
            'per_page' => ['sometimes', 'integer', 'between:1,100'],
        ]);

        $applications = IndeedJobApplication::query()
            ->when($filters['sync_status'] ?? null, fn (Builder $q, $status) => $q->where('job_seeker_sync_status', $status))
            ->when($filters['job_seeker_user_id'] ?? null, fn (Builder $q, $id) => $q->forJobSeeker((int) $id))
            ->latest('id')
            ->paginate($filters['per_page'] ?? 15);

        return IndeedJobApplicationResource::collection($applications);
    }

    public function show(IndeedJobApplication $indeedJobApplication): IndeedJobApplicationResource
    {
        return IndeedJobApplicationResource::make($indeedJobApplication);
    }
}
```

**File:** `routes/api.php` (full updated file)

```php
<?php

use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\IndeedJobApplicationController;
use App\Http\Controllers\Api\PostingController;
use App\Http\Controllers\Api\ScrapingJobCompleteController;
use App\Http\Controllers\Api\ScrapingJobController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->middleware('protected')->group(function () {
    Route::get('companies', [CompanyController::class, 'index']);
    Route::post('companies', [CompanyController::class, 'store']);

    Route::get('postings', [PostingController::class, 'index']);

    Route::get('applications', [IndeedJobApplicationController::class, 'index']);
    Route::get('applications/{indeedJobApplication}', [IndeedJobApplicationController::class, 'show']);

    Route::get('scraping-jobs', [ScrapingJobController::class, 'index']);
    Route::get('scraping-jobs/status', [ScrapingJobController::class, 'status']);   // before {scrapingJob}!
    Route::get('scraping-jobs/{scrapingJob}', [ScrapingJobController::class, 'show']);
    Route::post('scraping-jobs', [ScrapingJobController::class, 'store']);
    Route::post('scraping-jobs/trigger-all', [ScrapingJobController::class, 'triggerAll']);
    Route::post('scraping-jobs/{scrapingJob}/retry', [ScrapingJobController::class, 'retry']);
});

// Machine-to-machine callback: protected by the HMAC signature, not by the admin key.
Route::post('scraping-jobs/{spider}/complete', ScrapingJobCompleteController::class);
```

**File:** `tests/Feature/IndeedJobApplicationApiTest.php`

```php
<?php

namespace Tests\Feature;

use App\Models\IndeedJobApplication;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndeedJobApplicationApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['scraping.admin_api_key' => 'test-key']);
    }

    private function headers(): array
    {
        return ['X-Admin-Key' => 'test-key'];
    }

    public function test_it_requires_the_admin_key(): void
    {
        $this->getJson('/api/admin/applications')->assertUnauthorized();
    }

    public function test_it_lists_applications_with_status_labels(): void
    {
        IndeedJobApplication::factory()->create();

        $this->getJson('/api/admin/applications', $this->headers())
            ->assertOk()
            ->assertJsonPath('data.0.sync_status', 'pending')
            ->assertJsonPath('data.0.sync_status_label', 'Waiting to sync')
            ->assertJsonPath('data.0.can_retry_sync', false);
    }

    public function test_it_filters_by_sync_status(): void
    {
        IndeedJobApplication::factory()->create();
        IndeedJobApplication::factory()->failed()->create();

        $this->getJson('/api/admin/applications?sync_status=failed', $this->headers())
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.can_retry_sync', true);
    }

    public function test_an_invalid_sync_status_is_rejected(): void
    {
        $this->getJson('/api/admin/applications?sync_status=banana', $this->headers())
            ->assertUnprocessable()
            ->assertJsonValidationErrors('sync_status');
    }

    public function test_show_returns_404_for_an_unknown_application(): void
    {
        $this->getJson('/api/admin/applications/9999', $this->headers())->assertNotFound();
    }
}
```

**Concepts**

- The Resource exposes enum helper methods (`label()`, `isRetryable()`), so the frontend never re-implements them.
- Route-model binding needs the parameter name to equal the camel-cased type-hint variable:
  `{indeedJobApplication}` with `IndeedJobApplication $indeedJobApplication`.
- `->when(...)` chains optional filters; the scope `forJobSeeker()` is reused from the model.
- Query-string values arrive as strings, hence `(int) $id`.

**Run:** `php artisan test --filter=IndeedJobApplicationApiTest`

[Back to question](#q36) · [Learn about API Resources](README.md#15-api-resources)

---

## K. Hardening

<a id="s37"></a>

### S37. Expire stale jobs

**Run:**

```bash
php artisan make:command ExpireStaleScrapingJobs
php artisan make:test ExpireStaleScrapingJobsTest
```

**File:** `app/Console/Commands/ExpireStaleScrapingJobs.php`

```php
<?php

namespace App\Console\Commands;

use App\Enums\ScrapingJobStatusEnum;
use App\Models\ScrapingJob;
use Illuminate\Console\Command;

class ExpireStaleScrapingJobs extends Command
{
    protected $signature = 'scraping:expire-stale';

    protected $description = 'Mark pending/processing scraping jobs that never called back as timed out';

    public function handle(): int
    {
        $cutoff = now()->subMinutes((int) config('scraping.stale_timeout_minutes'));

        $count = ScrapingJob::query()
            ->active()
            ->where('created_at', '<', $cutoff)
            ->update([
                'status' => ScrapingJobStatusEnum::TimedOut->value,
                'error_message' => 'Timed out waiting for the scraper callback',
                'completed_at' => now(),
            ]);

        $this->info("Marked {$count} job(s) as timed out");

        return self::SUCCESS;
    }
}
```

**File:** `routes/console.php` (full updated file)

```php
<?php

use App\Enums\ScrapingSpiderEnum;
use Illuminate\Support\Facades\Schedule;

Schedule::command('scraping:dispatch-companies --spider='.ScrapingSpiderEnum::AirregiJobs->value)
    ->onOneServer()
    ->withoutOverlapping()
    ->dailyAt('06:30')
    ->timezone('Asia/Tokyo');

Schedule::command('scraping:dispatch-companies --spider='.ScrapingSpiderEnum::IndeedJobDetails->value)
    ->onOneServer()
    ->withoutOverlapping()
    ->dailyAt('07:15')
    ->timezone('Asia/Tokyo');

Schedule::command('scraping:dispatch-companies --spider='.ScrapingSpiderEnum::IndeedJobApplications->value)
    ->onOneServer()
    ->withoutOverlapping()
    ->dailyAt('07:30')
    ->timezone('Asia/Tokyo');

Schedule::command('scraping:expire-stale')
    ->onOneServer()
    ->withoutOverlapping()
    ->everyFifteenMinutes();
```

**File:** `tests/Feature/ExpireStaleScrapingJobsTest.php`

```php
<?php

namespace Tests\Feature;

use App\Enums\ScrapingJobStatusEnum;
use App\Models\Company;
use App\Models\ScrapingJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpireStaleScrapingJobsTest extends TestCase
{
    use RefreshDatabase;

    public function test_old_active_jobs_are_marked_timed_out_and_recent_ones_are_left_alone(): void
    {
        config(['scraping.stale_timeout_minutes' => 45]);

        $stale = ScrapingJob::factory()->create(['created_at' => now()->subHour()]);
        $fresh = ScrapingJob::factory()->create();
        $done = ScrapingJob::factory()->create([
            'status' => ScrapingJobStatusEnum::Completed,
            'created_at' => now()->subHours(3),
        ]);

        $this->artisan('scraping:expire-stale')
            ->expectsOutput('Marked 1 job(s) as timed out')
            ->assertSuccessful();

        $this->assertSame(ScrapingJobStatusEnum::TimedOut, $stale->refresh()->status);
        $this->assertSame(ScrapingJobStatusEnum::Pending, $fresh->refresh()->status);
        $this->assertSame(ScrapingJobStatusEnum::Completed, $done->refresh()->status);
    }

    public function test_an_expired_job_becomes_retryable(): void
    {
        config([
            'scraping.stale_timeout_minutes' => 45,
            'scraping.admin_api_key' => 'test-key',
        ]);

        $company = Company::factory()->create(['serial_number' => 'S-1']);
        $stale = ScrapingJob::factory()->create([
            'company_id' => $company->id,
            'spider' => 'indeed-job-details',
            'created_at' => now()->subHour(),
        ]);

        $this->artisan('scraping:expire-stale')->assertSuccessful();

        $this->assertTrue($stale->refresh()->status->isRetryable());

        $this->postJson("/api/admin/scraping-jobs/{$stale->id}/retry", [], ['X-Admin-Key' => 'test-key'])
            ->assertCreated()
            ->assertJsonPath('data.retry_count', 1);
    }
}
```

**Concepts**

- Even without this command, a stale job stops **blocking** dispatch (the `runningJob()` time window). The command
  makes the **status** truthful so the admin UI shows `timed_out` and the Retry button works.
- A query-builder `->update([...])` runs one SQL `UPDATE` for all matching rows and returns the affected count
  (no model events, no casts: pass `->value` strings).
- Factories bypass `$fillable`, so tests can set `created_at` directly.
- `expectsOutput()` asserts command output; the third job proves terminal jobs are never touched.

**Run:** `php artisan test --filter=ExpireStaleScrapingJobsTest` then `php artisan schedule:list`

[Back to question](#q37) · [Learn about the Scheduler](README.md#18-console-commands-and-the-scheduler)

---

# Completion checklist

After Q37, run these once:

```bash
php artisan migrate:fresh --seed
php artisan test --compact          # all tests green
vendor/bin/pint --dirty             # format
php artisan route:list --path=api   # 13 routes
php artisan schedule:list           # 4 entries
php artisan serve                   # terminal 1
php artisan queue:work              # terminal 2
bash scripts/demo.sh                # terminal 3
```

**What you will have built and learned**

| Area                                                  | Questions        |
| ----------------------------------------------------- | ---------------- |
| Config, middleware, exception handling                | Q1-Q2            |
| Enums (backed, methods, `match`, casts, `Rule::enum`) | Q3-Q5            |
| Exceptions mapped to HTTP status codes                | Q6, Q23          |
| Migrations, models, relations, scopes, casts          | Q7-Q9, Q34       |
| Factories, seeders                                    | Q10, Q34         |
| Interface + trait + registry + container tags         | Q11-Q13          |
| Queued jobs, events, listeners                        | Q14, Q16         |
| Service layer, cache locks, transactions              | Q15, Q17, Q21    |
| Form Requests, HMAC authorization, Resources          | Q18-Q20, Q36     |
| Controllers, route-model binding, routes              | Q21-Q25, Q36     |
| Artisan commands, scheduler                           | Q26-Q28, Q37     |
| Feature and unit tests                                | Q30-Q33, Q35-Q37 |

When you want to continue, send: **"Answer Q1"** (then Q2, Q3, ...). Each answer will use the exact file paths and
names above, so every later question plugs into what you already built.
