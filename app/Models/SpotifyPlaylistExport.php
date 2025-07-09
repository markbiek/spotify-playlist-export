<?php
/**
 * This file is part of the Spotify Playlist Export application.
 *
 * @category Model
 * @package  App\Models
 * @author   Mark Biek <mark.biek@automattic.com>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     https://github.com/user/spotify-playlist-export
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * Model representing a Spotify playlist export job.
 *
 * @category Model
 * @package  App\Models
 * @author   Mark Biek <mark.biek@automattic.com>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     https://github.com/user/spotify-playlist-export
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
     * Boot the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(
            /**
             * Handle the model "deleting" event.
             * Deletes the export folder and the .zip file if they exist.
             *
             * @param SpotifyPlaylistExport $export The export being deleted.
             * @return void
             */
            function ($export) {
                // Delete the .zip file if it exists.
				$zipPath = 'exports/' . $export->export_folder . '.zip';
				if (Storage::exists($zipPath)) {
					Storage::delete($zipPath);
				}
            }
        );
    }

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
        if (!empty($this->export_folder)) {
            return $this->export_folder;
        }
        return sprintf(
            '%d-%s',
            $this->user_id,
            $this->created_at->format('YmdHis')
        );
    }
}
