<?php

namespace App\Console\Commands;

use App\Models\Album;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class DeleteOldAlbums extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:delete-old-albums';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deletes albums older than 15 days.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $albums = Album::where('created_at', '<', Carbon::now()->subDays(15))->whereNotNull('user_id')->get();
        foreach($albums as $album){
            $album->stickers()->delete();
            $album->delete();
            $filesToDelete = Storage::allFiles('images/albums/' . $album->id);
            Storage::delete($filesToDelete);
            Storage::deleteDirectory('images/albums/' . $album->id);
            $this->info('Old albums deleted!');
        }
    }
}
