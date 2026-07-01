<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Facades\Cache;
use Tests\TestCase;
use TouchEstate\Sdk\TouchEstateClient;

class PropertyFilterSecurityTest extends TestCase
{
    private function mockClient(array $capturedParams = []): void
    {
        $mock           = $this->createMock(TouchEstateClient::class);
        $propertiesMock = new class ($capturedParams) {
            public array $lastParams = [];

            public function __construct(array &$ref)
            {
                $this->ref = &$ref;
            }

            public function list(array $params): array
            {
                $this->ref = $params;

                return ['items' => [], 'totalCount' => 0, 'hasNextPage' => false];
            }
        };

        $mock->method('properties')->willReturn($propertiesMock);
        $this->app->instance(TouchEstateClient::class, $mock);
    }

    /** #6 — ?status=Sold must return 422, not leak non-active listings */
    public function test_status_param_is_prohibited(): void
    {
        $this->get('/en/property?status=Sold')
            ->assertStatus(422);
    }

    public function test_status_param_array_is_prohibited(): void
    {
        $this->get('/en/property?status[]=Draft&status[]=Sold')
            ->assertStatus(422);
    }

    /** #4 — invalid enum values must return 422 */
    public function test_invalid_property_type_returns_422(): void
    {
        $this->get('/en/property?propertyType=DROP')
            ->assertStatus(422);
    }

    public function test_invalid_transaction_type_returns_422(): void
    {
        $this->get('/en/property?transactionType=RentMonthly')
            ->assertStatus(422);
    }

    public function test_invalid_feature_element_returns_422(): void
    {
        $this->get('/en/property?features[]=DROP&features[]=Elevator')
            ->assertStatus(422);
    }

    public function test_negative_price_returns_422(): void
    {
        $this->get('/en/property?minPrice=-99999')
            ->assertStatus(422);
    }

    public function test_non_numeric_price_returns_422(): void
    {
        $this->get('/en/property?minPrice=abc')
            ->assertStatus(422);
    }

    /** #7 — pageSize cap */
    public function test_page_size_above_100_returns_422(): void
    {
        $this->get('/en/property?pageSize=1000000')
            ->assertStatus(422);
    }

    /** Wave 2 — new enum arrays */
    public function test_invalid_heating_type_returns_422(): void
    {
        $this->get('/en/property?heatingType[]=DROP&heatingType[]=Central')
            ->assertStatus(422);
    }

    public function test_invalid_parking_type_returns_422(): void
    {
        $this->get('/en/property?parkingType[]=BadValue')
            ->assertStatus(422);
    }

    public function test_invalid_window_view_returns_422(): void
    {
        $this->get('/en/property?windowView[]=Invalid&windowView[]=Garden')
            ->assertStatus(422);
    }

    /** Wave 2 — new single enums */
    public function test_invalid_balcony_type_returns_422(): void
    {
        $this->get('/en/property?balconyType=Invalid')
            ->assertStatus(422);
    }

    public function test_invalid_terrace_type_returns_422(): void
    {
        $this->get('/en/property?terraceType=Xyz')
            ->assertStatus(422);
    }

    /** Wave 2 — new booleans */
    public function test_invalid_boolean_returns_422(): void
    {
        $this->get('/en/property?isUninhabited=garbage')
            ->assertStatus(422);
    }

    public function test_invalid_sun_direction_returns_422(): void
    {
        $this->get('/en/property?sunDirection=maybe')
            ->assertStatus(422);
    }

    public function test_valid_long_term_rental_passes(): void
    {
        Cache::flush();

        $this->get('/en/property?isLongTermRental=1')
            ->assertStatus(200);
    }

    /** Valid requests must pass through */
    public function test_valid_filters_pass(): void
    {
        Cache::flush();

        $this->get('/en/property?propertyType=Apartment&transactionType=Sale&minPrice=0&maxPrice=500000')
            ->assertStatus(200);
    }

    public function test_valid_wave2_arrays_pass(): void
    {
        Cache::flush();

        $this->get('/en/property?heatingType[]=Central&heatingType[]=Solar&parkingType[]=Garage&windowView[]=City&balconyType=Open&terraceType=Closed&isUninhabited=1&sunDirection=1')
            ->assertStatus(200);
    }
}
