# Multicurrency System Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Реализовать мультивалютную систему (AMD/USD/EUR/RUB) с курсами ЦБ Армении, реактивным переключателем валюты в шапке и кросс-валютным поиском по цене.

**Architecture:** `CbaRatesService` получает курсы через SOAP API ЦБА и кэширует 12 часов с fallback на hardcoded значения. Курсы передаются в JS через `window.__FX_RATES__` и Alpine.js store `fx`, который реактивно форматирует цены во всех компонентах без перезагрузки. Кросс-валютный поиск запускает 4 параллельных curl_multi запроса к TouchEstate API с конвертированными диапазонами цен.

**Tech Stack:** PHP 8.2, Laravel 11, Alpine.js 3.x, cURL/SOAP, Blade, Tailwind CSS

## Global Constraints

- Fallback курсы: `USD=390, EUR=420, RUB=4.3, AMD=1` — используй эти точные значения
- Cache TTL курсов: `43200` секунд (12 часов)
- Cache TTL кросс-валютного поиска: `900` секунд (15 минут)
- Cache ключ курсов: `'cba_rates'`
- localStorage ключ: `'te_currency'`
- Alpine store имя: `'fx'`
- JS глобальная переменная: `window.__FX_RATES__`
- SOAP endpoint: `http://api.cba.am/exchangerates.asmx`
- SOAPAction: `http://www.cba.am/ExchangeRatesLatest`
- Поддерживаемые валюты: `['USD', 'EUR', 'AMD', 'RUB']`
- AMD числа ≥ 1,000,000 отображать как `֏ 1.5M` (одна значимая цифра после точки, без `.0`)
- Коммиты без строки `Co-Authored-By: Claude`

---

## Task 1: CbaRatesService — получение и кэширование курсов

**Files:**
- Create: `app/Services/CbaRatesService.php`

**Interfaces:**
- Produces:
  - `CbaRatesService::getRates(): array` — возвращает `['USD' => 390.0, 'EUR' => 420.0, 'RUB' => 4.3, 'AMD' => 1.0]`
  - `CbaRatesService::convert(float $amount, string $from, string $to): float`

- [ ] **Step 1: Создай файл сервиса**

```php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class CbaRatesService
{
    private const FALLBACK = ['USD' => 390.0, 'EUR' => 420.0, 'RUB' => 4.3, 'AMD' => 1.0];

    public function getRates(): array
    {
        return Cache::remember('cba_rates', 43200, fn() => $this->fetchFromApi());
    }

    public function convert(float $amount, string $from, string $to): float
    {
        $rates = $this->getRates();
        $amd = $amount * ($rates[$from] ?? 1.0);
        return $amd / ($rates[$to] ?? 1.0);
    }

    private function fetchFromApi(): array
    {
        $soap = '<?xml version="1.0" encoding="utf-8"?>'
            . '<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"'
            . ' xmlns:xsd="http://www.w3.org/2001/XMLSchema"'
            . ' xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">'
            . '<soap:Body><ExchangeRatesLatest xmlns="http://www.cba.am/" /></soap:Body>'
            . '</soap:Envelope>';

        $ch = curl_init('http://api.cba.am/exchangerates.asmx');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $soap,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: text/xml; charset=utf-8',
                'SOAPAction: "http://www.cba.am/ExchangeRatesLatest"',
            ],
        ]);
        $response = curl_exec($ch);
        $errno    = curl_errno($ch);
        curl_close($ch);

        if ($errno || !$response) {
            return Cache::get('cba_rates') ?? self::FALLBACK;
        }

        return $this->parseResponse($response);
    }

    private function parseResponse(string $xml): array
    {
        $rates = ['AMD' => 1.0];
        try {
            $doc = new \SimpleXMLElement($xml);
            $doc->registerXPathNamespace('ns', 'http://www.cba.am/');
            $items = $doc->xpath('//ns:ExchangeRate') ?: [];
            foreach ($items as $item) {
                $iso    = trim((string) ($item->ISO ?? ''));
                $rate   = (float) ($item->Rate ?? 0);
                $amount = (float) ($item->Amount ?? 1);
                if ($iso && $rate > 0 && $amount > 0) {
                    $rates[$iso] = $rate / $amount;
                }
            }
        } catch (\Throwable) {
            return Cache::get('cba_rates') ?? self::FALLBACK;
        }

        // Must have at least USD and EUR to be valid
        if (empty($rates['USD']) || empty($rates['EUR'])) {
            return Cache::get('cba_rates') ?? self::FALLBACK;
        }

        return $rates;
    }
}
```

- [ ] **Step 2: Зарегистрируй сервис в AppServiceProvider и добавь View::share курсов**

Открой `app/Providers/AppServiceProvider.php`. Добавь use импорт и в метод `boot()` добавь View share после существующего View::composer:

```php
use App\Services\CbaRatesService;
```

В `boot()` после блока `View::composer('*', ...)`:

```php
        // Share exchange rates with all views (cached 12h)
        View::share('cbaRates', app(CbaRatesService::class)->getRates());
```

- [ ] **Step 3: Вручную проверь что SOAP запрос работает**

```bash
cd /home/innovayse/www/touchestate-demo
php artisan tinker --execute="dd(app(\App\Services\CbaRatesService::class)->getRates());"
```

Ожидаемый вывод: массив с USD, EUR, RUB, AMD ключами и числовыми значениями (или fallback если API недоступен).

- [ ] **Step 4: Коммит**

```bash
git add app/Services/CbaRatesService.php app/Providers/AppServiceProvider.php
git commit -m "feat: add CbaRatesService with SOAP fetch, 12h cache, and fallback rates"
```

---

## Task 2: Artisan команда rates:fetch и scheduler

**Files:**
- Create: `app/Console/Commands/FetchRates.php`
- Modify: `routes/console.php`

**Interfaces:**
- Consumes: `CbaRatesService::getRates()` из Task 1

- [ ] **Step 1: Создай команду**

```php
<?php

namespace App\Console\Commands;

use App\Services\CbaRatesService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class FetchRates extends Command
{
    protected $signature   = 'rates:fetch';
    protected $description = 'Fetch latest exchange rates from CBA Armenia and refresh cache';

    public function handle(CbaRatesService $service): int
    {
        Cache::forget('cba_rates');
        $rates = $service->getRates();

        $this->info('Rates updated:');
        foreach (['USD', 'EUR', 'RUB', 'AMD'] as $cur) {
            if (isset($rates[$cur])) {
                $this->line("  {$cur}: {$rates[$cur]}");
            }
        }

        return 0;
    }
}
```

- [ ] **Step 2: Добавь scheduler в routes/console.php**

Открой `routes/console.php` и добавь после существующего содержимого:

```php
use Illuminate\Support\Facades\Schedule;

Schedule::command('rates:fetch')->everyTwelveHours();
```

- [ ] **Step 3: Проверь команду**

```bash
cd /home/innovayse/www/touchestate-demo
php artisan rates:fetch
```

Ожидаемый вывод:
```
Rates updated:
  USD: 390
  EUR: 420
  ...
```

- [ ] **Step 4: Коммит**

```bash
git add app/Console/Commands/FetchRates.php routes/console.php
git commit -m "feat: add rates:fetch artisan command with 12h scheduler"
```

---

## Task 3: Alpine.js fx store и передача курсов в layout

**Files:**
- Modify: `resources/views/layout/app.blade.php`
- Modify: `resources/js/app.js`

**Interfaces:**
- Consumes: `$cbaRates` из View::share (Task 1)
- Produces: `Alpine.store('fx')` с методами `format(amount, from)`, `convert(amount, from)`, `conversions(amount, originalCurrency)`, `setCurrency(cur)`

- [ ] **Step 1: Добавь window.__FX_RATES__ в layout**

Открой `resources/views/layout/app.blade.php`. Найди строку `@vite(...)` (строка ~16). Перед тегом `</head>` добавь блок скрипта с курсами. Найди `</head>` и добавь сразу перед ним:

```html
    <script>window.__FX_RATES__ = @json($cbaRates);</script>
```

Итоговый `</head>` должен выглядеть так:
```html
    <script>window.__FX_RATES__ = @json($cbaRates);</script>
</head>
```

- [ ] **Step 2: Добавь Alpine store fx в app.js**

Открой `resources/js/app.js`. Найди строку `Alpine.store('contactModal', { open: false });` (около строки 304). Вставь перед ней новый store:

```js
Alpine.store('fx', {
    rates: window.__FX_RATES__ || { USD: 390, EUR: 420, RUB: 4.3, AMD: 1 },
    currency: localStorage.getItem('te_currency') || 'USD',

    setCurrency(cur) {
        this.currency = cur;
        localStorage.setItem('te_currency', cur);
    },

    convert(amount, from) {
        if (!amount) return null;
        const amd = amount * (this.rates[from] ?? 1);
        return amd / (this.rates[this.currency] ?? 1);
    },

    format(amount, from) {
        const converted = this.convert(amount, from);
        if (converted === null) return '';
        return this.formatValue(converted, this.currency);
    },

    formatValue(value, cur) {
        const symbols = { USD: '$', EUR: '€', RUB: '₽', AMD: '֏' };
        const sym = symbols[cur] ?? cur;
        if (cur === 'AMD' && value >= 1_000_000) {
            return sym + ' ' + (value / 1_000_000).toFixed(1).replace(/\.0$/, '') + 'M';
        }
        return sym + ' ' + new Intl.NumberFormat().format(Math.round(value));
    },

    conversions(amount, originalCurrency) {
        const all = ['USD', 'EUR', 'AMD', 'RUB'];
        return all
            .filter(c => c !== originalCurrency)
            .map(c => ({
                currency: c,
                label: this.formatValue(
                    amount * (this.rates[originalCurrency] ?? 1) / (this.rates[c] ?? 1),
                    c
                ),
            }));
    },
});

```

- [ ] **Step 3: Пересобери фронтенд**

```bash
cd /home/innovayse/www/touchestate-demo
npm run build
```

Ожидаемый вывод: `✓ built in ...` без ошибок.

- [ ] **Step 4: Коммит**

```bash
git add resources/views/layout/app.blade.php resources/js/app.js public/build/
git commit -m "feat: add Alpine.js fx store with currency conversion and AMD formatting"
```

---

## Task 4: Переключатель валюты в header

**Files:**
- Modify: `resources/views/partials/header.blade.php`

**Interfaces:**
- Consumes: `Alpine.store('fx').setCurrency(cur)` из Task 3
- Consumes: `Alpine.store('fx').currency` — текущая валюта

- [ ] **Step 1: Добавь переключатель валюты в drawer header**

Открой `resources/views/partials/header.blade.php`. Найди блок `{{-- Language switcher in drawer --}}` (около строки 193). Вставь переключатель валюты **перед** блоком языкового переключателя, чтобы они стояли рядом:

Замени:
```html
            {{-- Language switcher in drawer --}}
            <div x-data="{ open: false }" class="relative">
```

На:
```html
            {{-- Currency + Language switchers --}}
            <div class="flex items-center gap-2">

            {{-- Currency switcher --}}
            <div x-data="{ open: false }" class="relative" x-init="$store.fx && ($store.fx.currency = localStorage.getItem('te_currency') || 'USD')">
                <button @click="open = !open" @click.outside="open = false"
                        class="flex h-10 items-center gap-1.5 rounded-full border border-sand bg-panel px-3 text-sm font-medium text-ink">
                    <span x-text="$store.fx.currency">USD</span>
                    <svg width="11" height="11" viewBox="0 0 12 8" fill="none" stroke="currentColor" stroke-width="1.5" :class="open?'rotate-180':''"><path d="M1 1l5 5 5-5"/></svg>
                </button>
                <div x-show="open" x-transition x-cloak
                     class="absolute bottom-12 right-0 w-32 overflow-hidden rounded-xl border border-sand bg-white py-1 shadow-lg">
                    @foreach(['USD' => '$', 'EUR' => '€', 'AMD' => '֏', 'RUB' => '₽'] as $cur => $sym)
                        <button @click="$store.fx.setCurrency('{{ $cur }}'); open = false"
                                class="flex w-full items-center gap-2 px-4 py-2.5 text-sm text-left"
                                :class="$store.fx.currency === '{{ $cur }}' ? 'bg-brand-50 text-brand-700 font-semibold' : 'text-ink hover:bg-brand-50'">
                            <span>{{ $sym }}</span>
                            <span>{{ $cur }}</span>
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Language switcher in drawer --}}
            <div x-data="{ open: false }" class="relative">
```

Найди закрывающий `</div>` блока language switcher и добавь после него `</div>` для обёртки. Блок language switcher заканчивается так:

```html
                </div>
            </div>
        </div>
    </div>
</header>
```

Измени на:
```html
                </div>
            </div>

            </div>{{-- end currency+language wrapper --}}
        </div>
    </div>
</header>
```

- [ ] **Step 2: Проверь визуально**

Запусти сервер (`php artisan serve`) и открой сайт. В мобильном drawer (или просто в header) должен появиться дропдаун `USD` рядом с языковым. При нажатии на него открывается список: `$ USD`, `€ EUR`, `֏ AMD`, `₽ RUB`.

- [ ] **Step 3: Коммит**

```bash
git add resources/views/partials/header.blade.php
git commit -m "feat: add currency switcher dropdown in header drawer"
```

---

## Task 5: Реактивные цены в property-card и property-single

**Files:**
- Modify: `resources/views/components/property-card.blade.php`
- Modify: `resources/views/property-single.blade.php`

**Interfaces:**
- Consumes: `Alpine.store('fx').format(amount, from)` из Task 3
- Consumes: `Alpine.store('fx').conversions(amount, originalCurrency)` из Task 3

- [ ] **Step 1: Обнови property-card.blade.php**

Открой `resources/views/components/property-card.blade.php`. Найди строки (около 17-18):
```php
    $price = isset($prop['price']) ? number_format((float) $prop['price']) : null;
    $currency = $prop['currency'] ?? '';
```

Добавь под ними:
```php
    $rawPrice    = isset($prop['price']) ? (float) $prop['price'] : null;
    $rawCurrency = $prop['currency'] ?? 'AMD';
```

Найди отображение цены (около строки 94-95):
```html
            @if($price)
                <div class="font-display text-xl font-bold text-brand-700">{{ $price }} {{ $currency }}</div>
```

Замени на:
```html
            @if($rawPrice)
                <div class="font-display text-xl font-bold text-brand-700"
                     x-data
                     x-text="$store.fx.format({{ $rawPrice }}, '{{ $rawCurrency }}')">{{ number_format($rawPrice) }} {{ $rawCurrency }}</div>
```

> Примечание: текст внутри тега — fallback на случай если Alpine ещё не инициализировался.

- [ ] **Step 2: Обнови property-single.blade.php — основная цена**

Открой `resources/views/property-single.blade.php`. Найди PHP-блок в начале (строки ~16-17):
```php
    $price       = isset($property['price']) ? number_format((float) $property['price']) : null;
    $currency    = $property['currency'] ?? '';
```

Добавь под ними:
```php
    $rawPrice    = isset($property['price']) ? (float) $property['price'] : null;
    $rawCurrency = $property['currency'] ?? 'AMD';
```

Найди отображение основной цены (около строки 292-295):
```html
                        @if($price)
                            ...
                                <span class="font-display text-3xl font-bold text-brand-700">{{ $price }} {{ $currency }}</span>
```

Замени `<span class="font-display text-3xl font-bold text-brand-700">{{ $price }} {{ $currency }}</span>` на:
```html
                                <span class="font-display text-3xl font-bold text-brand-700"
                                      x-data
                                      x-text="$store.fx.format({{ $rawPrice ?? 0 }}, '{{ $rawCurrency }}')">{{ $price }} {{ $currency }}</span>
```

После этой строки добавь блок конвертаций (вставь сразу после закрывающего `</span>`):
```html
                                @if($rawPrice)
                                <div x-data class="flex flex-wrap gap-x-4 gap-y-0.5 mt-1 text-sm text-neutral-500">
                                    <template x-for="c in $store.fx.conversions({{ $rawPrice }}, '{{ $rawCurrency }}')" :key="c.currency">
                                        <span x-text="c.label"></span>
                                    </template>
                                </div>
                                @endif
```

- [ ] **Step 3: Обнови pricePerSqm и deposit в property-single.blade.php**

Найди строки (около строки 79-81):
```php
    if (!empty($property['pricePerSqm']))     { $specs[__('property-single.price_per_sqm')] = number_format((float)$property['pricePerSqm']) . ' ' . ($property['currency'] ?? ''); }
    ...
    if (!empty($property['deposit']))         { $specs[__('property-single.deposit')] = number_format((float)$property['deposit']) . ' ' . ($property['currency'] ?? ''); }
```

Замени эти две строки на:

```php
    if (!empty($property['pricePerSqm']))     { $specs[__('property-single.price_per_sqm')] = '<span x-data x-text="$store.fx.format(' . (float)$property['pricePerSqm'] . ', \'' . ($property['currency'] ?? 'AMD') . '\')">' . number_format((float)$property['pricePerSqm']) . ' ' . ($property['currency'] ?? '') . '</span>'; }
    ...
    if (!empty($property['deposit']))         { $specs[__('property-single.deposit')] = '<span x-data x-text="$store.fx.format(' . (float)$property['deposit'] . ', \'' . ($property['currency'] ?? 'AMD') . '\')">' . number_format((float)$property['deposit']) . ' ' . ($property['currency'] ?? '') . '</span>'; }
```

Затем найди место где `$specs` отображается в шаблоне (поиск по `$specs`) и убедись что значения выводятся через `{!! $value !!}` (unescaped), а не `{{ $value }}`. Если используется `{{ }}` — замени на `{!! !!}` для строк содержащих HTML.

- [ ] **Step 4: Проверь страницу объекта**

Открой любую страницу объекта. Должна быть видна основная цена. Переключи валюту в header — цена должна мгновенно пересчитаться. Под основной ценой должны быть 3 конвертации в другие валюты.

- [ ] **Step 5: Коммит**

```bash
git add resources/views/components/property-card.blade.php resources/views/property-single.blade.php
git commit -m "feat: make property prices reactive via Alpine fx store"
```

---

## Task 6: rawPrice/rawCurrency в map data + реактивные цены на карте

**Files:**
- Modify: `app/Http/Controllers/PropertyController.php` — метод `mapData()`
- Modify: `resources/views/map.blade.php`

**Interfaces:**
- Consumes: `Alpine.store('fx')` из Task 3
- Produces: поле `rawPrice` (float) и `rawCurrency` (string) в JSON ответе `/map/data`

- [ ] **Step 1: Добавь rawPrice и rawCurrency в mapData()**

Открой `app/Http/Controllers/PropertyController.php`. Найди блок формирования `$meta` в методе `mapData()` (около строки 273):

```php
                $meta = [
                    'slug'  => $slug,
                    'title' => $item['title'] ?? '',
                    'price' => isset($item['price']) ? number_format((float) $item['price']) . ' ' . ($item['currency'] ?? '') : '',
                    'url'   => url('/' . $locale . '/property/' . ($slug ?? '')),
                    'img'   => $item['primaryImageUrl'] ?? '',
                    'city'  => ($item['city'] ?? '') . (!empty($item['district']) ? ', ' . $item['district'] : ''),
                ];
```

Замени на:
```php
                $meta = [
                    'slug'        => $slug,
                    'title'       => $item['title'] ?? '',
                    'price'       => isset($item['price']) ? number_format((float) $item['price']) . ' ' . ($item['currency'] ?? '') : '',
                    'rawPrice'    => isset($item['price']) ? (float) $item['price'] : null,
                    'rawCurrency' => $item['currency'] ?? 'AMD',
                    'url'         => url('/' . $locale . '/property/' . ($slug ?? '')),
                    'img'         => $item['primaryImageUrl'] ?? '',
                    'city'        => ($item['city'] ?? '') . (!empty($item['district']) ? ', ' . $item['district'] : ''),
                ];
```

- [ ] **Step 2: Обнови buildBalloonHtml в map.blade.php**

Открой `resources/views/map.blade.php`. Найди функцию `buildBalloonHtml(item)` (около строки 244-246):
```js
    const priceHtml = item.price ? `<p class="ymap-card__price">${item.price}</p>` : '';
    return `<a class="ymap-card" href="${item.url}">...`;
```

Замени строку с priceHtml на:
```js
    const fxStore = window.Alpine && Alpine.store('fx');
    const displayPrice = (fxStore && item.rawPrice)
        ? fxStore.format(item.rawPrice, item.rawCurrency || 'AMD')
        : item.price;
    const priceHtml = displayPrice ? `<p class="ymap-card__price">${displayPrice}</p>` : '';
```

- [ ] **Step 3: Обнови addSidebarCard в map.blade.php**

Найди функцию `addSidebarCard` (около строки 300-306). Найди строку:
```js
    const price = item.price ? `<p class="mt-1 font-display text-sm font-bold text-brand-700">${item.price}</p>` : '';
```

Замени на:
```js
    const fxStore = window.Alpine && Alpine.store('fx');
    const sidebarPrice = (fxStore && item.rawPrice)
        ? fxStore.format(item.rawPrice, item.rawCurrency || 'AMD')
        : item.price;
    const price = sidebarPrice ? `<p class="mt-1 font-display text-sm font-bold text-brand-700">${sidebarPrice}</p>` : '';
```

- [ ] **Step 4: Проверь карту**

Открой `/ru/map`. Balloon и sidebar карточки должны показывать цену. При смене валюты в header... **Важно:** цены на карте НЕ обновятся автоматически при смене валюты (balloon строится один раз при hover). Это приемлемо — при следующем открытии balloon цена будет в новой валюте. Sidebar обновится при следующем клике.

- [ ] **Step 5: Коммит**

```bash
git add app/Http/Controllers/PropertyController.php resources/views/map.blade.php
git commit -m "feat: add rawPrice/rawCurrency to map data and use fx store for price display"
```

---

## Task 7: Кросс-валютный поиск

**Files:**
- Modify: `app/Http/Controllers/PropertyController.php` — метод `index()` и новый метод `crossCurrencySearch()`
- Modify: `resources/views/property.blade.php` — убрать currency select, добавить индикатор и selectedCurrency

**Interfaces:**
- Consumes: `CbaRatesService::getRates()` из Task 1
- Consumes: `Alpine.store('fx').currency` из Task 3 (для JS-части формы)

- [ ] **Step 1: Добавь метод crossCurrencySearch в PropertyController**

Открой `app/Http/Controllers/PropertyController.php`. Найди место после метода `buildFilters()` (конец около строки 200). Добавь новый приватный метод:

```php
    private function crossCurrencySearch(array $baseFilters, array $queries): array
    {
        $cacheKey = 'te_xfx:' . md5(serialize($queries) . serialize($baseFilters));
        return Cache::remember($cacheKey, 900, function () use ($baseFilters, $queries) {
            $currencies = array_keys($queries);
            $mh = curl_multi_init();
            $handles = [];

            foreach ($currencies as $cur) {
                $filters = $baseFilters;
                unset($filters['currency']);
                $filters['currency']  = $cur;
                $filters['minPrice']  = (int) round($queries[$cur]['min']);
                $filters['maxPrice']  = (int) round($queries[$cur]['max']);

                // Build signed request same way as TouchEstateClient
                // We reuse the same client by temporarily overriding — instead,
                // call the SDK for each currency sequentially (simpler and safe)
                $handles[$cur] = null; // placeholder
            }
            curl_multi_close($mh);

            // Sequential calls (SDK is not curl_multi-aware, but 4 calls × ~200ms = ~800ms cached for 15min)
            $seen    = [];
            $results = ['items' => [], 'totalCount' => 0, 'hasNextPage' => false];

            foreach ($currencies as $cur) {
                $filters = $baseFilters;
                unset($filters['currency']);
                $filters['currency']  = $cur;
                $filters['minPrice']  = (int) round($queries[$cur]['min']);
                $filters['maxPrice']  = (int) round($queries[$cur]['max']);

                try {
                    $res = $this->client->properties()->list($filters);
                    foreach ($res['items'] ?? [] as $item) {
                        $slug = $item['slug'] ?? null;
                        if ($slug && !isset($seen[$slug])) {
                            $seen[$slug] = true;
                            $results['items'][] = $item;
                        }
                    }
                    if (!empty($res['totalCount'])) {
                        $results['totalCount'] = max($results['totalCount'], (int) $res['totalCount']);
                    }
                    if (!empty($res['hasNextPage'])) {
                        $results['hasNextPage'] = true;
                    }
                } catch (\Throwable) {
                    // skip failed currency
                }
            }

            return $results;
        });
    }
```

- [ ] **Step 2: Обнови метод index() для кросс-валютного поиска**

Найди метод `index()` в PropertyController (около строки 213). Найди блок:
```php
        $filters = $this->buildFilters();
        try {
            $properties = Cache::remember('te_list:' . md5(serialize($filters)), 3600, function () use ($filters) {
                return $this->client->properties()->list($filters);
            });
        } catch (\Exception $e) {
            $properties = ['items' => [], 'totalCount' => 0, 'hasNextPage' => false];
        }
```

Замени на:
```php
        $filters = $this->buildFilters();

        // Cross-currency search: when price range is set, search in all 4 currencies
        if (request()->filled('minPrice') || request()->filled('maxPrice')) {
            $selectedCurrency = request('selectedCurrency', 'USD');
            $minPrice = request()->filled('minPrice') ? (float) request('minPrice') : 0;
            $maxPrice = request()->filled('maxPrice') ? (float) request('maxPrice') : PHP_INT_MAX;

            $rates = app(\App\Services\CbaRatesService::class)->getRates();
            $currencies = ['USD', 'EUR', 'AMD', 'RUB'];
            $queries = [];
            foreach ($currencies as $cur) {
                $queries[$cur] = [
                    'min' => $minPrice * ($rates[$selectedCurrency] ?? 1) / ($rates[$cur] ?? 1),
                    'max' => $maxPrice < PHP_INT_MAX
                        ? $maxPrice * ($rates[$selectedCurrency] ?? 1) / ($rates[$cur] ?? 1)
                        : PHP_INT_MAX,
                ];
            }

            // Build base filters without price (crossCurrencySearch adds per-currency price)
            $baseFilters = $filters;
            unset($baseFilters['minPrice'], $baseFilters['maxPrice'], $baseFilters['currency']);

            try {
                $properties = $this->crossCurrencySearch($baseFilters, $queries);
            } catch (\Exception $e) {
                $properties = ['items' => [], 'totalCount' => 0, 'hasNextPage' => false];
            }
        } else {
            try {
                $properties = Cache::remember('te_list:' . md5(serialize($filters)), 3600, function () use ($filters) {
                    return $this->client->properties()->list($filters);
                });
            } catch (\Exception $e) {
                $properties = ['items' => [], 'totalCount' => 0, 'hasNextPage' => false];
            }
        }
```

Добавь use в начало файла если не хватает:
```php
use App\Services\CbaRatesService;
```

- [ ] **Step 3: Обнови property.blade.php — убери currency select, добавь индикатор**

Открой `resources/views/property.blade.php`. Найди блок `{{-- Price range --}}` (около строки 88). Найди:
```html
                        <x-custom-select name="currency" class="mt-3"
                            :selected="request('currency', '')"
                            :placeholder="__('property.any_currency')"
                            :options="['USD'=>'USD','EUR'=>'EUR','AMD'=>'AMD','RUB'=>'RUB']" />
```

Замени на:
```html
                        {{-- Currency indicator (read-only, auto from switcher in header) --}}
                        <p class="mt-2 text-xs text-neutral-400"
                           x-data
                           x-text="'{{ __('property.prices_in') }} ' + $store.fx.currency">
                            {{ __('property.prices_in') }} USD
                        </p>
                        <input type="hidden" name="selectedCurrency" id="selectedCurrencyInput" value="USD">
```

Найди форму фильтров (тег `<form`). Добавь Alpine-директиву на форму для передачи текущей валюты при сабмите:

```html
<form ... x-data @submit="document.getElementById('selectedCurrencyInput').value = $store.fx.currency">
```

Если на форме уже есть `x-data` или обработчик `@submit` — добавь `document.getElementById('selectedCurrencyInput').value = $store.fx.currency` в существующий обработчик.

- [ ] **Step 4: Добавь ключ переводов**

Открой `lang/en/property.php` и добавь:
```php
'prices_in' => 'Prices in',
```

Открой `lang/ru/property.php` и добавь:
```php
'prices_in' => 'Цены в',
```

Открой `lang/hy/property.php` и добавь:
```php
'prices_in' => 'Գները',
```

- [ ] **Step 5: Проверь кросс-валютный поиск**

1. Открой `/en/property`
2. Переключи валюту на `EUR` в header
3. В фильтре введи minPrice = 100000
4. Нажми поиск — скрытый input должен иметь value=EUR
5. Сервер должен найти объекты во всех 4 валютах, дедуплицировать по slug и показать объединённый результат

- [ ] **Step 6: Коммит**

```bash
git add app/Http/Controllers/PropertyController.php resources/views/property.blade.php lang/en/property.php lang/ru/property.php lang/hy/property.php
git commit -m "feat: cross-currency price search with 4-currency deduplication"
```

---

## Финальная проверка

- [ ] Переключатель валюты в header drawer работает: `$ USD`, `€ EUR`, `֏ AMD`, `₽ RUB`
- [ ] Карточки объектов в листинге показывают цену в выбранной валюте
- [ ] Страница объекта: основная цена реактивна, под ней 3 конвертации
- [ ] Balloon и sidebar на карте показывают цену
- [ ] Кросс-валютный поиск: при выборе EUR и minPrice=100000 возвращает объекты
- [ ] AMD цены ≥ 1M отображаются как `֏ 150M`
- [ ] Смена валюты сохраняется в localStorage между страницами
- [ ] `php artisan rates:fetch` работает и выводит курсы
