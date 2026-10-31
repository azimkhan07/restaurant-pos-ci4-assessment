# Restaurant POS - CodeIgniter 4 Assessment

This project completes the three tasks in `_PHP Task (2).pdf`:

1. **Task 1** - understand the supplied restaurant data and document the findings.
2. **Task 2** - CodeIgniter 4 API that lists orders with full item, price and payment details.
3. **Task 3** - CodeIgniter 4 cart with five items, quantity controls, 12.5% inclusive tax, total incl. tax and tax amount.

> The task PDF explicitly says **CodeIgniter 4** (`Codignator 4`). This project uses CodeIgniter **4.7.5**.

## GitHub repository description (copy this)

**Short description (GitHub "About" field):**

```text
CodeIgniter 4 restaurant POS assessment: orders + payments API, data-quality findings, and a session cart with 12.5% inclusive tax.
```

**Long description:**

```text
A complete CodeIgniter 4 (PHP 8) assessment project for a restaurant POS use case. It contains: (1) a normalised SQLite/MySQL database built from the supplied menu, order-history and payments data, with documented data-quality findings; (2) a paginated REST API that lists orders with full line-item, price, payment, tips, discount, refund and balance-due details, plus a live data-quality endpoint; and (3) a session-based cart with five items, quantity increase/decrease, and correct 12.5% inclusive tax calculation in integer pence. Includes migrations, seeder, Postman collection, security filters (API key, rate limiting, CSRF), indexes and setup instructions.
```

**Suggested GitHub topics:**

```text
codeigniter4, php, php8, rest-api, mysql, sqlite, postman, restaurant-pos, shopping-cart, tax-calculation, interview-assessment
```

## What is included

- `app/` - controllers, models, services, filters, views, migration and seeder
- `database/mysql_schema.sql` - optional MySQL schema + all sample data
- `docs/DATA_FINDINGS.md` - Task 1 notes in detail
- `postman/Restaurant-POS.postman_collection.json` - Task 2 Postman requests
- `writable/database/restaurant_pos.sqlite` - ready-to-use SQLite database
- `run.bat` - Windows one-click server start
- `.env` - local SQLite configuration (no secrets)

## Requirements

| Requirement | Details |
|---|---|
| PHP | 8.1 or newer; tested on 8.2.12 |
| PHP extensions | `intl`, `sqlite3`, `mbstring`, `json` (the first two are the important ones for CI4 + SQLite) |
| Composer | Needed only if `vendor/` is missing (it is not committed to Git) |
| Database | SQLite is included; MySQL is optional |
| Browser | Any modern browser for the UI |
| Postman | Optional, for importing the API collection |

Check your PHP setup:

```bat
php -v
php -m
```

You should see `intl` and `sqlite3` in the module list. If they are missing, open `php.ini` and uncomment:

```ini
extension=intl
extension=sqlite3
```

Restart the terminal/Apache after changing `php.ini`.

## How to run - full steps

### Option A - Windows with XAMPP (recommended)

1. Clone the repository:

   ```bat
   git clone https://github.com/azimkhan07/restaurant-pos-ci4-assessment.git
   cd restaurant-pos-ci4-assessment
   ```

2. Install dependencies if the `vendor` folder is missing:

   ```bat
   composer install
   ```

   If Composer reports that `ext-intl` is missing, enable `intl` in `php.ini` first, or run:

   ```bat
   php -d extension=intl C:\composer\composer.phar install
   ```

3. Make sure `.env` exists. It is committed in this repository. If it is missing:

   ```bat
   copy env .env
   ```

4. Start the app:

   ```bat
   run.bat
   ```

   `run.bat` automatically uses `D:\xampp\php\php.exe` when `php` is not on PATH and starts the server with `intl` + `sqlite3` enabled.

5. Open <http://localhost:8080>.

The bundled SQLite database is already seeded. If you want to rebuild it from scratch:

```bat
php spark migrate --all
php spark db:seed AssessmentSeeder
```

### Option B - Any OS with PHP on PATH

```bash
composer install
cp env .env          # only if .env is missing
php spark migrate --all
php spark db:seed AssessmentSeeder
php spark serve
```

If the `intl`/`sqlite3` extensions are not enabled globally, start the PHP server directly with them enabled:

```bash
php -d extension=intl -d extension=sqlite3 -S 127.0.0.1:8080 -t public public/index.php
```

Then open <http://localhost:8080>.

### Option C - MySQL instead of SQLite

1. Create a MySQL database named `restaurant_pos`.
2. Import `database/mysql_schema.sql`.
3. Copy the values from `env.mysql.example` into `.env`:

   ```ini
   database.default.hostname = localhost
   database.default.database = restaurant_pos
   database.default.username = root
   database.default.password =
   database.default.DBDriver = MySQLi
   database.default.port = 3306
   ```

4. Restart the server. No migration is needed because the SQL file creates and seeds all tables.

## Pages to open

| URL | What it shows |
|---|---|
| <http://localhost:8080> | Project landing page |
| <http://localhost:8080/orders> | Order history with items, prices and payments |
| <http://localhost:8080/orders/10> | One order in full detail |
| <http://localhost:8080/cart> | Task 3 cart |
| <http://localhost:8080/api/orders> | Task 2 API |
| <http://localhost:8080/api/data-quality> | Task 1 live findings |

## Troubleshooting

| Problem | Fix |
|---|---|
| `Class "IntlDateFormatter" not found` | Enable `extension=intl` in `php.ini` or use `run.bat` |
| `SQLite3 class not found` | Enable `extension=sqlite3` or use `run.bat` |
| `vendor/autoload.php` missing | Run `composer install` |
| Port 8080 already in use | Change the port in `run.bat` and `app/Config/App.php` / `.env` |
| Database locked | Stop other PHP processes using `writable/database/restaurant_pos.sqlite` |
| Composer asks for zip/unzip | Install 7-Zip/unzip or use `composer install --prefer-source` |

The database file is created at `writable/database/restaurant_pos.sqlite`.

## Database

Normalised tables:

- `menus`
- `categories`
- `items`
- `item_prices` (supports Small / Large variants)
- `orders`
- `order_items` (historical price snapshot per line)
- `payments` (supports split payments, tips, discounts and refunds)

The seed data is the exact data from the PDF, stored in `app/Database/Seeds/assessment_data.json`.

### Optional MySQL setup

1. Create a database named `restaurant_pos`.
2. Import `database/mysql_schema.sql`.
3. Copy `env.mysql.example` values into `.env` (or edit the `database.default.*` lines).
4. Set `database.default.DBDriver = MySQLi` and run the app.

## API (Task 2)

Base URL: `http://localhost:8080`

| Method | Endpoint | Description |
|---|---|---|
| GET | `/api/health` | Health check |
| GET | `/api/orders` | Paginated orders with items and payments |
| GET | `/api/orders/{id}` | One order with full details and reconciliation |
| GET | `/api/menu` | Nested menu -> category -> item -> prices |
| GET | `/api/data-quality` | Live findings from Task 1 |

### Query parameters for `/api/orders`

| Parameter | Default | Notes |
|---|---|---|
| `page` | 1 | Positive integer |
| `per_page` | 20 | 1-100 |
| `status` | all | e.g. `Completed` |
| `search` | empty | Numeric order number, or status text |

Examples:

```text
GET /api/orders
GET /api/orders?status=Completed&per_page=5
GET /api/orders/10
GET /api/orders/17
GET /api/menu
GET /api/data-quality
```

A ready-to-import Postman collection is in:

```text
postman/Restaurant-POS.postman_collection.json
```

It contains all endpoints plus an example order that has the payment mismatch (order 17).

### API response shape

```json
{
  "status": "success",
  "data": [],
  "meta": {
    "page": 1,
    "per_page": 20,
    "total": 11,
    "total_pages": 1,
    "filters": { "status": null, "search": null }
  }
}
```

Each order contains:

- order header and status
- line items with item name, category, menu, size, historical unit price, qty and line total
- payments with transaction reference, amount due, tips, discount, amount paid, type and status
- `order_total`, `paid_total`, `refunded_total`, `net_paid`, `balance_due` and `payment_status`

## Cart (Task 3)

Open <http://localhost:8080/cart>.

- Five items: £10.00, £7.50, £5.00, £2.50 and £3.00.
- Click an item to add it to the session cart.
- `+` / `-` changes quantity; price and total recalculate immediately.
- `Load example` reproduces the task sample: Item 1 x1, Item 2 x2, Item 4 x2 = **£30.00 total incl. tax**, **£3.33 tax**, **£26.67 net**.
- Tax is 12.5% inclusive: `tax = total x 12.5 / 112.5 = total / 9`.
- Money is calculated in integer pence to avoid floating-point errors.

The cart state is also available as JSON at `/cart/state` after loading the sample.

## Security and performance choices

- CSRF protection on all cart POST routes.
- Optional API key: set `api.key` in `.env` and send `X-API-Key`.
- API rate limit: 120 requests per minute per IP (custom `RateLimitFilter`).
- Input validation for pagination, order id, status and search length.
- Query Builder / prepared statements for all database access; no user SQL concatenation.
- Output escaping with `esc()` in all HTML views.
- Security headers enabled globally (`secureheaders` filter).
- Foreign keys, unique keys and indexes on `orders.order_date`, `orders.status`, `order_items.order_id`, `order_items.item_id`, `payments.order_id`, `payments.status`.
- No N+1 queries: the order list uses one query for orders, one for all items and one for all payments, then groups them in PHP.
- Pagination caps `per_page` at 100.
- Payment reconciliation avoids summing the repeated `amount_due` value across split payments.

## Task 1 notes

Full findings: [`docs/DATA_FINDINGS.md`](docs/DATA_FINDINGS.md).

Top findings:

- Order History is order-line level, not one row per order.
- 50 of 52 order lines use a historical price that differs from the current menu.
- Order 17 has a £0.00438 order-total vs payment-amount mismatch.
- Order 15 is marked Completed even though its only payment is Refunded.
- Item 6 is missing its size on order 11, line 5.
- Item 5 has a category/menu conflict (`cat_id` 2 = Drinks, but `menu_id` 1 = Food).
- Split payments repeat `amount_due`, so it must not be summed.
- Missing IDs: order line 40; payment refs 112, 113, 114, 117, 118.

## Project structure

```text
app/
  Controllers/        Web + API controllers
  Database/           Migration, seeder and exact PDF data JSON
  Filters/            API key and rate-limit filters
  Helpers/            Money and payment badge helpers
  Libraries/          OrderReportService and CartService
  Models/             One model per table
  Views/              Layout, orders and cart UI
database/             MySQL schema + data
docs/                 Data findings
postman/              Postman collection
public/assets/        CSS
writable/database/    SQLite database
```

## AI-assisted development note

AI assistance was used for boilerplate generation, converting the PDF tables into seed JSON, and a first-pass review of the money calculations. Every generated file was then run and checked locally:

- migrations and seeding were executed,
- `/api/orders`, `/api/orders/{id}`, `/api/menu`, `/api/data-quality` were called and inspected,
- the cart was exercised with the exact sample (£30 total, £3.33 tax),
- all PHP files were linted with `php -l`.

The final logic (especially the payment reconciliation and inclusive-tax calculation) is documented above so it can be explained line by line in the interview.

## Suggested repository name

`restaurant-pos-ci4-assessment`
