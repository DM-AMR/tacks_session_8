<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'status',
        'due_date',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'due_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the validation rules for creating a task.
     *
     * @return array<string, string>
     */
    public static function createRules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'status' => 'nullable|in:pending,in_progress,completed',
            'due_date' => 'nullable|date_format:Y-m-d H:i:s',
        ];
    }

    /**
     * Get the validation rules for updating a task.
     *
     * @return array<string, string>
     */
    public static function updateRules(): array
    {
        return [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'status' => 'nullable|in:pending,in_progress,completed',
            'due_date' => 'nullable|date_format:Y-m-d H:i:s',
        ];
    }
}
