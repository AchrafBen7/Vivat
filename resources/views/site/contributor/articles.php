<?php
$submissions = $submissions ?? [];

$statusStyles = [
    'draft' => [
        'label' => 'Brouillon',
        'pill' => 'background: #F3E8CC; color: #7A5A14; border: 1px solid rgba(122,90,20,0.18);',
        'dot' => '#C69214',
    ],
    'pending' => [
        'label' => 'En attente',
        'pill' => 'background: rgba(0,66,65,0.10); color: #004241; border: 1px solid rgba(0,66,65,0.16);',
        'dot' => '#004241',
    ],
    'approved' => [
        'label' => 'Approuvé',
        'pill' => 'background: rgba(82,126,126,0.14); color: #2D5C5C; border: 1px solid rgba(82,126,126,0.18);',
        'dot' => '#527E7E',
    ],
    'rejected' => [
        'label' => 'Rejeté',
        'pill' => 'background: rgba(174,66,46,0.10); color: #AE422E; border: 1px solid rgba(174,66,46,0.16);',
        'dot' => '#AE422E',
    ],
];
?>
<h1 class="font-medium text-[#004241] text-2xl mb-2">Mes articles</h1>
<p class="text-[#004241]/80 mb-8">Vos soumissions et brouillons</p>

<div class="rounded-[30px] bg-[#EBF1EF] border border-[#EBF1EF] p-8">
    <?php if (empty($submissions)): ?>
    <p class="text-[#004241]/70">Vous n'avez pas encore soumis d'article.</p>
    <a href="<?= url('/contributor/new') ?>" class="inline-flex items-center justify-center rounded-full bg-[#004241] text-white font-semibold text-sm h-12 px-6 hover:bg-[#003535] transition mt-6">
        Rédiger votre premier article
    </a>
    <?php else: ?>
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-5">
        <?php foreach ($submissions as $sub): ?>
        <?php
        $status = $sub['status'] ?? 'draft';
        $statusStyle = $statusStyles[$status] ?? $statusStyles['draft'];
        $cover = $sub['cover_image_path'] ?? null;
        $fallbackImage = $status === 'pending'
            ? '/quotidien.jpg'
            : ($status === 'approved' ? '/chezsoi.jpg' : ($status === 'rejected' ? '/sante.jpg' : '/finance.jpg'));
        $coverSrc = $cover ?: $fallbackImage;
        ?>
        <article class="group relative overflow-hidden rounded-[30px] border border-[#004241]/10 bg-white shadow-[0_18px_40px_rgba(0,66,65,0.08)] transition-all duration-500 ease-[cubic-bezier(0.22,1,0.36,1)] hover:-translate-y-1 hover:scale-[1.01]">
            <div class="relative h-[228px] overflow-hidden">
                <img src="<?= htmlspecialchars($coverSrc) ?>" alt="<?= htmlspecialchars($sub['title']) ?>" class="absolute inset-0 h-full w-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-t from-[#002F2F]/70 via-[#002F2F]/16 to-transparent"></div>
                <div class="absolute left-[18px] right-[18px] bottom-[18px] rounded-[21px] bg-[rgba(190,190,190,0.10)] backdrop-blur-[15px] border border-[rgba(230,230,230,0.20)] p-[18px]">
                    <?php if (!empty($sub['category']['name'])): ?>
                    <span class="inline-flex items-center justify-center rounded-full px-3 py-[7px] text-xs font-medium text-white border border-[rgba(230,230,230,0.25)] bg-[rgba(190,190,190,0.10)]">
                        <?= htmlspecialchars($sub['category']['name']) ?>
                    </span>
                    <?php endif; ?>
                    <h2 class="mt-3 text-[22px] leading-[28px] font-semibold text-white line-clamp-3"><?= htmlspecialchars($sub['title']) ?></h2>
                    <div class="mt-3 flex items-center gap-3 text-xs text-white/80">
                        <span><?= htmlspecialchars($sub['created_at'] ?? '') ?></span>
                        <?php if (!empty($sub['reading_time'])): ?>
                        <span>•</span>
                        <span><?= (int) $sub['reading_time'] ?> min</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <div class="flex items-center justify-between gap-4">
                    <span class="inline-flex items-center gap-2 rounded-full px-3 py-[7px] text-xs font-medium" style="<?= htmlspecialchars($statusStyle['pill']) ?>">
                        <span class="h-2.5 w-2.5 rounded-full" style="background: <?= htmlspecialchars($statusStyle['dot']) ?>"></span>
                        <?= htmlspecialchars($sub['status_label'] ?? $statusStyle['label']) ?>
                    </span>
                    <span class="text-xs font-medium uppercase tracking-[0.08em] text-[#004241]/45">Soumission</span>
                </div>

                <p class="mt-4 min-h-[72px] text-sm leading-6 text-[#004241]/72">
                    <?= htmlspecialchars($sub['excerpt'] ?: 'Votre article est enregistré dans votre espace rédacteur et attend la prochaine étape de traitement.') ?>
                </p>

                <div class="mt-5 flex items-center justify-between gap-3 border-t border-[#004241]/10 pt-4">
                    <div class="text-xs text-[#004241]/55">
                        <?= !empty($sub['category']['name']) ? htmlspecialchars($sub['category']['name']) : 'Sans catégorie' ?>
                    </div>
                    <a href="<?= url('/contributor/new') ?>" class="inline-flex items-center justify-center rounded-full border border-[#004241]/12 bg-[#F8F6F2] px-4 py-2 text-xs font-medium text-[#004241] transition hover:bg-[#EBF1EF]">
                        Créer un nouvel article
                    </a>
                </div>
            </div>
        </article>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
