<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ isset($experience) ? 'Éditer' : 'Ajouter' }} une expérience
        </h2>
    </x-slot>

    <div class="py-10 max-w-2xl mx-auto px-4">
        <div class="bg-white shadow rounded-lg p-6">
            <form action="{{ isset($experience) ? route('resumes.experiences.update', [$resume, $experience]) : route('resumes.experiences.store', $resume) }}"
                  method="POST" class="space-y-4">
                @csrf
                @if(isset($experience)) @method('PATCH') @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Poste *</label>
                        <input type="text" name="position_title" value="{{ old('position_title', $experience->position_title ?? '') }}"
                               class="w-full border border-gray-300 rounded px-3 py-2" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Entreprise *</label>
                        <input type="text" name="company" value="{{ old('company', $experience->company ?? '') }}"
                               class="w-full border border-gray-300 rounded px-3 py-2" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Lieu</label>
                        <input type="text" name="location" value="{{ old('location', $experience->location ?? '') }}"
                               class="w-full border border-gray-300 rounded px-3 py-2" placeholder="Lyon, France">
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Début *</label>
                            <input type="text" name="start_date" value="{{ old('start_date', $experience->start_date ?? '') }}"
                                   class="w-full border border-gray-300 rounded px-3 py-2" placeholder="Jul 2024" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Fin</label>
                            <input type="text" name="end_date" value="{{ old('end_date', $experience->end_date ?? '') }}"
                                   class="w-full border border-gray-300 rounded px-3 py-2" placeholder="Present">
                        </div>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="5"
                              class="w-full border border-gray-300 rounded px-3 py-2 font-mono text-sm"
                              placeholder="- Bullet point&#10;- Another achievement">{{ old('description', $experience->description ?? '') }}</textarea>
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="bg-indigo-600 text-white px-5 py-2 rounded hover:bg-indigo-700">Enregistrer</button>
                    <a href="{{ route('resumes.edit', $resume) }}" class="bg-gray-100 text-gray-700 px-5 py-2 rounded hover:bg-gray-200">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
