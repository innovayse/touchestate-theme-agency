# TouchEstate Agency Theme

[![CI](https://github.com/innovayse/touchestate-theme-agency/actions/workflows/ci.yml/badge.svg)](https://github.com/innovayse/touchestate-theme-agency/actions/workflows/ci.yml)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)
[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?logo=php&logoColor=white)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel&logoColor=white)](https://laravel.com)

A multilingual real estate agency website theme built on the [TouchEstate](https://touchestate.io) API. Designed and developed by [Innovayse Digital Agency](https://innovayse.com).

## Features

- Property listings with filtering and search
- Property detail pages with image gallery and map
- Agent profiles
- Favorites (localStorage-based)
- Interactive map view
- Multilingual support: Armenian (`/hy`), Russian (`/ru`), English (`/en`)
- Geocoding via Yandex Maps + Nominatim

## Requirements

- PHP 8.2+
- Laravel 12
- [TouchEstate](https://touchestate.io) API credentials
- Yandex Maps API key

## Installation

```bash
git clone https://github.com/innovayse/touchestate-theme-agency.git
cd touchestate-theme-agency

composer install
npm install && npm run build

cp .env.example .env
php artisan key:generate
```

## Configuration

Edit `.env` and fill in your credentials:

```env
TOUCHESTATE_BASE_URL=https://touchestate.io
TOUCHESTATE_PUBLIC_KEY=your_public_key
TOUCHESTATE_SECRET_KEY=your_secret_key

YANDEX_MAPS_API_KEY=your_yandex_maps_api_key
```

TouchEstate API credentials can be obtained from your workspace settings at [touchestate.io](https://touchestate.io).

## Built With

- [Laravel 12](https://laravel.com)
- [TouchEstate PHP SDK](https://github.com/innovayse-admin/touchestate-php-sdk)
- Yandex Maps API
- Nominatim (OpenStreetMap)

## License

MIT License — see [LICENSE](LICENSE) for details.

---

Made by [Innovayse Digital Agency](https://innovayse.com)
