# Simplestore

A simple CRUD api for managing products in a store. Created using the `http` library in [Utopia PHP](https://github.com/utopia-php/http) framework.

Check out the postman documentation:

[![Postman](https://img.shields.io/badge/Postman-FF6C37?style=for-the-badge&logo=postman&logoColor=white)](https://documenter.getpostman.com/view/26816728/2sAYJ6BKkp)
## Getting Started

### Prerequisites

1. PHP 8.0 or higher (8.3 recommended)
Follow installation steps [here](https://www.php.net/manual/en/install.php).
2. Install PHP extensions
    ```bash
    pecl install swoole xdebug
    ```
3. Install Composer: https://getcomposer.org/
4. Install dependencies
    ```bash
    composer install
    ```
   
## Running the server

```bash
php src/server.php
```

Server will start on port 80.

## Example Requests

1. Fetch all products
    ```bash
    curl -X GET http://localhost:80/products
    ```

## Data Format

The api uses in-memory storage to store products. The data is stored in the following format:

```json
{
  "id": "string",
  "name": "string",
  "description": "string",
  "price": "float",
  "currency": "string",
  "category": "string",
  "brand": "string",
  "sku": "string",
  "stock": "int",
  "rating": "float"
}
```