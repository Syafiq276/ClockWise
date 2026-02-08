@extends('layouts.app')

@section('title', 'Assets')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="flex flex-col gap-2 mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Asset Library</h1>
        <p class="text-gray-600">Upload, organize, and manage files by folder.</p>
    </div>

    <style>
        .asset-toolbar button {
            border: 1px solid #e5e7eb;
            background: white;
        }
        .asset-toolbar button.active {
            background: #eef2ff;
            border-color: #c7d2fe;
            color: #4338ca;
        }
        .asset-list {
            display: flex;
            flex-direction: column;
            gap: 0;
        }
        .asset-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 16px;
            border-bottom: 1px solid #f1f5f9;
            gap: 12px;
            flex-wrap: wrap;
        }
        @media (min-width: 640px) {
            .asset-item {
                padding: 16px 24px;
                gap: 16px;
                flex-wrap: nowrap;
            }
        }
        .asset-item:last-child {
            border-bottom: 0;
        }
        .asset-main {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
        }
        .asset-thumb {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            object-fit: cover;
            background: #f1f5f9;
            flex-shrink: 0;
        }
        .asset-grid .asset-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
            gap: 12px;
            padding: 12px;
        }
        @media (min-width: 640px) {
            .asset-grid .asset-list {
                grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
                gap: 16px;
                padding: 16px;
            }
        }
        .asset-grid .asset-item {
            flex-direction: column;
            align-items: flex-start;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            padding: 14px;
        }
        .asset-grid .asset-main {
            flex-direction: column;
            align-items: flex-start;
            width: 100%;
        }
        .asset-grid .asset-thumb {
            width: 100%;
            height: 140px;
            border-radius: 12px;
        }
        .asset-grid .asset-actions {
            width: 100%;
            justify-content: space-between;
        }
    </style>

    @if(session('success'))
        <div class="mb-4 rounded-lg bg-green-50 p-3 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    <div class="mb-6 flex flex-wrap gap-3 items-center">
        <a href="{{ route('assets.index') }}" class="text-sm text-blue-600 hover:text-blue-800">Root</a>
        @foreach($breadcrumbs as $crumb)
            <span class="text-gray-400">/</span>
            <a href="{{ route('assets.index', $crumb->id) }}" class="text-sm text-blue-600 hover:text-blue-800">
                {{ $crumb->name }}
            </a>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-xl shadow-md overflow-hidden">
                <div class="px-6 py-4 border-b flex items-center justify-between">
                    <h2 class="font-semibold text-gray-800">Folders</h2>
                </div>
                <div class="divide-y">
                    @forelse($subFolders as $folder)
                        <a href="{{ route('assets.index', $folder->id) }}" class="block px-6 py-4 hover:bg-gray-50">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <span class="text-blue-500">📁</span>
                                    <span class="font-medium text-gray-800">{{ $folder->name }}</span>
                                </div>
                                <span class="text-xs text-gray-500">{{ ucfirst($folder->type) }}</span>
                            </div>
                        </a>
                    @empty
                        <div class="px-6 py-6 text-sm text-gray-500">No folders found.</div>
                    @endforelse
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md overflow-hidden asset-panel" id="assetPanel">
                <div class="px-6 py-4 border-b flex items-center justify-between">
                    <h2 class="font-semibold text-gray-800">Files</h2>
                    <div class="asset-toolbar flex items-center gap-2">
                        <button type="button" id="viewList" class="px-3 py-1.5 rounded-lg text-sm">List</button>
                        <button type="button" id="viewGrid" class="px-3 py-1.5 rounded-lg text-sm">Grid</button>
                    </div>
                </div>
                <div class="asset-list">
                    @forelse($assets as $asset)
                        <div class="asset-item">
                            <div class="asset-main">
                                @if($asset->mime_type && str_starts_with($asset->mime_type, 'image/'))
                                    <img src="{{ route('assets.preview', $asset) }}" alt="{{ $asset->original_name }}" class="asset-thumb">
                                @else
                                    <div class="asset-thumb flex items-center justify-center text-slate-400">📄</div>
                                @endif
                                <div class="min-w-0">
                                    <p class="font-medium text-gray-800 truncate">{{ $asset->original_name }}</p>
                                    <p class="text-xs text-gray-500">
                                        {{ number_format($asset->size / 1024, 1) }} KB
                                        @if($asset->taken_at)
                                            · <span title="Date taken: {{ $asset->taken_at->format('Y-m-d H:i:s') }}">📷 {{ $asset->taken_at->format('d M Y, h:ia') }}</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <div class="asset-actions flex items-center gap-3">
                                <a href="{{ route('assets.download', $asset) }}" class="text-sm text-blue-600 hover:text-blue-800">Download</a>
                                @if(auth()->user()->role === 'admin')
                                    <a href="{{ route('assets.preview', $asset) }}" target="_blank" class="text-sm text-gray-600 hover:text-gray-800">View</a>
                                    <form method="POST" action="{{ route('assets.destroy', $asset) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-sm text-red-600 hover:text-red-800" type="submit">Delete</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="px-6 py-6 text-sm text-gray-500">No files uploaded.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-xl shadow-md p-6">
                <h3 class="font-semibold text-gray-800 mb-3">Upload Files</h3>
                <form method="POST" action="{{ route('assets.upload') }}" enctype="multipart/form-data" class="space-y-3">
                    @csrf
                    <input type="hidden" name="folder_id" value="{{ $currentFolder?->id }}">
                    <input type="file" name="files[]" multiple class="w-full border rounded-lg px-3 py-2" @if(auth()->user()->role !== 'admin') accept="image/*" @endif><br>
                    <br><button class="btn-primary px-4 py-2 rounded-lg w-full" type="submit">Upload</button>
                </form>
            </div>

            @if(auth()->user()->role === 'admin')
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h3 class="font-semibold text-gray-800 mb-3">Create Folder</h3>
                    <form method="POST" action="{{ route('assets.folder.create') }}" class="space-y-3">
                        @csrf
                        <input type="hidden" name="folder_id" value="{{ $currentFolder?->id }}">
                        <input type="text" name="name" placeholder="Folder name" class="w-full border rounded-lg px-3 py-2">
                        <button class="btn-primary px-4 py-2 rounded-lg w-full" type="submit">Create</button>
                    </form>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
    (function () {
        const panel = document.getElementById('assetPanel');
        const listBtn = document.getElementById('viewList');
        const gridBtn = document.getElementById('viewGrid');

        if (!panel || !listBtn || !gridBtn) {
            return;
        }

        const applyView = (view) => {
            if (view === 'grid') {
                panel.classList.add('asset-grid');
                gridBtn.classList.add('active');
                listBtn.classList.remove('active');
            } else {
                panel.classList.remove('asset-grid');
                listBtn.classList.add('active');
                gridBtn.classList.remove('active');
            }
            localStorage.setItem('assetViewMode', view);
        };

        listBtn.addEventListener('click', () => applyView('list'));
        gridBtn.addEventListener('click', () => applyView('grid'));

        const stored = localStorage.getItem('assetViewMode') || 'list';
        applyView(stored);
    })();
</script>
@endsection
