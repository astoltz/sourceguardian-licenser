# SourceGuardian Licenser

A robust, web-based license management system for SourceGuardian-protected PHP applications. This application allows you to manage projects, versions, variations, customers, and generate license files via a modern web interface or a RESTful API.

## Features

*   **Project Management**: Organize your software into projects.
*   **Versioning**: Manage different versions of your software.
*   **Variations**: Handle different editions (e.g., Standard, Pro, Enterprise).
*   **Customer Management**: Track your customers and their licenses.
*   **License Generation**: Generate `.lic` files using the SourceGuardian `licgen` tool.
*   **Flexible Restrictions**: Bind licenses to IP addresses, domains, MAC addresses, and machine IDs.
*   **Custom Constants**: Define custom constants and header texts at any level (Project, Version, Variation, Customer, License).
*   **REST API**: Full API support for integration with other systems.
*   **User Management**: Manage administrator access.

## Requirements

*   PHP 8.2 or higher
*   Composer
*   Node.js and NPM
*   SourceGuardian `licgen` binary (for license generation)
*   Database (SQLite, MySQL, PostgreSQL, etc.)

## Installation

1.  **Clone the repository:**
    ```bash
    git clone <repository-url>
    cd sourceguardian-licenser
    ```

2.  **Install PHP dependencies:**
    ```bash
    composer install
    ```

3.  **Install Frontend dependencies:**
    ```bash
    npm install
    ```

4.  **Environment Setup:**
    Copy the example environment file and generate the application key:
    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

5.  **Configure Database:**
    Update the `DB_*` variables in your `.env` file to point to your database. For SQLite (default), you can create the file:
    ```bash
    touch database/database.sqlite
    ```

6.  **Configure Licgen:**
    Set the path to your SourceGuardian `licgen` executable in the `.env` file. This is crucial for license generation.
    ```env
    LICGEN_PATH="/path/to/your/licgen"
    ```
    If `licgen` is in your system PATH, you can set it to just `licgen`.

7.  **Run Migrations and Seeders:**
    This will create the database tables and populate them with sample data (including a default admin user).
    ```bash
    php artisan migrate:fresh --seed
    ```

8.  **Build Assets:**
    ```bash
    npm run build
    ```

## Usage

### Development Server

To start the local development server:

```bash
php artisan serve
```

You can access the application at `http://127.0.0.1:8000`.

**Default Login:**
*   **Email:** `admin@example.com`
*   **Password:** `secret`

### License Generation

1.  Navigate to the **Licenses** section.
2.  Click **Create License**.
3.  Select the Project, Version, Variation, and Customer.
4.  Set any restrictions (Expiration, IP, Domain, etc.).
5.  Click **Create**.
6.  On the license details page, click **Generate** to create the `.lic` file.
7.  Click **Download** to get the file.

### API

The application provides a full REST API under `/api/v1`.

*   **Projects**: `GET /api/v1/projects`
*   **Customers**: `GET /api/v1/customers`
*   **Licenses**: `GET /api/v1/licenses`
*   **Download License**: `GET /api/v1/licenses/{id}/download`

## Production Deployment

For production environments, ensure you optimize the application:

```bash
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache
npm run build
```

Ensure the `storage` and `bootstrap/cache` directories are writable by the web server.
