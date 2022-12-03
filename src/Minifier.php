<?php declare(strict_types = 1);

namespace Lemonade\Assets;

use Lemonade\Assets\Compiler\JS;
use Lemonade\Assets\Compiler\CSS;

final class Minifier {
    
    /**
     * Js komprese
     * @param string $code
     * @return string
     */
    public static function js(string $code = null) {
        
        return (new JS($code))->minify();
    }
    
    /**
     * Css komprese
     * @param string $code
     * @return string
     */
    public static function css(string $code = null) {
        
        return (new CSS($code))->minify();
    }
    
}