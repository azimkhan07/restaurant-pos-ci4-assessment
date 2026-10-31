# Restaurant POS - CodeIgniter 4

This is my submission for the PHP/CodeIgniter assessment. The task sheet mentions CodeIgniter 4, so I built it on CodeIgniter 4.7.5 with PHP 8.

The project has three parts:

- Task 1: understand the supplied menu, order and payment data and note the findings.
- Task 2: an API that returns orders with their items, prices and payment details.
- Task 3: a cart with five items, quantity controls and 12.5% inclusive tax.

I tested it on Windows with XAMPP and PHP 8.2.12. SQLite is used by default so it runs without setting up MySQL. A MySQL schema is also included.

## Repository description (copy for GitHub)

Short description:

```text
CodeIgniter 4 restaurant POS assessment with an orders/payments API, data-quality findings and a session cart that handles 12.5% inclusive tax.
```

Long description:

```text
CodeIgniter 4 (PHP 8) assessment project for a restaurant POS. It includes a normalised menu/order/payment database, a paginated REST API for orders with full item and payment details, a live data-quality endpoint with the findings from the supplied data, and a session-based cart with quantity controls and 12.5% inclusive tax. The project includes migrations, seed data, a Postman collection, API key support, rate limiting, CSRF protection and setup instructions for SQLite and MySQL.
```

Topics:

```text
codeigniter4, php, php8, rest-api, mysql, sqlite, postman, restaurant-pos, shopping-cart, tax-calculation, interview-assessment
```

## Requirements

- PHP 8.1 or newer. I used PHP 8.2.12.
- PHP extensions: `intl`, `sqlite3`, `mbstring` and `json`.
- Composer. You only need it if the `vendor` folder is missing.
- XAMPP or any PHP web server. For a quick run I used PHP's built-in server.

You can check the extensions with:

```bat
php -v
php -m
```

`intl` and `sqlite3` should be in the list. If they are not, open `php.ini` and remove the `;` from these two lines:

```ini
extension=intl
extension=sqlite3
```

After changing `php.ini`, close and reopen the terminal.

## How to run on Windows with XAMPP

1. Clone the repo and go into the folder:

```bat
git clone https://github.com/azimkhan07/restaurant-pos-ci4-assessment.git
cd restaurant-pos-ci4-assessment
```

2. Install the dependencies if `vendor` is not there:

```bat
composer install
```

If Composer says `ext-intl` is missing, enable `intl` in `php.ini` first. You can also run:

```bat
php -d extension=intl C:\composer\composer.phar install
```

3. The `.env` file is already included. If it is missing, copy it from the sample:

```bat
copy env .env
```

4. Start the server:

```bat
run.bat
```

`run.bat` uses `D:\xampp\php\php.exe` if `php` is not on PATH, and starts the server with `intl` and `sqlite3` enabled.

5. Open http://localhost:8080 in the browser.

The SQLite database is already created and seeded. If you want to rebuild it:

```bat
php spark migrate --all
php spark db:seed AssessmentSeeder
```

If `php` is not on PATH, use the full path to the XAMPP PHP, for example:

```bat
D:\xampp\php\php.exe -d extension=intl -d extension=sqlite3 spark migrate --all
D:\xampp\php\php.exe -d extension=intl -d extension=sqlite3 spark db:seed AssessmentSeeder
```

## How to run without XAMPP

Make sure PHP and Composer are installed, then run:

```bash
composer install
cp env .env
php spark migrate --all
php spark db:seed AssessmentSeeder
php spark serve
```

If `intl` and `sqlite3` are not enabled globally, start PHP directly with them:

```bash
php -d extension=intl -d extension=sqlite3 -S 127.0.0.1:8080 -t public public/index.php
```

## Using MySQL instead of SQLite

SQLite is the default because it needs no extra setup. If you want to use MySQL:

1. Create a database called `restaurant_pos`.
2. Import `database/mysql_schema.sql`. It creates the tables and inserts the sample data.
3. Copy the database settings from `env.mysql.example` into `.env`.
4. Restart the server.

The MySQL settings look like this:

```ini
database.default.hostname = localhost
database.default.database = restaurant_pos
database.default.username = root
database.default.password =
database.default.DBDriver = MySQLi
database.default.port = 3306
```

## URLs

- http://localhost:8080 - home page
- http://localhost:8080/orders - order history with items and payments
- http://localhost:8080/orders/10 - one order in detail
- http://localhost:8080/cart - Task 3 cart
- http://localhost:8080/api/orders - Task 2 API
- http://localhost:8080/api/data-quality - Task 1 findings as JSON

## API endpoints

All API responses are JSON.

- `GET /api/health` - simple health check
- `GET /api/orders` - paginated order list with items and payments
- `GET /api/orders/{id}` - one order with full details
- `GET /api/menu` - menu, categories, items and prices
- `GET /api/data-quality` - the main data findings

`/api/orders` accepts these query parameters:

- `page` - default `1`
- `per_page` - default `20`, maximum `100`
- `status` - for example `Completed`
- `search` - order number or status text

Some example requests:

```text
GET /api/orders
GET /api/orders?status=Completed&per_page=5
GET /api/orders/10
GET /api/orders/17
GET /api/menu
GET /api/data-quality
```

The response looks like this:

```json
{
  "status": "success",
  "data": [],
  "meta": {
    "page": 1,
    "per_page": 20,
    "total": 11,
    "total_pages": 1,
    "filters": {
      "status": null,
      "search": null
    }
  }
}
```

Each order includes the order header, all line items with their historical prices, all payments, and the calculated order total, paid amount, refunded amount and balance due.

The Postman collection is here:

```text
postman/Restaurant-POS.postman_collection.json
```

Import it in Postman and run it while the app is running. It includes order 17, which is the order with the payment mismatch, and order 18, which has a split payment and a tip.

## Cart

Open http://localhost:8080/cart.

The cart has five items: £10.00, £7.50, £5.00, £2.50 and £3.00. Clicking an item adds it to the session cart. The `+` and `-` buttons change the quantity and update the line price and totals.

The tax is 12.5% and the prices already include it. The tax amount is calculated like this:

```text
tax = total incl. tax * 12.5 / 112.5
tax = total incl. tax / 9
```

For the sample in the task (Item 1 x1, Item 2 x2, Item 4 x2):

```text
Total incl. tax: £30.00
Tax: £3.33
Net: £26.67
```

The cart uses integer pence for the calculations, so quantity changes do not cause floating-point errors. You can also see the cart state as JSON at `/cart/state`.

## Data findings

The detailed notes are in `docs/DATA_FINDINGS.md`.

The main points I found:

- Order History is order-line level, not one row per order.
- 50 of the 52 order lines have a price that does not match the current menu price. The order line price should be treated as the price charged at that time.
- Order 17 has a small mismatch: the order lines add up to 10.89618 but the payment amount due is 10.8918.
- Order 15 is marked as Completed, but its only payment is marked as Refunded.
- Item 6 is missing its size on order 11, line 5.
- Item 5 belongs to category 2 (Soft Drinks, Drinks menu) but its own `menu_id` is 1 (Food).
- Split payments repeat the same `amount_due` value, so it must not be summed across payment rows.
- Some IDs are missing: order line 40 and payment references 112, 113, 114, 117 and 118.

These findings are also returned by `GET /api/data-quality`, so they can be checked against the actual database.

## Security and performance

A few things I added to keep the API and cart safe:

- CSRF protection on cart POST requests.
- Optional API key. Set `api.key` in `.env` and send it in the `X-API-Key` header.
- Rate limiting on the API: 120 requests per minute per IP.
- Validation for page, per-page, order id, status and search length.
- Query Builder and prepared statements for database queries.
- Escaped output in the views.
- Security headers enabled globally.
- Foreign keys, unique keys and indexes on the order, order item and payment tables.
- The order list uses three queries (orders, items, payments) instead of running a query for every order.
- `per_page` is capped at 100.

## How I used AI

I used AI assistance for some boilerplate, for converting the PDF tables into the seed JSON, and for a first review of the payment and tax calculations. After that I ran the project locally and checked the results myself:

- Ran the migration and seeder.
- Checked `/api/orders`, `/api/orders/10`, `/api/orders/17`, `/api/menu` and `/api/data-quality`.
- Tested the cart with the sample values and checked that the total is £30.00 and tax is £3.33.
- Checked that changing quantity updates the totals.
- Ran `php -l` on the PHP files.

The payment reconciliation and the inclusive tax calculation are the two parts I would be ready to explain in the interview.

## Folder overview

```text
app/Controllers     web and API controllers
app/Database        migration, seeder and PDF data as JSON
app/Filters         API key and rate-limit filters
app/Helpers         money and payment badge helpers
app/Libraries       order report service and cart service
app/Models          one model per table
app/Views           layout, orders and cart pages
database            MySQL schema and sample data
docs                data findings
postman             Postman collection
public/assets       CSS
writable/database   SQLite database
```

## Common problems

- `IntlDateFormatter not found`: enable `extension=intl` in `php.ini`, or use `run.bat`.
- `SQLite3 class not found`: enable `extension=sqlite3`, or use `run.bat`.
- `vendor/autoload.php` missing: run `composer install`.
- Port 8080 already in use: stop the other process or change the port in `run.bat` and `.env`.
- Database locked: close other PHP processes that are using the SQLite file.
- Composer complains about zip/unzip: install 7-Zip or unzip, or run `composer install --prefer-source`.
