<?php declare(strict_types = 1);

namespace Lemonade\Assets;
use stdClass;
use function ltrim;
use function str_replace;

final class ElementLoader
{

    /**
     * @param string   $type Typ elementu ("js" nebo "css")
     * @param Compiler $compiler Instance kompilátoru generujícího soubory
     * @param string   $tempPath Cesta k vygenerovaným assetům (např. "./assets")
     */
    public function __construct(
        protected readonly string $type,
        protected readonly Compiler $compiler,
        protected readonly string $tempPath
    ) {}

    /**
     * Vrátí HTML element s vloženým odkazem a volitelným integrity hash.
     * @param string      $source URL vygenerovaného souboru
     * @param string|null $integrity Hash pro atribut integrity (volitelné)
     * @return string Výsledný HTML kód
     */
    public function getElement(string $source, ?string $integrity = null): string
    {
        return str_replace(
            ["{source}", "{integrity}"],
            [$source, $integrity ?? ''],
            match ($this->type) {
                'js'  => $this->_elementJavascript($integrity),
                'css' => $this->_elementStylesheet($integrity),
                default => '',
            }
        );
    }

    /**
     * Generuje finální HTML pro všechny vygenerované soubory.
     * @return string HTML kód elementů
     */
    public function render(): string
    {
        foreach ($this->compiler->generate() as $file) {

            $source = $this->getGeneratedFilePath($file);
            $filePath = $this->getGeneratedFileIntegrity($file);
            $integrity = $this->calculateIntegrityHash($filePath);

            return $this->getElement($source, $integrity);
        }

        return "";
    }

    /**
     * Vrací absolutní cestu k vygenerovanému souboru pro výpočet integrity.
     * @param stdClass $file Objekt se souborem (musí mít vlastnost "file")
     * @return string Absolutní cesta k souboru
     */
    protected function getGeneratedFileIntegrity(stdClass $file): string
    {
        return Path::resolve($this->tempPath . '/' . $file->file);
    }

    /**
     * Vrací URL cestu k vygenerovanému souboru pro použití ve stránce.
     * @param stdClass $file Objekt se souborem (musí mít vlastnost "file")
     * @return string URL adresa
     */
    protected function getGeneratedFilePath(stdClass $file): string
    {
        if (function_exists("base_url")) {
            return base_url(ltrim($this->tempPath, '.') . '/' . $file->file);
        }

        return '/' . ltrim($this->tempPath, './') . '/' . $file->file;
    }

    /**
     * Spočítá integrity hash daného souboru.
     * @param string $filePath Absolutní cesta k souboru
     * @param string $algo Algoritmus hashování (výchozí je sha384)
     * @return string Integrity hash ve formátu "algo-base64hash"
     */
    protected function calculateIntegrityHash(string $filePath, string $algo = 'sha384'): string
    {
        $storage = IntegrityStorage::getInstance();

        if ($storage->has($filePath)) {
            return $storage->get($filePath) ?? '';
        }

        $hash = base64_encode(hash_file($algo, $filePath, true));
        $integrity = "{$algo}-{$hash}";
        $storage->set($filePath, $integrity);

        return $integrity;
    }


    /**
     * Vrací HTML script element s volitelnou integritou.
     * @param string|null $integrity Integrity hash
     * @return string HTML <script> tag
     */
    protected function _elementJavascript(?string $integrity = null): string
    {
        return <<<patternJs

    <script data-id="Lemonade\\Assets\\JSBuilder">
      (function(w, d, tag, id, src){
        w[id] = w[id] || [];
        const js = d.createElement(tag);
        js.async = true;
        js.src = src;
        js.integrity = '{$integrity}';
        js.crossOrigin = 'anonymous';
        d.head.appendChild(js);
      })(window, document, "script", "app-loader-js", "{source}");
    </script>

patternJs;
    }

    /**
     * Vrací HTML CSS link element s podporou integrity.
     * @param string|null $integrity Integrity hash pro soubor (volitelné)
     * @return string HTML <link> tag
     */
    protected function _elementStylesheet(?string $integrity = null): string
    {
        $integrityAttr = $integrity ? ' integrity="' . $integrity . '" crossorigin="anonymous"' : '';

        return <<<patternCss

    <link rel="stylesheet" media="screen" href="{source}"{$integrityAttr} data-id="Lemonade\\Assets\\CSSBuilder">
patternCss;
    }

}