<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Mes CVs</h2>
            <div class="flex gap-3">
                {{-- Import --}}
                <form action="{{ route('resumes.import-md') }}" method="POST" enctype="multipart/form-data" class="flex gap-2 items-center">
                    @csrf
                    <input type="file" name="markdown_file" accept=".md,.txt" class="text-sm text-gray-600 border rounded px-2 py-1">
                    <button type="submit" class="bg-gray-600 text-white px-3 py-1.5 rounded text-sm hover:bg-gray-700">
                        Importer .md
                    </button>
                </form>
                <a href="{{ route('resumes.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 text-sm">
                    + Nouveau CV
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-10 max-w-5xl mx-auto px-4">
        @if(session('success'))
            <div class="mb-4 bg-green-100 text-green-800 px-4 py-2 rounded">{{ session('success') }}</div>
        @endif

        @if($resumes->isEmpty())
            <div class="text-center text-gray-500 py-20">
                <p class="text-lg mb-4">Aucun CV pour l'instant.</p>
                <a href="{{ route('resumes.create') }}" class="bg-indigo-600 text-white px-5 py-2 rounded hover:bg-indigo-700">
                    Créer mon premier CV
                </a>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($resumes as $resume)
                <div class="bg-white shadow rounded-lg p-5 flex flex-col justify-between">
                    <div>
                        <h3 class="font-bold text-lg text-gray-800">{{ $resume->name }}</h3>
                        <p class="text-sm text-gray-500 mt-1">Langue : {{ strtoupper($resume->language) }}</p>
                        <p class="text-sm text-gray-400 mt-1">Créé le {{ $resume->created_at->format('d/m/Y') }}</p>
                    </div>
                    <div class="mt-4 flex gap-2 flex-wrap">
                        <a href="{{ route('resumes.edit', $resume) }}" class="bg-indigo-600 text-white px-3 py-1.5 rounded text-sm hover:bg-indigo-700">Éditer</a>
                        <a href="{{ route('resumes.show', $resume) }}" class="bg-gray-100 text-gray-700 px-3 py-1.5 rounded text-sm hover:bg-gray-200">Aperçu</a>
                        <a href="{{ route('resumes.export-md', $resume) }}" class="bg-green-600 text-white px-3 py-1.5 rounded text-sm hover:bg-green-700">Export .md</a>
                        <form action="{{ route('resumes.destroy', $resume) }}" method="POST" onsubmit="return confirm('Supprimer ce CV ?')">
                            @csrf @method('DELETE')
                            <button class="bg-red-500 text-white px-3 py-1.5 rounded text-sm hover:bg-red-600">Supprimer</button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>
