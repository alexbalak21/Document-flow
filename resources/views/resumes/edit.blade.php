<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Éditer : {{ $resume->name }}
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('resumes.show', $resume) }}" class="bg-gray-100 text-gray-700 px-3 py-1.5 rounded text-sm hover:bg-gray-200">Aperçu</a>
                <a href="{{ route('resumes.export-md', $resume) }}" class="bg-green-600 text-white px-3 py-1.5 rounded text-sm hover:bg-green-700">Export .md</a>
                <a href="{{ route('resumes.index') }}" class="bg-gray-100 text-gray-700 px-3 py-1.5 rounded text-sm hover:bg-gray-200">← Retour</a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 max-w-5xl mx-auto px-4 space-y-8">

        @if(session('success'))
            <div class="bg-green-100 text-green-800 px-4 py-2 rounded">{{ session('success') }}</div>
        @endif

        {{-- ── INFOS GÉNÉRALES ─────────────────────────────────── --}}
        <section class="bg-white shadow rounded-lg p-6">
            <h3 class="font-semibold text-lg text-gray-700 mb-4">Informations générales</h3>
            <form action="{{ route('resumes.update', $resume) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf @method('PATCH')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nom du CV</label>
                        <input type="text" name="name" value="{{ $resume->name }}"
                               class="w-full border border-gray-300 rounded px-3 py-2" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Langue</label>
                        <select name="language" class="w-full border border-gray-300 rounded px-3 py-2">
                            <option value="fr" {{ $resume->language === 'fr' ? 'selected' : '' }}>Français</option>
                            <option value="en" {{ $resume->language === 'en' ? 'selected' : '' }}>English</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Photo de profil</label>
                    @if($resume->profile_image_base64)
                        <img src="{{ $resume->profile_image_base64 }}" class="w-20 h-20 rounded-full object-cover mb-2">
                    @endif
                    <input type="file" name="profile_image" accept="image/*" class="text-sm text-gray-600">
                </div>
                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 text-sm">
                    Enregistrer
                </button>
            </form>
        </section>

        {{-- ── SECTIONS MARKDOWN ───────────────────────────────── --}}
        <section class="bg-white shadow rounded-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-lg text-gray-700">Sections Markdown</h3>
                <a href="{{ route('resumes.sections.create', $resume) }}" class="bg-indigo-600 text-white px-3 py-1.5 rounded text-sm hover:bg-indigo-700">+ Section</a>
            </div>
            @forelse($resume->sections as $section)
                <div class="border border-gray-200 rounded p-4 mb-3">
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-medium text-gray-800">{{ $section->title }}</span>
                        <div class="flex gap-2">
                            <a href="{{ route('resumes.sections.edit', [$resume, $section]) }}" class="text-indigo-600 text-sm hover:underline">Éditer</a>
                            <form action="{{ route('resumes.sections.destroy', [$resume, $section]) }}" method="POST" onsubmit="return confirm('Supprimer ?')">
                                @csrf @method('DELETE')
                                <button class="text-red-500 text-sm hover:underline">Supprimer</button>
                            </form>
                        </div>
                    </div>
                    @if($section->markdown_content)
                        <pre class="text-xs text-gray-500 bg-gray-50 rounded p-2 overflow-auto max-h-24">{{ Str::limit($section->markdown_content, 200) }}</pre>
                    @endif
                </div>
            @empty
                <p class="text-gray-400 text-sm">Aucune section. Ajoutez HEADER, PROFILE, CONTACT, HOBBIES…</p>
            @endforelse
        </section>

        {{-- ── EXPÉRIENCES ─────────────────────────────────────── --}}
        @include('resumes.partials.experience-list')

        {{-- ── FORMATIONS ──────────────────────────────────────── --}}
        @include('resumes.partials.education-list')

        {{-- ── CERTIFICATIONS ──────────────────────────────────── --}}
        @include('resumes.partials.certification-list')

        {{-- ── COMPÉTENCES ─────────────────────────────────────── --}}
        @include('resumes.partials.skill-list')

        {{-- ── LANGUES ─────────────────────────────────────────── --}}
        @include('resumes.partials.language-list')

        {{-- ── SOFT SKILLS ─────────────────────────────────────── --}}
        @include('resumes.partials.softskill-list')

    </div>
</x-app-layout>
