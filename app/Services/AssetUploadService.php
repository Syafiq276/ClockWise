<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\Folder;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class AssetUploadService
{
    /**
     * Handle file upload: extract date-taken, auto-sort into Year/Month folders.
     */
    public function handle(UploadedFile $file, ?int $folderId = null): Asset
    {
        // 1. Extract the date the photo/file was created/taken
        $takenAt = $this->extractDateTaken($file);

        // 2. Resolve target folder (manual folder or auto-sort by date)
        $targetFolder = $folderId
            ? Folder::findOrFail($folderId)
            : $this->resolveDateFolder($takenAt);

        // 3. Generate unique filename & store
        $extension = $file->getClientOriginalExtension();
        $safeExtension = $extension ? strtolower($extension) : 'bin';
        $fileName = (string) Str::uuid() . '.' . $safeExtension;

        $pathPrefix = $targetFolder?->path ?? 'assets/root';
        $filePath = $file->storeAs($pathPrefix, $fileName, 'public');

        return Asset::create([
            'folder_id'     => $targetFolder?->id,
            'uploaded_by'   => Auth::id(),
            'original_name' => $file->getClientOriginalName(),
            'file_name'     => $fileName,
            'file_path'     => $filePath,
            'mime_type'     => $file->getClientMimeType(),
            'size'          => $file->getSize(),
            'taken_at'      => $takenAt,
        ]);
    }

    /**
     * Create / find Year > Month folders based on the resolved date.
     */
    private function resolveDateFolder(?Carbon $date): ?Folder
    {
        $date = $date ?? now();

        $yearFolder = Folder::firstOrCreate(
            ['name' => $date->format('Y'), 'parent_id' => null],
            ['type' => 'auto']
        );

        $monthFolder = Folder::firstOrCreate(
            ['name' => $date->format('F'), 'parent_id' => $yearFolder->id],
            ['type' => 'auto']
        );

        return $monthFolder;
    }

    // ------------------------------------------------------------------
    //  Date-Taken Detection  (priority order)
    // ------------------------------------------------------------------

    /**
     * Try multiple strategies to determine when the photo/file was originally
     * created or taken. Returns null only if nothing at all can be determined.
     *
     * Priority:
     *   1. EXIF  DateTimeOriginal / DateTimeDigitized / DateTime
     *   2. EXIF  GPSDateStamp + GPSTimeStamp
     *   3. XMP   DateCreated  (for PNGs, TIFFs that embed XMP)
     *   4. File   last-modified time (decent fallback for screenshots, etc.)
     */
    private function extractDateTaken(UploadedFile $file): ?Carbon
    {
        $path = $file->getRealPath();
        if (!$path) {
            return null;
        }

        // Strategy 1 & 2 – EXIF (JPEG / TIFF)
        $date = $this->fromExif($path);
        if ($date) {
            return $date;
        }

        // Strategy 3 – XMP embedded in the file (PNG, TIFF, PDF, etc.)
        $date = $this->fromXmp($path);
        if ($date) {
            return $date;
        }

        // Strategy 4 – file's last-modified timestamp
        $date = $this->fromFileModifiedTime($path);
        if ($date) {
            return $date;
        }

        return null;
    }

    // ---------- Strategy 1 & 2: EXIF ----------

    private function fromExif(string $path): ?Carbon
    {
        if (!function_exists('exif_read_data')) {
            return null;
        }

        try {
            $exif = @exif_read_data($path, 'ANY_TAG', true);
            if (!$exif) {
                return null;
            }

            // Flatten sections if present
            $flat = [];
            foreach ($exif as $section) {
                if (is_array($section)) {
                    $flat = array_merge($flat, $section);
                }
            }
            $flat = array_merge($flat, $exif);

            // 1a. Standard EXIF date tags (most reliable)
            foreach (['DateTimeOriginal', 'DateTimeDigitized', 'DateTime'] as $tag) {
                if (!empty($flat[$tag]) && is_string($flat[$tag])) {
                    $parsed = $this->parseExifDate($flat[$tag]);
                    if ($parsed) {
                        return $parsed;
                    }
                }
            }

            // 1b. GPS date + time (some cameras only write GPS timestamps)
            if (!empty($flat['GPSDateStamp'])) {
                return $this->parseGpsDate(
                    $flat['GPSDateStamp'],
                    $flat['GPSTimeStamp'] ?? null
                );
            }
        } catch (\Throwable $e) {
            // Silently ignore unreadable EXIF
        }

        return null;
    }

    private function parseExifDate(string $dateString): ?Carbon
    {
        // Common EXIF format:  "2024:07:15 13:45:00"
        // Sometimes with sub-seconds: "2024:07:15 13:45:00.123"
        $dateString = trim($dateString);
        if ($dateString === '' || $dateString === '0000:00:00 00:00:00') {
            return null;
        }

        // Strip sub-second suffix if present
        $dateString = preg_replace('/\.\d+$/', '', $dateString);

        try {
            return Carbon::createFromFormat('Y:m:d H:i:s', $dateString);
        } catch (\Throwable) {
            // Try alternative format used by some cameras
            try {
                return Carbon::parse($dateString);
            } catch (\Throwable) {
                return null;
            }
        }
    }

    private function parseGpsDate(string $dateStamp, ?array $timeStamp): ?Carbon
    {
        // GPSDateStamp format: "2024:07:15"
        try {
            $date = Carbon::createFromFormat('Y:m:d', trim($dateStamp))->startOfDay();

            if ($timeStamp && count($timeStamp) === 3) {
                $hours   = $this->gpsRationalToFloat($timeStamp[0]);
                $minutes = $this->gpsRationalToFloat($timeStamp[1]);
                $seconds = $this->gpsRationalToFloat($timeStamp[2]);

                $date->setTime((int) $hours, (int) $minutes, (int) $seconds);
            }

            return $date;
        } catch (\Throwable) {
            return null;
        }
    }

    private function gpsRationalToFloat(mixed $value): float
    {
        if (is_string($value) && str_contains($value, '/')) {
            [$num, $den] = explode('/', $value, 2);
            return $den != 0 ? (float) $num / (float) $den : 0;
        }

        return (float) $value;
    }

    // ---------- Strategy 3: XMP ----------

    private function fromXmp(string $path): ?Carbon
    {
        try {
            // Read first 100 KB to look for XMP packet (enough for most embedded XMP)
            $handle = fopen($path, 'rb');
            if (!$handle) {
                return null;
            }

            $chunk = fread($handle, 102400);
            fclose($handle);

            if (!$chunk) {
                return null;
            }

            // Look for common XMP date tags
            $patterns = [
                '/xmp:DateCreated["\s>]*([^<"]+)/i',
                '/photoshop:DateCreated["\s>]*([^<"]+)/i',
                '/exif:DateTimeOriginal["\s>]*([^<"]+)/i',
                '/dc:date["\s>]*([^<"]+)/i',
                '/xmp:CreateDate["\s>]*([^<"]+)/i',
            ];

            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $chunk, $m)) {
                    $parsed = $this->parseIso8601($m[1]);
                    if ($parsed) {
                        return $parsed;
                    }
                }
            }
        } catch (\Throwable) {
            // Silently ignore
        }

        return null;
    }

    private function parseIso8601(string $dateString): ?Carbon
    {
        $dateString = trim($dateString);
        if ($dateString === '') {
            return null;
        }

        try {
            return Carbon::parse($dateString);
        } catch (\Throwable) {
            return null;
        }
    }

    // ---------- Strategy 4: File modification time ----------

    private function fromFileModifiedTime(string $path): ?Carbon
    {
        try {
            $mtime = filemtime($path);
            if ($mtime !== false) {
                return Carbon::createFromTimestamp($mtime);
            }
        } catch (\Throwable) {
            // Ignore
        }

        return null;
    }
}
