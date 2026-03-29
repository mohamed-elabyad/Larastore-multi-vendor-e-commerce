# LaraStore

A multi-vendor e-commerce marketplace built with Laravel 12 where multiple sellers can manage their own products and storefronts while customers browse, shop, and pay — all through a single unified platform.

## Key Features

**Multi-Vendor Marketplace**
- Vendors register, set up store profiles (name, address, cover image), and manage their product listings independently.
- Each vendor gets a public storefront accessible via a unique URL (`/s/{store_name}`).
- Admin-controlled vendor approval workflow with status tracking (Pending → Approved / Rejected).

**Products & Catalog**
- Organized by departments and categories with nested relationships.
- Support for product variations (size, color, material, etc.) rendered as select dropdowns, radio buttons, or image swatches.
- Each variation combination can have its own price and stock quantity.
- Media gallery management with image uploads and automatic thumbnail generation via Spatie Media Library.
- SEO meta tags (title, description) and Open Graph tags for social sharing.
- Product statuses: Draft and Published.

**Smart Cart System**
- Guest users can add products to the cart — items are stored in browser cookies and persist across sessions.
- On login, all cookie-based cart items are automatically transferred to the database and cookies are cleared.
- Authenticated users have their cart stored in the database for cross-device access.
- Real-time cart updates with AJAX support and stock validation.
- Cart items are grouped by vendor for a clear checkout experience.
- Users can checkout products from a specific vendor or all cart items across all vendors at once.

**Payments & Payouts**
- Stripe Checkout integration for secure payment processing.
- Stripe Connect (Express) allows vendors to onboard their own Stripe accounts directly from their profile.
- Orders are created per-vendor with line items, then processed through a single Stripe Checkout Session.
- Webhook listeners handle asynchronous payment confirmations and order status updates.
- Payout system for transferring earnings to approved vendors with active Stripe accounts.

**Order Management**
- Full order lifecycle: Draft → Paid → Shipped → Delivered → Cancelled.
- Orders are linked to both the customer and the vendor for dual-side tracking.
- Each order item records the product, selected variation options, quantity, and price at time of purchase.

**Admin Dashboard**
- Powered by Filament 4, the admin panel at `/admin` provides full control over the marketplace:
  - **Products** — Create, edit, and manage products with images, variations, and SEO fields.
  - **Categories & Departments** — Organize the product catalog with parent-child relationships.
  - **Users** — Manage accounts and assign roles.
  - **Vendors** — Review applications, approve or reject vendors, and monitor store details.
  - **Analytics Widgets** — Revenue chart, orders chart, order status breakdown, top-selling products, products added over time, and a stats overview panel.

**Vendor Dashboard**
- Vendors manage their store from the profile page using a Livewire-powered interface.
- Update store name, address, and cover image in real-time.
- Connect a Stripe account to start receiving payouts.
- New users can apply to become a vendor through an in-app onboarding flow.
- Approved vendors also access the Filament panel at `/admin` where all analytics widgets are automatically scoped to show only their own data:
  - **Stats Overview** — My Orders this year, My Products this year, and My Revenue this year (vendor subtotal), each with month-over-month trend indicators.
  - **Revenue Chart** — Monthly revenue chart showing the vendor's own `vendor_subtotal` earnings over the year.
  - **Orders Chart** — Monthly order volume chart filtered to the vendor's own orders.
  - **Order Status Chart** — Breakdown of the vendor's own orders by status (Paid, Shipped, Delivered, etc.).
  - **Top Selling Products** — The vendor's own best-performing products ranked by sales.
  - **Products Added Chart** — Catalog growth chart showing products added by that vendor over time.

**Authentication & Authorization**
- Built on Laravel Breeze with email verification support.
- Three roles managed via Spatie Laravel Permission: **Admin**, **Vendor**, and **User**.
- Role-based route protection ensures vendors and admins access only what they should.

## Tech Stack

| Layer           | Technology                              |
|-----------------|-----------------------------------------|
| Framework       | Laravel 12                              |
| Admin Panel     | Filament 4                              |
| Frontend        | Blade Templates, Livewire 3, Alpine.js  |
| Styling         | Tailwind CSS 3                          |
| Build Tool      | Vite 7                                  |
| Payments        | Stripe Checkout + Stripe Connect        |
| Media Handling  | Spatie Laravel Media Library             |
| Authorization   | Spatie Laravel Permission                |
| Database        | MySQL                                   |
| Queue           | Database driver (configurable)          |

## Admin Dashboard

Access the admin panel at `/admin`. Built with Filament 4, the dashboard includes these resource managers and analytics:

| Resource     | Features                                                        |
|--------------|-----------------------------------------------------------------|
| Products     | CRUD, media gallery, variation types, variation combinations    |
| Categories   | CRUD with department association                                |
| Departments  | CRUD with nested categories relation manager                    |
| Users        | Account management, role assignment, associated products        |
| Vendors      | Application review, status management, store details            |

**Dashboard Widgets:**
- Stats Overview — Key marketplace metrics at a glance.
- Revenue Chart — Income trends over time.
- Orders Chart — Order volume tracking.
- Order Status Chart — Breakdown by status (Paid, Shipped, Delivered, etc.).
- Top Selling Products — Best performers by sales.
- Products Added Chart — Catalog growth over time.

## Payment Flow

```
Customer adds items to cart
        │
        ▼
Proceeds to checkout (authentication required)
        │
        ▼
Orders created per vendor (status: Draft)
        │
        ▼
Stripe Checkout Session created with all line items
        │
        ▼
Customer completes payment on Stripe
        │
        ▼
Webhook confirms payment → Orders updated to Paid
        │
        ▼
Vendor payouts processed via Stripe Connect
```

## Requirements

- PHP 8.2+
- Composer 2.x
- Node.js 18+ & npm
- MySQL 8.0+
- Stripe account with API keys

## Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/your-username/larastore.git
   cd larastore
   ```

2. **Install dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Configure environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   Update `.env` with your database credentials and Stripe keys:
   ```env
   DB_DATABASE=e_commerce
   DB_USERNAME=root
   DB_PASSWORD=

   STRIPE_KEY=your_stripe_publishable_key
   STRIPE_SECRET=your_stripe_secret_key
   STRIPE_WEBHOOK_SECRET=your_webhook_secret
   ```

4. **Run migrations**
   ```bash
   php artisan migrate --seed
   ```

5. **Build frontend assets**
   ```bash
   npm run build
   ```

6. **Start the application**
   ```bash
   composer dev
   ```
   This runs the Laravel server, queue worker, and Vite dev server concurrently.

## Project Structure

```
app/
├── Enums/              # OrderStatus, ProductStatus, VendorStatus, Roles, VariationTypes
├── Filament/
│   ├── Resources/      # Admin CRUD (Products, Categories, Departments, Users, Vendors)
│   └── Widgets/        # Dashboard analytics (Revenue, Orders, Stats, Top Products)
├── Http/Controllers/   # Product, Cart, Stripe, StripeConnect, Vendor, Profile
├── Livewire/           # CartPopup, VendorDetails
├── Models/             # Product, Category, Department, Order, Cart, Vendor, User, etc.
├── Services/           # CartService, StripeConnectService
└── Exceptions/         # Custom exceptions (PaymentFailed, OutOfStock, QuantityExceeded, VendorNotApproved)

resources/views/
├── components/         # Reusable Blade components
├── layouts/            # Application layouts
├── products/           # Product listing and detail pages
├── vendor/             # Vendor store profile pages
├── Cart/               # Shopping cart views
├── Stripe/             # Checkout and payment status pages
└── livewire/           # Livewire component views
```

## Email Notifications

Two queued emails are dispatched automatically after a successful payment:

### Customer — Checkout Completed (`CheckoutCompletedMail`)
Sent to the **buyer** after payment is confirmed. One email covers all vendors in a single checkout, and for each order it includes:
- Seller name with a link to their storefront.
- Order ID, number of items, and order total.
- An itemised table with product thumbnail, product name, quantity, and unit price.
- A "View Website" button and a thank-you note.

### Vendor — New Order (`NewOrderMail`)
Sent to the **vendor** every time one of their products is purchased. The email contains:
- Order ID and order date.
- Order total (full amount paid by customer).
- Platform fee (website commission deducted).
- Vendor's net earnings (`vendor_subtotal` after commission).
- An itemised table with product thumbnail, product name, quantity, and unit price.
- A "View Website" button.

Both mail classes implement `ShouldQueue`, so they are processed asynchronously through the queue worker without blocking the HTTP request.

## Exception Handling

All domain-level errors are handled through a custom exception hierarchy rooted at `AppException`:

```
AppException
├── CartQuantityExceededException   — thrown when the requested quantity exceeds available stock in the cart
├── ProductOutOfStockException      — thrown when a product has no remaining stock
├── PaymentFailedException          — thrown when a Stripe payment or webhook processing fails
└── VendorNotApprovedException      — thrown when a user tries to perform a vendor action before being approved
```

`AppException` defines a `render()` method that redirects the user back to the previous page and flashes the exception message as an `error` session variable. All four subclasses extend it without adding extra logic, so they all behave the same way — any caught domain exception automatically surfaces as a user-friendly flash error without exposing stack traces.

### License

This project is open-source software licensed under the MIT license.

---

**Built with Laravel, Filament, and modern web technologies to deliver a seamless healthcare booking experience.**

### Author

Developed by **Mohamed Elabyad**

📧 [m.elabyad.work@gmail.com](mailto:m.elabyad.work@gmail.com)

Feel free to reach out for questions, feedback, or collaboration opportunities!
