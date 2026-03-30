<?php /** @var \App\Models\Submission $submission */ ?>
<?php /** @var \App\Models\Article $article */ ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Article accepté</title>
</head>
<body style="margin:0;padding:0;background:#f6f7f4;color:#173f3d;font-family:Arial,Helvetica,sans-serif;">
<div style="max-width:640px;margin:0 auto;padding:32px 20px;">
    <div style="background:#ffffff;border-radius:24px;padding:32px;border:1px solid #dfe8e3;">
        <p style="margin:0 0 16px;font-size:12px;letter-spacing:.08em;text-transform:uppercase;color:#6d7d76;">Vivat</p>
        <h1 style="margin:0 0 16px;font-size:28px;line-height:1.2;color:#0d4f4b;">Votre article a été accepté</h1>
        <p style="margin:0 0 16px;font-size:16px;line-height:1.7;">
            Bonne nouvelle : votre article <strong><?= e($submission->title) ?></strong> a été validé par l’équipe éditoriale et est maintenant publié sur Vivat.
        </p>
        <p style="margin:0 0 24px;font-size:16px;line-height:1.7;">
            Vous pouvez le consulter en ligne et le partager dès maintenant.
        </p>
        <p style="margin:0 0 24px;">
            <a href="<?= e($articleUrl) ?>" style="display:inline-block;background:#0d4f4b;color:#ffffff;text-decoration:none;padding:14px 22px;border-radius:999px;font-weight:700;">
                Voir l’article publié
            </a>
        </p>
        <p style="margin:0;font-size:14px;line-height:1.7;color:#5f6d67;">
            Merci pour votre contribution.
        </p>
    </div>
</div>
</body>
</html>
