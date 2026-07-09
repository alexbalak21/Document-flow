<section class="bg-white shadow rounded-lg p-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="font-semibold text-lg text-gray-700">Certifications</h3>
        <a href="{{ route('resumes.certifications.create', $resume) }}" class="bg-indigo-600 text-white px-3 py-1.5 rounded text-sm hover:bg-indigo-700">+ Ajouter</a>
    </div>
    @forelse($resume->certifications as $cert)
        <div class="border border-gray-200 rounded p-4 mb-3 flex items-center justify-between">
            <div>
                <p class="font-medium text-gray-800">{{ $cert->name }}</p>
                <p class="text-sm text-gray-500">{{ $cert->organization }}{{ $cert->year ? ' · ' . $cert->year : '' }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('resumes.certifications.edit', [$resume, $cert]) }}" class="text-indigo-600 text-sm hover:underline">Éditer</a>
                <form action="{{ route('resumes.certifications.destroy', [$resume, $cert]) }}" method="POST" onsubmit="return confirm('Supprimer ?')">
                    @csrf @method('DELETE')
                    <button class="text-red-500 text-sm hover:underline">Supprimer</button>
                </form>
            </div>
        </div>
    @empty
        <p class="text-gray-400 text-sm">Aucune certification.</p>
    @endforelse
</section>
