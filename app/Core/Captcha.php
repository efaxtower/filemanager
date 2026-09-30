<?php

namespace App\Core;

final class Captcha
{
    /**
     * Genera un código aleatorio de 5 caracteres (sin letras confusas).
     */
    public static function generateCode(): string
    {
        $chars = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';
        $code = '';
        for ($i = 0; $i < 5; $i++) {
            $code .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $code;
    }

    /**
     * Genera la imagen del captcha con el código dado.
     * Devuelve el binario PNG.
     */
    public static function generateImage(string $code): string
    {
        $width = 280;
        $height = 90;

        $img = imagecreatetruecolor($width, $height);

        // Fondo con gradiente suave
        for ($y = 0; $y < $height; $y++) {
            $ratio = $y / $height;
            $r = (int) (240 + (215 - 240) * $ratio);
            $g = (int) (245 + (225 - 245) * $ratio);
            $b = (int) (250 + (240 - 250) * $ratio);
            $color = imagecolorallocate($img, $r, $g, $b);
            imageline($img, 0, $y, $width, $y, $color);
        }

        // Líneas de ruido
        for ($i = 0; $i < 8; $i++) {
            $lineColor = imagecolorallocate($img, random_int(150, 200), random_int(150, 200), random_int(150, 200));
            imageline($img, 0, random_int(0, $height), $width, random_int(0, $height), $lineColor);
        }

        // Puntos de ruido
        for ($i = 0; $i < 400; $i++) {
            $dotColor = imagecolorallocate($img, random_int(120, 220), random_int(120, 220), random_int(120, 220));
            imagesetpixel($img, random_int(0, $width), random_int(0, $height), $dotColor);
        }

        // Texto con fuente built-in más grande
        $textColor = imagecolorallocate($img, 30, 45, 60);
        $font = 5; // Fuente built-in más grande (1-5)
        
        // Escala: imagefontwidth(5) = 9px, imagefontheight(5) = 15px
        $charW = imagefontwidth($font);
        $charH = imagefontheight($font);
        
        // Escalar cada carácter x3 con imagecopyresized
        $scale = 3;
        $scaledW = $charW * $scale;
        $scaledH = $charH * $scale;
        $spacing = 12;
        
        $totalWidth = (strlen($code) * $scaledW) + ((strlen($code) - 1) * $spacing);
        $startX = (int) (($width - $totalWidth) / 2);
        $startY = (int) (($height - $scaledH) / 2);

        for ($i = 0; $i < strlen($code); $i++) {
            $char = $code[$i];
            $x = $startX + ($i * ($scaledW + $spacing));
            $yOffset = random_int(-8, 8);
            $y = $startY + $yOffset;

            // Crear una imagen temporal para el carácter
            $tmp = imagecreatetruecolor($charW, $charH);
            $bgTmp = imagecolorallocate($tmp, 240, 245, 250);
            imagefill($tmp, 0, 0, $bgTmp);
            $blackTmp = imagecolorallocate($tmp, 0, 0, 0);
            imagestring($tmp, $font, 0, 0, $char, $blackTmp);

            // Sombra
            $shadowColor = imagecolorallocate($img, 180, 190, 200);
            imagecopyresized($img, $tmp, $x + 2, $y + 2, 0, 0, $scaledW, $scaledH, $charW, $charH);

            // Carácter principal (usando imagecopyresized con color)
            imagecopyresized($img, $tmp, $x, $y, 0, 0, $scaledW, $scaledH, $charW, $charH);

            imagedestroy($tmp);
        }

        // Capturar el output
        ob_start();
        imagepng($img);
        $binary = ob_get_clean();
        imagedestroy($img);

        return $binary;
    }
}