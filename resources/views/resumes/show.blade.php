<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Aperçu : {{ $resume->name }}</h2>
            <div class="flex gap-2">
                <a href="{{ route('resumes.edit', $resume) }}" class="bg-indigo-600 text-white px-3 py-1.5 rounded text-sm hover:bg-indigo-700">Éditer</a>
                <a href="{{ route('resumes.export-md', $resume) }}" class="bg-green-600 text-white px-3 py-1.5 rounded text-sm hover:bg-green-700">Export .md</a>
                <a href="{{ route('resumes.index') }}" class="bg-gray-100 text-gray-700 px-3 py-1.5 rounded text-sm hover:bg-gray-200">← Retour</a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 max-w-4xl mx-auto px-4">
        <div class="bg-white shadow rounded-lg overflow-hidden">

            {{-- Header band --}}
            <div class="bg-indigo-700 text-white p-8 flex items-center gap-6">
                @if($resume->profile_image_base64)
                    <img src="{{ $resume->profile_image_base64 }}" class="w-24 h-24 rounded-full object-cover border-4 border-white">
                @endif
                <div>
                    @php $header = $resume->sections->where('section_type','header')->first() @endphp
                    @if($header)
                        @php $lines = array_values(array_filter(explode("\n", $header->markdown_content))); @endphp
                        <h1 class="text-3xl font-bold">{{ $lines[0] ?? $resume->name }}</h1>
                        <p class="text-indigo-200 text-lg mt-1">{{ $lines[1] ?? '' }}</p>
                    @else
                        <h1 class="text-3xl font-bold">{{ $resume->name }}</h1>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-3">
                {{-- Sidebar --}}
                <div class="col-span-1 bg-indigo-50 p-6 space-y-6">

                    {{-- Contact --}}
                    @php $contact = $resume->sections->where('section_type','contact')->first() @endphp
                    @if($contact)
                        <div>
                            <h3 class="font-bold text-indigo-800 uppercase text-xs tracking-widest mb-2">{{ $contact->title }}</h3>
                            <div class="text-sm text-gray-600 space-y-1">
                                @foreach(array_filter(explode("\n", $contact->markdown_content)) as $line)
                                    <p>{{ ltrim($line, '- ') }}</p>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Skills --}}
                    @if($resume->skills->count())
                        <div>
                            <h3 class="font-bold text-indigo-800 uppercase text-xs tracking-widest mb-2">Compétences</h3>
                            <ul class="space-y-1">
                                @foreach($resume->skills as $skill)
                                    <li class="text-sm text-gray-700">
                                        @if($skill->icon)<i class="fa-solid fa-{{ $skill->icon }} mr-1 text-indigo-500"></i>@endif
                                        {{ $skill->name }}
                                        <span class="text-xs text-gray-400">
                                            @if($skill->level_type === 'percentage') — {{ $skill->level_value }}%
                                            @else — {{ ucfirst($skill->level_type) }}
                                            @endif
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- Languages --}}
                    @if($resume->languages->count())
                        <div>
                            <h3 class="font-bold text-indigo-800 uppercase text-xs tracking-widest mb-2">Langues</h3>
                            <ul class="space-y-1">
                                @foreach($resume->languages as $lang)
                                    <li class="text-sm text-gray-700">{{ $lang->name }} <span class="text-xs text-gray-400">— {{ ucfirst($lang->level) }}</span></li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- Certifications --}}
                    @if($resume->certifications->count())
                        <div>
                            <h3 class="font-bold text-indigo-800 uppercase text-xs tracking-widest mb-2">Certifications</h3>
                            <ul class="space-y-1">
                                @foreach($resume->certifications as $cert)
                                    <li class="text-sm text-gray-700">{{ $cert->name }} <span class="text-xs text-gray-400">· {{ $cert->organization }}</span></li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                </div>

                {{-- Main --}}
                <div class="col-span-2 p-6 space-y-6">

                    {{-- Profile --}}
                    @php $profile = $resume->sections->where('section_type','profile')->first() @endphp
                    @if($profile)
                        <div>
                            <h3 class="font-bold text-indigo-700 uppercase text-xs tracking-widest border-b border-indigo-200 pb-1 mb-3">{{ $profile->title }}</h3>
                            <p class="text-sm text-gray-700 leading-relaxed">{{ $profile->markdown_content }}</p>
                        </div>
                    @endif

                    {{-- Experience --}}
                    @if($resume->experiences->count())
                        <div>
                            <h3 class="font-bold text-indigo-700 uppercase text-xs tracking-widest border-b border-indigo-200 pb-1 mb-3">Expériences</h3>
                            @foreach($resume->experiences as $exp)
                                <div class="mb-4">
                                    <p class="font-semibold text-gray-800">{{ $exp->position_title }} — {{ $exp->company }}</p>
                                    <p class="text-xs text-indigo-500 mb-1">{{ $exp->start_date }} → {{ $exp->end_date ?? 'Présent' }}@if($exp->location) · {{ $exp->location }}@endif</p>
                                    @if($exp->description)
                                        <ul class="text-sm text-gray-600 list-disc list-inside space-y-0.5">
                                            @foreach(array_filter(explode("\n", $exp->description)) as $line)
                                                <li>{{ ltrim($line, '- ') }}</li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif

                    {{-- Education --}}
                    @if($resume->educations->count())
                        <div>
                            <h3 class="font-bold text-indigo-700 uppercase text-xs tracking-widest border-b border-indigo-200 pb-1 mb-3">Formations</h3>
                            @foreach($resume->educations as $edu)
                                <div class="mb-3">
                                    <p class="font-semibold text-gray-800">{{ $edu->diploma }}</p>
                                    <p class="text-sm text-gray-500">{{ $edu->school }} · {{ $edu->year }}</p>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    {{-- Soft Skills --}}
                    @if($resume->softSkills->count())
                        <div>
                            <h3 class="font-bold text-indigo-700 uppercase text-xs tracking-widest border-b border-indigo-200 pb-1 mb-3">Soft Skills</h3>
                            <ul class="space-y-1">
                                @foreach($resume->softSkills as $ss)
                                    <li class="text-sm text-gray-700">
                                        @if($ss->icon)<i class="fa-solid fa-{{ $ss->icon }} mr-1 text-indigo-400"></i>@endif
                                        <span class="font-medium">{{ $ss->name }}</span>
                                        @if($ss->description) — <span class="text-gray-500">{{ $ss->description }}</span>@endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- Hobbies --}}
                    @php $hobbies = $resume->sections->where('section_type','hobbies')->first() @endphp
                    @if($hobbies)
                        <div>
                            <h3 class="font-bold text-indigo-700 uppercase text-xs tracking-widest border-b border-indigo-200 pb-1 mb-3">{{ $hobbies->title }}</h3>
                            <ul class="text-sm text-gray-600 list-disc list-inside space-y-0.5">
                                @foreach(array_filter(explode("\n", $hobbies->markdown_content)) as $line)
                                    <li>{{ ltrim($line, '- ') }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
