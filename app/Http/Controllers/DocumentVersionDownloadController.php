<?php

namespace App\Http\Controllers;

use App\Models\DocumentVersion;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentVersionDownloadController extends Controller
{
    public function __invoke(DocumentVersion $version): StreamedResponse
    {
        abort_if($version->file_path === null, 404);
        abort_unless(Storage::disk('local')->exists($version->file_path), 404);

        $filename = $version->original_filename ?? basename($version->file_path);

        return Storage::disk('local')->download(
            $version->file_path,
            $filename,
            ['Content-Disposition' => 'attachment; filename="'.addslashes($filename).'"'],
        );
    }
}
