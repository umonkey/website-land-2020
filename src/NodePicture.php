<?php

namespace App;

class NodePicture
{
    private $img;

    private $font;

    public function __construct()
    {
        $this->font = __DIR__ . '/../../fonts/pt-sans.ttf';
    }

    public function render($template, $text)
    {
        if (!file_exists($template)) {
            return null;
        }

        if (!file_exists($this->font)) {
            return null;
        }

        $data = file_get_contents($template);
        $img = imagecreatefromstring($data);

        $this->drawText($img, $text);

        ob_start();
        imagejpeg($img, null, 70);
        $data = ob_get_clean();

        return $data;
    }

    protected function drawText($img, $text)
    {
        $top = 220;
        $left = 80;
        $width = 960 - $left - 20;

        $size = 40;
        $lsize = 60;

        // Some typo.
        $text = str_replace(' в ', ' в ', $text);

        $words = preg_split('@\s+@', $text, -1, PREG_SPLIT_NO_EMPTY);

        while ($words) {
            for ($count = 1; $count <= count($words); $count++) {
                $line = implode(' ', array_slice($words, 0, $count));

                $box = imagettfbbox($size, 0, $this->font, $line);
                $bwidth = $box[4];
                $bheight = 0 - $box[5];

                if ($bwidth > $width) {
                    $count--;
                    break;
                }
            }

            $line = implode(' ', array_slice($words, 0, $count));

            imagettftext($img, $size, 0, $left, $top, 0xffffff, $this->font, $line);
            $top += $lsize;

            $words = array_slice($words, $count);
        }
    }
}
