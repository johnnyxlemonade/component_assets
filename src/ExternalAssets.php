<?php declare(strict_types=1);

namespace Lemonade\Assets;

final class ExternalAssets
{
    /**
     * Vrátí pole URL externích CSS pro app.
     *
     * @return array<int, string>
     */
    public static function getPhoneCss(): array
    {
        return [
            "https://cdn.lemonadeframework.cz/static/phone/app.css",
        ];
    }

    /**
     * Vrátí pole URL CSS pro Font Awesome.
     *
     * @return array<int, string>
     */
    public static function getFontAwesomeCss(): array
    {
        return [
            "https://cdn.lemonadeframework.cz/fonts/fontawesome/webfont.css",
        ];
    }

    /**
     * Vrátí pole URL CSS pro Material Icons.
     *
     * @return array<int, string>
     */
    public static function getMaterialCss(): array
    {
        return [
            "https://cdn.lemonadeframework.cz/fonts/material/webfont.css",
        ];
    }

}
