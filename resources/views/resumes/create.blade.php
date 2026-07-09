<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Nouveau CV</h2>
    </x-slot>

    <div class="py-10 max-w-xl mx-auto px-4">
        <div class="bg-white shadow rounded-lg p-6">
            <form action="{{ route('resumes.store') }}" method="POST" class="space-y-5">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nom du CV</label>
                    <input type="text" name="name" value="{{ old('name') }}"
                           class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-400"
                           placeholder="Ex: CV Développeur Full-Stack" required>
                    @error('name')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Langue</label>
                    <select name="language" class="w-full border border-gray-300 rounded px-3 py-2">
                        <option value="fr" {{ old('language') === 'fr' ? 'selected' : '' }}>Français</option>
                        <option value="en" {{ old('language') === 'en' ? 'selected' : '' }}>English</option>
                    </select>
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="bg-indigo-600 text-white px-5 py-2 rounded hover:bg-indigo-700">Créer</button>
                    <a href="{{ route('resumes.index') }}" class="bg-gray-100 text-gray-700 px-5 py-2 rounded hover:bg-gray-200">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
