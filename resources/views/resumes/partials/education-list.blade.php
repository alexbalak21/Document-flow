<section class="bg-white shadow rounded-lg p-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="font-semibold text-lg text-gray-700">Formations</h3>
        <a href="{{ route('resumes.educations.create', $resume) }}" class="bg-indigo-600 text-white px-3 py-1.5 rounded text-sm hover:bg-indigo-700">+ Ajouter</a>
    </div>
    @forelse($resume->educations as $edu)
        <div class="border border-gray-200 rounded p-4 mb-3 flex items-center justify-between">
            <div>
                <p class="font-medium text-gray-800">{{ $edu->diploma }}</p>
                <p class="text-sm text-gray-500">{{ $edu->school }} · {{ $edu->year }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('resumes.educations.edit', [$resume, $edu]) }}" class="text-indigo-600 text-sm hover:underline">Éditer</a>
                <form action="{{ route('resumes.educations.destroy', [$resume, $edu]) }}" method="POST" onsubmit="return confirm('Supprimer ?')">
                    @csrf @method('DELETE')
                    <button class="text-red-500 text-sm hover:underline">Supprimer</button>
                </form>
            </div>
        </div>
    @empty
        <p class="text-gray-400 text-sm">Aucune formation.</p>
    @endforelse
</section>
