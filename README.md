# Hotel Booking API

A REST API for searching hotels, checking room availability and managing bookings, built with **Laravel** and **PostgreSQL**.

This is a learning project I built to practise backend development: designing a database, building REST endpoints, validating input, handling business rules, integrating an external API and writing automated tests.

## Tech Stack

- PHP 8.5 & Laravel 13
- PostgreSQL
- Pest (testing)
- Laravel HTTP Client & Cache
- Git & GitHub

## Features

- List hotels and filter them by city
- View a hotel with all of its rooms
- Check if a room is available for specific dates
- Create bookings with input validation and automatic price calculation
- Prevent double bookings (overlapping dates for the same room)
- Cancel bookings (a booking can only be cancelled once)
- Convert room prices to other currencies using a live exchange rate API, with caching
- Consistent JSON responses, including errors
- Feature tests covering the main scenarios

## Getting Started

### Requirements
- PHP 8.3+
- Composer
- PostgreSQL

### Installation

```bash
git clone https://github.com/EvaGroz/hotel-booking-api.git
cd hotel-booking-api
composer install
cp .env.example .env        # on Windows: copy .env.example .env
php artisan key:generate
```

Create a PostgreSQL database called `hotel_booking` and update `.env`:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=hotel_booking
DB_USERNAME=postgres
DB_PASSWORD=your_password
```

Run the migrations with sample data and start the server:

```bash
php artisan migrate --seed
php artisan serve
```

The API is now available at `http://127.0.0.1:8000/api`.

## API Endpoints

| Method | Endpoint | Description |
|---|---|---|
| GET | `/api/hotels` | List all hotels (optional `?city=Ohrid`) |
| GET | `/api/hotels/{id}` | Show a hotel with its rooms (optional `?currency=USD` for converted prices) |
| GET | `/api/rooms/{id}/availability?check_in=YYYY-MM-DD&check_out=YYYY-MM-DD` | Check room availability |
| POST | `/api/bookings` | Create a booking |
| GET | `/api/bookings/{id}` | Show a booking |
| PATCH | `/api/bookings/{id}/cancel` | Cancel a booking |

### Example: create a booking

**Request** `POST /api/bookings`
```json
{
  "room_id": 2,
  "guest_name": "Jane Doe",
  "guest_email": "jane@example.com",
  "check_in": "2026-12-10",
  "check_out": "2026-12-13"
}
```

**Response** `201 Created`
```json
{
  "data": {
    "id": 2,
    "status": "confirmed",
    "guest_name": "Jane Doe",
    "guest_email": "jane@example.com",
    "check_in": "2026-12-10",
    "check_out": "2026-12-13",
    "nights": 3,
    "total_price": 210,
    "hotel": "Hotel Vardar",
    "room": {
      "id": 2,
      "room_number": "102",
      "type": "double",
      "capacity": 2,
      "price_per_night": 70
    }
  }
}
```

### Example: prices in another currency

**Request** `GET /api/hotels/1?currency=USD`
```json
{
  "data": {
    "id": 1,
    "name": "Hotel Vardar",
    "rooms": [
      {
        "id": 1,
        "room_number": "101",
        "type": "single",
        "capacity": 1,
        "price_per_night": 45,
        "converted_price": {
          "currency": "USD",
          "amount": 49.46
        }
      }
    ]
  }
}
```

### Status codes

| Code | Meaning |
|---|---|
| `200` | Success |
| `201` | Booking created |
| `404` | Resource not found |
| `409` | Room is not available for the selected dates |
| `422` | Validation error or booking already cancelled |
| `503` | Exchange rate service is unavailable |

Ready-to-use example requests are in [`requests.http`](requests.http) (works with the VS Code REST Client extension).

## Business Rules

- Check-in cannot be in the past, and check-out must be after check-in.
- Two bookings overlap when one starts before the other ends. Overlapping confirmed bookings for the same room are rejected with `409`.
- Cancelled bookings do not block the room.
- Total price = number of nights × room price per night.
- All prices are stored in EUR.

## External Integration

When `?currency=` is provided, `CurrencyService` fetches the exchange rate from the [Frankfurter API](https://frankfurter.dev) (European Central Bank rates, no API key needed).

- The logic lives in a separate service class and is injected into the controller.
- Rates are cached for 1 hour to avoid unnecessary API calls.
- If the external service fails or times out, the API returns `503` instead of crashing.
- Only supported currency codes are accepted (for example `USD`, `GBP`, `CHF`).
- Tests use `Http::fake()`, so they never call the real API.

## Running Tests

Create a separate database called `hotel_booking_test` (configured in `phpunit.xml`), then run:

```bash
php artisan test
```

Tests cover listing and filtering hotels, creating bookings, price calculation, double-booking prevention, validation, availability checks, cancellation and currency conversion (including caching and external API failures).

## Project Structure

```
app/
├── Http/
│   ├── Controllers/Api/   # HotelController, RoomController, BookingController
│   ├── Requests/          # StoreBookingRequest (validation)
│   └── Resources/         # JSON response formatting
├── Models/                # Hotel, Room, Booking + relationships
└── Services/              # CurrencyService (external API integration)
database/
├── migrations/            # Database tables
└── seeders/               # Sample data
routes/api.php             # API routes
tests/Feature/             # Pest tests
```

## How I Used AI

I used an AI assistant (Claude) as a mentor that guided me through the project step by step.

- **Guidance:** AI helped me plan the order of the work, discusse the database design and which endpoints the API needs.
- **Learning:** I asked for explanations of Laravel concepts (migrations, Form Requests, API Resources, dependency injection, HTTP fakes) so I understood them before using them.
- **Debugging:** when I hit errors (a PHP syntax error, a web server port conflict on Windows, a seeder that was not running), I used AI to understand the cause and fixed them myself.
- **Testing:** AI helped me by suggesting test scenarios and think of edge cases such as overlapping dates, cancelled bookings and an unavailable external API.

I did the work myself: I set up the environment and the database, built the project step by step, tested every endpoint manually and with automated tests, committed my progress to GitHub along the way, and made sure I understand the concept and how each part works asking AI.

## What I Would Add Next

- Authentication with Laravel Sanctum
- Pagination and more search filters (dates, price, number of guests)
- Docker setup
- CI pipeline with GitHub Actions to run tests on every push

## Author

**Eva Groz**: [GitHub](https://github.com/EvaGroz)