<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Asset;
use App\Models\Folder;
use App\Services\AssetUploadService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\File;

class AssetController extends Controller
{
    // The "Main View" - Acts like opening a folder
    public function index($folderId = null)
    {
        // 1. If no folder specified, get the Root Folders (parent_id is NULL)
        // 2. If folder specified, get that folder's contents

        $currentFolder = $folderId ? Folder::findOrFail($folderId) : null;

        // Fetch Sub-folders
        $subFolders = Folder::where('parent_id', $folderId)->get();

        // Fetch Files (Assets) — sorted by date taken (newest first)
        $assetsQuery = Asset::where('folder_id', $folderId)
            ->orderByDesc('taken_at')
            ->orderByDesc('created_at');

        if (!$this->isAdmin()) {
            $adminIds = User::where('role', 'admin')->pluck('id');
            $assetsQuery->where(function ($query) use ($adminIds) {
                $query->where('uploaded_by', Auth::id())
                    ->orWhereIn('uploaded_by', $adminIds);
            });
        }

        $assets = $assetsQuery->get();

        // Breadcrumb Logic (To show: Home > 2026 > January)
        $breadcrumbs = [];
        if ($currentFolder) {
            $temp = $currentFolder;
            while ($temp) {
                array_unshift($breadcrumbs, $temp);
                $temp = $temp->parent;
            }
        }

        return view('assets.index', compact('currentFolder', 'subFolders', 'assets', 'breadcrumbs'));
    }

    // The "Smart Upload" Action
    public function store(Request $request, AssetUploadService $uploader)
    {
        $rules = [
            'folder_id' => 'nullable|exists:folders,id',
            'files' => 'required',
            'files.*' => 'file|max:51200' // Max 50MB per file
        ];

        if (!$this->isAdmin()) {
            $rules['files.*'] = 'image|mimes:jpg,jpeg,png,gif,webp|max:51200';
        }

        $request->validate($rules);

        $folderId = $request->input('folder_id'); // Can be null (Root)

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                // Pass to our Service to handle logic/EXIF sorting
                $uploader->handle($file, $folderId);
            }
        }

        return back()->with('success', 'Files uploaded and sorted successfully!');
    }

    // Create a New Folder Manually
    public function createFolder(Request $request)
    {
        if (!$this->isAdmin()) {
            abort(403);
        }
        $request->validate([
            'name' => 'required|string|max:255',
            'folder_id' => 'nullable|exists:folders,id',
        ]);

        Folder::create([
            'name' => $request->name,
            'parent_id' => $request->folder_id, // Can be null
            'type' => 'custom'
        ]);

        return back()->with('success', 'Folder created.');
    }

    // Delete Logic
    public function destroy(Asset $asset)
    {
        if (!$this->isAdmin()) {
            abort(403);
        }
        // Delete physical file
        Storage::disk('public')->delete($asset->file_path);

        // Delete DB record
        $asset->delete();

        return back()->with('success', 'File deleted.');
    }

    public function download(Asset $asset)
    {
        if (!$this->isAdmin()) {
            $adminIds = User::where('role', 'admin')->pluck('id');
            if ($asset->uploaded_by !== Auth::id() && !$adminIds->contains($asset->uploaded_by)) {
                abort(403);
            }

            $fullPath = Storage::disk('public')->path($asset->file_path);
            $mimeType = $asset->mime_type ?: (File::exists($fullPath) ? File::mimeType($fullPath) : null);
            if (!$mimeType || !str_starts_with($mimeType, 'image/')) {
                abort(403);
            }
        }

        $fullPath = Storage::disk('public')->path($asset->file_path);

        if (!File::exists($fullPath)) {
            return back()->with('error', 'File is no longer available (lost after server redeploy).');
        }

        return response()->download($fullPath, $asset->original_name);
    }

    public function preview(Asset $asset)
    {
        if (!$this->isAdmin()) {
            $adminIds = User::where('role', 'admin')->pluck('id');
            if ($asset->uploaded_by !== Auth::id() && !$adminIds->contains($asset->uploaded_by)) {
                abort(403);
            }

            $fullPath = Storage::disk('public')->path($asset->file_path);
            $mimeType = $asset->mime_type ?: (File::exists($fullPath) ? File::mimeType($fullPath) : null);
            if (!$mimeType || !str_starts_with($mimeType, 'image/')) {
                abort(403);
            }
        }

        $fullPath = Storage::disk('public')->path($asset->file_path);

        // If file doesn't exist (e.g. lost after container redeploy), return a placeholder
        if (!File::exists($fullPath)) {
            return $this->placeholderResponse($asset->original_name);
        }

        $mimeType = $asset->mime_type ?: File::mimeType($fullPath);

        return response()->file($fullPath, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    /**
     * Return a placeholder SVG when the original file is missing (ephemeral storage).
     */
    private function placeholderResponse(string $filename): \Illuminate\Http\Response
    {
        $name = e(\Illuminate\Support\Str::limit($filename, 20));
        $svg = <<<SVG
        <svg xmlns="http://www.w3.org/2000/svg" width="300" height="200" viewBox="0 0 300 200">
            <rect width="300" height="200" fill="#F1F5F9" rx="8"/>
            <text x="150" y="85" text-anchor="middle" fill="#94A3B8" font-family="sans-serif" font-size="32">📷</text>
            <text x="150" y="115" text-anchor="middle" fill="#94A3B8" font-family="sans-serif" font-size="12">File unavailable</text>
            <text x="150" y="135" text-anchor="middle" fill="#CBD5E1" font-family="sans-serif" font-size="10">{$name}</text>
            <text x="150" y="155" text-anchor="middle" fill="#CBD5E1" font-family="sans-serif" font-size="9">(lost after redeploy)</text>
        </svg>
        SVG;

        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    private function isAdmin(): bool
    {
        return Auth::check() && Auth::user()?->role === 'admin';
    }
}