<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class AssignmentSubmissionFile extends Model
{
    protected $fillable = [
        'assignment_submission_id',
        'file_path',
        'original_name',
        'mime_type',
        'file_size',
    ];

    protected $casts = [
        'file_size' => 'integer',
    ];

    public function submission(): BelongsTo
    {
        return $this->belongsTo(AssignmentSubmission::class, 'assignment_submission_id');
    }

    public function absolutePath(): string
    {
        return Storage::disk('public')->path($this->file_path);
    }

    public function existsOnDisk(): bool
    {
        return Storage::disk('public')->exists($this->file_path);
    }

    public function isPreviewableInline(): bool
    {
        $mime = (string) $this->mime_type;
        $name = strtolower($this->original_name);

        if (str_starts_with($mime, 'image/') || $mime === 'application/pdf') {
            return true;
        }

        return str_ends_with($name, '.pdf')
            || str_ends_with($name, '.jpg')
            || str_ends_with($name, '.jpeg')
            || str_ends_with($name, '.png')
            || str_ends_with($name, '.webp');
    }

    public function humanSize(): string
    {
        $bytes = (int) $this->file_size;
        if ($bytes < 1024) {
            return $bytes.' B';
        }
        if ($bytes < 1048576) {
            return number_format($bytes / 1024, 1).' KB';
        }

        return number_format($bytes / 1048576, 1).' MB';
    }
}
