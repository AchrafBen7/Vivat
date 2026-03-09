<?php
$title = $title ?? 'Vivat';
$meta_description = $meta_description ?? '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    <meta name="description" content="<?= htmlspecialchars($meta_description) ?>">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;500;600;700&family=Righteous&display=swap" rel="stylesheet">
    <style>
        :root {
            --vivat-teal: #004241;
            --vivat-bg-search: #EBF1EF;
            --vivat-cream: #FFF0D4;
            --vivat-overlay-dark: rgba(0, 0, 0, 0.2);
            --vivat-card-glass: rgba(255, 255, 255, 0.11);
            --vivat-card-border: rgba(255, 255, 255, 0.15);
        }
        body { font-family: 'Figtree', sans-serif; }
        .font-righteous { font-family: 'Righteous', cursive; }
    </style>
</head>
<body class="bg-gray-50 text-gray-900 antialiased">
    <!-- Navbar - Design System Figma -->
    <header class="bg-white border-b border-gray-100">
        <div class="max-w-[1400px] mx-auto px-5 lg:px-20 flex items-center h-[88px]" style="padding-top: 35px; padding-bottom: 35px;">
            <!-- Logo: 32px, #004241, Righteous 400, letter-spacing 3%, 612px space avant searchbar -->
            <a href="/" class="font-righteous text-[32px] font-normal flex-shrink-0" style="color: #004241; letter-spacing: 0.03em;">Vivat</a>

            <!-- Espace logo - searchbar: 612px sur desktop -->
            <div class="hidden lg:block flex-shrink-0" style="width: 612px;"></div>
            <div class="flex-1 lg:hidden"></div>

            <!-- Search bar: 326x48, #EBF1EF, full radius -->
            <div class="flex items-center flex-shrink-0 rounded-full border border-gray-200 h-12 px-4 gap-2" style="width: 326px; min-width: 120px; background: #EBF1EF;">
                <input type="search" placeholder="Rechercher un article" class="flex-1 bg-transparent text-sm outline-none placeholder:opacity-80" style="color: #004241;">
                <svg class="w-5 h-5 flex-shrink-0" style="color: #004241;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>

            <!-- 9px espace -->
            <div class="w-[9px] flex-shrink-0"></div>

            <!-- Contactez-nous: 162x48, #004241, Figtree 500 16px, text #FFFFFF -->
            <a href="/contact" class="flex items-center justify-center rounded-full flex-shrink-0 h-12 font-medium text-base leading-none" style="width: 162px; background: #004241; color: #FFFFFF; padding: 12px 20px;">
                Contactez-nous
            </a>

            <!-- 19px espace -->
            <div class="w-[19px] flex-shrink-0"></div>

            <!-- Hamburger: 48x48, radius 30px - 3 barres pill-shaped #004241, pas de background -->
            <button type="button" id="hamburger-menu" class="flex flex-col items-center justify-center gap-1.5 rounded-full flex-shrink-0 w-12 h-12 bg-transparent" style="border-radius: 30px;" aria-label="Menu">
                <span class="block rounded-full" style="width: 20px; height: 3px; background: #004241;"></span>
                <span class="block rounded-full" style="width: 20px; height: 3px; background: #004241;"></span>
                <span class="block rounded-full" style="width: 20px; height: 3px; background: #004241;"></span>
            </button>
        </div>
    </header>
    <main class="max-w-[1400px] mx-auto px-5 lg:px-20 py-8">
        <?= $content ?? '' ?>
    </main>
    <footer class="border-t border-gray-200 mt-12 py-8">
        <div class="max-w-[1400px] mx-auto px-5 lg:px-20 text-center text-gray-500 text-sm">
            © <?= date('Y') ?> Vivat. Tous droits réservés.
        </div>
    </footer>
</body>
</html>
