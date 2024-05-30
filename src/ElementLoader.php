<?php declare(strict_types = 1);

namespace Lemonade\Assets;
use stdClass;
use function ltrim;
use function str_replace;

final class ElementLoader
{

    /**
     * @param string $type
     * @param Compiler $compiler
     * @param string $tempPath
     */
    public function __construct(

        protected readonly string $type,
        protected readonly Compiler $compiler,
        protected readonly string $tempPath

    )
    {

    }

    /**
     * @param string $source
     * @return string
     */
    public function getElement(string $source): string
    {

        return str_replace(search: "{source}", replace: $source, subject: match ($this->type) {
            default => "",
            "js" => ElementLoader::_JS(),
            "css" => ElementLoader::_CSS()
        });


    }

    /**
     * Generovani
     * @return string
     */
    public function render(): string
    {
        
        foreach ($this->compiler->generate() as $file) {

            return $this->getElement(source: $this->getGeneratedFilePath(file: $file)) . PHP_EOL;
        }

        return "";
    }


    /**
     * @param stdClass $file
     * @return string
     */
    protected function getGeneratedFilePath(stdClass $file): string
    {

        if(function_exists(function: "base_url")) {

            return base_url(uri: ltrim(string: $this->tempPath, characters: ".") . "/" . $file->file);
        }

        return ltrim(string: $this->tempPath, characters: ".") . "/" . $file->file;
    }


    /**
     * @return string
     */
    protected function _JS(): string
    {

        return <<<patternJs
    
     <script data-id="Lemonade\Assets\JSBuilder">
     (function(c,o,r,e){
       c[e]=c[o]||[];
       var f=o.getElementsByTagName(r)[0], j=o.createElement(r);
       j.async=true;
       j.src="{source}";
       f.parentNode.insertBefore(j,f);
     })(window,document,"script","app-loader-js");
    </script>

patternJs;


    }


    /**
     * @return string
     */
    protected function _CSS(): string
    {

        return  <<<patternCss
    
     <link rel="stylesheet" media="screen" href="{source}" data-id="Lemonade\Assets\CSSBuilder">

patternCss;


    }
}