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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 text-gray-900 antialiased">
    <header class="bg-white border-b border-gray-200">
        <div class="max-w-6xl mx-auto px-4 py-4 flex justify-between items-center">
            <a href="/" class="text-xl font-bold text-gray-900">Vivat</a>
            <nav class="flex gap-6">
                <a href="/" class="text-gray-600 hover:text-gray-900">Accueil</a>
                <a href="/categories" class="text-gray-600 hover:text-gray-900">Rubriques</a>
            </nav>
        </div>
    </header>
    <main class="max-w-6xl mx-auto px-4 py-8">
        <?= $content ?? '' ?>
    </main>
    <footer class="border-t border-gray-200 mt-12 py-8">
        <div class="max-w-6xl mx-auto px-4 text-center text-gray-500 text-sm">
            © <?= date('Y') ?> Vivat. Tous droits réservés.
        </div>
    </footer>
</body>
</html>
