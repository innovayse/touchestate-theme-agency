<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\ParallelPropertyFetcher;
use Tests\TestCase;

class FavoritesLoadTest extends TestCase
{
    private function mockFetcher(array $properties): void
    {
        $mock = $this->createMock(ParallelPropertyFetcher::class);
        $mock->method('fetchMany')->willReturn($properties);
        $this->app->instance(ParallelPropertyFetcher::class, $mock);
    }

    public function test_load_returns_per_slug_items(): void
    {
        $this->mockFetcher([
            'test-slug-1' => [
                'slug'            => 'test-slug-1',
                'title'           => 'Test Property',
                'price'           => 100000,
                'currency'        => 'USD',
                'transactionType' => 'Sale',
                'propertyType'    => 'Apartment',
                'media'           => [],
            ],
        ]);

        $response = $this->postJson('/en/favorites/load', ['slugs' => ['test-slug-1']]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'items' => [['slug', 'html']],
            'validSlugs',
        ]);
        $this->assertSame('test-slug-1', $response->json('items.0.slug'));
        $this->assertStringContainsString('test-slug-1', $response->json('items.0.html'));
        $this->assertSame(['test-slug-1'], $response->json('validSlugs'));
    }

    public function test_load_returns_empty_when_no_slugs(): void
    {
        $response = $this->postJson('/en/favorites/load', ['slugs' => []]);

        $response->assertStatus(200);
        $response->assertExactJson(['items' => [], 'validSlugs' => []]);
    }

    public function test_load_skips_missing_slugs(): void
    {
        $this->mockFetcher([]);

        $response = $this->postJson('/en/favorites/load', ['slugs' => ['nonexistent-slug']]);

        $response->assertStatus(200);
        $response->assertJson(['items' => [], 'validSlugs' => []]);
    }

    public function test_load_rejects_more_than_20_slugs(): void
    {
        $this->mockFetcher([]);
        $slugs = array_map(fn($i) => "slug-$i", range(1, 25));

        $response = $this->postJson('/en/favorites/load', ['slugs' => $slugs]);

        $response->assertStatus(200);
    }
}
