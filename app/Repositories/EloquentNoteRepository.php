<?php

namespace App\Repositories;

use App\Models\Note;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentNoteRepository implements NoteRepositoryInterface
{
    public function all(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return Note::query()
            ->where('user_id', $user->id)
            ->orderByDesc('is_pinned')
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function find(int $id, User $user): Note
    {
        return Note::query()
            ->where('user_id', $user->id)
            ->findOrFail($id);
    }

    public function create(array $data, User $user): Note
    {
        return Note::create([
            'is_pinned' => false,
            ...$data,
            'user_id' => $user->id,
        ]);
    }

    public function update(int $id, array $data, User $user): Note
    {
        $note = $this->find($id, $user);
        $note->update($data);

        return $note->fresh();
    }

    public function delete(int $id, User $user): bool
    {
        return (bool) $this->find($id, $user)->delete();
    }

    public function countForUser(User $user): int
    {
        return Note::query()->where('user_id', $user->id)->count();
    }
}
