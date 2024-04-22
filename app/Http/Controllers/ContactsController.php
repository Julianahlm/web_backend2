<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactsCreateRequest;
use App\Http\Requests\ContactsUpdateRequest;
use App\Http\Resources\ContactsResource;
use App\Models\Contacts;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ContactsController extends Controller
{
    public function create(ContactsCreateRequest $request): JsonResponse
    {
        $contact = Contacts::create($request->validated());
        return response()->json([
            'data' => new ContactsResource($contact),
            'message' => 'Contact created successfully'
        ], 201);
    }

    public function update(ContactsUpdateRequest $request, $id): JsonResponse
    {
        $contact = Contacts::findOrFail($id);
        $contact->update($request->validated());
        return response()->json([
            'data' => new ContactsResource($contact),
            'message' => 'Contact updated successfully'
        ]);
    }

    public function delete($id): JsonResponse
    {
        $contact = Contacts::findOrFail($id);
        $contact->delete();
        return response()->json([
            'data' => true,
            'message' => 'Contact deleted successfully'
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        $query = Contacts::query();
        $contacts = $query->get();
        return response()->json([
            'data' => ContactsResource::collection($contacts),
            'message' => 'Contacts retrieved successfully'
        ]);
    }
}
