<?php

namespace App\Repositories;

use App\Models\Note;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface NoteRepositoryInterface
{
    public function all(User $user): LengthAwarePaginator;

    public function find(int $id, User $user): Note;

    public function create(array $data, User $user): Note;

    public function update(int $id, array $data, User $user): Note;

    public function delete(int $id, User $user): bool;

    public function countForUser(User $user): int;
}
