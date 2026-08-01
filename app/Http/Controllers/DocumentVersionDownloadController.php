<?php

namespace App\Http\Controllers;

use App\Models\DocumentVersion;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentVersionDownloadController extends Controller
{
    // v1 authorization decision: ANY authenticated user may download ANY document version.
    // EUROFOND OS is currently a single-PM app with no per-project membership/ownership concept,
    // so there is nothing meaningful to scope the check against yet. This is pinned by a
    // contract test (see DocumentDownloadTest) and MUST be revisited once roles/ownership land.
    public function __invoke(DocumentVersion $version): StreamedResponse
    {
        abort_if($version->file_path === null, 404);

        // Path guard: file_path is only ever written by our own upload code (always prefixed
        // with "documents/"), but the version is user-suppliable via route-model binding, so we
        // defensively refuse to serve anything outside that prefix rather than trust the column.
        abort_unless(str_starts_with($version->file_path, 'documents/'), 404);

        abort_unless(Storage::disk('local')->exists($version->file_path), 404);

        $filename = $version->original_filename ?? basename($version->file_path);

        // Strip control characters (e.g. \r\n): PHP's header() silently drops the entire
        // Content-Disposition header if the value contains a raw CR/LF, which would degrade
        // the download UX without any visible error. The /u modifier makes preg_replace return
        // null if $filename isn't valid UTF-8 (rather than stripping nothing), so fall back to
        // the safe on-disk basename in that case instead of feeding null into the header below.
        $filename = preg_replace('/[\x00-\x1F\x7F]/u', '', $filename) ?? basename($version->file_path);

        // Storage::download()'s default Content-Disposition builder (Symfony's
        // HeaderUtils::makeDisposition) ASCII-folds the plain filename= parameter for any
        // non-ASCII name and only puts the real name in filename*=UTF-8''..., so browsers/tests
        // reading filename= would see a mangled name. We set Content-Disposition explicitly to
        // serve the exact UTF-8 filename in filename=, while still including the RFC 6266
        // filename*=UTF-8''... extended parameter for user agents that prefer it.
        $escaped = str_replace(['\\', '"'], ['\\\\', '\\"'], $filename);

        return Storage::disk('local')->download(
            $version->file_path,
            $filename,
            [
                'Content-Disposition' => 'attachment; filename="'.$escaped.'"; filename*=UTF-8\'\''.rawurlencode($filename),
                'X-Content-Type-Options' => 'nosniff',
                'Cache-Control' => 'private, no-store',
            ],
        );
    }
}
