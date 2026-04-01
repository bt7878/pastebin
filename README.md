# Secure Pastebin

A secure, minimalist Pastebin clone built with PHP 8.5, PostgreSQL, and Nginx. It features end-to-server-side
encryption, ensuring that the server cannot read your pastes without the unique token generated for each one.

## Features

- **Double Encryption**: Pastes are encrypted with a unique per-paste key before storage.
- **Secure Tokens**: The decryption key and paste ID are stored in a Base64URL-encoded token, which is itself encrypted
  and HMAC-signed using system-wide keys to prevent tampering.
- **Minimalist Interface**: Simple web interface for creating and viewing pastes.
- **Dockerized**: Ready to run with Docker and Docker Compose.
- **Modern PHP**: Built using PHP 8.5+, PSR-4 autoloading, and Dependency Injection.

## Technologies Used

- **PHP 8.5+**
- **PostgreSQL 18**
- **Nginx**
- **[PHP-DI](https://php-di.org/)** for Dependency Injection.
- **[FastRoute](https://github.com/nikic/FastRoute)** for routing.
- **[vlucas/phpdotenv](https://github.com/vlucas/phpdotenv)** for environment management.
- **[PHP CS Fixer](https://github.com/PHP-CS-Fixer/PHP-CS-Fixer)** for code style.

## Prerequisites

- [Docker](https://www.docker.com/) and [Docker Compose](https://docs.docker.com/compose/)
- Alternatively, if running locally without Docker:
    - PHP 8.5+ with `openssl` and `pdo_pgsql` extensions.
    - PostgreSQL 18.
    - Composer.

## Installation and Setup

### 1. Clone the repository

```bash
git clone https://github.com/bt7878/pastebin.git
cd pastebin
```

### 2. Configure Environment Variables

Copy the `.env.example` to `.env` and fill in the required values.

```bash
cp .env.example .env
```

You will need to generate 32-byte Base64 encoded keys for `ENC_KEY` and `MAC_KEY`. You can use the following command:

```bash
php -r 'echo base64_encode(random_bytes(32)) . PHP_EOL;'
```

### 3. Run with Docker Compose

The easiest way to get started is using Docker Compose:

```bash
docker compose up -d
```

This will spin up:

- `app`: The PHP-FPM application container.
- `db`: PostgreSQL 18 database.
- `web`: Nginx web server.

The application will be accessible at `http://localhost:8080`.

### 4. Manual Setup (Non-Docker)

If you prefer not to use Docker:

1. Ensure you have a PostgreSQL server running and the schema in `postgres/init.sql` is applied.
2. Install dependencies: `composer install`.
3. Configure your web server (e.g., Nginx or Apache) to point to the `public/` directory.
4. Ensure the `.env` file is correctly configured for your local environment.

## Usage

1. **Create a Paste**: Navigate to the homepage, enter your text, and click submit.
2. **Share your Paste**: You will be redirected to a unique URL containing a token. Share this URL with others so they
   can view the paste.
3. **View a Paste**: Anyone with the unique token can view the original text.

## Security Overview

This application implements a multi-layer security model for stored pastes:

1. **Client-Secret Encryption**: When a paste is submitted, a random 32-byte key is generated. The paste content is
   encrypted using AES-256-CBC with this key.
2. **Encrypted Token**: The decryption key and the database ID of the paste are bundled into a JSON object. This object
   is then:
    - Encrypted with a system-wide `ENC_KEY` (AES-256-CBC).
    - Signed with a system-wide `MAC_KEY` (HMAC-SHA256).
3. **Storage**: Only the encrypted paste content is stored in the database. The server does not store the per-paste
   decryption key.
4. **Retrieval**: To view a paste, the user must provide the token. The server verifies the HMAC signature, decrypts the
   token to get the per-paste key and ID, then uses those to retrieve and decrypt the original content.

## Code Style

The project uses `PHP CS Fixer` to maintain a consistent code style. You can run the formatter using:

```bash
composer format
```
