<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ isset($education) ? 'Éditer' : 'Ajouter' }} une formation
        </h2>
    </x-slot>

    <div class="py-10 max-w-xl mx-auto px-4">
        <div class="bg-white shadow rounded-lg p-6">
            <form action="{{ isset($education) ? route('resumes.educations.update', [$resume, $education]) : route('resumes.educations.store', $resume) }}"
                  method="POST" class="space-y-4">
                @csrf
                @if(isset($education)) @method('PATCH') @endif

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">École / Établissement *</label>
                    <input type="text" name="school" value="{{ old('school', $education->school ?? '') }}"
                           class="w-full border border-gray-300 rounded px-3 py-2" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Diplôme *</label>
                    <input type="text" name="diploma" value="{{ old('diploma', $education->diploma ?? '') }}"
                           class="w-full border border-gray-300 rounded px-3 py-2" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Année *</label>
                    <input type="text" name="year" value="{{ old('year', $education->year ?? '') }}"
                           class="w-full border border-gray-300 rounded px-3 py-2" placeholder="2021" required>
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="bg-indigo-600 text-white px-5 py-2 rounded hover:bg-indigo-700">Enregistrer</button>
                    <a href="{{ route('resumes.edit', $resume) }}" class="bg-gray-100 text-gray-700 px-5 py-2 rounded hover:bg-gray-200">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
