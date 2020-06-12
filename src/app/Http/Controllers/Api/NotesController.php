<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Note;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class NotesController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws AuthorizationException
     * @throws Throwable
     */
    public function store(Request $request)
    {
        $this->authorize('create', new Note($request->all()));

        $request->validate(Note::getValidationRules());

        $note = Note::createNote($request->all());

        return response()->json($note, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param Note $note
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function show(Note $note)
    {
        $this->authorize('view', $note);

        $note = Note::getNote($note->id);

        return response()->json($note);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Note $note
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function update(Request $request, Note $note)
    {
        $this->authorize('update', $note);

        $request->validate(Note::getValidationRules());

        $updated = $note->update($request->all());

        if (!$updated) {
            return response()->json(null, 500);
        }

        $note = $note->refresh();

        return response()->json($note, 204);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Note $note
     * @return JsonResponse
     * @throws Exception
     */
    public function destroy(Note $note)
    {
        $this->authorize('delete', $note);

        $deleted = $note->delete();

        if (!$deleted) {
            return response()->json(null, 500);
        }

        return response()->json(null, 204);
    }
}
