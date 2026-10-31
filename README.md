# Restaurant POS - CodeIgniter 4 Assessment

This project completes the three tasks in `_PHP Task (2).pdf`:

1. **Task 1** - understand the supplied restaurant data and document the findings.
2. **Task 2** - CodeIgniter 4 API that lists orders with full item, price and payment details.
3. **Task 3** - CodeIgniter 4 cart with five items, quantity controls, 12.5% inclusive tax, total incl. tax and tax amount.

> The task PDF explicitly says **CodeIgniter 4** (`Codignator 4`). This project uses CodeIgniter **4.7.5**.

## Requirements

- PHP 8.1+ (tested on PHP 8.2.12)
- `intl` and `sqlite3` PHP extensions
- Composer (only needed if you reinstall dependencies)

The project ships with SQLite so it runs without a MySQL server. A MySQL schema + data file is also included in `database/mysql_schema.sql` if you prefer MySQL.

## Quick start (Windows / XAMPP)

```bat
cd restaurant-pos
composer install
php spark migrate --all
php spark db:seed AssessmentSeeder
php spark serve
```

If `intl` / `sqlite3` are not enabled in `php.ini`, either uncomment these lines:

```ini
extension=intl
extension=sqlite3
```

or use the bundled `run.bat`, which starts PHP with both extensions:

```bat
run.bat
```

Then open <http://localhost:8080>.

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
