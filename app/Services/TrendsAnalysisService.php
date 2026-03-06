<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Analyse les tendances à partir d'un CSV d'articles via l'API OpenAI.
 *
 * Utilise le prompt prédéfini (config/trends_analysis.php) pour que l'IA
 * retourne : connexions, corrélations, tendances, meilleur sujet, poids,
 * hot news vs article de fond, et fiche rédactionnelle.
 */
class TrendsAnalysisService
{
    public function __construct(
        private ?string $apiKey = null,
        private ?string $model = null,
        private ?int $maxCsvChars = null
    ) {
        $this->apiKey = $apiKey ?? config('services.openai.api_key');
        $this->model = $model ?? config('services.openai.model', 'gpt-4o');
        $this->maxCsvChars = $maxCsvChars ?? config('trends_analysis.max_csv_chars', 45000);
    }

    /**
     * Envoie le CSV à OpenAI avec le prompt prédéfini et retourne l'analyse texte.
     * Si le CSV est trop volumineux, il est tronqué (l'IA n'a pas besoin de tout lire).
     *
     * @param  string  $csvContent  Contenu brut du CSV (séparateur ;, UTF-8)
     * @return array{success: bool, analysis?: string, error?: string, truncated?: bool, truncated_at_chars?: int}
     */
    public function analyze(string $csvContent): array
    {
        if (! $this->apiKey) {
            Log::warning('TrendsAnalysisService: OPENAI_API_KEY not set');

            return ['success' => false, 'error' => 'OPENAI_API_KEY non configurée.'];
        }

        $originalLength = mb_strlen($csvContent);
        $csvContent = $this->truncateToMaxChars($csvContent);
        $truncated = $originalLength > $this->maxCsvChars;

        $systemPrompt = config('trends_analysis.system_prompt');
        $userPromptTemplate = config('trends_analysis.user_prompt_template');
        $userContent = str_replace('{csv_content}', $csvContent, $userPromptTemplate);

        $attempt = 0;
        $maxAttempts = 2;

        while ($attempt < $maxAttempts) {
            $attempt++;

            try {
                $response = Http::withToken($this->apiKey)
                    ->timeout(120)
                    ->post('https://api.openai.com/v1/chat/completions', [
                        'model' => $this->model,
                        'messages' => [
                            ['role' => 'system', 'content' => $systemPrompt],
                            ['role' => 'user', 'content' => $userContent],
                        ],
                        'temperature' => 0.3,
                        'max_tokens' => config('services.openai.max_tokens', 4000),
                    ]);
            } catch (\Throwable $e) {
                Log::error('TrendsAnalysisService OpenAI request failed: ' . $e->getMessage());

                return ['success' => false, 'error' => $e->getMessage()];
            }

            if ($response->status() === 429) {
                if ($attempt < $maxAttempts) {
                    Log::info('TrendsAnalysisService: 429 rate limit, retry in 60s');
                    sleep(60);
                    continue;
                }

                return ['success' => false, 'error' => 'OpenAI: quota ou rate limit dépassé (429). Attendez quelques minutes puis réessayez.'];
            }

            if ($response->status() === 402) {
                return ['success' => false, 'error' => 'OpenAI: quota dépassé (402).'];
            }

            if ($response->failed()) {
                $body = $response->body();
                Log::error('TrendsAnalysisService OpenAI error: ' . $body);

                return ['success' => false, 'error' => 'OpenAI: ' . $body];
            }

            $content = $response->json('choices.0.message.content');

            if (! is_string($content)) {
                return ['success' => false, 'error' => 'Réponse OpenAI invalide (pas de content).'];
            }

            $result = ['success' => true, 'analysis' => trim($content)];
            if ($truncated) {
                $result['truncated'] = true;
                $result['truncated_at_chars'] = $this->maxCsvChars;
            }

            return $result;
        }

        return ['success' => false, 'error' => 'OpenAI: échec après retry.'];
    }

    private function truncateToMaxChars(string $csv): string
    {
        if (mb_strlen($csv) <= $this->maxCsvChars) {
            return $csv;
        }

        return mb_substr($csv, 0, $this->maxCsvChars) . "\n\n[... tronqué pour limite de contexte ...]";
    }
}
