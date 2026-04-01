<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmez votre inscription — Vivat</title>
</head>
<body style="margin:0;padding:0;background:#F8F6F2;font-family:Figtree,Helvetica,Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#F8F6F2;padding:40px 16px;">
    <tr>
        <td align="center">
            <table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;">

                <!-- Logo -->
                <tr>
                    <td align="center" style="padding-bottom:32px;">
                        <a href="{{ config('app.url') }}" style="text-decoration:none;font-size:28px;font-weight:700;color:#004241;letter-spacing:-0.5px;">Vivat</a>
                    </td>
                </tr>

                <!-- Card -->
                <tr>
                    <td style="background:#fff;border-radius:24px;padding:40px 36px;box-shadow:0 8px 32px rgba(0,66,65,0.07);">
                        <p style="margin:0 0 8px;font-size:13px;font-weight:600;color:#004241;text-transform:uppercase;letter-spacing:0.08em;">Newsletter</p>
                        <h1 style="margin:0 0 16px;font-size:26px;font-weight:700;color:#004241;line-height:1.2;">Confirmez votre abonnement</h1>
                        <p style="margin:0 0 28px;font-size:16px;color:#004241;opacity:0.78;line-height:1.6;">
                            Bonjour {{ $subscriber->name ?? 'lecteur' }},<br><br>
                            Merci de vous être inscrit à la newsletter Vivat. Un clic suffit pour activer votre abonnement.
                        </p>
                        <a href="{{ $confirmUrl }}" style="display:inline-block;background:#004241;color:#fff;text-decoration:none;font-size:15px;font-weight:600;padding:14px 32px;border-radius:100px;">
                            Confirmer mon inscription
                        </a>
                        <p style="margin:24px 0 0;font-size:13px;color:#004241;opacity:0.5;line-height:1.5;">
                            Si vous n'êtes pas à l'origine de cette demande, ignorez simplement cet email.
                        </p>
                    </td>
                </tr>

                <!-- Footer -->
                <tr>
                    <td align="center" style="padding-top:28px;">
                        <p style="margin:0;font-size:12px;color:#004241;opacity:0.45;">
                            © {{ date('Y') }} Vivat ·
                            <a href="{{ $unsubscribeUrl }}" style="color:#004241;opacity:0.45;">Se désinscrire</a>
                        </p>
                    </td>
                </tr>

            </table>
        </td>
    </tr>
</table>
</body>
</html>
