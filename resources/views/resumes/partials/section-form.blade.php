<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ isset($section) ? 'Éditer' : 'Ajouter' }} une section
        </h2>
    </x-slot>

    <div class="py-10 max-w-2xl mx-auto px-4">
        <div class="bg-white shadow rounded-lg p-6">
            <form action="{{ isset($section) ? route('resumes.sections.update', [$resume, $section]) : route('resumes.sections.store', $resume) }}"
                  method="POST" class="space-y-4">
                @csrf
                @if(isset($section)) @method('PATCH') @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Titre affiché *</label>
                        <input type="text" name="title" value="{{ old('title', $section->title ?? '') }}"
                               class="w-full border border-gray-300 rounded px-3 py-2" placeholder="Profil, Contact…" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Type de section (clé)</label>
                        <select name="section_type" class="w-full border border-gray-300 rounded px-3 py-2">
                            @foreach(['header','profile','contact','hobbies','soft_skills','custom'] as $t)
                                <option value="{{ $t }}" {{ old('section_type', $section->section_type ?? '') === $t ? 'selected' : '' }}>
                                    {{ strtoupper($t) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Icône (FontAwesome slug)</label>
                        <input type="text" name="icon" value="{{ old('icon', $section->icon ?? '') }}"
                               class="w-full border border-gray-300 rounded px-3 py-2" placeholder="user, briefcase…">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Ordre</label>
                        <input type="number" name="order_index" value="{{ old('order_index', $section->order_index ?? 0) }}"
                               class="w-full border border-gray-300 rounded px-3 py-2">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Contenu Markdown</label>
                    <textarea name="markdown_content" rows="12"
                              class="w-full border border-gray-300 rounded px-3 py-2 font-mono text-sm">{{ old('markdown_content', $section->markdown_content ?? '') }}</textarea>
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="bg-indigo-600 text-white px-5 py-2 rounded hover:bg-indigo-700">Enregistrer</button>
                    <a href="{{ route('resumes.edit', $resume) }}" class="bg-gray-100 text-gray-700 px-5 py-2 rounded hover:bg-gray-200">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
