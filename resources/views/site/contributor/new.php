<?php
$categories = $categories ?? [];
$errors = $errors ?? [];
$old = $old ?? [];
?>
<h1 class="font-medium text-[#004241] text-2xl mb-2">Nouvel article</h1>
<p class="text-[#004241]/80 mb-8">Partagez vos idées avec la communauté Vivat</p>

<form action="<?= url('/contributor/new') ?>" method="post" enctype="multipart/form-data" class="flex flex-col gap-6">
    <?= csrf_field() ?>

    <div class="rounded-[20px] border-2 border-dashed border-gray-300 bg-gray-50 p-12 text-center">
        <label class="cursor-pointer block">
            <input type="file" name="cover_image" accept="image/jpeg,image/png" class="hidden">
            <svg class="w-12 h-12 mx-auto text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            <span class="text-[#004241]/70 text-sm">Cliquez pour ajouter une image</span>
            <span class="block text-gray-400 text-xs mt-1">JPG, PNG - max 5 Mo</span>
        </label>
    </div>

    <div>
        <label for="title" class="block font-medium text-[#004241] mb-2">Titre de l'article</label>
        <input type="text" name="title" id="title" value="<?= htmlspecialchars($old['title'] ?? '') ?>" placeholder="Titre de l'article" required
            class="w-full max-w-2xl h-12 pl-4 pr-4 rounded-xl border border-gray-300 bg-white text-[#004241] placeholder:text-gray-400 outline-none focus:ring-2 focus:ring-[#004241]/25 focus:border-[#004241]">
        <?php if (!empty($errors['title'])): ?>
        <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars(is_array($errors['title']) ? $errors['title'][0] : $errors['title']) ?></p>
        <?php endif; ?>
    </div>

    <div class="flex gap-4 flex-wrap">
        <div>
            <label for="category_id" class="block font-medium text-[#004241] mb-2">Catégorie</label>
            <select name="category_id" id="category_id" class="h-12 pl-4 pr-4 rounded-xl border border-gray-300 bg-white text-[#004241] outline-none focus:ring-2 focus:ring-[#004241]/25">
                <option value="">Choisir...</option>
                <?php foreach ($categories as $cat): ?>
                <option value="<?= htmlspecialchars($cat['id']) ?>" <?= ($old['category_id'] ?? '') === $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label for="reading_time" class="block font-medium text-[#004241] mb-2">Temps</label>
            <input type="number" name="reading_time" id="reading_time" value="<?= htmlspecialchars($old['reading_time'] ?? '5') ?>" placeholder="5 min" min="1" max="120"
                class="h-12 pl-4 pr-4 rounded-xl border border-gray-300 bg-white text-[#004241] placeholder:text-gray-400 outline-none focus:ring-2 focus:ring-[#004241]/25 w-24">
        </div>
    </div>

    <div>
        <label for="excerpt" class="block font-medium text-[#004241] mb-2">Extrait / Chapô</label>
        <textarea name="excerpt" id="excerpt" rows="3" placeholder="Commencez à écrire votre article ici..."
            class="w-full max-w-2xl pl-4 pr-4 py-3 rounded-xl border border-gray-300 bg-white text-[#004241] placeholder:text-gray-400 outline-none focus:ring-2 focus:ring-[#004241]/25"><?= htmlspecialchars($old['excerpt'] ?? '') ?></textarea>
    </div>

    <div>
        <label for="content" class="block font-medium text-[#004241] mb-2">Contenu</label>
        <textarea name="content" id="content" rows="12" placeholder="Commencez à écrire votre article ici..." required
            class="w-full max-w-2xl pl-4 pr-4 py-3 rounded-xl border border-gray-300 bg-white text-[#004241] placeholder:text-gray-400 outline-none focus:ring-2 focus:ring-[#004241]/25"><?= htmlspecialchars($old['content'] ?? '') ?></textarea>
        <?php if (!empty($errors['content'])): ?>
        <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars(is_array($errors['content']) ? $errors['content'][0] : $errors['content']) ?></p>
        <?php endif; ?>
    </div>

    <div class="flex items-center justify-between pt-4">
        <span class="text-sm text-[#004241]/60">Brouillon sauvegardé automatiquement</span>
        <div class="flex gap-3">
            <button type="submit" name="status" value="draft" class="h-12 px-6 rounded-full border border-gray-300 bg-white text-[#004241] font-medium hover:bg-gray-50 transition">
                Brouillon
            </button>
            <button type="submit" name="status" value="submitted" class="h-12 px-6 rounded-full bg-[#004241] text-white font-semibold hover:bg-[#003535] transition inline-flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                Publier
            </button>
        </div>
    </div>
</form>
