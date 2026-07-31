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

        // Strip control characters (e.g. \r\n): PHP's header() silently drops the entire
        // Content-Disposition header if the value contains a raw CR/LF, which would degrade
        // the download UX without any visible error.
        $filename = preg_replace('/[\x00-\x1F\x7F]/u', '', $filename);

        // Storage::download()'s default Content-Disposition builder (Symfony's
        // HeaderUtils::makeDisposition) ASCII-folds the plain filename= parameter for any
        // non-ASCII name and only puts the real name in filename*=UTF-8''..., so browsers/tests
        // reading filename= would see a mangled name. We set Content-Disposition explicitly to
        // serve the exact UTF-8 filename in filename= instead.
        return Storage::disk('local')->download(
            $version->file_path,
            $filename,
            ['Content-Disposition' => 'attachment; filename="'.str_replace(['\\', '"'], ['\\\\', '\\"'], $filename).'"'],
        );
    }
}
