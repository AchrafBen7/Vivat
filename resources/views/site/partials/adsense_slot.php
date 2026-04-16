<?php
$slotKey = $slotKey ?? null;
$slotConfig = is_string($slotKey) ? config('vivat.adsense.slots.'.$slotKey) : null;
$client = config('vivat.adsense.client');

if (! is_array($slotConfig) || ! is_string($client) || $client === '' || empty($slotConfig['slot'])) {
    return;
}

$slotId = (string) $slotConfig['slot'];
$mode = (string) ($slotConfig['mode'] ?? 'responsive');
$width = (int) ($slotConfig['width'] ?? 0);
$height = (int) ($slotConfig['height'] ?? 0);
?>
<ins class="adsbygoogle"
     <?php if ($mode === 'fixed' && $width > 0 && $height > 0) { ?>
     style="display:inline-block;width:<?= $width ?>px;height:<?= $height ?>px"
     <?php } else { ?>
     style="display:block"
     data-ad-format="auto"
     data-full-width-responsive="true"
     <?php } ?>
     data-ad-client="<?= htmlspecialchars($client) ?>"
     data-ad-slot="<?= htmlspecialchars($slotId) ?>"></ins>
<script>
    (adsbygoogle = window.adsbygoogle || []).push({});
</script>
