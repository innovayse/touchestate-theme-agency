<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use TouchEstate\Sdk\TouchEstateClient;

class ContactController extends Controller
{
    public function __construct(private TouchEstateClient $client) {}

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

    /**
     * Submit a general contact form inquiry (no property required)
     */
    public function inquiry(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'    => 'required|string|max:100',
            'email'   => 'required|email|max:150',
            'phone'   => 'nullable|string|max:30',
            'message' => 'nullable|string|max:1000',
        ]);

        try {
            $this->client->contacts()->inquiry([
                'name'    => $data['name'],
                'email'   => $data['email'],
                'phone'   => $data['phone'] ?? '',
                'message' => $data['message'] ?? '',
            ]);
            return response()->json(['ok' => true]);
        } catch (\Exception $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
