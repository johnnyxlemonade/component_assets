<?php declare(strict_types=1);

namespace Lemonade\Assets;

final class AssetTagBuilder
{
    /**
     * Vytvoří <script> tag s volitelným atributem integrity a crossorigin.
     * Pokud je soubor externí (např. CDN), integrity se nepočítá.
     *
     * @param string $path Relativní cesta nebo URL k JS souboru
     * @param string $baseUrl Base cesta (např. "/assets/") – přidává se před $path
     * @param string $algo Algoritmus hashování (např. "sha384")
     * @return string Hotový HTML <script> tag
     */
    public static function js(string $path, string $baseUrl = '', string $algo = 'sha384'): string
    {
        if (Path::isExternal($path)) {
            $path = self::appendVersionIfNeeded($path);
            return self::formatTag('script', [
                'src' => $path,
                'crossorigin' => 'anonymous'
            ]);
        }

        $fullUrl = helperVersion($baseUrl . $path);
        $fullPath = $baseUrl . $path;
        $integrity = self::calculateHash($fullPath, $algo);

        return self::formatTag('script', [
            'src' => $fullUrl,
            'integrity' => $integrity ?: null,
            'crossorigin' => $integrity ? 'anonymous' : null,
        ]);
    }

    /**
     * Vytvoří <link> tag s volitelným atributem integrity a crossorigin.
     * Pokud je soubor externí (např. CDN), integrity se nepočítá.
     *
     * @param string $path Relativní cesta nebo URL k CSS souboru
     * @param string $baseUrl Base cesta (např. "/assets/") – přidává se před $path
     * @param string $algo Algoritmus hashování (např. "sha384")
     * @return string Hotový HTML <link> tag
     */
    public static function css(string $path, string $baseUrl = '', string $algo = 'sha384'): string
    {
        if (Path::isExternal($path)) {
            $path = self::appendVersionIfNeeded($path);
            return self::formatTag('link', [
                'rel' => 'stylesheet',
                'href' => $path,
                'crossorigin' => 'anonymous'
            ]);
        }

        $fullUrl = helperVersion($baseUrl . $path);
        $fullPath = $baseUrl . $path;
        $integrity = self::calculateHash($fullPath, $algo);

        return self::formatTag('link', [
            'rel' => 'stylesheet',
            'href' => $fullUrl,
            'integrity' => $integrity ?: null,
            'crossorigin' => $integrity ? 'anonymous' : null,
        ]);
    }

    /**
     * Vytvoří <link rel="preload"> tag pro fontový soubor (např. woff2).
     * Používá interně metodu preload().
     *
     * @param string $url URL nebo cesta k fontu
     * @param string $type MIME typ fontu (např. font/woff2)
     * @return string HTML <link> tag pro preload fontu
     */
    public static function preloadFont(string $url, string $type = 'font/woff2'): string
    {
        return self::preload($url, 'font', ['type' => $type]);
    }

    /**
     * Vytvoří obecný <link rel="preload"> tag pro libovolný typ assetu.
     * Automaticky přidá ?v=timestamp pro externí URL bez query stringu.
     *
     * @param string $url URL nebo cesta k souboru
     * @param string $as Hodnota atributu "as" (např. font, script, style, image)
     * @param array<string, string> $extra Dodatečné atributy (např. type, media, crossorigin)
     * @return string HTML <link> tag pro preload daného typu
     */
    public static function preload(string $url, string $as, array $extra = []): string
    {
        if (Path::isExternal($url) && !str_contains($url, '?')) {
            $url .= '?v=' . time();
        }

        $attrs = array_merge([
            'rel' => 'preload',
            'href' => $url,
            'as' => $as,
            'crossorigin' => 'anonymous',
        ], $extra);

        return self::formatTag('link', $attrs);
    }

    /**
     * Vyrenderuje podmíněný HTML blok pro staré verze Internet Exploreru (např. IE 9).
     * Vloží polyfill skript z CDN s připojeným ?v=timestamp pro cache busting.
     *
     * @return string
     */
    public final static function renderIeFix(): string
    {
        $version = time(); // nebo např. definovaný ASSETS_VERSION
        $html = <<<EOT

    <!--[if (IE 9)&!(IEMobile)]>
      <script src="https://cdn.core1.agency/static/js/ie.polyfill.min.js?v={$version}"></script>
    <![endif]-->

EOT;

        return $html;
    }

    /**
     * Spočítá integrity hash souboru z lokálního disku.
     *
     * @param string $relativePath Cesta k souboru relativní k rootu projektu
     * @param string $algo Algoritmus hashování (např. "sha384", "sha256")
     * @return string Integrity hash ve formátu "algo-base64hash" nebo prázdný řetězec pokud soubor neexistuje
     */
    protected static function calculateHash(string $relativePath, string $algo): string
    {
        $absolutePath = Path::resolve($relativePath);

        if (!file_exists($absolutePath)) {
            return '';
        }

        $hash = base64_encode(hash_file($algo, $absolutePath, true));
        return "{$algo}-{$hash}";
    }

    /**
     * Pokud URL neobsahuje query string, přidá na konec ?v=timestamp.
     * Využívá se k vynucení refrešování externích souborů z CDN.
     *
     * @param string $url Původní URL (např. https://cdn.core1.agency/lib.js)
     * @return string Upravená URL s verzí, nebo původní URL pokud už obsahuje query
     */
    protected static function appendVersionIfNeeded(string $url): string
    {
        return str_contains($url, '?') ? $url : $url . '?v=' . time();
    }

    /**
     * Vygeneruje HTML tag (např. <script> nebo <link>) z předaných atributů.
     * Automaticky escapuje hodnoty a vynechá null/prázdné atributy.
     *
     * @param string $tag Název HTML tagu (např. 'script', 'link')
     * @param array<string, string|null> $attrs Pole atributů ve formátu ['attr' => 'value']
     * @return string Sestavený HTML tag jako string
     */
    protected static function formatTag(string $tag, array $attrs): string
    {
        $attrString = '';
        foreach ($attrs as $key => $value) {
            if ($value !== null && $value !== '') {
                $attrString .= sprintf(' %s="%s"', $key, htmlspecialchars((string) $value, ENT_QUOTES));
            }
        }

        return $tag === 'script'
            ? sprintf('<script%s></script>', $attrString)
            : sprintf('<%s%s>', $tag, $attrString);
    }
}
