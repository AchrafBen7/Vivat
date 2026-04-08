<x-mail::message>
# Alerte pipeline IA

Le contrôle automatique du pipeline a détecté un ou plusieurs signaux à surveiller.

@foreach(($snapshot['issues'] ?? []) as $issue)
- {{ $issue }}
@endforeach

<x-mail::panel>
Statut global : {{ $snapshot['status'] ?? 'unknown' }}

Dernière activité pipeline : {{ $snapshot['pipeline']['latest_pipeline_activity_at'] ?? 'n/a' }}

Dernier enrichissement : {{ $snapshot['pipeline']['latest_enriched_at'] ?? 'n/a' }}

Items en attente d'enrichissement : {{ $snapshot['pipeline']['new_items_count'] ?? 0 }}

Dernier snapshot Horizon : {{ $snapshot['horizon']['latest_snapshot_at'] ?? 'n/a' }}

Jobs en échec : {{ $snapshot['pipeline']['failed_jobs_count'] ?? 0 }}
</x-mail::panel>

<x-mail::button :url="url('/admin/pipeline-daily-automation')">
Ouvrir le suivi du jour
</x-mail::button>

Vérifie Horizon, les flux RSS et les jobs en échec avant la prochaine génération.

</x-mail::message>
