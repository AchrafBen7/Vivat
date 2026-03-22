<?php
$categories = $categories ?? [];
$errors = $errors ?? [];
$old = $old ?? [];

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
<h1 class="font-medium text-[#004241] text-2xl mb-2">Nouvel article</h1>
<p class="text-[#004241]/80 mb-8">Partagez vos idées avec la communauté Vivat</p>

<form id="contributor-new-article-form" action="<?= url('/contributor/new') ?>" method="post" enctype="multipart/form-data" class="flex flex-col gap-6">
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
                <span class="text-[#004241]/70 text-sm">Cliquez pour ajouter une image</span>
                <span class="block text-gray-400 text-xs mt-1">JPG, PNG - max 5 Mo</span>
                <span class="block text-gray-400 text-xs mt-1">Limite PHP locale actuelle: <?= htmlspecialchars($uploadMaxRaw) ?></span>
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
        <label for="title" class="block font-medium text-[#004241] mb-2">Titre de l'article</label>
        <input type="text" name="title" id="title" value="<?= htmlspecialchars($old['title'] ?? '') ?>" placeholder="Titre de l'article" required
            class="w-full max-w-2xl h-12 pl-4 pr-4 rounded-xl border border-[#DED8CE99] bg-[#F8F6F2] text-[#004241] placeholder:text-gray-400 outline-none focus:ring-2 focus:ring-[#004241]/25 focus:border-[#004241]">
        <?php if (!empty($errors['title'])): ?>
        <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars(is_array($errors['title']) ? $errors['title'][0] : $errors['title']) ?></p>
        <?php endif; ?>
    </div>

    <div class="flex gap-4 flex-wrap">
        <div>
            <label for="category_id" class="block font-medium text-[#004241] mb-2">Catégorie</label>
            <select name="category_id" id="category_id" class="h-12 pl-4 pr-4 rounded-xl border border-[#DED8CE99] bg-[#F8F6F2] text-[#004241] outline-none focus:ring-2 focus:ring-[#004241]/25">
                <option value="">Choisir...</option>
                <?php foreach ($categories as $cat): ?>
                <option value="<?= htmlspecialchars($cat['id']) ?>" <?= ($old['category_id'] ?? '') === $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label for="reading_time" class="block font-medium text-[#004241] mb-2">Temps</label>
            <input type="number" name="reading_time" id="reading_time" value="<?= htmlspecialchars($old['reading_time'] ?? '5') ?>" placeholder="5 min" min="1" max="120"
                class="h-12 pl-4 pr-4 rounded-xl border border-[#DED8CE99] bg-[#F8F6F2] text-[#004241] placeholder:text-gray-400 outline-none focus:ring-2 focus:ring-[#004241]/25 w-24">
        </div>
    </div>

    <div>
        <label for="excerpt" class="block font-medium text-[#004241] mb-2">Extrait / Chapô</label>
        <textarea name="excerpt" id="excerpt" rows="3" placeholder="Commencez à écrire votre article ici..."
            class="w-full max-w-2xl pl-4 pr-4 py-3 rounded-xl border border-[#DED8CE99] bg-[#F8F6F2] text-[#004241] placeholder:text-gray-400 outline-none focus:ring-2 focus:ring-[#004241]/25"><?= htmlspecialchars($old['excerpt'] ?? '') ?></textarea>
    </div>

    <div>
        <label for="content" class="block font-medium text-[#004241] mb-2">Contenu</label>
        <textarea name="content" id="content" rows="12" placeholder="Commencez à écrire votre article ici..." required
            class="w-full max-w-2xl pl-4 pr-4 py-3 rounded-xl border border-[#DED8CE99] bg-[#F8F6F2] text-[#004241] placeholder:text-gray-400 outline-none focus:ring-2 focus:ring-[#004241]/25"><?= htmlspecialchars($old['content'] ?? '') ?></textarea>
        <?php if (!empty($errors['content'])): ?>
        <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars(is_array($errors['content']) ? $errors['content'][0] : $errors['content']) ?></p>
        <?php endif; ?>
    </div>

    <div class="flex items-center justify-between pt-4">
        <span class="text-sm text-[#004241]/60">Brouillon sauvegardé automatiquement</span>
        <div class="flex gap-3">
            <button type="submit" name="status" value="draft" class="h-12 px-6 rounded-full border border-gray-300 bg-white text-[#004241] font-medium hover:bg-gray-50 transition disabled:opacity-50 disabled:cursor-not-allowed">
                Brouillon
            </button>
            <button type="submit" name="status" value="submitted" class="h-12 px-6 rounded-full bg-[#004241] text-white font-semibold hover:bg-[#003535] transition inline-flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                Publier
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

<script>
(() => {
    const form = document.getElementById('contributor-new-article-form');
    const input = document.getElementById('cover_image');
    const previewWrapper = document.getElementById('cover-image-preview-wrapper');
    const preview = document.getElementById('cover-image-preview');
    const emptyState = document.getElementById('cover-image-empty-state');
    const nameEl = document.getElementById('cover-image-name');
    const sizeEl = document.getElementById('cover-image-size');
    const errorEl = document.getElementById('cover-image-client-error');
    const submitButtons = Array.from(document.querySelectorAll('button[type="submit"]'));
    const publishButton = form.querySelector('button[name="status"][value="submitted"]');
    const overlay = document.getElementById('publish-feedback-overlay');
    const overlayTitle = document.getElementById('publish-feedback-title');
    const overlayMessage = document.getElementById('publish-feedback-message');
    const overlayButton = document.getElementById('publish-feedback-button');
    const spinner = document.getElementById('publish-feedback-spinner');
    const check = document.getElementById('publish-feedback-check');
    const errorIcon = document.getElementById('publish-feedback-error-icon');

    if (!form || !input || !previewWrapper || !preview || !emptyState || !nameEl || !sizeEl || !errorEl || !publishButton || !overlay || !overlayTitle || !overlayMessage || !overlayButton || !spinner || !check || !errorIcon) {
        return;
    }

    let previewUrl = null;
    let pendingRedirectUrl = null;

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
        overlayTitle.textContent = 'Transmission en cours...';
        overlayMessage.textContent = 'Nous envoyons votre article à l’espace rédacteur. Vous pouvez patienter quelques instants.';
        overlayButton.textContent = 'Continuer';
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

    function normalizeMessage(message, fallback) {
        const cleaned = String(message || '')
            .replace(/\s+/g, ' ')
            .trim();

        if (!cleaned) {
            return fallback;
        }

        if (cleaned.length > 220) {
            return fallback;
        }

        if (cleaned.startsWith('<!DOCTYPE') || cleaned.startsWith('<html') || cleaned.includes('http://127.0.0.1')) {
            return fallback;
        }

        return cleaned;
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

    overlayButton.addEventListener('click', () => {
        if (pendingRedirectUrl) {
            window.location.href = pendingRedirectUrl;
            return;
        }

        overlay.classList.add('hidden');
        overlay.classList.remove('flex');
        setButtonsDisabled(false);
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
            errorEl.textContent = `Cette image dépasse la limite PHP locale actuelle (${maxLabel}). Choisissez un fichier plus léger.`;
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
    });

    form.addEventListener('submit', (event) => {
        const submitter = event.submitter;
        const isPublishAction = submitter && submitter.name === 'status' && submitter.value === 'submitted';

        if (!isPublishAction) {
            return;
        }

        event.preventDefault();

        if (submitButtons.some((button) => button.disabled)) {
            return;
        }

        setButtonsDisabled(true);
        openOverlay();
        let statusField = form.querySelector('input[data-publish-status]');
        if (!statusField) {
            statusField = document.createElement('input');
            statusField.type = 'hidden';
            statusField.name = 'status';
            statusField.setAttribute('data-publish-status', '1');
            form.appendChild(statusField);
        }
        statusField.value = 'submitted';

        window.setTimeout(() => {
            HTMLFormElement.prototype.submit.call(form);
        }, 40);
    });
})();
</script>
