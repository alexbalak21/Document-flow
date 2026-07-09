<section class="bg-white shadow rounded-lg p-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="font-semibold text-lg text-gray-700">Compétences</h3>
        <a href="{{ route('resumes.skills.create', $resume) }}" class="bg-indigo-600 text-white px-3 py-1.5 rounded text-sm hover:bg-indigo-700">+ Ajouter</a>
    </div>
    @forelse($resume->skills as $skill)
        <div class="border border-gray-200 rounded p-3 mb-2 flex items-center justify-between">
            <div class="flex items-center gap-3">
                @if($skill->icon)<span class="text-gray-500 text-sm"><i class="fa-solid fa-{{ $skill->icon }}"></i></span>@endif
                <span class="font-medium text-gray-800 text-sm">{{ $skill->name }}</span>
                <span class="text-xs text-gray-400 bg-gray-100 rounded px-2 py-0.5">{{ $skill->level_type }}{{ $skill->level_value ? ' · ' . $skill->level_value . '%' : '' }}</span>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('resumes.skills.edit', [$resume, $skill]) }}" class="text-indigo-600 text-sm hover:underline">Éditer</a>
                <form action="{{ route('resumes.skills.destroy', [$resume, $skill]) }}" method="POST" onsubmit="return confirm('Supprimer ?')">
                    @csrf @method('DELETE')
                    <button class="text-red-500 text-sm hover:underline">Supprimer</button>
                </form>
            </div>
        </div>
    @empty
        <p class="text-gray-400 text-sm">Aucune compétence.</p>
    @endforelse
</section>
