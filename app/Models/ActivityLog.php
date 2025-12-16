<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    use HasFactory;

    protected $table = 'activity_logs';

    protected $fillable = [
        'user_id',
        'action',
        'description',
        'ip_address',
        'user_agent',
    ];

    /**
     * Get the user that performed the action.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get action badge color.
     */
    public function getActionColorAttribute(): string
    {
        return match($this->action) {
            'create' => 'green',
            'update' => 'yellow',
            'delete' => 'red',
            default => 'gray',
        };
    }

    /**
     * Get action badge text.
     */
    public function getActionTextAttribute(): string
    {
        return match($this->action) {
            'create' => 'Tambah',
            'update' => 'Edit',
            'delete' => 'Hapus',
            default => ucfirst($this->action),
        };
    }
}
