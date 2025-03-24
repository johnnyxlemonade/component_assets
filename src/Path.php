<?php declare(strict_types = 1);

namespace Lemonade\Assets;

final class Path
{

    /**
     * Normalizuje cestu – odstraní . a .., sjednotí oddělovače atd.
     *
     * @param string $path
     * @return string
     */
    public static function normalize($path): string
    {
        
        $path = strtr($path, '\\', '/');
        $root = (strpos($path, '/') === 0) ? '/' : '';
        $pieces = explode('/', trim($path, '/'));
        $res = array();
        
        foreach ($pieces as $piece) {
            if ($piece === '.' || $piece === '') {
                continue;
            }
            if ($piece === '..') {
                array_pop($res);
            } else {
                array_push($res, $piece);
            }
        }
        
        return $root . implode('/', $res);
    }

    /**
     * Určuje, zda je daná cesta nebo URL externí (např. začíná na http://, https:// nebo //).
     *
     * @param string $path Cesta nebo URL
     * @return bool True pokud jde o externí zdroj
     */
    public static function isExternal(string $path): bool
    {
        return str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '//');
    }

    /**
     * Vrátí absolutní cestu k souboru, CLI-safe. Pokud realpath selže, použije getcwd().
     *
     * @param string $path Relativní nebo částečná cesta
     * @return string Absolutní filesystemová cesta
     */
    public static function resolve(string $path): string
    {
        $normalized = self::normalize($path);
        $absolutePath = realpath(getcwd() . '/' . ltrim($normalized, '/'));

        return $absolutePath ?: getcwd() . '/' . ltrim($normalized, '/');
    }
    
}