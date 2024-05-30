<?php declare(strict_types=1);

namespace Lemonade\Assets\Interfaces;

use Lemonade\Assets\Compiler;

interface NamingConventionInterface
{

    /**
     * @param array $files
     * @param Compiler $compiler
     * @return string
     */
    public function getFilename(array $files, Compiler $compiler): string;

}

