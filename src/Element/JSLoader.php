<?php declare(strict_types = 1);

namespace Lemonade\Assets\Element;
use function str_replace;

final class JSLoader extends ElementLoader
{

    /**
     * @var string
     */
    private static string $pattern = <<<patternJs
    
     <script>
     (function(c,o,r,e){
       c[e]=c[o]||[];
       var f=o.getElementsByTagName(r)[0], j=o.createElement(r);
       j.async=true;
       j.src="{source}";
       f.parentNode.insertBefore(j,f);
     })(window,document,"script","app-loader-js");
    </script>

patternJs;

    /**
     * @param $source
     * @return string
     */
    public function getElement($source): string
    {
        
        return (string) str_replace(search: "{source}", replace: $source, subject: self::$pattern);
    }
    
    
    
    
}