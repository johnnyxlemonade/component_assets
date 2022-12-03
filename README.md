
```php

use Lemonade\Assets\AssetsFactory;

try {
    
    $path = "./themes/frontend";
    
    $css = [
        "/css/app.bootstrap.css",
        "/css/app.files.css",
        "/css/app.font.css"
    ];
    
    $js = [
        "/js/app.vendor.js",
        "/js/app.prototype.js",
        "/js/app.extends.js",
        "/js/app.ajax.js",
        "/js/app.cms.js",
    ];
    
    echo AssetsFactory::createCss($path, $css);
    echo AssetsFactory::createJs($path, $js);
    
    
} catch (Exception $e) {
    
    echo $e->getMessage();
}

/*

<link rel="stylesheet" media="screen" href="/themes/frontend/compiled/loader-763c44d78ab1-050f7466c536.css">

<script>
 (function(c,o,r,e){
  c[e]=c[o]||[];  
  var f=o.getElementsByTagName(r)[0], j=o.createElement(r); 
      j.async=true;
      j.src="/themes/frontend/compiled/loader-fb02138ea359-d4ada2c563cb.js";
      f.parentNode.insertBefore(j,f);      
 })(window,document,"script","app-loader-js");
</script>
*/