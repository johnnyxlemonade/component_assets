<?php declare(strict_types = 1);

namespace Lemonade\Assets\Element;

final class JSLoader extends ElementLoader {
    
    private $pattern = <<<patternJs
    
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
     * 
     * {@inheritDoc}
     * @see \Lemonade\Assets\Element\ElementLoader::getElement()
     */
    public function getElement($source) {
        
        return \str_replace("{source}", $source, $this->pattern);
    }
    
    
    
    
}