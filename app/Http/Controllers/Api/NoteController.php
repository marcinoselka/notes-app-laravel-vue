<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNoteRequest;
use App\Http\Requests\UpdateNoteRequest;
use App\Http\Resources\NoteResource;
use App\Http\Resources\NoteResourceCollection;
use App\Models\Note;
use App\Services\NoteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    public function __construct(private readonly NoteService $notes) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): NoteResourceCollection
    {
        return new NoteResourceCollection(
            $this->notes->list($request->user())
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreNoteRequest $request): JsonResponse
    {
        $note = $this->notes->create($request->validated(), $request->user());

        return (new NoteResource($note))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Note $note): NoteResource
    {
        $this->authorize('view', $note);

        return new NoteResource($this->notes->find($note->id, $request->user()));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateNoteRequest $request, Note $note): NoteResource
    {
        $updated = $this->notes->update($note->id, $request->validated(), $request->user());

        return new NoteResource($updated);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Note $note): JsonResponse
    {
        $this->authorize('delete', $note);

        $this->notes->delete($note->id, $request->user());

        return response()->json(null, 204);
    }
}
