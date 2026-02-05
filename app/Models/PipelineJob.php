<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class PipelineJob extends Model
{
    use HasUuids;

    public $timestamps = false;

    const CREATED_AT = 'created_at';

    protected $fillable = [
        'job_type',
        'status',
        'started_at',
        'completed_at',
        'error_message',
        'metadata',
        'retry_count',
    ];

    protected $casts = [
        'metadata' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'retry_count' => 'integer',
    ];

    public function scopeRunning($query)
    {
        return $query->where('status', 'running');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('job_type', $type);
    }

    public function start(): void
    {
        $this->update([
            'status' => 'running',
            'started_at' => now(),
        ]);
    }

    public function complete(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    public function fail(string $message): void
    {
        $this->update([
            'status' => 'failed',
            'completed_at' => now(),
            'error_message' => $message,
            'retry_count' => $this->retry_count + 1,
        ]);
    }
}
