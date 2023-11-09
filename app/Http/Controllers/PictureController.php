<?php

namespace App\Http\Controllers;

use App\Enums\ResponseStatus;
use App\Models\Picture;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PictureController extends Controller
{
    public function destroy(Request $request, Picture $picture)
    {
        abort_unless(Storage::delete($picture->getRawOriginal('name')), ResponseStatus::BAD_REQUEST->value, 'Failed to delete picture from storage');
        abort_unless($picture->delete(), ResponseStatus::BAD_REQUEST->value, 'Failed to delete picture');
        return response()->json([
            'message' => 'Deleted'
        ]);
    }
}
