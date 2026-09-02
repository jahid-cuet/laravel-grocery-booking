# Grocery Booking System — Laravel Backend Track

> **PARAMETER-X Limited · Take-Home Assessment**  
> **Role:** Full Stack Engineer (PHP/Laravel)  
> **Stack:** PHP 8.4 · Laravel 13 · MySQL · JWT Auth · Blade + AJAX · Docker · Pest PHP

A complete and robust **Grocery Booking System** where an **Admin** manages inventory/catalogue and **Users** register, browse products, and place multi-item bookings with **atomic, concurrency-safe stock deduction**.

---

## 🎬 Application Demo & Video Walkthrough

> **Interactive Walkthrough**: Real-time grocery browsing, AJAX live stock checking, slide-over cart drawer, and atomic booking checkout flow.


https://github.com/user-attachments/assets/a4ca1f4a-e40a-4d77-b036-71f84d0bda68




## Table of Contents
- [Demo & Video Walkthrough](#-application-demo--video-walkthrough)
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
   - Catalogue and order persistence are accessed through repository contracts; the order service intentionally uses a pessimistic Eloquent row lock inside its transaction to protect stock during checkout.

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
- Browse available in-stock items with pagination and instant search — **no login required**.
- Perform **Live Stock Checks** over AJAX without reloading the page — **no login required**.
- Book multiple grocery items in a single atomic order with delivery notes — **requires authentication** (`auth:api`).
- View personalized order history with real-time status and line-item summaries — **requires authentication** (`auth:api`).

> **Authentication Policy**: Catalogue browsing (`GET /api/groceries`) and Live Stock Checks (`GET /api/groceries/{id}`) are fully public. Placing orders (`POST /api/orders`) and viewing order history (`GET /api/orders`) strictly require a valid JWT bearer token. Unauthenticated order requests return `401 Unauthorized`.

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

Base URL for local testing:

```text
http://127.0.0.1:8000
```

The catalogue GET endpoints are public. Authentication, order, and admin endpoints require a JWT in this header:

```text
Accept: application/json
Authorization: Bearer YOUR_JWT_TOKEN
```

Recommended testing order: register or login → copy the returned JWT → call `/api/auth/me` → test orders or admin endpoints. A normal public registration can never choose the `admin` role.

Common response codes: `200` successful request, `201` resource created, `401` missing/invalid token, `403` insufficient role, `404` resource not found, and `422` validation error.

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

## Quick cURL Testing Guide

Reviewers can test all core API features immediately using these step-by-step cURL commands:

### 1. Admin Workflow (Add, View, Update, Stock Management, Delete):
```bash
# Optional: create a normal customer account (role is assigned automatically)
curl -s -X POST http://127.0.0.1:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"name":"New User", "email":"new@example.com", "password":"secret123"}'

# 1. Login as Admin & extract JWT Token
TOKEN=$(curl -s -X POST http://127.0.0.1:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"email": "admin@grocery.com", "password": "password123"}' | grep -o '"token":"[^"]*' | cut -d'"' -f4)

# 2. Add a new grocery item (POST /api/admin/groceries)
curl -X POST http://127.0.0.1:8000/api/admin/groceries \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"name": "Organic Honey 500g", "price": 8.99, "stock_quantity": 40, "is_active": true}'

# 3. View catalogue & search (GET /api/admin/groceries?search=Honey)
curl -s -X GET "http://127.0.0.1:8000/api/admin/groceries?search=Honey" \
  -H "Authorization: Bearer $TOKEN"

# 4. Manage inventory stock (PATCH /api/admin/groceries/1/inventory)
curl -X PATCH http://127.0.0.1:8000/api/admin/groceries/1/inventory \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"quantity": 100, "operation": "set"}'

# 5. Remove grocery item (DELETE /api/admin/groceries/1)
curl -X DELETE http://127.0.0.1:8000/api/admin/groceries/1 \
  -H "Authorization: Bearer $TOKEN"
```

### 2. User / Customer Workflow (Browse, Atomic Booking, Order History):
```bash
# 1. Login as Customer & extract JWT Token
USER_TOKEN=$(curl -s -X POST http://127.0.0.1:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email": "user@grocery.com", "password": "password123"}' | grep -o '"token":"[^"]*' | cut -d'"' -f4)

# 2. Browse available in-stock items (GET /api/groceries)
curl -s -X GET http://127.0.0.1:8000/api/groceries

# 3. Book multiple items with atomic safe stock deduction (POST /api/orders)
curl -X POST http://127.0.0.1:8000/api/orders \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $USER_TOKEN" \
  -d '{
    "items": [
      { "grocery_item_id": 2, "quantity": 2 },
      { "grocery_item_id": 3, "quantity": 1 }
    ],
    "notes": "Please deliver fresh items."
  }'

# 4. View user order history (GET /api/orders)
curl -s -X GET http://127.0.0.1:8000/api/orders \
  -H "Authorization: Bearer $USER_TOKEN"
```

---

## Frontend (Blade + AJAX & Localization)

### 1. Interactive Blade Storefront:
- **Customer Registration**: `/register` provides a validated Blade registration form and automatically signs in the new customer.
- **Product Browsing**: Clean product grid with price tags, categories, and dynamic stock badges (`In Stock`, `Low Stock`, `Out of Stock`) — publicly accessible.
- **AJAX Live Stock Check**: Section 5 interaction — clicking *"Live Stock Check"* queries `/api/groceries/{id}` and updates stock status without full page reload — publicly accessible.
- **AJAX Cart Drawer**: Slide-over cart for adding, updating, and removing line items dynamically.
- **AJAX Checkout**: Order submission requires an authenticated session. If a guest tries to check out, the UI immediately redirects them to `/login` before any API call is made.

### 2. Localization Support (Bonus):
- English (`en`) and Bangla (`bn`) translations for the customer-facing storefront and shared layout (`lang/en/messages.php`, `lang/bn/messages.php`).
- Language toggle in the navigation bar switching locale instantly with session persistence.

### 3. Web Authentication & 1-Click Demo Login (Bonus UX)

- **Core Requirement Alignment**: The assessment specifically mandates **JWT-based API authentication** (`/api/auth/register`, `/api/auth/login`, etc.) for client consumers.
- **Why Blade Auth Exists**: To provide an interactive and complete browser evaluation experience, dedicated Blade `/login` and `/register` views were added.
- **1-Click Evaluator Login**: The login page includes 1-click demo login buttons for **Admin Portal** (`admin@grocery.com`) and **Customer Store** (`user@grocery.com`) to allow reviewers to immediately explore role-protected features in the browser without manual typing.
- **Unified Logic**: Both API and Blade authentication share the same core `AuthService` so business logic, role assignments, and password validations remain strictly consistent across all entry points.

---

## Automated Testing

The Pest test suite covers API and Blade authentication, rejection of privileged registration, role middleware, repository bindings, inventory CRUD, order booking, and localized web views. Run the suite in a configured MySQL environment to see the current test and assertion totals.

```bash
# Run complete test suite
php artisan test

# Run code style formatting (Laravel Pint)
vendor/bin/pint
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
- [x] **Bonus — Localization**: English/Bangla translations for the customer-facing storefront with an instant session-based switcher.
- [x] **Automated Tests**: Pest feature and unit tests are included; run `php artisan test` to verify the configured environment.
- [x] **Documentation**: Full setup guide, API endpoint table, and architecture notes.
