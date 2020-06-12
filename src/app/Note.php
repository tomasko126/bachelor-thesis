<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Throwable;

class Note extends Model
{
    use SoftDeletes;

    protected $guarded = ['id', 'creator_id', 'created_at', 'updated_at', 'deleted_at'];
    protected $hidden = ['deleted_at'];

    protected static function booted()
    {
        self::saving(function (Note $note) {
            $result = 0;

            if (!empty($note->animal_id)) {
                $result += 1;
            }

            if (!empty($note->litter_id)) {
                $result += 2;
            }

            if ($result !== 1 && $result !== 2) {
                return false;
            }

            return true;
        });
    }

    public function creator() {
        return $this->belongsTo('App\User');
    }

    public function animal() {
        return $this->belongsTo('App\Animal', 'animal_id');
    }

    public function litter() {
        return $this->belongsTo('App\Litter', 'litter_id');
    }

    /**
     * Create new note
     *
     * @param $data
     * @return Note
     * @throws Throwable
     */
    public static function createNote($data) {
        $note = new Note($data);
        $note->creator_id = Auth::id();

        $note->saveOrFail();

        return $note->refresh();
    }

    /**
     * Create new note from litter approval request answer
     *
     * @param array $data
     * @return bool
     * @throws Throwable
     */
    public static function createNoteFromLitterApprovalRequest(array &$data) {
        if (empty($data['litter_registrator_note'])) {
            unset($data['litter_registrator_note']);
            return false;
        }

        $noteData = [
            'litter_id' => $data['litter_id'],
            'note' => $data['litter_registrator_note'],
            'category' => 'general',
            'public' => true,
        ];

        // Remove registrator's litter note
        unset($data['litter_registrator_note']);

        self::createNote($noteData);
    }

    /**
     * Get note by id
     *
     * @param $id
     * @return Builder|Model|object|null
     */
    public static function getNote($id) {
        return self::with('creator')->findOrFail($id);
    }

    /**
     * Get all notes for model respecting the public/private note property
     *
     * @param Request $request
     * @param string $model
     * @param int $modelId
     * @return Builder[]|Collection
     */
    public static function getNotesForModel(Request $request, string $model, int $modelId) {
        $isAdmin = $request->user()->hasRole('admin');

        $field = null;

        switch ($model) {
            case Animal::class: $field = 'animal_id';
                                break;
            case Litter::class: $field = 'litter_id';
                                break;
        }

        $notes = self::with('creator')->where($field, '=', $modelId);

        if (!$isAdmin) {
            // If user is not admin, show only public notes and notes from that user
            $notes = $notes->where(function ($query) {
                $query->where('public', '=', 1)->orWhere('creator_id', '=', Auth::id());
            });
        }

        return $notes->orderByDesc('created_at')->get();
    }

    public static function getValidationRules() {
        $categories = ['general', 'warning', 'alert'];

        return [
            'animal_id' => ['present', 'nullable', 'integer'],
            'litter_id' => ['present', 'nullable', 'integer'],
            'category' => ['required', 'string', Rule::in($categories)],
            'note' => ['required', 'string', 'max:500'],
            'public' => ['required', 'boolean'],
        ];
    }
}
