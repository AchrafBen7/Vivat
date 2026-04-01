<!DOCTYPE html>
<html lang="{{ $locale ?? 'fr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject }}</title>
</head>
<body style="margin:0;padding:0;background:#F8F6F2;font-family:Helvetica,Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#F8F6F2;padding:40px 16px;">
    <tr>
        <td align="center">
            <table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;">

                <!-- Logo + header -->
                <tr>
                    <td style="background:#004241;border-radius:20px 20px 0 0;padding:28px 36px;">
                        <a href="{{ config('app.url') }}" style="text-decoration:none;font-size:26px;font-weight:700;color:#fff;letter-spacing:-0.5px;">Vivat</a>
                        <p style="margin:6px 0 0;font-size:13px;color:rgba(255,255,255,0.65);">{{ $headerLine }}</p>
                    </td>
                </tr>

                <!-- Articles -->
                <tr>
                    <td style="background:#fff;padding:32px 36px;">

                        @foreach($articles as $index => $article)
                        @php
                            $isFirst = $index === 0;
                            $articleUrl = config('app.url') . '/articles/' . $article['slug'];
                        @endphp

                        @if($isFirst)
                        <!-- Article featured -->
                        <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:28px;">
                            <tr>
                                <td>
                                    @if(!empty($article['cover_image_url']))
                                    <img src="{{ $article['cover_image_url'] }}" alt="{{ $article['title'] }}"
                                         style="width:100%;height:220px;object-fit:cover;border-radius:14px;display:block;margin-bottom:16px;">
                                    @endif
                                    @if(!empty($article['category_name']))
                                    <p style="margin:0 0 8px;font-size:11px;font-weight:600;color:#004241;text-transform:uppercase;letter-spacing:0.08em;">{{ $article['category_name'] }}</p>
                                    @endif
                                    <h2 style="margin:0 0 10px;font-size:22px;font-weight:700;color:#004241;line-height:1.25;">
                                        <a href="{{ $articleUrl }}" style="color:#004241;text-decoration:none;">{{ $article['title'] }}</a>
                                    </h2>
                                    @if(!empty($article['excerpt']))
                                    <p style="margin:0 0 16px;font-size:15px;color:#004241;opacity:0.75;line-height:1.6;">{{ Str::limit($article['excerpt'], 160) }}</p>
                                    @endif
                                    <a href="{{ $articleUrl }}" style="display:inline-block;background:#004241;color:#fff;text-decoration:none;font-size:13px;font-weight:600;padding:10px 22px;border-radius:100px;">
                                        Lire l'article @if(!empty($article['reading_time']))· {{ $article['reading_time'] }} min@endif
                                    </a>
                                </td>
                            </tr>
                        </table>
                        <hr style="border:none;border-top:1px solid #EBF1EF;margin:0 0 24px;">

                        @else
                        <!-- Article compact -->
                        <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:20px;">
                            <tr>
                                <td style="vertical-align:top;padding-right:16px;">
                                    @if(!empty($article['category_name']))
                                    <p style="margin:0 0 4px;font-size:11px;font-weight:600;color:#004241;text-transform:uppercase;letter-spacing:0.06em;opacity:0.6;">{{ $article['category_name'] }}</p>
                                    @endif
                                    <h3 style="margin:0 0 6px;font-size:16px;font-weight:600;color:#004241;line-height:1.3;">
                                        <a href="{{ $articleUrl }}" style="color:#004241;text-decoration:none;">{{ $article['title'] }}</a>
                                    </h3>
                                    <p style="margin:0;font-size:12px;color:#004241;opacity:0.5;">
                                        {{ $article['published_at_display'] ?? '' }}
                                        @if(!empty($article['reading_time'])) · {{ $article['reading_time'] }} min @endif
                                    </p>
                                </td>
                                @if(!empty($article['cover_image_url']))
                                <td style="vertical-align:top;width:80px;flex-shrink:0;">
                                    <img src="{{ $article['cover_image_url'] }}" alt="{{ $article['title'] }}"
                                         style="width:80px;height:80px;object-fit:cover;border-radius:10px;display:block;">
                                </td>
                                @endif
                            </tr>
                        </table>
                        @if(!$loop->last)
                        <hr style="border:none;border-top:1px solid #EBF1EF;margin:0 0 20px;">
                        @endif
                        @endif

                        @endforeach

                    </td>
                </tr>

                <!-- CTA footer card -->
                <tr>
                    <td style="background:#EBF1EF;padding:24px 36px;border-radius:0 0 20px 20px;">
                        <p style="margin:0 0 12px;font-size:14px;color:#004241;line-height:1.5;">
                            Découvrez tous nos articles sur <a href="{{ config('app.url') }}" style="color:#004241;font-weight:600;">Vivat</a>.
                        </p>
                        <p style="margin:0;font-size:11px;color:#004241;opacity:0.45;line-height:1.6;">
                            Vous recevez cet email car vous êtes abonné à la newsletter Vivat. ·
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
