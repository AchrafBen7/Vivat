<x-filament-panels::page>
    <div class="space-y-6">
        <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="text-xl font-semibold tracking-tight text-gray-900">Meilleurs sujets à générer</h2>
            <p class="mt-2 max-w-3xl text-sm text-gray-600">
                Cette vue classe automatiquement les sujets les plus prometteurs à partir des items enrichis :
                fraîcheur, qualité, potentiel SEO, diversité des sources et fréquence du sujet.
            </p>
        </div>

        @if ($proposals === [])
            <div class="rounded-3xl border border-dashed border-gray-300 bg-white p-10 text-center text-sm text-gray-500 shadow-sm">
                Aucune proposition disponible pour le moment. Lance d’abord le fetch RSS puis l’enrichissement IA.
            </div>
        @else
            <div class="grid gap-6 xl:grid-cols-2">
                @foreach ($proposals as $index => $proposal)
                    <section class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <div class="flex items-center gap-3">
                                    <span class="inline-flex rounded-full bg-teal-50 px-3 py-1 text-xs font-semibold text-teal-700">
                                        Score {{ $proposal['score'] ?? 0 }}/100
                                    </span>
                                    <span class="inline-flex rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">
                                        {{ match($proposal['suggested_article_type'] ?? 'standard') {
                                            'hot_news' => 'Hot news',
                                            'long_form' => 'Long form',
                                            default => 'Standard',
                                        } }}
                                    </span>
                                </div>
                                <h3 class="mt-4 text-lg font-semibold text-gray-900">
                                    {{ $proposal['topic'] ?? 'Sujet sans titre' }}
                                </h3>
                                <p class="mt-2 text-sm text-gray-600">
                                    {{ $proposal['reasoning'] ?? 'Aucune justification disponible.' }}
                                </p>
                            </div>

                            <button
                                type="button"
                                wire:click="generateProposal({{ $index }})"
                                class="inline-flex shrink-0 items-center rounded-full bg-teal-700 px-4 py-2 text-sm font-medium text-white transition hover:bg-teal-800"
                            >
                                Générer
                            </button>
                        </div>

                        <div class="mt-5 grid gap-3 sm:grid-cols-3">
                            <div class="rounded-2xl bg-gray-50 p-4">
                                <div class="text-xs font-medium uppercase tracking-wide text-gray-500">Catégorie</div>
                                <div class="mt-2 text-sm font-semibold text-gray-900">{{ $proposal['category']['name'] ?? 'Non définie' }}</div>
                            </div>
                            <div class="rounded-2xl bg-gray-50 p-4">
                                <div class="text-xs font-medium uppercase tracking-wide text-gray-500">Sources</div>
                                <div class="mt-2 text-sm font-semibold text-gray-900">{{ $proposal['source_count'] ?? 0 }}</div>
                            </div>
                            <div class="rounded-2xl bg-gray-50 p-4">
                                <div class="text-xs font-medium uppercase tracking-wide text-gray-500">Qualité moyenne</div>
                                <div class="mt-2 text-sm font-semibold text-gray-900">{{ $proposal['avg_quality'] ?? 0 }}/100</div>
                            </div>
                        </div>

                        <div class="mt-5">
                            <div class="text-xs font-medium uppercase tracking-wide text-gray-500">Mots-clés SEO</div>
                            <div class="mt-3 flex flex-wrap gap-2">
                                @foreach (($proposal['seo_keywords'] ?? []) as $keyword)
                                    <span class="inline-flex rounded-full bg-amber-50 px-3 py-1 text-xs font-medium text-amber-700">
                                        {{ is_array($keyword) ? ($keyword['word'] ?? '') : $keyword }}
                                    </span>
                                @endforeach
                            </div>
                        </div>

                        <div class="mt-5">
                            <div class="text-xs font-medium uppercase tracking-wide text-gray-500">Items source</div>
                            <div class="mt-3 space-y-3">
                                @foreach (($proposal['items'] ?? []) as $item)
                                    <div class="rounded-2xl border border-gray-200 p-4">
                                        <div class="text-sm font-semibold text-gray-900">{{ $item['title'] ?? 'Sans titre' }}</div>
                                        <div class="mt-1 text-xs text-gray-500">
                                            {{ $item['source'] ?? 'Source inconnue' }}
                                            @if (!empty($item['quality_score']))
                                                · Qualité {{ $item['quality_score'] }}/100
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </section>
                @endforeach
            </div>
        @endif
    </div>
</x-filament-panels::page>
