<?php
$src = imagecreatefrompng('C:\Users\Wency\.gemini\antigravity\brain\a55fb8c8-bb90-47b2-9719-9378ba630099\media__1777562639994.png');
$w = imagesx($src);
$h = imagesy($src);
$out = imagecreatetruecolor($w, $h);
imagesavealpha($out, true);
imagefill($out, 0, 0, imagecolorallocatealpha($out, 0, 0, 0, 127));

for ($x = 0; $x < $w; $x++) {
    for ($y = 0; $y < $h; $y++) {
        $rgb = imagecolorat($src, $x, $y);
        $colors = imagecolorsforindex($src, $rgb);
        // use red channel as alpha (0 = transparent, 255 = opaque)
        $alpha = 127 - floor($colors['red'] / 2); 
        // make it solid white with calculated alpha
        $color = imagecolorallocatealpha($out, 255, 255, 255, $alpha);
        imagesetpixel($out, $x, $y, $color);
    }
}
imagepng($out, 'logo.png');
echo 'Done';
?>

