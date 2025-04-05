<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpotifyPlaylistExport extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
    'user_id',
    'finished',
    'playlist_count',
    'playlists_exported',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
    'finished' => 'boolean',
    'playlist_count' => 'integer',
    'playlists_exported' => 'integer',
    ];

    /**
     * Get the user that owns the export.
     */
    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }
}
