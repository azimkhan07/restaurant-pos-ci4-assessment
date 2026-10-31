# Task 1 - Data understanding and findings

## 1. How the data fits together

The workbook is a restaurant POS export with four related areas:

```
menus 1---* categories 1---* items 1---* item_prices
                                      |
orders 1---* order_items *------------+
   |
   +---* payments
```

| Sheet | What it really contains | Important fields |
|---|---|---|
| Menu | Menu -> category -> item -> price | `menu_id`, `cat_id`, `item_id`, `size`, `price` |
| Order History | **Order line level** data, not one row per order | line `ID`, `Order ID`, `Item ID`, `Size`, `Price`, `Qty`, `Total` |
| Payments | One row per payment attempt / split payment | `Payment ID`, `Order ID`, `Amount Due`, `Tips`, `Discount`, `Total Paid`, `Payment Status` |

The main modelling point is that **Order History is an order-lines table**. The order header must be derived by grouping rows by `Order ID`; `Total` on each row is `Price x Qty`.

## 2. Orders and payments reconcile, with one exception

I recalculated every order from its lines and compared it with the payment `Amount Due`:

| Order | Calculated line total | Payment amount due | Difference |
|---|---:|---:|---:|
| 10 | 9.25000 | 9.25000 | 0 |
| 11 | 21.25000 | 21.25000 | 0 |
| 12 | 17.00000 | 17.00000 | 0 |
| 13 | 15.50000 | 15.50000 | 0 |
| 14 | 42.81930 | 42.81930 | 0 |
| 15 | 5.13600 | 5.13600 | 0 |
| 16 | 19.75800 | 19.75800 | 0 |
| **17** | **10.89618** | **10.89180** | **0.00438** |
| 18 | 26.33588 | 26.33588 | 0 |
| 19 | 72.13188 | 72.13188 | 0 |
| 20 | 52.25730 | 52.25730 | 0 |

Order 17 is the only genuine reconciliation break. This is exposed by `GET /api/data-quality`.

## 3. Split payments and the "Amount Due" trap

Six orders have more than one payment row: 11, 14, 16, 18, 19 and 20.

`payments.amount_due` is the **order-level amount due, repeated on every split row**. Summing it would double count the order. The correct approach is:

- order value = sum of `order_items.line_total`
- paid = sum of `payments.total_paid` where status is `Completed`
- refunded = sum of `payments.total_paid` where status is `Refunded`
- net paid = paid - refunded
- expected amount = order value + tips - discount
- balance due = max(0, expected amount - net paid)

The API follows this rule.

Examples:

- Order 11: £21.25 = Cash £10.00 + Card £11.25.
- Order 18: £26.33588 + £2.00 tip = £28.33588, paid as £25.00 cash + £3.34 card.
- Order 12: amount due £17.00 + tips £3.00 - discount £4.00 = £16.00 paid.
- Order 13: £15.50 - £2.00 discount = £13.50 paid.

## 4. Historical prices do not match the current menu

The order lines contain the price charged at the time of sale, while the Menu sheet contains the current menu price. They are not the same:

- Item 1 Small is £1.50 on the menu, but order lines contain £3.75, £2.75, £2.50, £2.5698, etc.
- Item 6 Large is £3.60 on the menu, but order lines contain £1.75, £5.50, £6.586, £1.256, etc.
- Item 8 Large is £6.50 on the menu, but order lines contain £2.75 and £5.23569.

**50 of the 52 order lines have a unit price that differs from the current menu price.** This is expected in a POS if prices change over time, but it means the order line price must be treated as a historical snapshot. Never recalculate an old order from today's menu price.

## 5. Variant-size data is incomplete

Items 1, 6 and 8 have `Small` and `Large` variants. However:

- Order 11, line 5 is Item 6 but the `Size` field is blank.
- Most other Item 6 lines correctly say `Large` or `Small`.

This is a data-quality issue: for a variant item, size should be mandatory. The API reports this as `variant_items_missing_size`.

## 6. Refunded payment on a "Completed" order

Order 15 has one payment of £5.14 with status **Refunded**, but the order status is **Completed**.

That is contradictory: either the order should be marked refunded, or the payment status is wrong. I keep both facts in the response and show the order as `payment_status: Refunded` with the balance due outstanding. The inconsistency is reported by `/api/data-quality`.

## 7. Missing IDs

- `order_items` line ID **40** is missing (the sequence jumps from 39 to 41).
- Payment transaction references **112, 113, 114, 117 and 118** are missing (the sequence jumps 111 -> 115 -> 116 -> 119).
- The payments table's own IDs are 1-17, while the external payment references are 100-121; these are different identifiers.

Missing IDs are not automatically errors, but they often indicate deleted/voided records and should be investigated.

## 8. Money precision is inconsistent

- Menu prices use 2 decimals.
- Order lines use 2 to 5 decimals (for example `2.75655`, `5.36245`).
- Some payments are rounded to 2 decimals (`5.14`, `10.90`, `72.13`), while order totals retain 5 decimals.

This creates small rounding differences:

| Order | Order total | Paid | Difference |
|---|---:|---:|---:|
| 15 | 5.13600 | 5.14000 | +0.00400 |
| 17 | 10.89618 | 10.90000 | +0.00382 |
| 18 | 28.33588 (incl. tip) | 28.34000 | +0.00412 |
| 19 | 72.13188 | 72.13000 | -0.00188 |
| 20 | 52.25730 | 52.28000 | +0.02270 |

Recommendation: store money in integer minor units (pence) or use a fixed `DECIMAL(12,2)` for presentation, and keep the calculation precision in the database.

## 9. Category/menu inconsistency

Item 5 is assigned to `cat_id = 2` (Soft Drinks, which belongs to `menu_id = 2`, Drinks) but its own `menu_id` is `1` (Food).

This is a denormalised-data conflict. The menu tree should be derived from the category, not duplicated on the item, or the item row should be corrected. The API menu endpoint follows the category -> menu relationship, so Item 5 appears under Drinks > Soft Drinks.

## 10. No tax data in the historical export

The Order History and Payments sheets contain no tax field. Task 3 explicitly adds a **12.5% inclusive** tax requirement. For inclusive tax:

```
tax = total incl. tax x 12.5 / 112.5
    = total incl. tax / 9
```

For the sample total of £30.00:

- tax = £3.33
- net = £26.67
- total incl. tax = £30.00

The cart calculates in integer pence and rounds the tax to the nearest penny, so quantity changes do not introduce floating-point drift.

## 11. Other observations

- All 11 orders are `Completed`; all order lines are `Completed`.
- 16 payments are `Completed`; 1 payment is `Refunded`.
- Payment dates can be after the order date (for example order 12 ordered 01 Oct, paid 02 Oct; order 13 ordered 01 Oct, paid 03 Oct), which is normal for post-payment.
- The dataset has no customer, table, staff, shift or store dimension, so per-customer or per-branch reporting is not possible from this export alone.
- Currency is assumed to be GBP because of the `£` symbol in the task and the price format.
- `order_items.status` duplicates `orders.status`; keeping it is useful for historical line status, but it must be kept in sync.

## 12. What I would change in production

1. Store order-line prices as historical snapshots and never recalculate from the menu.
2. Store money as integer minor units or fixed decimal; avoid floats.
3. Add a `tax_amount` / `tax_rate` column to orders and payments if tax reporting is required.
4. Make `size` mandatory for variant items with a database check/validation.
5. Add a proper payment allocation model if one payment can cover multiple orders, or if one order can have refunds/partial refunds.
6. Add foreign keys and unique constraints (already added in the assessment schema).
7. Reconcile order 17 and decide the correct handling for order 15's refund.
8. Add indexes on `order_items.order_id`, `payments.order_id`, `payments.status` and `orders.order_date` (already added).
