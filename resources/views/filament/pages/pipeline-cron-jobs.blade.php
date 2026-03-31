<x-filament-panels::page>
    <div class="space-y-6">
        <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="text-xl font-semibold tracking-tight text-gray-900">Historique des cron jobs</h2>
            <p class="mt-2 max-w-3xl text-sm text-gray-600">Exécutions réelles du pipeline IA, classées par jour.</p>
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
                                        <div class="mt-1 text-sm text-gray-700">{{ $job['summary'] }}</div>
                                        <div class="mt-1 text-xs text-gray-500">
                                            Début
                                            <span class="font-medium text-gray-700">{{ $job['started_at'] ?? '—' }}</span>
                                            · Fin
                                            <span class="font-medium text-gray-700">{{ $job['completed_at'] ?? '—' }}</span>
                                        </div>

                                        @if (!empty($job['details']))
                                            <div class="mt-3 flex flex-wrap gap-2">
                                                @foreach ($job['details'] as $detail)
                                                    <span class="inline-flex rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700">
                                                        {{ $detail }}
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
                                            @if(($job['metadata']['retry_scheduled'] ?? false) === true) bg-amber-50 text-amber-700
                                            @elseif($job['status'] === 'completed') bg-emerald-50 text-emerald-700
                                            @elseif($job['status'] === 'failed') bg-rose-50 text-rose-700
                                            @elseif($job['status'] === 'running') bg-sky-50 text-sky-700
                                            @else bg-gray-100 text-gray-700
                                            @endif">
                                            {{ $job['status_label'] }}
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
