# Quickstart: Migrate Legacy App Logic

**Feature**: `001-migrate-legacy-app`

## Prerequisites
- PHP 8.4+
- Composer
- Node.js & NPM
- MySQL
- Stripe CLI (for webhook testing)

## Installation

1.  **Clone & Install Dependencies**
    ```bash
    git checkout 001-migrate-legacy-app
    composer install
    npm install
    ```

2.  **Environment Setup**
    ```bash
    cp .env.example .env
    php artisan key:generate
    ```
    *Update `.env` with your database credentials, Stripe keys, and Resend key.*

3.  **Database Setup**
    ```bash
    php artisan migrate --seed
    ```

4.  **Build Assets**
    ```bash
    npm run build
    ```

## Running the Application

1.  **Start the Server** (Laravel Valet)
    The site is linked in Valet.
    Visit `http://ibefoundation.test` in your browser.

2.  **Start the Queue Worker** (Required for emails)
    ```bash
    php artisan queue:listen
    ```

3.  **Start Stripe Webhook Listener**
    ```bash
    stripe listen --forward-to http://ibefoundation.test/webhooks/stripe
    ```

## Testing

1.  **Run All Tests**
    ```bash
    php artisan test
    ```

2.  **Run Browser Tests** (Requires Chrome Driver)
    ```bash
    php artisan dusk
    ```
