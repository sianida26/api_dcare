<?php

namespace App\Models;

use App\Actions\HasCoverPhoto;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Article extends Model
{
    use HasFactory, HasCoverPhoto;

    protected $fillable = [
        'title',
        'content',
    ];

    protected $appends = [
        'cover_url',
    ];

    /**
     * Get the user that owns the Article
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function addViews(): void
    {
        $this->forceFill([
            'views' => ++$this->views,
        ])->save();
    }
}
