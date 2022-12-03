<?php declare(strict_types = 1);

namespace Lemonade\Assets\Interfaces;

use Lemonade\Assets\Compiler;

interface NamingConventionInterface {
    
    /**
     * Vraci nazev souboru
     * @param array $files
     * @param Compiler $compiler
     */
    public function getFilename(array $files, Compiler $compiler);
    
}

