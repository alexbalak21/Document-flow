<section class="bg-white shadow rounded-lg p-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="font-semibold text-lg text-gray-700">Expériences</h3>
        <a href="{{ route('resumes.experiences.create', $resume) }}" class="bg-indigo-600 text-white px-3 py-1.5 rounded text-sm hover:bg-indigo-700">+ Ajouter</a>
    </div>
    @forelse($resume->experiences as $exp)
        <div class="border border-gray-200 rounded p-4 mb-3">
            <div class="flex items-start justify-between">
                <div>
                    <p class="font-medium text-gray-800">{{ $exp->position_title }} — {{ $exp->company }}</p>
                    <p class="text-sm text-gray-500">{{ $exp->start_date }} → {{ $exp->end_date ?? 'Présent' }} @if($exp->location) · {{ $exp->location }} @endif</p>
                    @if($exp->description)
                        <p class="text-xs text-gray-400 mt-1">{{ Str::limit($exp->description, 120) }}</p>
                    @endif
                </div>
                <div class="flex gap-2 ml-4 shrink-0">
                    <a href="{{ route('resumes.experiences.edit', [$resume, $exp]) }}" class="text-indigo-600 text-sm hover:underline">Éditer</a>
                    <form action="{{ route('resumes.experiences.destroy', [$resume, $exp]) }}" method="POST" onsubmit="return confirm('Supprimer ?')">
                        @csrf @method('DELETE')
                        <button class="text-red-500 text-sm hover:underline">Supprimer</button>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <p class="text-gray-400 text-sm">Aucune expérience.</p>
    @endforelse
</section>
