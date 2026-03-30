<x-filament-panels::page>
    <div class="space-y-6">
        <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="text-xl font-semibold tracking-tight text-gray-900">Historique des cron jobs</h2>
            <p class="mt-2 max-w-3xl text-sm text-gray-600">
                Cette vue affiche les exécutions réelles du scheduler du pipeline, classées par jour.
                Tu peux donc voir quels jobs se sont lancés, à quelle heure, et s’ils ont réussi ou échoué.
            </p>
        </div>

        @if ($this->getRecentJobsByDay() === [])
            <div class="rounded-3xl border border-dashed border-gray-300 bg-white p-10 text-center text-sm text-gray-500 shadow-sm">
                Aucun historique disponible pour le moment. Les lignes apparaîtront dès que les cron jobs du pipeline se lanceront.
            </div>
        @else
            <div class="space-y-6">
                @foreach ($this->getRecentJobsByDay() as $day)
                    <section class="rounded-3xl border border-gray-200 bg-white shadow-sm">
                        <div class="border-b border-gray-100 px-6 py-5">
                            <h3 class="text-lg font-semibold text-gray-900">{{ $day['date'] }}</h3>
                        </div>

                        <div class="divide-y divide-gray-100">
                            @foreach ($day['jobs'] as $job)
                                <div class="flex flex-col gap-4 px-6 py-5 lg:flex-row lg:items-start lg:justify-between">
                                    <div class="min-w-0">
                                        <div class="text-sm font-semibold text-gray-900">{{ $job['label'] }}</div>
                                        <div class="mt-1 text-xs text-gray-500">
                                            Début
                                            <span class="font-medium text-gray-700">{{ $job['started_at'] ?? '—' }}</span>
                                            · Fin
                                            <span class="font-medium text-gray-700">{{ $job['completed_at'] ?? '—' }}</span>
                                        </div>

                                        @if (!empty($job['metadata']))
                                            <div class="mt-2 flex flex-wrap gap-2">
                                                @foreach ($job['metadata'] as $key => $value)
                                                    <span class="inline-flex rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700">
                                                        {{ str_replace('_', ' ', $key) }}: {{ is_array($value) ? json_encode($value) : $value }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif

                                        @if (!empty($job['error_message']))
                                            <div class="mt-3 rounded-2xl bg-rose-50 px-4 py-3 text-sm text-rose-700">
                                                {{ $job['error_message'] }}
                                            </div>
                                        @endif
                                    </div>

                                    <div class="shrink-0">
                                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold
                                            @if($job['status'] === 'completed') bg-emerald-50 text-emerald-700
                                            @elseif($job['status'] === 'failed') bg-rose-50 text-rose-700
                                            @elseif($job['status'] === 'running') bg-amber-50 text-amber-700
                                            @else bg-gray-100 text-gray-700
                                            @endif">
                                            @if($job['status'] === 'completed')
                                                Réussi
                                            @elseif($job['status'] === 'failed')
                                                Échec
                                            @elseif($job['status'] === 'running')
                                                En cours
                                            @else
                                                En attente
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endforeach
            </div>
        @endif
    </div>
</x-filament-panels::page>
