# Laravel Task Management API

A RESTful API built with **Laravel 12** for managing users, subscription plans, and user subscriptions.

The project provides CRUD APIs for:

* 👤 Users
* 📦 Subscription Plans
* 🔄 Subscriptions

The API is designed using Laravel RESTful API conventions and includes **Laravel Sanctum** for API authentication and **Swagger/OpenAPI** documentation.

---

## 🚀 Tech Stack

* **Laravel 12**
* **PHP 8.2+**
* **MySQL**
* **Laravel Sanctum**
* **Swagger / OpenAPI**
* **L5-Swagger**
* **Swagger-PHP**
* **Eloquent ORM**
* **RESTful API**

---

## 📌 Features

### User Management

* Create user
* View all users
* View single user
* Update user
* Delete user

### Plan Management

* Create subscription plan
* View all plans
* View single plan
* Update plan
* Delete plan

### Subscription Management

* Create subscription
* View all subscriptions
* View single subscription
* Update subscription
* Delete subscription

### API Documentation

Interactive Swagger documentation is available for testing and exploring all API endpoints.

---

# 📁 Project Structure

```text
LaravelTask/
│
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       ├── PlanController.php
│   │       ├── UserController.php
│   │       └── SubscriptionController.php
│   │
│   ├── Models/
│   │   ├── Plan.php
│   │   ├── User.php
│   │   └── Subscription.php
│   │
│   └── ...
│
├── database/
│   ├── migrations/
│   ├── seeders/
│   └── factories/
│
├── routes/
│   ├── api.php
│   └── web.php
│
├── config/
│   └── l5-swagger.php
│
├── resources/
│
├── public/
│
├── LaravelTask.postman_collection.json
├── laravelTask.sql
├── composer.json
├── package.json
└── README.md
```

---

# ⚙️ Installation

## 1. Clone Repository

```bash
git clone https://github.com/KamleshPatel98/LaravelTask.git
```

Move into the project:

```bash
cd LaravelTask
```

---

## 2. Install PHP Dependencies

```bash
composer install
```

---

## 3. Create Environment File

```bash
cp .env.example .env
```

---

## 4. Generate Application Key

```bash
php artisan key:generate
```

---

## 5. Configure Database

Update `.env` according to your MySQL configuration:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_task
DB_USERNAME=root
DB_PASSWORD=
```

---

## 6. Run Migrations

```bash
php artisan migrate
```

If you want to use the included SQL database file, you can import:

```text
laravelTask.sql
```

---

## 7. Install Frontend Dependencies

```bash
npm install
```

Build assets:

```bash
npm run build
```

For development:

```bash
npm run dev
```

---

## 8. Start Laravel Server

```bash
php artisan serve
```

Application will be available at:

```text
http://127.0.0.1:8000
```

---

# 🔗 API Base URL

```text
http://127.0.0.1:8000/api
```

---

# 📚 API Endpoints

The API uses Laravel `apiResource` routes.

```php
Route::apiResource('plans', PlanController::class);

Route::apiResource('users', UserController::class);

Route::apiResource('subscriptions', SubscriptionController::class);
```

---

# 📦 Plans API

## Get All Plans

```http
GET /api/plans
```

Returns a list of all subscription plans.

### Example

```bash
curl --location 'http://127.0.0.1:8000/api/plans'
```

---

## Get Single Plan

```http
GET /api/plans/{id}
```

Example:

```bash
curl --location 'http://127.0.0.1:8000/api/plans/1'
```

---

## Create Plan

```http
POST /api/plans
```

### Example Request

```json
{
    "name": "Premium",
    "price": 999,
    "duration": 30
}
```

---

## Update Plan

```http
PUT /api/plans/{id}
```

Example:

```json
{
    "name": "Premium Plus",
    "price": 1299,
    "duration": 30
}
```

---

## Delete Plan

```http
DELETE /api/plans/{id}
```

Example:

```bash
curl --location --request DELETE \
'http://127.0.0.1:8000/api/plans/1'
```

---

# 👤 Users API

## Get All Users

```http
GET /api/users
```

Example:

```bash
curl --location 'http://127.0.0.1:8000/api/users'
```

---

## Get Single User

```http
GET /api/users/{id}
```

Example:

```bash
curl --location 'http://127.0.0.1:8000/api/users/1'
```

---

## Create User

```http
POST /api/users
```

Example:

```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password"
}
```

---

## Update User

```http
PUT /api/users/{id}
```

Example:

```json
{
    "name": "John Updated",
    "email": "john.updated@example.com"
}
```

---

## Delete User

```http
DELETE /api/users/{id}
```

---

# 🔄 Subscriptions API

## Get All Subscriptions

```http
GET /api/subscriptions
```

---

## Get Single Subscription

```http
GET /api/subscriptions/{id}
```

---

## Create Subscription

```http
POST /api/subscriptions
```

Example:

```json
{
    "user_id": 1,
    "plan_id": 1,
    "start_date": "2026-10-01",
    "end_date": "2026-10-31"
}
```

---

## Update Subscription

```http
PUT /api/subscriptions/{id}
```

Example:

```json
{
    "plan_id": 2,
    "start_date": "2026-10-01",
    "end_date": "2026-11-30"
}
```

---

## Delete Subscription

```http
DELETE /api/subscriptions/{id}
```

---

# 📊 API Resource Summary

| Resource      | GET All              | GET Single                | POST                 | PUT                       | DELETE                    |
| ------------- | -------------------- | ------------------------- | -------------------- | ------------------------- | ------------------------- |
| Plans         | `/api/plans`         | `/api/plans/{id}`         | `/api/plans`         | `/api/plans/{id}`         | `/api/plans/{id}`         |
| Users         | `/api/users`         | `/api/users/{id}`         | `/api/users`         | `/api/users/{id}`         | `/api/users/{id}`         |
| Subscriptions | `/api/subscriptions` | `/api/subscriptions/{id}` | `/api/subscriptions` | `/api/subscriptions/{id}` | `/api/subscriptions/{id}` |

---

# 📖 Swagger API Documentation

This project uses **L5-Swagger** and **Swagger-PHP** to generate interactive OpenAPI documentation.

Generate Swagger documentation using:

```bash
php artisan l5-swagger:generate
```

After starting the application, open:

```text
http://127.0.0.1:8000/api/documentation
```

Swagger UI allows you to:

* View all API endpoints
* View request parameters
* View request bodies
* View API responses
* Test APIs directly from the browser
* Explore Plans, Users and Subscriptions APIs

---

# 🧾 Swagger/OpenAPI Resources

The API documentation should cover the following resources:

```text
User
Plan
Subscription
```

Recommended API tags:

```text
Users
Plans
Subscriptions
```

---

# 🔐 Authentication

Laravel Sanctum is included in the project for API authentication.

For authenticated APIs, send the token using:

```http
Authorization: Bearer YOUR_TOKEN
```

Example:

```http
Accept: application/json
Content-Type: application/json
Authorization: Bearer YOUR_TOKEN
```

---

# 📮 Postman Collection

A Postman collection is included in the repository:

```text
LaravelTask.postman_collection.json
```

Import this file into Postman to test the API endpoints.

The collection can be used for:

* User CRUD
* Plan CRUD
* Subscription CRUD
* API request testing

---

# 🗄️ Database

The project includes database migrations and an SQL database file:

```text
laravelTask.sql
```

Main entities:

```text
users
plans
subscriptions
```

### Relationship

```text
User
 │
 └── hasMany
       │
       ▼
Subscriptions
       │
       └── belongsTo
              │
              ▼
             Plan
```

Conceptually:

```text
User 1 ──────── * Subscription * ──────── 1 Plan
```

---

# 🧪 Testing

Run Laravel tests with:

```bash
php artisan test
```

Or:

```bash
composer test
```

---

# 🛠️ Useful Artisan Commands

Clear application cache:

```bash
php artisan optimize:clear
```

Run migrations:

```bash
php artisan migrate
```

Generate Swagger documentation:

```bash
php artisan l5-swagger:generate
```

Start development server:

```bash
php artisan serve
```

---

# 📌 API Response Format

A typical successful API response can be structured as:

```json
{
    "success": true,
    "message": "Request successful",
    "data": {}
}
```

Validation errors should return appropriate HTTP status codes with error details.

---

# 💡 Development Highlights

This project demonstrates:

* RESTful API development with Laravel
* Resource controllers
* Eloquent models and relationships
* API validation
* Laravel Sanctum authentication
* CRUD operations
* MySQL database integration
* Swagger/OpenAPI API documentation
* Postman API testing
* Laravel migrations and seeders

---

# 👨‍💻 Author

**Kamlesh Patel**

Laravel Developer

GitHub:

https://github.com/KamleshPatel98

LinkedIn:

https://linkedin.com/in/kamlesh-patel-350bbb246

---
