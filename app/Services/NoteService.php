<?php

namespace App\Services;

use App\Events\NoteCreated;
use App\Exceptions\NoteLimitExceededException;
use App\Models\Note;
use App\Models\User;
use App\Repositories\NoteRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class NoteService
{
    public const MAX_NOTES_PER_USER = 100;

    public function __construct(private readonly NoteRepositoryInterface $notes) {}

    public function list(User $user): LengthAwarePaginator
    {
        return $this->notes->all($user);
    }

    public function find(int $id, User $user): Note
    {
        return $this->notes->find($id, $user);
    }

    public function create(array $data, User $user): Note
    {
        if ($this->notes->countForUser($user) >= self::MAX_NOTES_PER_USER) {
            throw new NoteLimitExceededException(self::MAX_NOTES_PER_USER);
        }

        $note = $this->notes->create($data, $user);

        event(new NoteCreated($note));

        return $note;
    }

    public function update(int $id, array $data, User $user): Note
    {
        return $this->notes->update($id, $data, $user);
    }

    public function delete(int $id, User $user): bool
    {
        return $this->notes->delete($id, $user);
    }
}
