<?php /** @var \App\Models\Submission $submission */ ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Article à corriger</title>
</head>
<body style="margin:0;padding:0;background:#f6f7f4;color:#173f3d;font-family:Arial,Helvetica,sans-serif;">
<div style="max-width:640px;margin:0 auto;padding:32px 20px;">
    <div style="background:#ffffff;border-radius:24px;padding:32px;border:1px solid #dfe8e3;">
        <p style="margin:0 0 16px;font-size:12px;letter-spacing:.08em;text-transform:uppercase;color:#6d7d76;">Vivat</p>
        <h1 style="margin:0 0 16px;font-size:28px;line-height:1.2;color:#0d4f4b;">Votre article nécessite quelques ajustements</h1>
        <p style="margin:0 0 16px;font-size:16px;line-height:1.7;">
            Votre soumission <strong><?= e($submission->title) ?></strong> n'a pas encore été retenue en l'état.
        </p>
        <?php if ($submission->reviewer_notes): ?>
            <div style="margin:0 0 24px;padding:18px 20px;background:#f5faf8;border-radius:18px;border:1px solid #d7e7df;">
                <p style="margin:0 0 8px;font-size:13px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#58736b;">Retour éditorial</p>
                <p style="margin:0;font-size:15px;line-height:1.7;color:#173f3d;"><?= nl2br(e($submission->reviewer_notes)) ?></p>
            </div>
        <?php endif; ?>
        <p style="margin:0 0 24px;font-size:16px;line-height:1.7;">
            Vous pouvez modifier votre texte puis le renvoyer en validation directement depuis votre espace contributeur.
        </p>
        <p style="margin:0 0 12px;">
            <a href="<?= e($editUrl) ?>" style="display:inline-block;background:#0d4f4b;color:#ffffff;text-decoration:none;padding:14px 22px;border-radius:999px;font-weight:700;">
                Corriger mon article
            </a>
        </p>
        <p style="margin:0;font-size:14px;line-height:1.7;color:#5f6d67;">
            Vous pouvez aussi retrouver toutes vos soumissions dans votre tableau de bord : <a href="<?= e($dashboardUrl) ?>" style="color:#0d4f4b;">ouvrir mon espace</a>.
        </p>
    </div>
</div>
</body>
</html>
