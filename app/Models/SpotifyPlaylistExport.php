<?php
/**
 * This file is part of the Spotify Playlist Export application.
 *
 * @package App\Models
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model representing a Spotify playlist export job.
 *
 * @property int     $id
 * @property int     $user_id
 * @property bool    $finished
 * @property int     $playlist_count
 * @property int     $playlists_exported
 * @property string  $folder_name
 */
class SpotifyPlaylistExport extends Model
{
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
     * The accessors to append to the model's array form.
     *
     * @var array<string>
     */
    protected $appends = [
        'folder_name',
    ];

    /**
     * Get the user that owns the export.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo The relationship to the user model.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the folder name for this export.
     *
     * @return string The folder name in the format {user_id}-{created_at_timestamp}.
     */
    public function getFolderNameAttribute(): string
    {
        return sprintf(
            '%d-%s',
            $this->user_id,
            $this->created_at->format('YmdHis')
        );
    }
}
