<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ isset($certification) ? 'Éditer' : 'Ajouter' }} une certification
        </h2>
    </x-slot>

    <div class="py-10 max-w-xl mx-auto px-4">
        <div class="bg-white shadow rounded-lg p-6">
            <form action="{{ isset($certification) ? route('resumes.certifications.update', [$resume, $certification]) : route('resumes.certifications.store', $resume) }}"
                  method="POST" class="space-y-4">
                @csrf
                @if(isset($certification)) @method('PATCH') @endif

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nom *</label>
                    <input type="text" name="name" value="{{ old('name', $certification->name ?? '') }}"
                           class="w-full border border-gray-300 rounded px-3 py-2" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Organisation *</label>
                    <input type="text" name="organization" value="{{ old('organization', $certification->organization ?? '') }}"
                           class="w-full border border-gray-300 rounded px-3 py-2" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Année</label>
                    <input type="text" name="year" value="{{ old('year', $certification->year ?? '') }}"
                           class="w-full border border-gray-300 rounded px-3 py-2" placeholder="2024">
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="bg-indigo-600 text-white px-5 py-2 rounded hover:bg-indigo-700">Enregistrer</button>
                    <a href="{{ route('resumes.edit', $resume) }}" class="bg-gray-100 text-gray-700 px-5 py-2 rounded hover:bg-gray-200">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
