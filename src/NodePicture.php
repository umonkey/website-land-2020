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
    }
}
