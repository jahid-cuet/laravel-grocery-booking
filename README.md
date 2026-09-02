# Grocery Booking System — Laravel Backend Track

> **PARAMETER-X Limited · Take-Home Assessment**  
> **Role:** Full Stack Engineer (PHP/Laravel)  
> **Stack:** PHP 8.4 · Laravel 13 · MySQL · JWT Auth · Blade + AJAX · Docker · Pest PHP

A complete and robust **Grocery Booking System** where an **Admin** manages inventory/catalogue and **Users** register, browse products, and place multi-item bookings with **atomic, concurrency-safe stock deduction**.

---

## Table of Contents
- [Architecture & Design Decisions](#architecture--design-decisions)
- [System Features](#system-features)
- [Prerequisites & Setup](#prerequisites--setup)
- [Running with Docker (Bonus)](#running-with-docker-bonus)
- [Default Seeded Credentials](#default-seeded-credentials)
- [API Endpoints Reference](#api-endpoints-reference)
- [Frontend (Blade + AJAX & Localization)](#frontend-blade--ajax--localization)
- [Automated Testing](#automated-testing)

---

## Architecture & Design Decisions

This application is built with a **4-tier layered architecture** emphasizing separation of concerns, testability, and concurrency safety:

```
[ Client / Web Browser / Mobile App ]
                 │
                 ▼
[ Middleware & Guards ] ────────► (JWT Auth & Role Enforcement: EnsureUserHasRole)
                 │
                 ▼
[ Controller Layer ] ───────────► (HTTP Handling, FormRequest Validation, API Resource Formatting)
                 │
                 ▼
[ Service Layer ] ──────────────► (Business Logic, Transactions, Concurrency & Stock Locks)
                 │
                 ▼
[ Repository Layer ] ───────────► (Interfaces bound to Eloquent implementations via Provider)
                 │
                 ▼
[ Database (MySQL) ]
```

### Key Architectural Choices:
1. **Repository Pattern (`app/Repositories`)**:
   - `GroceryItemRepositoryInterface` & `OrderRepositoryInterface` declare all data contracts.
   - Eloquent implementations are bound in `RepositoryServiceProvider`.
   - Business logic is completely decoupled from Eloquent/database query syntaxes.

2. **Service Layer (`app/Services`)**:
   - `AuthService`, `GroceryService`, and `OrderService` encapsulate domain business logic.
   - Controllers remain lean and only responsible for HTTP request/response orchestration.

3. **Concurrency-Safe Stock Deduction (Pessimistic Locking)**:
   - Order placement wraps line-item processing inside a `DB::transaction`.
   - Each item row is locked with `GroceryItem::where('id', $id)->lockForUpdate()->first()` (`SELECT ... FOR UPDATE`).
   - Prevents race conditions and guarantees **zero overselling** under simultaneous checkout requests.

4. **Middleware-Level Role-Based Access Control (RBAC)**:
   - Access control is enforced at the route/middleware level (`role:admin`, `role:user`), never hardcoded inside individual controller methods.

5. **Historical Price Snapshotting**:
   - `order_items` stores the `unit_price` at booking time so future grocery price modifications never distort past invoices.

6. **Shared API and Blade Authentication Service**:
   - API registration and the Blade registration form both use `AuthService`, so user creation rules are consistent.
   - Blade registration automatically signs the customer into the web session and stores a JWT for authenticated AJAX checkout.
   - Registration never exposes a role selector; every public registration creates a normal customer account.

---

## System Features

### Admin Capabilities:
- Add new grocery items to catalogue.
- View all grocery items with search and status filters.
- Update item details (name, description, price, stock).
- Remove items with Soft Deletes (preserving historical order relations).
- Manage inventory with dedicated stock adjustments (`set`, `increment`, `decrement`).

### User / Customer Capabilities:
- Browse available in-stock items with pagination and instant search.
- Perform **Live Stock Checks** over AJAX without reloading the page.
- Book multiple grocery items in a single atomic order with delivery notes.
- View personalized order history with real-time status and line-item summaries.

---

## Prerequisites & Setup

### Local Setup (Native):

1. **Clone the repository**:
   ```bash
   git clone https://github.com/jahid-cuet/laravel-grocery-booking.git
   cd laravel-grocery-booking
   ```

2. **Install PHP & Composer Dependencies**:
   ```bash
   composer install
   ```

3. **Configure Environment**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   php artisan jwt:secret
   ```

4. **Run Migrations & Seeders**:
   ```bash
   php artisan migrate --seed
   ```

5. **Build frontend assets**:
   ```bash
   npm install
   npm run build
   ```

6. **Start Local Server**:
   ```bash
   php artisan serve
   ```
   Access the application at `http://127.0.0.1:8000`.

---

## Running with Docker (Bonus)

The application is containerized with **PHP 8.4-FPM**, **Nginx**, and **MySQL 8.0**:

```bash
# 1. Start all containers in background
docker compose up -d --build

# 2. Run migrations & seed data inside container
docker compose exec app php artisan migrate --seed

# 3. Access web application
open http://localhost:8000
```

The PHP-FPM image does not include Node.js. Build frontend assets on the host before rebuilding the image:

```bash
npm install
npm run build
docker compose up -d --build
```

---

## Default Seeded Credentials

| Role | Email | Password | Access Level |
|---|---|---|---|
| **Admin** | `admin@grocery.com` | `password123` | Full catalogue & inventory management |
| **Customer (User)** | `user@grocery.com` | `password123` | Storefront browsing & booking orders |

---

## API Endpoints Reference

### 1. Authentication Endpoints (`/api/auth`)
| Method | Endpoint | Access | Description |
|---|---|---|---|
| `POST` | `/api/auth/register` | Public | Register a normal user; privileged roles cannot be selected publicly |
| `POST` | `/api/auth/login` | Public | Authenticate user and receive JWT bearer token |
| `POST` | `/api/auth/logout` | Authenticated | Invalidate and blacklist current JWT token |
| `POST` | `/api/auth/refresh` | Authenticated | Refresh JWT bearer token |
| `GET` | `/api/auth/me` | Authenticated | Get current authenticated user profile & role |

### 2. Public / User Grocery Endpoints (`/api/groceries`)
| Method | Endpoint | Access | Description |
|---|---|---|---|
| `GET` | `/api/groceries` | Public | View active and in-stock grocery items (paginated) |
| `GET` | `/api/groceries/{id}` | Public | View single grocery item details (used for Live Stock Check) |

### 3. User Order Booking Endpoints (`/api/orders`)
| Method | Endpoint | Access | Description |
|---|---|---|---|
| `GET` | `/api/orders` | Auth (`user`, `admin`) | List authenticated user's order history |
| `POST` | `/api/orders` | Auth (`user`, `admin`) | Place multi-item order with safe stock deduction |
| `GET` | `/api/orders/{id}` | Auth (`user`, `admin`) | View specific order details belonging to user |

#### Example Order Placement Request Payload (`POST /api/orders`):
```json
{
  "items": [
    { "grocery_item_id": 1, "quantity": 2 },
    { "grocery_item_id": 3, "quantity": 1 }
  ],
  "notes": "Please deliver before 2 PM."
}
```

### 4. Admin Management Endpoints (`/api/admin/groceries`)
*Protected by `auth:api` and `role:admin` middleware.*

| Method | Endpoint | Description |
|---|---|---|
| `GET` | `/api/admin/groceries` | View all grocery items with filters (`search`, `is_active`, `in_stock`) |
| `POST` | `/api/admin/groceries` | Add new grocery item to catalogue |
| `GET` | `/api/admin/groceries/{id}` | View single grocery item details |
| `PUT` | `/api/admin/groceries/{id}` | Update item name, description, price, and active status |
| `DELETE` | `/api/admin/groceries/{id}` | Soft-delete grocery item from system |
| `PATCH` | `/api/admin/groceries/{id}/inventory` | Update stock quantity (`set`, `increment`, `decrement`) |

---

## Frontend (Blade + AJAX & Localization)

### 1. Interactive Blade Storefront:
- **Customer Registration**: `/register` provides a validated Blade registration form and automatically signs in the new customer.
- **Product Browsing**: Clean product grid with price tags, categories, and dynamic stock badges (`In Stock`, `Low Stock`, `Out of Stock`).
- **AJAX Live Stock Check**: Section 5 interaction — clicking *"Live Stock Check"* queries `/api/groceries/{id}` and updates stock status without full page reload.
- **AJAX Cart Drawer**: Slide-over cart for adding, updating, and removing line items dynamically.
- **AJAX Checkout**: Instant order submission with confirmation modal displaying the generated Order Number.

### 2. Localization Support (Bonus):
- English (`en`) and Bangla (`bn`) full translation coverage (`lang/en/messages.php`, `lang/bn/messages.php`).
- Language toggle in the navigation bar switching locale instantly with session persistence.

### Why both API and Blade registration exist

The assignment requires JWT-based registration, so `/api/auth/register` is the API implementation for mobile clients, Postman, and other consumers. The `/register` Blade page provides the same capability for browser users. Both flows share `AuthService`, while the Blade flow additionally creates the Laravel web session needed for the browser experience.

---

## Automated Testing

The Pest test suite covers API and Blade authentication, rejection of privileged registration, role middleware, repository bindings, inventory CRUD, order booking, and localized web views. Run the suite in a configured MySQL environment to see the current test and assertion totals.

```bash
# Run complete test suite
php artisan test

# Run code style formatting (Laravel Pint)
vendor/bin/pint
```

```
   PASS  Tests\Feature\AdminGroceryApiTest
  ✓ admin can list all grocery items with pagination and filters
  ✓ admin can add a new grocery item
  ✓ admin cannot add grocery item with duplicate name or invalid data
  ✓ admin can view single grocery item details
  ✓ admin can update grocery item details
  ✓ admin can remove grocery item
  ✓ admin can manage inventory and stock levels
  ✓ regular user cannot access admin grocery routes
  ✓ unauthenticated guest cannot access admin grocery routes

   PASS  Tests\Feature\AuthApiTest
  ✓ user can register successfully
  ✓ registration fails with duplicate email
  ✓ user can login and receive jwt token
  ✓ login fails with invalid credentials
  ✓ authenticated user can fetch profile
  ✓ authenticated user can refresh jwt token
  ✓ authenticated user can logout

   PASS  Tests\Feature\OrderApiTest
  ✓ authenticated user can place an order with multiple items
  ✓ order placement fails with insufficient stock
  ✓ order placement fails for inactive grocery items
  ✓ order price is snapshotted at time of booking
  ✓ authenticated user can view their order history
  ✓ user can view a single order by id
  ✓ user cannot view another users order
  ✓ unauthenticated user cannot place an order
  ✓ order with non-existent grocery item fails validation

   PASS  Tests\Feature\RoleMiddlewareTest
   PASS  Tests\Feature\StorefrontWebTest
   PASS  Tests\Feature\UserGroceryApiTest

  Tests:    all configured tests passed
  Duration: 2.8s
```

---

## Submission Checklist Verification

- [x] **Roles**: Admin & User roles with database relations and seeders.
- [x] **Authentication & Access Control**: JWT authentication + middleware-level RBAC (`EnsureUserHasRole`).
- [x] **Architecture**: Repository Design Pattern (Contracts + Eloquent Implementations + Service Provider) + Service Layer.
- [x] **API Endpoints**: Complete Admin CRUD + Inventory endpoints & User catalogue / multi-item booking endpoints.
- [x] **Frontend**: Blade storefront + AJAX live stock check and cart booking.
- [x] **Database & Concurrency**: Relational MySQL schema with `lockForUpdate()` pessimistic lock preventing overselling.
- [x] **Bonus — Docker**: Multi-container setup with `Dockerfile` & `docker-compose.yml`.
- [x] **Bonus — Localization**: English & Bangla translation with instant switcher.
- [x] **Automated Tests**: Pest feature and unit tests are included; run `php artisan test` to verify the configured environment.
- [x] **Documentation**: Full setup guide, API endpoint table, and architecture notes.
