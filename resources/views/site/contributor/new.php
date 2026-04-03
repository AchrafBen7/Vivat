<?php
$categories = $categories ?? [];
$errors = $errors ?? [];
$old = $old ?? [];
$t = fn (string $key, ?string $fallback = null) => __($key) !== $key ? __($key) : ($fallback ?? $key);
$submission = $submission ?? null;
$formAction = $form_action ?? url('/contributor/new');
$isEditing = (bool) ($is_editing ?? false);
$stripeKey = $stripe_key ?? '';
$publicationPrice = (int) ($publication_price ?? 1500);
$paymentCreateUrl = $payment_create_url ?? '';
$paymentConfirmUrl = $payment_confirm_url ?? '';
$canBypassPayment = (bool) ($can_bypass_payment ?? false);
$publicationPriceLabel = number_format($publicationPrice / 100, 2, ',', ' ') . ' EUR';
$jsTranslations = [
    'saving_draft' => $t('site.js_saving_draft', 'Sauvegarde du brouillon…'),
    'draft_saved_at' => $t('site.js_draft_saved_at', 'Brouillon sauvegardé à'),
    'autosave_failed' => $t('site.js_autosave_failed', 'La sauvegarde automatique a échoué.'),
    'unsaved_changes' => $t('site.js_unsaved_changes', 'Modifications non enregistrées…'),
    'sending_in_progress_title' => $t('site.js_sending_in_progress_title', 'Transmission en cours...'),
    'sending_in_progress_text' => $t('site.js_sending_in_progress_text', 'Nous envoyons votre article à l’espace rédacteur. Vous pouvez patienter quelques instants.'),
    'continue' => $t('site.got_it', 'Continuer'),
    'action_interrupted' => $t('site.js_action_interrupted', 'Action interrompue'),
    'draft_saved_auto' => $t('site.js_draft_saved_auto', 'Brouillon sauvegardé automatiquement'),
    'session_expired' => $t('site.js_session_expired', 'Votre session a expiré. Rechargez la page puis réessayez.'),
    'server_problem' => $t('site.js_server_problem', 'Le serveur a rencontré un problème. Réessayez dans quelques instants.'),
    'unexpected_response' => $t('site.js_unexpected_response', 'Le serveur a renvoyé une réponse inattendue.'),
    'stripe_key_missing' => $t('site.js_stripe_key_missing', 'La clé Stripe publishable est absente.'),
    'preparing_submission_title' => $t('site.js_preparing_submission_title', 'Préparation de votre soumission...'),
    'preparing_payment_text' => $t('site.js_preparing_payment_text', 'Votre brouillon est enregistré. Nous préparons maintenant le paiement de :amount pour l’envoyer en validation éditoriale.'),
    'payment_prepare_failed' => $t('site.js_payment_prepare_failed', 'Impossible de preparer le paiement pour le moment.'),
    'payment_prepare_stripe_failed' => $t('site.js_payment_prepare_stripe_failed', 'Impossible de preparer le paiement Stripe.'),
    'stripe_load_failed' => $t('site.js_stripe_load_failed', 'Stripe.js n’a pas pu etre charge.'),
    'payment_form_not_ready' => $t('site.js_payment_form_not_ready', 'Le formulaire de paiement n’est pas prêt.'),
    'payment_refused' => $t('site.js_payment_refused', 'Le paiement a ete refuse. Verifiez vos donnees puis reessayez.'),
    'payment_validated_title' => $t('site.js_payment_validated_title', 'Paiement validé...'),
    'payment_validated_text' => $t('site.js_payment_validated_text', 'Votre règlement a bien été accepté. Nous transmettons maintenant votre article à l’équipe éditoriale.'),
    'payment_confirm_failed' => $t('site.js_payment_confirm_failed', 'La confirmation du paiement n’a pas pu être finalisée pour le moment.'),
    'payment_done_but_local_failed' => $t('site.js_payment_done_but_local_failed', 'Le paiement a ete effectue, mais la confirmation locale a echoue. Verifiez le tableau de bord dans quelques instants.'),
    'article_sent' => $t('site.js_article_sent', 'Article transmis'),
    'article_sent_text' => $t('site.js_article_sent_text', 'Votre article a bien ete envoye en validation.'),
    'payment_interrupted' => $t('site.js_payment_interrupted', 'Paiement interrompu'),
    'payment_finalize_failed' => $t('site.js_payment_finalize_failed', 'Le paiement n’a pas pu etre finalise.'),
    'image_too_large' => $t('site.js_image_too_large', 'Cette image dépasse la limite PHP locale actuelle (:limit). Choisissez un fichier plus léger.'),
    'saving_before_publish' => $t('site.js_saving_before_publish', 'Enregistrement du brouillon...'),
    'saving_before_direct_publish' => $t('site.js_saving_before_direct_publish', 'Nous sauvegardons votre article avant publication directe.'),
    'saving_before_payment' => $t('site.js_saving_before_payment', 'Nous sauvegardons votre article avant de lancer le paiement.'),
    'saving_draft_text' => $t('site.js_saving_draft_text', 'Nous enregistrons votre brouillon pour que vous puissiez le reprendre à tout moment.'),
    'draft_save_failed' => $t('site.js_draft_save_failed', 'Le brouillon n’a pas pu être enregistré. Vérifiez les champs puis réessayez.'),
    'submit_prepare_failed' => $t('site.js_submit_prepare_failed', 'Votre article n’a pas pu être préparé pour l’envoi. Vérifiez les champs puis réessayez.'),
    'draft_not_saved' => $t('site.js_draft_not_saved', 'Brouillon non enregistré'),
    'send_impossible' => $t('site.js_send_impossible', 'Envoi impossible'),
    'submission_not_ready_for_stripe' => $t('site.js_submission_not_ready_for_stripe', 'La soumission n’a pas pu etre preparee pour Stripe.'),
    'draft_saved' => $t('site.js_draft_saved', 'Brouillon enregistré'),
    'draft_saved_text' => $t('site.js_draft_saved_text', 'Votre brouillon a bien été enregistré. Vous pourrez le reprendre plus tard.'),
];

$uploadMaxRaw = ini_get('upload_max_filesize') ?: '2M';
$uploadMaxBytes = (function (string $value): int {
    $value = trim($value);
    if ($value === '') {
        return 2 * 1024 * 1024;
    }

    $unit = strtolower(substr($value, -1));
    $number = (float) $value;

    return match ($unit) {
        'g' => (int) round($number * 1024 * 1024 * 1024),
        'm' => (int) round($number * 1024 * 1024),
        'k' => (int) round($number * 1024),
        default => (int) round((float) $value),
    };
})($uploadMaxRaw);
?>
<h1 class="font-medium text-[#004241] text-2xl mb-2"><?= htmlspecialchars($isEditing ? $t('site.edit_article', 'Modifier l’article') : $t('site.create_article', 'Nouvel article')) ?></h1>
<p class="text-[#004241]/80 mb-8">
    <?= $canBypassPayment
        ? htmlspecialchars($isEditing ? $t('site.admin_edit_article_text', 'Mettez à jour votre article puis publiez-le directement depuis cet écran.') : $t('site.admin_create_article_text', 'Créez et publiez un article sans étape de paiement.'))
        : htmlspecialchars($isEditing ? $t('site.edit_submission_text', 'Mettez à jour votre soumission puis renvoyez-la en validation.') : $t('site.create_submission_text', 'Partagez vos idées avec la communauté Vivat')) ?>
</p>

<?php if ($isEditing && !empty($submission['reviewer_notes'])): ?>
<div class="mb-6 rounded-[24px] border border-[#D6E3E1] bg-[#F4F8F7] px-5 py-4 text-[#004241] shadow-[0_10px_24px_rgba(0,66,65,0.05)]">
    <div class="flex items-center gap-2 text-sm font-semibold">
        <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-[#004241] text-white">i</span>
        <span><?= htmlspecialchars($t('site.editorial_feedback', "Retour de l’équipe éditoriale")) ?></span>
    </div>
    <p class="mt-3 text-sm leading-6 text-[#004241]/84"><?= nl2br(htmlspecialchars($submission['reviewer_notes'])) ?></p>
    <?php if (!empty($submission['reviewer_name']) || !empty($submission['reviewed_at'])): ?>
    <p class="mt-3 text-xs font-medium uppercase tracking-[0.14em] text-[#004241]/52">
        <?= htmlspecialchars(trim(($submission['reviewer_name'] ?? '') . (!empty($submission['reviewed_at']) ? ' • ' . $submission['reviewed_at'] : ''))) ?>
    </p>
    <?php endif; ?>
</div>
<?php endif; ?>

<form
    id="contributor-new-article-form"
    action="<?= htmlspecialchars($formAction) ?>"
    method="post"
    enctype="multipart/form-data"
    class="flex flex-col gap-6"
    data-stripe-key="<?= htmlspecialchars($stripeKey) ?>"
    data-payment-create-url="<?= htmlspecialchars($paymentCreateUrl) ?>"
    data-payment-confirm-url="<?= htmlspecialchars($paymentConfirmUrl) ?>"
    data-publication-price-label="<?= htmlspecialchars($publicationPriceLabel) ?>"
    data-is-editing="<?= $isEditing ? '1' : '0' ?>"
    data-can-bypass-payment="<?= $canBypassPayment ? '1' : '0' ?>"
>
    <?= csrf_field() ?>

    <div class="rounded-[20px] border-2 border-dashed border-gray-300 bg-gray-50 p-12 text-center">
        <label class="cursor-pointer block">
            <input
                type="file"
                name="cover_image"
                id="cover_image"
                accept="image/jpeg,image/png"
                class="hidden"
                data-max-bytes="<?= $uploadMaxBytes ?>"
                data-max-label="<?= htmlspecialchars($uploadMaxRaw) ?>"
            >
            <div id="cover-image-empty-state">
                <svg class="w-12 h-12 mx-auto text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span class="text-[#004241]/70 text-sm"><?= htmlspecialchars($isEditing && !empty($submission['cover_image_url']) ? $t('site.replace_image', 'Cliquez pour remplacer l’image') : $t('site.add_image', 'Cliquez pour ajouter une image')) ?></span>
                <span class="block text-gray-400 text-xs mt-1">JPG, PNG - max 5 Mo</span>
                <span class="block text-gray-400 text-xs mt-1"><?= htmlspecialchars($t('site.current_php_limit', 'Limite PHP locale actuelle')) ?>: <?= htmlspecialchars($uploadMaxRaw) ?></span>
            </div>

            <div id="cover-image-preview-wrapper" class="hidden">
                <img id="cover-image-preview" src="" alt="Aperçu de l'image sélectionnée" class="mx-auto h-40 w-auto max-w-full rounded-2xl object-cover shadow-sm">
                <p id="cover-image-name" class="mt-4 text-sm font-medium text-[#004241]"></p>
                <p id="cover-image-size" class="mt-1 text-xs text-[#004241]/70"></p>
            </div>
        </label>
        <?php if (!empty($errors['cover_image'])): ?>
        <p class="mt-3 text-sm text-red-600"><?= htmlspecialchars(is_array($errors['cover_image']) ? $errors['cover_image'][0] : $errors['cover_image']) ?></p>
        <?php endif; ?>
        <p id="cover-image-client-error" class="mt-3 hidden text-sm text-red-600"></p>
    </div>

    <div>
        <div class="mb-2 flex items-center justify-between gap-3">
            <label for="title" class="block font-medium text-[#004241]"><?= htmlspecialchars($t('site.article_title', "Titre de l'article")) ?></label>
            <span id="title-char-count" class="text-xs font-medium tabular-nums text-[#004241]/50">0/255</span>
        </div>
        <input type="text" name="title" id="title" value="<?= htmlspecialchars($old['title'] ?? '') ?>" placeholder="<?= htmlspecialchars($t('site.article_title', "Titre de l'article")) ?>" required maxlength="255"
            data-char-max="255" data-char-target="title-char-count"
            class="w-full max-w-2xl h-12 pl-4 pr-4 rounded-xl border border-[#DED8CE99] bg-[#F8F6F2] text-[#004241] placeholder:text-gray-400 outline-none focus:ring-2 focus:ring-[#004241]/25 focus:border-[#004241]">
        <?php if (!empty($errors['title'])): ?>
        <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars(is_array($errors['title']) ? $errors['title'][0] : $errors['title']) ?></p>
        <?php endif; ?>
    </div>

    <div class="flex gap-4 flex-wrap">
        <div>
            <label for="category_id" class="block font-medium text-[#004241] mb-2"><?= htmlspecialchars($t('site.category', 'Catégorie')) ?></label>
            <select name="category_id" id="category_id" class="h-12 pl-4 pr-4 rounded-xl border border-[#DED8CE99] bg-[#F8F6F2] text-[#004241] outline-none focus:ring-2 focus:ring-[#004241]/25">
                <option value=""><?= htmlspecialchars($t('site.choose', 'Choisir...')) ?></option>
                <?php foreach ($categories as $cat): ?>
                <option value="<?= htmlspecialchars($cat['id']) ?>" <?= ($old['category_id'] ?? '') === $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <?php if (!empty($errors['category_id'])): ?>
            <p class="mt-2 text-sm text-red-600"><?= htmlspecialchars(is_array($errors['category_id']) ? $errors['category_id'][0] : $errors['category_id']) ?></p>
            <?php endif; ?>
        </div>
        <div>
            <label for="language" class="block font-medium text-[#004241] mb-2"><?= htmlspecialchars($t('site.language', 'Langue')) ?></label>
            <select name="language" id="language" class="h-12 pl-4 pr-4 rounded-xl border border-[#DED8CE99] bg-[#F8F6F2] text-[#004241] outline-none focus:ring-2 focus:ring-[#004241]/25">
                <?php $selectedLanguage = $old['language'] ?? 'fr'; ?>
                <option value="fr" <?= $selectedLanguage === 'fr' ? 'selected' : '' ?>>Français</option>
                <option value="nl" <?= $selectedLanguage === 'nl' ? 'selected' : '' ?>>Néerlandais</option>
            </select>
            <?php if (!empty($errors['language'])): ?>
            <p class="mt-2 text-sm text-red-600"><?= htmlspecialchars(is_array($errors['language']) ? $errors['language'][0] : $errors['language']) ?></p>
            <?php endif; ?>
        </div>
        <div>
            <label for="reading_time" class="block font-medium text-[#004241] mb-2"><?= htmlspecialchars($t('site.reading_time', 'Temps')) ?></label>
            <input type="number" name="reading_time" id="reading_time" value="<?= htmlspecialchars($old['reading_time'] ?? '5') ?>" placeholder="5 min" min="1" max="120"
                class="h-12 pl-4 pr-4 rounded-xl border border-[#DED8CE99] bg-[#F8F6F2] text-[#004241] placeholder:text-gray-400 outline-none focus:ring-2 focus:ring-[#004241]/25 w-24">
            <?php if (!empty($errors['reading_time'])): ?>
            <p class="mt-2 text-sm text-red-600"><?= htmlspecialchars(is_array($errors['reading_time']) ? $errors['reading_time'][0] : $errors['reading_time']) ?></p>
            <?php endif; ?>
        </div>
    </div>

    <div>
        <div class="mb-2 flex items-center justify-between gap-3">
            <label for="excerpt" class="block font-medium text-[#004241]"><?= htmlspecialchars($t('site.excerpt', 'Extrait / Chapô')) ?></label>
            <span id="excerpt-char-count" class="text-xs font-medium tabular-nums text-[#004241]/50">0/500</span>
        </div>
        <textarea name="excerpt" id="excerpt" rows="3" placeholder="<?= htmlspecialchars($t('site.start_writing', 'Commencez à écrire votre article ici...')) ?>" maxlength="500"
            data-char-max="500" data-char-target="excerpt-char-count"
            class="w-full max-w-2xl pl-4 pr-4 py-3 rounded-xl border border-[#DED8CE99] bg-[#F8F6F2] text-[#004241] placeholder:text-gray-400 outline-none focus:ring-2 focus:ring-[#004241]/25"><?= htmlspecialchars($old['excerpt'] ?? '') ?></textarea>
        <?php if (!empty($errors['excerpt'])): ?>
        <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars(is_array($errors['excerpt']) ? $errors['excerpt'][0] : $errors['excerpt']) ?></p>
        <?php endif; ?>
    </div>

    <div>
        <label for="content" class="block font-medium text-[#004241] mb-2"><?= htmlspecialchars($t('site.content', 'Contenu')) ?></label>
        <textarea name="content" id="content" rows="12" placeholder="<?= htmlspecialchars($t('site.start_writing', 'Commencez à écrire votre article ici...')) ?>" required
            class="w-full max-w-2xl pl-4 pr-4 py-3 rounded-xl border border-[#DED8CE99] bg-[#F8F6F2] text-[#004241] placeholder:text-gray-400 outline-none focus:ring-2 focus:ring-[#004241]/25"><?= htmlspecialchars($old['content'] ?? '') ?></textarea>
        <?php if (!empty($errors['content'])): ?>
        <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars(is_array($errors['content']) ? $errors['content'][0] : $errors['content']) ?></p>
        <?php endif; ?>
    </div>

    <div class="flex items-center justify-between pt-4">
        <span id="draft-autosave-status" class="text-sm text-[#004241]/60">
            <?= htmlspecialchars($t('site.autosave_info', 'Cet article est sauvegardé automatiquement pendant que vous écrivez.')) ?>
        </span>
        <div class="flex gap-3">
            <button type="submit" name="status" value="draft" class="h-12 px-6 rounded-full border border-gray-300 bg-white text-[#004241] font-medium hover:bg-gray-50 transition disabled:opacity-50 disabled:cursor-not-allowed">
                <?= htmlspecialchars($isEditing ? $t('site.save_draft', 'Enregistrer') : $t('site.draft_button', 'Brouillon')) ?>
            </button>
            <button type="submit" name="status" value="submitted" class="h-12 px-6 rounded-full bg-[#004241] text-white font-semibold hover:bg-[#003535] transition inline-flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                <?= htmlspecialchars($canBypassPayment ? $t('site.publish_now', 'Publier maintenant') : ($isEditing ? $t('site.resubmit', 'Renvoyer en validation') : $t('site.publish', 'Publier'))) ?>
            </button>
        </div>
    </div>
</form>

<div id="publish-feedback-overlay" class="fixed inset-0 z-[120] hidden items-center justify-center bg-[#004241]/25 px-4">
    <div class="w-full max-w-md rounded-[28px] border border-[#DED8CE] bg-[#F8F6F2] p-6 shadow-[0_24px_60px_rgba(0,66,65,0.18)]">
        <div class="flex items-start gap-4">
            <div id="publish-feedback-icon" class="mt-1 flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-[#004241] text-[#F8F6F2]">
                <svg id="publish-feedback-spinner" class="h-5 w-5 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-opacity="0.25" stroke-width="3"></circle>
                    <path d="M21 12a9 9 0 00-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round"></path>
                </svg>
                <svg id="publish-feedback-check" class="hidden h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <svg id="publish-feedback-error-icon" class="hidden h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M4.93 19h14.14c1.54 0 2.5-1.67 1.73-3L13.73 4c-.77-1.33-2.69-1.33-3.46 0L3.2 16c-.77 1.33.19 3 1.73 3z"/>
                </svg>
            </div>
            <div class="min-w-0">
                <h2 id="publish-feedback-title" class="text-[20px] font-semibold leading-7 text-[#1B4B3B]">Transmission en cours...</h2>
                <p id="publish-feedback-message" class="mt-2 max-h-[160px] overflow-y-auto text-sm leading-6 text-[#004241]/80">Nous envoyons votre article à l’espace rédacteur. Vous pouvez patienter quelques instants.</p>
            </div>
        </div>

        <div class="mt-6 flex justify-end">
            <button type="button" id="publish-feedback-button" class="hidden inline-flex h-11 items-center justify-center rounded-full bg-[#004241] px-6 text-sm font-semibold text-[#F8F6F2] transition hover:bg-[#003535]">
                Continuer
            </button>
        </div>
    </div>
</div>

<div id="stripe-payment-overlay" class="fixed inset-0 z-[130] hidden overflow-y-auto bg-[#004241]/35 px-4 py-6 md:items-center md:justify-center">
    <div class="mx-auto w-full max-w-xl rounded-[28px] border border-[#DED8CE] bg-white p-6 shadow-[0_24px_60px_rgba(0,66,65,0.18)] md:my-0">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#004241]/48">Validation éditoriale</p>
                <h2 class="mt-2 text-[24px] font-semibold leading-8 text-[#1B4B3B]">Votre article est prêt à être soumis</h2>
                <p class="mt-2 text-sm leading-6 text-[#004241]/80">
                    Votre brouillon est bien enregistré. Réglez maintenant <span id="stripe-payment-price-label" class="font-semibold text-[#004241]"><?= htmlspecialchars($publicationPriceLabel) ?></span> pour l’envoyer à notre équipe éditoriale et passer à l’étape de validation.
                </p>
            </div>
            <button type="button" id="stripe-payment-close" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-[#DED8CE] text-[#004241] transition hover:bg-white" aria-label="Fermer le paiement">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div class="mt-6 rounded-[24px] border border-[#DED8CE] bg-white p-5 shadow-[0_12px_28px_rgba(0,66,65,0.06)]">
            <div id="stripe-payment-element"></div>
            <p id="stripe-payment-error" class="mt-4 hidden text-sm text-red-600"></p>
        </div>

        <div class="mt-6 flex items-center justify-between gap-4">
            <p class="text-xs leading-5 text-[#004241]/58">Une fois le paiement confirmé, votre article part en relecture. Si la soumission n’est pas retenue, l’équipe pourra ensuite gérer un remboursement.</p>
            <div class="flex gap-3">
                <button type="button" id="stripe-payment-cancel" class="h-11 rounded-full border border-[#DED8CE] bg-white px-5 text-sm font-medium text-[#004241] transition hover:bg-[#F1F6F5]">
                    Plus tard
                </button>
                <button type="button" id="stripe-payment-submit" class="inline-flex h-11 items-center justify-center rounded-full bg-[#004241] px-6 text-sm font-semibold text-[#F8F6F2] transition hover:bg-[#003535] disabled:cursor-not-allowed disabled:opacity-60">
                    Payer
                </button>
            </div>
        </div>
    </div>
</div>

<?php if ($stripeKey !== ''): ?>
<script src="https://js.stripe.com/v3/"></script>
<?php endif; ?>

<script>
(() => {
    const i18n = <?= json_encode($jsTranslations, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const form = document.getElementById('contributor-new-article-form');
    const input = document.getElementById('cover_image');
    const previewWrapper = document.getElementById('cover-image-preview-wrapper');
    const preview = document.getElementById('cover-image-preview');
    const emptyState = document.getElementById('cover-image-empty-state');
    const nameEl = document.getElementById('cover-image-name');
    const sizeEl = document.getElementById('cover-image-size');
    const errorEl = document.getElementById('cover-image-client-error');
    const submitButtons = Array.from(document.querySelectorAll('button[type="submit"]'));
    const publishButton = form ? form.querySelector('button[name="status"][value="submitted"]') : null;
    const draftButton = form ? form.querySelector('button[name="status"][value="draft"]') : null;
    const overlay = document.getElementById('publish-feedback-overlay');
    const overlayTitle = document.getElementById('publish-feedback-title');
    const overlayMessage = document.getElementById('publish-feedback-message');
    const overlayButton = document.getElementById('publish-feedback-button');
    const spinner = document.getElementById('publish-feedback-spinner');
    const check = document.getElementById('publish-feedback-check');
    const errorIcon = document.getElementById('publish-feedback-error-icon');
    const paymentOverlay = document.getElementById('stripe-payment-overlay');
    const paymentCloseButton = document.getElementById('stripe-payment-close');
    const paymentCancelButton = document.getElementById('stripe-payment-cancel');
    const paymentSubmitButton = document.getElementById('stripe-payment-submit');
    const paymentError = document.getElementById('stripe-payment-error');
    const paymentElementHost = document.getElementById('stripe-payment-element');
    const autosaveStatus = document.getElementById('draft-autosave-status');

    if (!form || !input || !previewWrapper || !preview || !emptyState || !nameEl || !sizeEl || !errorEl || !publishButton || !draftButton || !overlay || !overlayTitle || !overlayMessage || !overlayButton || !spinner || !check || !errorIcon || !paymentOverlay || !paymentCloseButton || !paymentCancelButton || !paymentSubmitButton || !paymentError || !paymentElementHost || !autosaveStatus) {
        return;
    }

    const stripeKey = form.dataset.stripeKey || '';
    const paymentCreateUrl = form.dataset.paymentCreateUrl || '';
    const paymentConfirmUrl = form.dataset.paymentConfirmUrl || '';
    const publicationPriceLabel = form.dataset.publicationPriceLabel || '';
    const canBypassPayment = form.dataset.canBypassPayment === '1';
    const csrfToken = form.querySelector('input[name="_token"]')?.value || '';

    let previewUrl = null;
    let pendingRedirectUrl = null;
    let stripe = null;
    let elements = null;
    let paymentElement = null;
    let currentPaymentId = null;
    let autosaveTimeout = null;
    let autosaveDirty = false;
    let autosaveInFlight = false;
    let autosaveQueued = false;
    let autosaveLastFingerprint = null;
    let isPublishing = false;

    function setAutosaveStatus(message, tone = 'neutral') {
        autosaveStatus.textContent = message;
        autosaveStatus.className = 'text-sm';

        if (tone === 'success') {
            autosaveStatus.classList.add('text-[#006664]');
            return;
        }

        if (tone === 'error') {
            autosaveStatus.classList.add('text-[#AE422E]');
            return;
        }

        autosaveStatus.classList.add('text-[#004241]/60');
    }

    function currentLocalTimeLabel() {
        return new Date().toLocaleTimeString(document.documentElement.lang === 'nl' ? 'nl-BE' : 'fr-BE', {
            hour: '2-digit',
            minute: '2-digit',
        });
    }

    function updateCharCounter(field) {
        if (!field) {
            return;
        }

        const targetId = field.dataset.charTarget || '';
        const max = Number(field.dataset.charMax || '0');
        const target = targetId ? document.getElementById(targetId) : null;

        if (!target || max <= 0) {
            return;
        }

        const currentLength = (field.value || '').length;
        target.textContent = `${currentLength}/${max}`;
        target.className = `text-xs font-medium tabular-nums ${currentLength >= max ? 'text-[#AE422E]' : 'text-[#004241]/50'}`;
    }

    function buildAutosaveFingerprint() {
        const title = form.querySelector('#title')?.value || '';
        const excerpt = form.querySelector('#excerpt')?.value || '';
        const content = form.querySelector('#content')?.value || '';
        const category = form.querySelector('#category_id')?.value || '';
        const readingTime = form.querySelector('#reading_time')?.value || '';
        const file = input.files && input.files[0] ? `${input.files[0].name}:${input.files[0].size}:${input.files[0].lastModified}` : '';

        return [title, excerpt, content, category, readingTime, file].join('|~|');
    }

    function updateDraftRoute(data) {
        if (!data || !data.edit_url) {
            return;
        }

        if (form.action !== data.edit_url) {
            form.action = data.edit_url;
            if (window.location.pathname === '/contributor/new') {
                window.history.replaceState({}, '', data.edit_url);
            }
        }
    }

    async function autosaveDraft({ immediate = false } = {}) {
        if (isPublishing) {
            return;
        }

        const fingerprint = buildAutosaveFingerprint();
        const hasMeaningfulContent = form.querySelector('#title')?.value.trim()
            || form.querySelector('#excerpt')?.value.trim()
            || form.querySelector('#content')?.value.trim()
            || form.querySelector('#category_id')?.value
            || form.querySelector('#reading_time')?.value
            || (input.files && input.files[0]);

        if (!hasMeaningfulContent) {
            return;
        }

        if (!immediate && fingerprint === autosaveLastFingerprint) {
            autosaveDirty = false;
            return;
        }

        if (autosaveInFlight) {
            autosaveQueued = true;
            return;
        }

        autosaveInFlight = true;
        autosaveDirty = false;
        setAutosaveStatus(i18n.saving_draft);

        const formData = new FormData(form);
        formData.set('status', 'draft');
        formData.set('autosave', '1');

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Autosave': '1',
                },
                credentials: 'same-origin',
                body: formData,
            });

            const data = await readJsonResponse(response);

            if (!response.ok) {
                throw new Error(normalizeMessage(data.message, i18n.draft_save_failed));
            }

            autosaveLastFingerprint = buildAutosaveFingerprint();
            updateDraftRoute(data);
            setAutosaveStatus(`${i18n.draft_saved_at} ${currentLocalTimeLabel()}`, 'success');
        } catch (error) {
            autosaveDirty = true;
            setAutosaveStatus(normalizeMessage(error.message, i18n.autosave_failed), 'error');
        } finally {
            autosaveInFlight = false;

            if (autosaveQueued) {
                autosaveQueued = false;
                autosaveDraft({ immediate: true });
            }
        }
    }

    function scheduleAutosave(delay = 1200) {
        if (isPublishing) {
            return;
        }

        autosaveDirty = true;
        setAutosaveStatus(i18n.unsaved_changes);

        if (autosaveTimeout) {
            window.clearTimeout(autosaveTimeout);
        }

        autosaveTimeout = window.setTimeout(() => {
            autosaveTimeout = null;
            autosaveDraft();
        }, delay);
    }

    function formatBytes(bytes) {
        if (bytes < 1024) return `${bytes} octets`;
        if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} Ko`;
        return `${(bytes / (1024 * 1024)).toFixed(2)} Mo`;
    }

    function setButtonsDisabled(disabled) {
        submitButtons.forEach((button) => {
            button.disabled = disabled;
        });
    }

    function resetOverlayState() {
        overlayTitle.textContent = i18n.sending_in_progress_title;
        overlayMessage.textContent = i18n.sending_in_progress_text;
        overlayButton.textContent = i18n.continue;
        overlayButton.classList.add('hidden');
        spinner.classList.remove('hidden');
        check.classList.add('hidden');
        errorIcon.classList.add('hidden');
        pendingRedirectUrl = null;
    }

    function openOverlay() {
        resetOverlayState();
        overlay.classList.remove('hidden');
        overlay.classList.add('flex');
    }

    function showOverlayProgress(title, message) {
        openOverlay();
        overlayTitle.textContent = title;
        overlayMessage.textContent = message;
    }

    function setOverlaySuccess(title, message, redirectUrl = null) {
        overlayTitle.textContent = title;
        overlayMessage.textContent = message;
        overlayButton.textContent = i18n.continue;
        overlayButton.classList.remove('hidden');
        spinner.classList.add('hidden');
        errorIcon.classList.add('hidden');
        check.classList.remove('hidden');
        pendingRedirectUrl = redirectUrl;
    }

    function setOverlayError(message, title = i18n.action_interrupted) {
        overlayTitle.textContent = title;
        overlayMessage.textContent = message;
        overlayButton.textContent = i18n.close || 'Fermer';
        overlayButton.classList.remove('hidden');
        spinner.classList.add('hidden');
        check.classList.add('hidden');
        errorIcon.classList.remove('hidden');
        pendingRedirectUrl = null;
    }

    function normalizeMessage(message, fallback) {
        const cleaned = String(message || '')
            .replace(/\s+/g, ' ')
            .trim();

        if (!cleaned) {
            return fallback;
        }

        if (cleaned.length > 240) {
            return fallback;
        }

        if (cleaned.startsWith('<!DOCTYPE') || cleaned.startsWith('<html') || cleaned.includes('http://127.0.0.1')) {
            return fallback;
        }

        return cleaned;
    }

    function extractErrorMessage(data, fallback) {
        if (data && typeof data === 'object') {
            if (data.errors && typeof data.errors === 'object') {
                const firstField = Object.keys(data.errors)[0];
                const firstValue = firstField ? data.errors[firstField] : null;

                if (Array.isArray(firstValue) && firstValue[0]) {
                    return normalizeMessage(firstValue[0], fallback);
                }
            }

            if (typeof data.message === 'string' && data.message.trim() !== '') {
                return normalizeMessage(data.message, fallback);
            }
        }

        return fallback;
    }

    function resetPreview() {
        if (previewUrl) {
            URL.revokeObjectURL(previewUrl);
            previewUrl = null;
        }

        preview.removeAttribute('src');
        previewWrapper.classList.add('hidden');
        emptyState.classList.remove('hidden');
        nameEl.textContent = '';
        sizeEl.textContent = '';
        errorEl.textContent = '';
        errorEl.classList.add('hidden');
        setButtonsDisabled(false);
    }

    function resetPaymentElement() {
        if (paymentElement) {
            paymentElement.unmount();
            paymentElement = null;
        }

        elements = null;
        currentPaymentId = null;
        paymentElementHost.innerHTML = '';
        paymentError.textContent = '';
        paymentError.classList.add('hidden');
        paymentSubmitButton.disabled = false;
    }

    function closePaymentOverlay() {
        paymentOverlay.classList.add('hidden');
        paymentOverlay.classList.remove('flex');
        document.body.style.overflow = '';
        resetPaymentElement();
        setButtonsDisabled(false);
        isPublishing = false;
        setAutosaveStatus(i18n.draft_saved_auto);
    }

    function openPaymentOverlay() {
        paymentOverlay.classList.remove('hidden');
        paymentOverlay.classList.add('flex');
        document.body.style.overflow = 'hidden';
        paymentError.textContent = '';
        paymentError.classList.add('hidden');
        paymentSubmitButton.disabled = false;
    }

    function showPaymentError(message) {
        paymentError.textContent = message;
        paymentError.classList.remove('hidden');
        paymentSubmitButton.disabled = false;
    }

    function fallbackMessageForResponse(response, fallback) {
        if (response.status === 419) {
            return i18n.session_expired;
        }

        if (response.status >= 500) {
            return i18n.server_problem;
        }

        return fallback;
    }

    async function readJsonResponse(response, fallback = i18n.unexpected_response) {
        const contentType = response.headers.get('content-type') || '';

        if (!contentType.includes('application/json')) {
            const text = await response.text();
            throw new Error(normalizeMessage(text, fallbackMessageForResponse(response, fallback)));
        }

        return response.json();
    }

    async function preparePayment(submissionId) {
        if (!stripeKey) {
            throw new Error(i18n.stripe_key_missing);
        }

        showOverlayProgress(i18n.preparing_submission_title, i18n.preparing_payment_text.replace(':amount', publicationPriceLabel));

        const response = await fetch(paymentCreateUrl, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
            body: JSON.stringify({ submission_id: submissionId }),
        });

        const data = await readJsonResponse(response, i18n.payment_prepare_failed);

        if (!response.ok) {
            throw new Error(normalizeMessage(data.message, i18n.payment_prepare_stripe_failed));
        }

        if (!window.Stripe) {
            throw new Error(i18n.stripe_load_failed);
        }

        if (!stripe) {
            stripe = window.Stripe(stripeKey);
        }

        resetPaymentElement();
        currentPaymentId = data.payment_id;
        elements = stripe.elements({
            clientSecret: data.client_secret,
            appearance: {
                theme: 'stripe',
                variables: {
                    colorPrimary: '#004241',
                    colorBackground: '#ffffff',
                    colorText: '#1B4B3B',
                    borderRadius: '18px',
                },
            },
        });

        paymentElement = elements.create('payment');
        paymentElement.mount('#stripe-payment-element');

        overlay.classList.add('hidden');
        overlay.classList.remove('flex');
        openPaymentOverlay();
    }

    async function confirmSubmissionPayment() {
        if (!stripe || !elements || !currentPaymentId) {
            showPaymentError(i18n.payment_form_not_ready);
            return;
        }

        paymentSubmitButton.disabled = true;
        paymentError.textContent = '';
        paymentError.classList.add('hidden');

        const stripeResult = await stripe.confirmPayment({
            elements,
            redirect: 'if_required',
        });

        if (stripeResult.error) {
            showPaymentError(normalizeMessage(stripeResult.error.message, i18n.payment_refused));
            return;
        }

        paymentOverlay.classList.add('hidden');
        paymentOverlay.classList.remove('flex');
        document.body.style.overflow = '';
        showOverlayProgress(i18n.payment_validated_title, i18n.payment_validated_text);

        const response = await fetch(paymentConfirmUrl, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
            body: JSON.stringify({ payment_id: currentPaymentId }),
        });

        const data = await readJsonResponse(response, i18n.payment_confirm_failed);

        if (!response.ok) {
            setButtonsDisabled(false);
            setOverlayError(normalizeMessage(data.message, i18n.payment_done_but_local_failed));
            return;
        }

        setButtonsDisabled(false);
        setOverlaySuccess(
            i18n.article_sent,
            normalizeMessage(data.message, i18n.article_sent_text),
            window.location.origin + '/contributor/dashboard'
        );
    }

    overlayButton.addEventListener('click', () => {
        if (pendingRedirectUrl) {
            window.location.href = pendingRedirectUrl;
            return;
        }

        overlay.classList.add('hidden');
        overlay.classList.remove('flex');
        setButtonsDisabled(false);
    });

    [paymentCloseButton, paymentCancelButton].forEach((button) => {
        button.addEventListener('click', closePaymentOverlay);
    });

    paymentSubmitButton.addEventListener('click', async () => {
        try {
            await confirmSubmissionPayment();
        } catch (error) {
            setButtonsDisabled(false);
            setOverlayError(
                normalizeMessage(error.message, i18n.payment_finalize_failed),
                i18n.payment_interrupted
            );
        }
    });

    input.addEventListener('change', () => {
        const file = input.files && input.files[0] ? input.files[0] : null;
        const maxBytes = Number(input.dataset.maxBytes || '0');
        const maxLabel = input.dataset.maxLabel || '';

        if (!file) {
            resetPreview();
            return;
        }

        if (maxBytes > 0 && file.size > maxBytes) {
            resetPreview();
            errorEl.textContent = i18n.image_too_large.replace(':limit', maxLabel);
            errorEl.classList.remove('hidden');
            setButtonsDisabled(true);
            return;
        }

        if (previewUrl) {
            URL.revokeObjectURL(previewUrl);
        }

        previewUrl = URL.createObjectURL(file);
        preview.src = previewUrl;
        nameEl.textContent = file.name;
        sizeEl.textContent = formatBytes(file.size);
        previewWrapper.classList.remove('hidden');
        emptyState.classList.add('hidden');
        errorEl.textContent = '';
        errorEl.classList.add('hidden');
        setButtonsDisabled(false);
        scheduleAutosave(250);
    });

    ['title', 'excerpt', 'content', 'category_id', 'reading_time'].forEach((fieldId) => {
        const field = document.getElementById(fieldId);

        if (!field) {
            return;
        }

        field.addEventListener('input', () => scheduleAutosave());
        field.addEventListener('change', () => scheduleAutosave());
    });

    document.querySelectorAll('[data-char-target][data-char-max]').forEach((field) => {
        updateCharCounter(field);
        field.addEventListener('input', () => updateCharCounter(field));
        field.addEventListener('change', () => updateCharCounter(field));
    });

    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'hidden' && autosaveDirty && !autosaveInFlight && !isPublishing) {
            autosaveDraft({ immediate: true });
        }
    });

    form.addEventListener('submit', (event) => {
        const submitter = event.submitter;
        const isPublishAction = submitter && submitter.name === 'status' && submitter.value === 'submitted';
        const isDraftAction = submitter && submitter.name === 'status' && submitter.value === 'draft';

        if (!isPublishAction && !isDraftAction) {
            return;
        }

        event.preventDefault();

        if (submitButtons.some((button) => button.disabled)) {
            return;
        }

        isPublishing = isPublishAction;
        setButtonsDisabled(true);
        showOverlayProgress(
            i18n.saving_before_publish,
            isPublishAction
                ? (canBypassPayment
                    ? i18n.saving_before_direct_publish
                    : i18n.saving_before_payment)
                : i18n.saving_draft_text
        );

        const formData = new FormData(form);
        formData.set('status', isPublishAction ? 'submitted' : 'draft');
        const responseFallback = isDraftAction
            ? i18n.draft_save_failed
            : i18n.submit_prepare_failed;
        const errorTitle = isDraftAction ? i18n.draft_not_saved : i18n.send_impossible;

        fetch(form.action, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken,
            },
            credentials: 'same-origin',
            body: formData,
        })
            .then(async (response) => {
                const data = await readJsonResponse(response, responseFallback);

                if (!response.ok) {
                    throw new Error(extractErrorMessage(data, responseFallback));
                }

                if (data.requires_payment) {
                    if (!data.submission_id) {
                        throw new Error(i18n.submission_not_ready_for_stripe);
                    }

                    await preparePayment(data.submission_id);
                    return;
                }

                if (isDraftAction) {
                    updateDraftRoute(data);
                }

                setButtonsDisabled(false);
                setOverlaySuccess(
                    data.notice && data.notice.title ? data.notice.title : (isDraftAction ? i18n.draft_saved : i18n.article_sent),
                    normalizeMessage(
                        data.notice && data.notice.message ? data.notice.message : '',
                        isDraftAction
                            ? i18n.draft_saved_text
                            : i18n.article_sent_text
                    ),
                    data.redirect_url || null
                );
            })
            .catch((error) => {
                setButtonsDisabled(false);
                setOverlayError(
                    normalizeMessage(error.message, responseFallback),
                    errorTitle
                );
                isPublishing = false;
            });
    });
})();
</script>
