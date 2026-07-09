<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ isset($skill) ? 'Éditer' : 'Ajouter' }} une compétence
        </h2>
    </x-slot>

    <div class="py-10 max-w-xl mx-auto px-4">
        <div class="bg-white shadow rounded-lg p-6">
            <form action="{{ isset($skill) ? route('resumes.skills.update', [$resume, $skill]) : route('resumes.skills.store', $resume) }}"
                  method="POST" class="space-y-4">
                @csrf
                @if(isset($skill)) @method('PATCH') @endif

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nom *</label>
                    <input type="text" name="name" value="{{ old('name', $skill->name ?? '') }}"
                           class="w-full border border-gray-300 rounded px-3 py-2" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Icône (FontAwesome slug)</label>
                    <input type="text" name="icon" value="{{ old('icon', $skill->icon ?? '') }}"
                           class="w-full border border-gray-300 rounded px-3 py-2" placeholder="database, code, python…">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Niveau *</label>
                    <select name="level_type" class="w-full border border-gray-300 rounded px-3 py-2">
                        @foreach(['beginner','intermediate','advanced','expert','percentage'] as $lvl)
                            <option value="{{ $lvl }}" {{ old('level_type', $skill->level_type ?? 'intermediate') === $lvl ? 'selected' : '' }}>
                                {{ ucfirst($lvl) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Valeur % (si niveau = percentage)</label>
                    <input type="number" name="level_value" value="{{ old('level_value', $skill->level_value ?? '') }}"
                           min="0" max="100" class="w-full border border-gray-300 rounded px-3 py-2">
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="bg-indigo-600 text-white px-5 py-2 rounded hover:bg-indigo-700">Enregistrer</button>
                    <a href="{{ route('resumes.edit', $resume) }}" class="bg-gray-100 text-gray-700 px-5 py-2 rounded hover:bg-gray-200">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
