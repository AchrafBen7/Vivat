<?php
$subscriber = $subscriber ?? null;
$confirmUrl = $confirmUrl ?? '#';
$unsubscribeUrl = $unsubscribeUrl ?? '#';
$firstName = $subscriber && ! empty($subscriber->name) ? trim((string) $subscriber->name) : null;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmez votre inscription</title>
</head>
<body style="margin:0;padding:0;background:#f4f8f7;font-family:Arial,sans-serif;color:#123;">
    <div style="max-width:640px;margin:0 auto;padding:32px 20px;">
        <div style="background:#ffffff;border-radius:24px;padding:32px;border:1px solid rgba(0,66,65,0.08);">
            <p style="margin:0 0 12px;font-size:13px;letter-spacing:.12em;text-transform:uppercase;color:#5d7a77;font-weight:700;">Newsletter Vivat</p>
            <h1 style="margin:0 0 16px;font-size:32px;line-height:1.1;color:#004241;">Confirmez votre inscription</h1>
            <p style="margin:0 0 24px;font-size:16px;line-height:1.6;color:#355451;">
                <?= htmlspecialchars($firstName ? 'Bonjour ' . $firstName . ',' : 'Bonjour,') ?><br>
                Cliquez sur le bouton ci-dessous pour confirmer votre abonnement et recevoir la sélection éditoriale de Vivat.
            </p>
            <p style="margin:0 0 28px;">
                <a href="<?= htmlspecialchars($confirmUrl) ?>" style="display:inline-block;background:#004241;color:#fff;text-decoration:none;padding:14px 22px;border-radius:999px;font-weight:700;">Confirmer mon inscription</a>
            </p>
            <p style="margin:0 0 12px;font-size:14px;line-height:1.6;color:#5b6665;">
                Si vous n’êtes pas à l’origine de cette demande, vous pouvez simplement ignorer cet email.
            </p>
            <p style="margin:0;font-size:13px;line-height:1.6;color:#6d7877;">
                Se désinscrire : <a href="<?= htmlspecialchars($unsubscribeUrl) ?>" style="color:#004241;"><?= htmlspecialchars($unsubscribeUrl) ?></a>
            </p>
        </div>
    </div>
</body>
</html>
