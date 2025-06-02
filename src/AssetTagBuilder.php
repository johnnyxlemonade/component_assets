<?php declare(strict_types=1);

namespace Lemonade\Assets;

final class AssetTagBuilder
{

    /**
     * Vygeneruje inline <script>, který dynamicky vloží externí JS soubor s automatickým výpočtem integrity.
     *
     * @param string $path Relativní cesta nebo URL k JS souboru
     * @param string $baseUrl Base cesta k souboru (např. "/assets/") – přidává se před relativní cesty
     * @param string $algo Algoritmus hashování (např. sha384)
     * @param string $id ID skriptu (používá se jako klíč ve window)
     * @return string Inline <script> blok, který dynamicky načte JS
     */
    public static function jsDynamicLoader(string $path, string $baseUrl = '', string $algo = 'sha384', string $id = 'app-loader-js'): string
    {
        if (Path::isExternal($path)) {
            $src = self::appendVersionIfNeeded($path);
            $integrity = ''; // u externích integrity nepočítáme
        } else {
            $src = helperVersion($baseUrl . $path);
            $fullPath = $baseUrl . $path;
            $integrity = self::calculateHash($fullPath, $algo);
        }

        $template = <<<JS

<script data-id="Lemonade\\Assets\\JSBuilder">
  (function(w, d, tag, id, src){
    w[id] = w[id] || [];
    const js = d.createElement(tag);
    js.async = true;
    js.src = src;
    {integrityLine}
    d.head.appendChild(js);
  })(window, document, "script", "{$id}", "{source}");
</script>

JS;

        $integrityLine = $integrity
            ? "js.integrity = '{$integrity}';\n    js.crossOrigin = 'anonymous';"
            : '';

        return str_replace(
            ['{source}', '{integrityLine}'],
            [$src, $integrityLine],
            $template
        );
    }


    /**
     * Vygeneruje více <script> tagů najednou.
     *
     * @param array<int, string> $paths Pole cest nebo URL k JS souborům
     * @param string $baseUrl Základní cesta (např. "/assets/") – přidává se před relativní cesty
     * @param string $algo Algoritmus hashování pro integrity atribut (např. "sha384")
     * @return string Spojené HTML <script> tagy oddělené novým řádkem
     */
    public static function jsMultiple(array $paths, string $baseUrl = '', string $algo = 'sha384'): string
    {
        return self::renderMultiple($paths, [self::class, 'js'], $baseUrl, $algo);
    }

    /**
     * Vygeneruje více <link rel="stylesheet"> tagů najednou.
     *
     * @param array<int, string> $paths Pole cest nebo URL k CSS souborům
     * @param string $baseUrl Základní cesta (např. "/assets/") – přidává se před relativní cesty
     * @param string $algo Algoritmus hashování pro integrity atribut (např. "sha384")
     * @return string Spojené HTML <link> tagy oddělené novým řádkem
     */
    public static function cssMultiple(array $paths, string $baseUrl = '', string $algo = 'sha384'): string
    {
        return self::renderMultiple($paths, [self::class, 'css'], $baseUrl, $algo);
    }

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
        $version = \strtotime('this week monday');
        $html = <<<EOT

    <!--[if (IE 9)&!(IEMobile)]>
      <script src="https://cdn.lemonadeframework.cz/static/js/ie.polyfill.min.js?v={$version}"></script>
    <![endif]-->

EOT;

        return $html;
    }

    /**
     * Interní pomocná metoda pro generování více tagů (js, css, atd.).
     *
     * @param array<int, string> $paths Pole cest nebo URL
     * @param callable(string $path, string $baseUrl, string $algo): string $callback Callback metoda pro každý prvek
     * @param string $baseUrl Základní cesta pro relativní soubory
     * @param string $algo Algoritmus hashování integrity
     * @return string Spojené HTML tagy oddělené novým řádkem
     */
    protected static function renderMultiple(array $paths, callable $callback, string $baseUrl, string $algo): string
    {
        $tags = array_map(fn($path) => $callback($path, $baseUrl, $algo), $paths);
        return implode("\n", $tags);
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

        $storage = IntegrityStorage::getInstance();
        if ($storage->has($absolutePath)) {
            return $storage->get($absolutePath) ?? '';
        }

        $rawHash = @hash_file($algo, $absolutePath, true);
        if ($rawHash === false) {
            return '';
        }

        $hash = base64_encode($rawHash);
        $integrity = "{$algo}-{$hash}";
        $storage->set($absolutePath, $integrity);

        return $integrity;
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
        return str_contains($url, '?') ? $url : $url . '?v=' . self::getAssetVersion();
    }

    /**
     * Vrací verzi assetů (timestamp začátku aktuálního týdne, pondělí 00:00:00).
     * Využívá se pro verzování CDN souborů, aby se cache busting dělal jen jednou týdně.
     *
     * @return string Timestamp aktuálního týdne jako string
     */
    protected static function getAssetVersion(): string
    {
        return (string) strtotime('this week monday');
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