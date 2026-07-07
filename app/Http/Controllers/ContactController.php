<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use TouchEstate\Sdk\TouchEstateClient;

class ContactController extends Controller
{
    public function __construct(private TouchEstateClient $client)
    {
    }

    /**
     * List contacts with pagination and filtering
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $contacts = $this->client->contacts()->list($request->only([
                'pageNumber', 'pageSize', 'search', 'type', 'source',
                'assignedAgentId', 'sortBy', 'sortDescending',
            ]));
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch contacts'], 500);
        }

        return response()->json($contacts);
    }

    /**
     * Get a single contact by ID
     */
    public function show(string $id): JsonResponse
    {
        try {
            $contact = $this->client->contacts()->retrieve($id);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Contact not found'], 404);
        }

        return response()->json($contact);
    }
}
