# Мультивалютная система — Дизайн-документ

**Дата:** 2026-07-09  
**Статус:** Approved  

---

## 1. Цель

Внедрить автоматическую конвертацию валют (AMD, USD, EUR, RUB) на основе актуальных курсов Центрального банка Армении (CBA). Пользователь выбирает валюту один раз — цены пересчитываются везде мгновенно. Поиск по цене работает кросс-валютно.

---

## 2. Получение и хранение курсов (CbaRatesService)

**Файл:** `app/Services/CbaRatesService.php`

### API
- Endpoint: `http://api.cba.am/exchangerates.asmx`
- Протокол: SOAP 1.1
- Метод: `ExchangeRatesLatest`
- SOAPAction: `http://www.cba.am/ExchangeRatesLatest`

### SOAP envelope
```xml
<?xml version="1.0" encoding="utf-8"?>
<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
               xmlns:xsd="http://www.w3.org/2001/XMLSchema"
               xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
  <soap:Body>
    <ExchangeRatesLatest xmlns="http://www.cba.am/" />
  </soap:Body>
</soap:Envelope>
```

### Парсинг ответа
XML содержит теги `<ISO>`, `<Rate>`, `<Amount>`. Курс за 1 единицу валюты:
```
rates[ISO] = Rate / Amount   // AMD за 1 единицу иностранной валюты
```

### Кэширование
```php
Cache::remember('cba_rates', 43200, fn() => $this->fetchFromApi())
// 43200 секунд = 12 часов
```

### Fallback (приоритет по убыванию)
1. Живой ответ от CBA API
2. Последний закэшированный курс (даже если истёк TTL — через `Cache::get`)
3. Захардкоженные курсы как последний резерв: `USD=390, EUR=420, RUB=4.3, AMD=1`

### Публичные методы
```php
getRates(): array          // ['USD' => 390.0, 'EUR' => 420.0, 'RUB' => 4.3, 'AMD' => 1.0]
convert(float $amount, string $from, string $to): float
// Конвертация через AMD как базу: amount * rates[$from] / rates[$to]
```

### Artisan команда
`php artisan rates:fetch` — принудительное обновление кэша.  
Scheduler: каждые 12 часов (`->everyTwelveHours()`).

---

## 3. Глобальный Alpine.js store + передача курсов

### View share
В `AppServiceProvider::boot()`:
```php
View::share('cbaRates', app(CbaRatesService::class)->getRates());
```

### Layout (app.blade.php)
Один раз в конце `<body>`, до остальных скриптов:
```html
<script>
window.__FX_RATES__ = @json($cbaRates);
// { "USD": 390.0, "EUR": 420.0, "RUB": 4.3, "AMD": 1.0 }
</script>
```

### Alpine store (app.js)
```js
Alpine.store('fx', {
    rates: window.__FX_RATES__ || { USD: 390, EUR: 420, RUB: 4.3, AMD: 1 },
    currency: localStorage.getItem('te_currency') || 'USD',

    setCurrency(cur) {
        this.currency = cur;
        localStorage.setItem('te_currency', cur);
        window.dispatchEvent(new CustomEvent('currency-changed', { detail: cur }));
    },

    // Конвертировать amount из валюты from в текущую выбранную валюту
    convert(amount, from) {
        if (!amount) return null;
        const amd = amount * (this.rates[from] ?? 1);
        return amd / (this.rates[this.currency] ?? 1);
    },

    // Форматировать с сокращением больших AMD чисел
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

    // Блок конвертаций для страницы объекта (3 валюты, исключая оригинальную)
    conversions(amount, originalCurrency) {
        const all = ['USD', 'EUR', 'AMD', 'RUB'];
        return all
            .filter(c => c !== originalCurrency)
            .map(c => ({
                currency: c,
                label: this.formatValue(amount * (this.rates[originalCurrency] ?? 1) / (this.rates[c] ?? 1), c)
            }));
    }
});
```

---

## 4. Переключатель валюты в шапке

**Место:** `resources/views/partials/header.blade.php`  
**Дизайн:** Аналогично переключателю языка — дропдаун рядом с языковым.  
**Содержимое:** `$ USD`, `€ EUR`, `֏ AMD`, `₽ RUB`  
**Поведение:** При выборе вызывает `$store.fx.setCurrency('USD')` — мгновенно пересчитываются все цены на странице без перезагрузки.

---

## 5. Отображение цен в компонентах

### Принцип
Все blade-компоненты с ценами оборачиваются в Alpine выражение. PHP передаёт оригинальные `price` и `currency`, Alpine конвертирует и форматирует на клиенте.

### property-card.blade.php
```html
<span x-data x-text="$store.fx.format({{ (float)($prop['price'] ?? 0) }}, '{{ $prop['currency'] ?? 'AMD' }}')"></span>
```

### property-single.blade.php — основная цена
```html
<span x-data x-text="$store.fx.format({{ (float)$property['price'] }}, '{{ $currency }}')"></span>
```

### property-single.blade.php — блок конвертаций (3 валюты)
Под основной ценой:
```html
<div x-data class="flex gap-4 mt-1 text-sm text-neutral-500">
    <template x-for="c in $store.fx.conversions({{ (float)$property['price'] }}, '{{ $currency }}')" :key="c.currency">
        <span x-text="c.label"></span>
    </template>
</div>
```

### Остальные места с ценами
- `map.blade.php` — balloon карточка (цена в sidebar и balloon)
- `favorites.blade.php`
- `compare.blade.php`
- `property-single.blade.php` — pricePerSqm, deposit
- `index.blade.php` — если есть карточки объектов

---

## 6. Кросс-валютный поиск

### Изменения в UI (property.blade.php)
- Поле выбора `currency` в фильтре **удаляется**
- Рядом с полями minPrice/maxPrice показывается текущая валюта из `localStorage` (визуальный индикатор, не select)
- При сабмите формы JS добавляет скрытый input `selectedCurrency` = `localStorage.getItem('te_currency')`

### Логика в PropertyController::index()
```php
if (request()->filled('minPrice') || request()->filled('maxPrice')) {
    $selectedCurrency = request('selectedCurrency', 'USD');
    $minPrice = (float) request('minPrice', 0);
    $maxPrice = (float) request('maxPrice', PHP_INT_MAX);

    $rates = app(CbaRatesService::class)->getRates();
    $currencies = ['USD', 'EUR', 'AMD', 'RUB'];

    // Конвертируем в каждую валюту
    $queries = [];
    foreach ($currencies as $cur) {
        $queries[$cur] = [
            'min' => $minPrice * $rates[$selectedCurrency] / $rates[$cur],
            'max' => $maxPrice * $rates[$selectedCurrency] / $rates[$cur],
        ];
    }

    // 4 параллельных curl_multi запроса к TouchEstate API
    // Каждый с currency=$cur, minPrice=$queries[$cur]['min'], maxPrice=$queries[$cur]['max']
    // Объединяем результаты, дедупликация по slug
    $results = $this->crossCurrencySearch($filters, $queries);
} else {
    // Обычный поиск — один запрос как раньше
    $results = $this->client->properties()->list($filters);
}
```

### Кэш кросс-валютного поиска
TTL: 15 минут (короче стандартного, т.к. зависит от курсов).  
Ключ: `md5(serialize($queries) . serialize($otherFilters))`.

### Дедупликация
По полю `slug` — если один объект вернулся в нескольких валютных запросах, берём первый (оригинальная валюта объекта приоритетнее).

---

## 7. Карта (map.blade.php)

Цены в sidebar-карточках и balloon передаются как JS-данные (`item.price`, `item.currency`). Форматирование через `window.__FX_STORE__` или прямой вызов store:

```js
// В buildBalloonHtml / addSidebarCard
const fxStore = Alpine.store('fx');
const displayPrice = fxStore ? fxStore.format(item.rawPrice, item.rawCurrency) : item.price;
```

`mapData()` endpoint добавляет `rawPrice` и `rawCurrency` в ответ (сейчас передаётся уже отформатированная строка `"320,000 AMD"`).

---

## 8. Scheduler

В `app/Console/Kernel.php`:
```php
$schedule->command('rates:fetch')->everyTwelveHours();
```

---

## 9. Файлы затронутые изменениями

| Файл | Изменение |
|------|-----------|
| `app/Services/CbaRatesService.php` | **новый** |
| `app/Console/Commands/FetchRates.php` | **новый** |
| `app/Providers/AppServiceProvider.php` | View::share cbaRates |
| `app/Console/Kernel.php` | scheduler |
| `app/Http/Controllers/PropertyController.php` | кросс-валютный поиск, rawPrice в mapData |
| `resources/views/layout/app.blade.php` | `__FX_RATES__` JSON |
| `resources/views/partials/header.blade.php` | переключатель валюты |
| `resources/views/components/property-card.blade.php` | Alpine цена |
| `resources/views/property-single.blade.php` | Alpine цена + блок конвертаций |
| `resources/views/property.blade.php` | убрать currency select, добавить selectedCurrency |
| `resources/views/map.blade.php` | rawPrice/rawCurrency в JS |
| `resources/views/favorites.blade.php` | Alpine цена |
| `resources/views/compare.blade.php` | Alpine цена |
| `resources/views/index.blade.php` | Alpine цена (если есть) |
| `resources/js/app.js` | Alpine.store('fx') |
| `app/Http/Controllers/PropertyController.php` | buildFilters + crossCurrencySearch |
