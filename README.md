# Laravel TiteLive Client

[![run-tests](https://github.com/code16/laravel-titelive-client/actions/workflows/run-tests.yml/badge.svg)](https://github.com/code16/laravel-titelive-client/actions/workflows/run-tests.yml)

A Laravel client for the TiteLive / Epagine book directory API.

## Installation

You can install the package via composer:

```bash
composer require code16/laravel-titelive-client
```

The package will automatically register its service provider.

You can publish the config file with:

```bash
php artisan vendor:publish --tag="titelive-client-config"
```

## Configuration

Add the following environment variables to your `.env` file:

```env
TITELIVE_LOGIN=your_login
TITELIVE_PWD=your_password
TITELIVE_ENDPOINT=https://catsearch.epagine.fr/v1/
TITELIVE_LOGIN_ENDPOINT=https://login.epagine.fr/v1/
```

### Mocking for Development

To avoid making real API calls during development, you can enable the mock mode:

```env
TITELIVE_CLIENT_MOCK_BOOK_DIRECTORY=true
```

## Usage

### Searching for Books

The `SearchBooks` action allows you to search for books in the TiteLive directory.

```php
use Code16\LaravelTiteliveClient\Api\SearchBooks;

$results = app(SearchBooks::class)
    ->withUnavailable(false) // Filter for available books only (default is true)
    ->groupByEdition(true)   // Group by edition (default is true)
    ->search('The Lord of the Rings', page: 1, count: 24);

foreach ($results as $book) {
    echo $book->title;
}
```

### Finding a Book by Gencod (ISBN)

The `FindBook` action retrieves a specific book using its gencod.

```php
use Code16\LaravelTiteliveClient\Api\FindBook;

$book = app(FindBook::class)->find('9780261102385');

if ($book) {
    echo $book->title;
    echo $book->editor;
}
```

### The Book Model

The API results are mapped to `Code16\LaravelTiteliveClient\Models\Book` Eloquent models (non-persisted by default).

Useful methods and attributes:
- `$book->canBeOrdered()`: Checks if the book is available and has stock.
- `$book->hasStock()`: Checks if the book is currently in stock.
- `$book->visual('large')`: Retrieves a cover image URL for a given size.
- `$book->shortDetails`: A formatted string with authors and editor.

## Testing

```bash
composer test
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
