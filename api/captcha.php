<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // No-cache headers so every request generates a fresh CAPTCHA image
    header("Content-Type: image/svg+xml; charset=utf-8");
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Pragma: no-cache");
    header("Expires: 0");

    // 29 unambiguous uppercase letters & digits (excludes 0, O, 1, I, L, 2, Z)
    $alphabet = "ABCDEFGHJKMNPQRSTUVWXY3456789";
    $length = 5;
    $code = "";
    $maxIdx = strlen($alphabet) - 1;
    for ($i = 0; $i < $length; $i++) {
        $code .= $alphabet[random_int(0, $maxIdx)];
    }

    $_SESSION["contact_captcha"] = [
        "code"      => $code,
        "expires"   => time() + 600, // 10 minutes
        "issued_at" => time()
    ];

    // Vector stroke glyphs on a 10x14 grid (no readable <text> nodes in SVG source)
    $glyphs = [
        '3' => 'M1.5,2 C3.2,0.5 8.8,0.5 8.8,3.6 C8.8,5.8 6.5,6.8 4.2,6.8 C6.8,6.8 9,8.2 9,10.8 C9,13.6 3.2,14.2 1.2,12.2',
        '4' => 'M7.8,14 L7.8,1 L1,9.5 L9.6,9.5',
        '5' => 'M8.8,1 L2,1 L1.6,6.4 C3.6,5 8.8,5.6 8.8,9.6 C8.8,13.4 3.6,14.2 1.2,12.2',
        '6' => 'M8.4,2 C5.2,0.2 1.2,2.4 1.2,8.4 C1.2,12.8 3.8,13.8 5.8,13.8 C8.2,13.8 9.2,12 9.2,9.6 C9.2,7.2 7.4,6 5,6 C2.6,6 1.2,7.4 1.2,9.4',
        '7' => 'M1,1 L9.2,1 L3.8,14',
        '8' => 'M5,6.8 C2.4,6.8 1.4,5.2 1.4,3.5 C1.4,1.6 3,0.8 5,0.8 C7,0.8 8.6,1.6 8.6,3.5 C8.6,5.2 7.6,6.8 5,6.8 C2.2,6.8 1,8.4 1,10.6 C1,12.8 2.8,13.8 5,13.8 C7.2,13.8 9,12.8 9,10.6 C9,8.4 7.8,6.8 5,6.8',
        '9' => 'M1.6,12.2 C4.6,14.2 8.8,12.2 8.8,5.8 C8.8,2 6.8,0.8 4.8,0.8 C2.4,0.8 1.2,2.6 1.2,4.8 C1.2,7.2 2.8,8.4 5,8.4 C7.4,8.4 8.8,7 8.8,5',
        'A' => 'M1,14 L5,1 L9,14 M2.5,9.6 L7.5,9.6',
        'B' => 'M1.5,14 L1.5,1 L5.8,1 C8.2,1 8.8,2.4 8.8,4 C8.8,5.6 7.8,6.8 5.8,7 L1.5,7 M5.8,7 C8.6,7 9.2,8.6 9.2,10.4 C9.2,12.6 8,14 5.8,14 L1.5,14',
        'C' => 'M8.8,3 C7.2,0.6 2.8,0.6 1.6,3.8 C0.9,5.8 0.9,8.8 1.6,11 C2.8,13.8 7.2,13.8 8.8,11.4',
        'D' => 'M1.5,1 L1.5,14 L5.4,14 C8.2,14 9.2,11 9.2,7.5 C9.2,4 8.2,1 5.4,1 Z',
        'E' => 'M8.8,1 L1.5,1 L1.5,14 L8.8,14 M1.5,7.2 L7.2,7.2',
        'F' => 'M8.8,1 L1.5,1 L1.5,14 M1.5,7.2 L7.2,7.2',
        'G' => 'M8.8,3 C7.2,0.6 2.6,0.6 1.4,4 C0.8,6 0.8,9 1.5,11 C2.8,13.8 7.4,13.8 9,12 L9,7.8 L5.4,7.8',
        'H' => 'M1.5,1 L1.5,14 M8.5,1 L8.5,14 M1.5,7.2 L8.5,7.2',
        'J' => 'M8.2,1 L8.2,10.4 C8.2,12.8 6.4,13.8 4.4,13.8 C2.4,13.8 1.2,12.8 1,11',
        'K' => 'M1.5,1 L1.5,14 M8.8,1 L2,7.5 L9,14',
        'M' => 'M1,14 L1,1 L5,8.8 L9,1 L9,14',
        'N' => 'M1.5,14 L1.5,1 L8.5,14 L8.5,1',
        'P' => 'M1.5,14 L1.5,1 L5.8,1 C8.2,1 9,2.4 9,4.5 C9,6.5 8,8 5.8,8 L1.5,8',
        'Q' => 'M5,1 C2.2,1 1.2,3.6 1.2,7.2 C1.2,10.8 2.2,13.4 5,13.4 C7.8,13.4 8.8,10.8 8.8,7.2 C8.8,3.6 7.8,1 5,1 M6.4,10.4 L9.4,14',
        'R' => 'M1.5,14 L1.5,1 L5.8,1 C8.2,1 8.8,2.4 8.8,4.4 C8.8,6.4 7.8,7.8 5.6,7.8 L1.5,7.8 M5.6,7.8 L9,14',
        'S' => 'M8.6,2.8 C7.4,0.6 2.4,0.6 1.6,3 C1,4.8 2.2,6.2 5,7 C8,7.8 9.2,9.2 8.6,11.4 C7.8,13.8 2.6,14 1.2,11.6',
        'T' => 'M1,1 L9,1 M5,1 L5,14',
        'U' => 'M1.5,1 L1.5,10 C1.5,12.6 3,13.8 5,13.8 C7,13.8 8.5,12.6 8.5,10 L8.5,1',
        'V' => 'M1,1 L5,14 L9,1',
        'W' => 'M0.6,1 L2.8,14 L5,6 L7.2,14 L9.4,1',
        'X' => 'M1.2,1 L8.8,14 M8.8,1 L1.2,14',
        'Y' => 'M1.2,1 L5,7.4 L8.8,1 M5,7.4 L5,14',
    ];

    $width = 210;
    $height = 58;
    $inkColors = ['#4a0b3c', '#7c1163', '#2b1026', '#921574', '#580b46'];
    $noiseColors = ['#d8b8d2', '#c79cc0', '#e1c5dc', '#b885af'];

    $svg = '<?xml version="1.0" encoding="UTF-8"?>';
    $svg .= '<svg xmlns="http://www.w3.org/2000/svg" width="' . $width . '" height="' . $height . '" viewBox="0 0 ' . $width . ' ' . $height . '" role="img" aria-label="CAPTCHA">';
    $svg .= '<defs>';
    $svg .= '<linearGradient id="bg" x1="0%" y1="0%" x2="100%" y2="100%">';
    $svg .= '<stop offset="0%" stop-color="#faf5fc"/>';
    $svg .= '<stop offset="100%" stop-color="#f1e4f5"/>';
    $svg .= '</linearGradient>';
    $svg .= '</defs>';
    $svg .= '<rect width="100%" height="100%" rx="10" fill="url(#bg)"/>';

    // Subtle background curved lines
    for ($i = 0; $i < 4; $i++) {
        $y1 = random_int(4, $height - 4);
        $y2 = random_int(4, $height - 4);
        $cy = random_int(0, $height);
        $cx = random_int(40, $width - 40);
        $col = $noiseColors[random_int(0, count($noiseColors) - 1)];
        $svg .= '<path d="M0,' . $y1 . ' Q' . $cx . ',' . $cy . ' ' . $width . ',' . $y2 . '" fill="none" stroke="' . $col . '" stroke-width="1.3" opacity="0.65"/>';
    }

    // Subtle noise dots
    for ($i = 0; $i < 24; $i++) {
        $cx = random_int(6, $width - 6);
        $cy = random_int(5, $height - 5);
        $r = random_int(8, 18) / 10;
        $col = $noiseColors[random_int(0, count($noiseColors) - 1)];
        $svg .= '<circle cx="' . $cx . '" cy="' . $cy . '" r="' . $r . '" fill="' . $col . '" opacity="0.55"/>';
    }

    // Render each CAPTCHA character as a transformed vector path
    $startX = 20;
    $stepX = 35;
    for ($i = 0; $i < $length; $i++) {
        $ch = $code[$i];
        $path = $glyphs[$ch] ?? '';
        $tx = $startX + ($i * $stepX) + random_int(-3, 3);
        $ty = random_int(13, 21);
        $angle = random_int(-15, 15);
        $scale = random_int(165, 195) / 100;
        $color = $inkColors[random_int(0, count($inkColors) - 1)];
        $strokeW = number_format(random_int(15, 18) / 10, 1);

        $svg .= '<g transform="translate(' . $tx . ',' . $ty . ') rotate(' . $angle . ' 9 12) scale(' . $scale . ')">';
        $svg .= '<path d="' . $path . '" fill="none" stroke="' . $color . '" stroke-width="' . $strokeW . '" stroke-linecap="round" stroke-linejoin="round"/>';
        $svg .= '</g>';
    }

    // Foreground light interference curve
    $fy1 = random_int(12, $height - 12);
    $fy2 = random_int(12, $height - 12);
    $fcy = random_int(8, $height - 8);
    $svg .= '<path d="M10,' . $fy1 . ' Q' . ($width / 2) . ',' . $fcy . ' ' . ($width - 10) . ',' . $fy2 . '" fill="none" stroke="#c11a9a" stroke-width="1.1" stroke-dasharray="4 4" opacity="0.35"/>';

    $svg .= '</svg>';
    echo $svg;
