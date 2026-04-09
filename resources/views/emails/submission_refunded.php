<?php /** @var \App\Models\Payment $payment */ ?>
<?php /** @var \App\Models\Submission|null $submission */ ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Remboursement traité</title>
</head>
<body style="margin:0;padding:0;background:#f6f7f4;color:#173f3d;font-family:Arial,Helvetica,sans-serif;">
<div style="max-width:640px;margin:0 auto;padding:32px 20px;">
    <div style="background:#ffffff;border-radius:24px;padding:32px;border:1px solid #dfe8e3;">
        <p style="margin:0 0 16px;font-size:12px;letter-spacing:.08em;text-transform:uppercase;color:#6d7d76;">Vivat</p>
        <h1 style="margin:0 0 16px;font-size:28px;line-height:1.2;color:#0d4f4b;">Votre remboursement a été lancé</h1>
        <p style="margin:0 0 16px;font-size:16px;line-height:1.7;">
            Le paiement lié à votre soumission <strong><?= e($submission?->title ?? 'Article') ?></strong> a bien été remboursé.
        </p>
        <p style="margin:0 0 16px;font-size:16px;line-height:1.7;">
            Montant : <strong><?= e(number_format($payment->amount / 100, 2, ',', ' ')) ?> <?= e(strtoupper($payment->currency)) ?></strong>
        </p>
        <?php if ($payment->refund_reason): ?>
            <div style="margin:0 0 24px;padding:18px 20px;background:#f5faf8;border-radius:18px;border:1px solid #d7e7df;">
                <p style="margin:0 0 8px;font-size:13px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#58736b;">Motif</p>
                <p style="margin:0;font-size:15px;line-height:1.7;color:#173f3d;"><?= nl2br(e($payment->refund_reason)) ?></p>
            </div>
        <?php endif; ?>
        <p style="margin:0 0 24px;font-size:14px;line-height:1.7;color:#5f6d67;">
            Selon votre banque, le délai d'apparition peut varier de quelques jours.
        </p>
        <p style="margin:0;">
            <a href="<?= e($dashboardUrl) ?>" style="display:inline-block;background:#0d4f4b;color:#ffffff;text-decoration:none;padding:14px 22px;border-radius:999px;font-weight:700;">
                Retourner à mon espace
            </a>
        </p>
    </div>
</div>
</body>
</html>
