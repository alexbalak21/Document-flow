<section class="bg-white shadow rounded-lg p-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="font-semibold text-lg text-gray-700">Soft Skills</h3>
        <a href="{{ route('resumes.softskills.create', $resume) }}" class="bg-indigo-600 text-white px-3 py-1.5 rounded text-sm hover:bg-indigo-700">+ Ajouter</a>
    </div>
    @forelse($resume->softSkills as $ss)
        <div class="border border-gray-200 rounded p-3 mb-2 flex items-center justify-between">
            <div>
                <span class="font-medium text-gray-800 text-sm">{{ $ss->name }}</span>
                @if($ss->description)<p class="text-xs text-gray-400">{{ Str::limit($ss->description, 80) }}</p>@endif
            </div>
            <div class="flex gap-2">
                <a href="{{ route('resumes.softskills.edit', [$resume, $ss]) }}" class="text-indigo-600 text-sm hover:underline">Éditer</a>
                <form action="{{ route('resumes.softskills.destroy', [$resume, $ss]) }}" method="POST" onsubmit="return confirm('Supprimer ?')">
                    @csrf @method('DELETE')
                    <button class="text-red-500 text-sm hover:underline">Supprimer</button>
                </form>
            </div>
        </div>
    @empty
        <p class="text-gray-400 text-sm">Aucun soft skill.</p>
    @endforelse
</section>
