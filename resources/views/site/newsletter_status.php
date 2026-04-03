<?php
$heading = $heading ?? 'Newsletter';
$message = $message ?? '';
$success = (bool) ($success ?? true);
$t = fn (string $key, ?string $fallback = null) => __($key) !== $key ? __($key) : ($fallback ?? $key);
?>
<section class="mx-auto max-w-[920px] px-[18px] py-16 md:px-8 lg:px-10 xl:px-20">
    <div class="rounded-[34px] border border-[#004241]/10 bg-[#F4F8F7] p-8 shadow-[0_24px_64px_rgba(0,66,65,0.08)] md:p-12">
        <span class="inline-flex items-center justify-center rounded-full px-4 py-2 text-sm font-medium <?= $success ? 'bg-[#DCEEE9] text-[#004241]' : 'bg-[#FBE3DE] text-[#AE422E]' ?>">
            <?= $success ? 'Newsletter' : htmlspecialchars($t('site.attention', 'Attention')) ?>
        </span>
        <h1 class="mt-5 text-3xl font-medium leading-tight text-[#004241] md:text-5xl"><?= htmlspecialchars($heading) ?></h1>
        <p class="mt-4 max-w-[60ch] text-base leading-7 text-[#004241]/78 md:text-lg"><?= htmlspecialchars($message) ?></p>
        <div class="mt-8 flex flex-wrap gap-3">
            <a href="/" class="inline-flex h-12 items-center justify-center rounded-full bg-[#004241] px-6 text-sm font-semibold text-white transition hover:bg-[#003535]">
                <?= htmlspecialchars($t('site.return_home', "Retour à l'accueil")) ?>
            </a>
            <a href="/articles" class="inline-flex h-12 items-center justify-center rounded-full border border-[#004241]/12 bg-white px-6 text-sm font-semibold text-[#004241] transition hover:bg-[#EBF1EF]">
                <?= htmlspecialchars($t('site.see_articles', 'Voir les articles')) ?>
            </a>
        </div>
    </div>
</section>
