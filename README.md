# IBE Foundation Donation Platform

Welcome to the official repository for the **[IBE Foundation](https://ibefoundation.org)** donation platform. It serves as the digital bridge between generous donors and schools, facilitating contributions that support education.

The system delivers a seamless, secure, and transparent experience for donors while equipping administrators with powerful tools to manage operations effectively.

## Technology Stack

Our architecture is built upon a foundation of modern, industry-leading open-source technologies.

-   **[Laravel 12](https://github.com/laravel/framework)**: The robust PHP framework that powers the core application logic.
-   **[Filament 4](https://github.com/filamentphp/filament)**: An elegant administration panel for managing donors, donations, and schools.
-   **[Livewire 3](https://github.com/livewire/livewire)**: Enables dynamic, interactive user interfaces (like our donation wizard) without complex client-side code.
-   **[Inertia.js 2](https://github.com/inertiajs/inertia)**: Provides a modern single-page application experience for specific user flows.
-   **[Tailwind CSS 4](https://github.com/tailwindlabs/tailwindcss)**: A utility-first CSS framework for rapidly building custom user interfaces.
-   **[Pest 4](https://github.com/pestphp/pest)**: An elegant testing framework that ensures our code behaves exactly as expected.
-   **[Larastan](https://github.com/larastan/larastan)**: Adds static analysis to our codebase, catching potential errors before they ever run.
-   **[Spatie Laravel Data](https://github.com/spatie/laravel-data)**: Ensures data flowing through our system is structured and typed correctly.
-   **[Resend](https://github.com/resend/resend-php)**: A modern email API for transactional emails.

## Key Features

### For Donors
-   **Intuitive Donation Wizard:** A step-by-step process that makes giving easy, supporting various payment methods and recurring options.
-   **Secure Processing:** Integrated with industry-standard payment gateways to ensure financial data is handled with the highest security.
-   **Instant Receipts:** Automated PDF receipt generation for tax purposes.
    -   **Personal Dashboard:** A dedicated space for donors to view their complete donation history and manage their account.

### For the IBE Foundation Team
-   **Comprehensive Dashboard:** A centralized view of all foundation activities, including real-time donation tracking.
-   **Donor Management:** Tools to view donor history, manage profiles, and assist with inquiries.
-   **School Management:** Easy administration of participating schools and their details.
-   **Manual Entry:** The ability to manually record donations received via check or other direct methods.

## Engineering & Architecture

We have adopted a **Domain-Driven Design (DDD)** inspired approach to ensure the codebase remains maintainable and reliable as the platform grows.

### Action-Oriented Architecture
Instead of burying business logic in controllers, we encapsulate every distinct task into dedicated **Action** classes (e.g., `ProcessDonation`, `CreateUserForDonor`). This makes our code:
-   **Testable:** We can verify each action works in isolation.
-   **Reusable:** The same logic can be used from the web, API, or command line.
-   **Readable:** It is immediately clear what the application *does*.

### Quality Assurance
We prioritize stability through rigorous quality checks:
-   **Strict Typing:** We enforce strict data types across the application to prevent data-related bugs.
-   **Architecture Tests:** We use automated tests to enforce our coding standards (e.g., ensuring controllers never bypass business logic).
-   **Continuous Analysis:** Every change is analyzed by automated tools to detect issues before they reach production.

## Getting Started

To set up the project locally for development:

1.  **Install Dependencies:**
    ```bash
    composer install
    npm install
    ```

2.  **Setup Environment:**
    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

3.  **Initialize Database:**
    ```bash
    php artisan migrate --seed
    ```
