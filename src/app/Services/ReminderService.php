<?php

namespace App\Services;

use App\Models\Reminder;
use Illuminate\Database\Eloquent\Collection;

class ReminderService
{
    public function list(int $take): Collection
    {
        return Reminder::orderBy('remind_at', 'asc')
            ->take($take)
            ->get();
    }

    public function find(int $id): ?Reminder
    {
        return Reminder::find($id);
    }

    public function create(array $data): Reminder
    {
        $data['created_by'] = auth()->id();
        
        return Reminder::create($data);
    }

    public function update(int $id, array $data): ?Reminder
    {
        $reminder = Reminder::find($id);
        if ($reminder) {
            $reminder->update($data);
        }

        return $reminder;
    }

    public function delete(int $id): bool
    {
        $reminder = Reminder::find($id);
        if ($reminder) {
            return $reminder->delete();
        }

        return false;
    }
}