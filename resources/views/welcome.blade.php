<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CV Builder</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">

    {{-- Nav --}}
    <nav class="bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between">
        <span class="text-xl font-bold text-indigo-700">CV Builder</span>
        <div class="flex gap-3">
            <a href="{{ route('login') }}" class="text-gray-600 hover:text-indigo-700 px-4 py-2 rounded text-sm font-medium">
                Se connecter
            </a>
            @if (Route::has('register'))
                <a href="{{ route('register') }}" class="bg-indigo-600 text-white px-4 py-2 rounded text-sm font-medium hover:bg-indigo-700">
                    Créer un compte
                </a>
            @endif
        </div>
    </nav>

    {{-- Hero --}}
    <main class="flex-1 flex items-center justify-center px-6 py-20">
        <div class="max-w-2xl text-center">
            <h1 class="text-5xl font-bold text-gray-900 mb-6 leading-tight">
                Créez votre CV<br>
                <span class="text-indigo-600">professionnel</span> en quelques minutes
            </h1>
            <p class="text-xl text-gray-500 mb-10">
                Rédigez votre CV en Markdown, gérez vos expériences et compétences,
                exportez en PDF ou partagez votre profil en ligne.
            </p>
            <div class="flex gap-4 justify-center">
                <a href="{{ route('register') }}" class="bg-indigo-600 text-white px-8 py-3 rounded-lg text-base font-semibold hover:bg-indigo-700 shadow">
                    Commencer gratuitement
                </a>
                <a href="{{ route('login') }}" class="bg-white text-indigo-600 border border-indigo-300 px-8 py-3 rounded-lg text-base font-semibold hover:bg-indigo-50">
                    Se connecter
                </a>
            </div>
        </div>
    </main>

    {{-- Features --}}
    <section class="bg-white border-t border-gray-100 py-16 px-6">
        <div class="max-w-4xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-8 text-center">
            <div>
                <div class="text-3xl mb-3">✍️</div>
                <h3 class="font-semibold text-gray-800 mb-2">Édition Markdown</h3>
                <p class="text-sm text-gray-500">Rédigez votre profil, contact et hobbies en Markdown simple.</p>
            </div>
            <div>
                <div class="text-3xl mb-3">📂</div>
                <h3 class="font-semibold text-gray-800 mb-2">Structuré & organisé</h3>
                <p class="text-sm text-gray-500">Expériences, formations, compétences — tout est structuré en base de données.</p>
            </div>
            <div>
                <div class="text-3xl mb-3">📤</div>
                <h3 class="font-semibold text-gray-800 mb-2">Import / Export</h3>
                <p class="text-sm text-gray-500">Importez ou exportez votre CV complet au format Markdown.</p>
            </div>
        </div>
    </section>

    <footer class="text-center text-sm text-gray-400 py-6">
        © {{ date('Y') }} CV Builder
    </footer>

</body>
</html>
