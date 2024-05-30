<?php declare(strict_types=1);

namespace Lemonade\Assets;

use Exception;
use Nette\Utils\Finder;
use function sprintf;

final class AssetsFactory
{

    const TYP_CSS = "css";
    const TYP_JS = "js";
    const TYP_OUT = "out";

    /**
     * @var string
     */
    protected string $cssDir = "/css";

    /**
     * @var string
     */
    protected string $jsDir = "/js";

    /**
     * Compiled dir
     * @var string
     */
    protected string $compiled = "/compiled";


    /**
     * @param string $appTyp
     * @param string $appDirectory
     * @param Finder $appFinder
     * @param FileCollection $appCollection
     * @param array $files
     */
    public function __construct(

        protected readonly string $appTyp,
        protected readonly string $appDirectory,
        protected readonly Finder $appFinder,
        protected readonly FileCollection $appCollection,
        protected readonly array $files = []

    ) {

        if(in_array($this->appTyp, [AssetsFactory::TYP_CSS, AssetsFactory::TYP_JS])) {

            $this->appCollection->addFiles($files);
        }

        if($this->appTyp === AssetsFactory::TYP_CSS) {

            $this->appCollection
                 ->addWatchFiles(
                    $this->appFinder::findFiles("*.css", "*.sass", ".map")->in($this->getDirectory(AssetsFactory::TYP_CSS))
                 );
        }

        if($this->appTyp === AssetsFactory::TYP_JS) {

            $this->appCollection
                 ->addWatchFiles(
                    $this->appFinder::findFiles("*.js")->in($this->getDirectory(AssetsFactory::TYP_JS))
                 );
        }

    }


    /**
     * @return string
     */
    protected function _cssMinifier(): string
    {

        try {

            $compiler = Compiler::createCssCompiler(files: $this->appCollection, outputDir: $this->getDirectory(typ: AssetsFactory::TYP_OUT));
            $compiler->addFilter(function ($code) {

                return Minifier::css(code: $code);

            });

            return (new ElementLoader(type: AssetsFactory::TYP_CSS, compiler: $compiler, tempPath: $this->getDirectory(typ: AssetsFactory::TYP_OUT)))->render();

        } catch (Exception $e) {

            if (function_exists(function: "log_message")) {

                log_message("error", "Lemonade\\AssetsFactory\\CssMinifier: {$e->getMessage()}");
            }

            return "";
        }

    }

    /**
     * @return string
     */
    protected function _jsMinifier(): string
    {

        try {

            $compiler = Compiler::createJsCompiler(files: $this->appCollection, outputDir: $this->getDirectory(typ: AssetsFactory::TYP_OUT));
            $compiler->addFilter(function ($code) {

                return Minifier::js(code: $code);

            });

            return (new ElementLoader(type: AssetsFactory::TYP_JS, compiler: $compiler, tempPath: $this->getDirectory(typ: AssetsFactory::TYP_OUT)))->render();

        } catch (Exception $e) {

            if (function_exists(function: "log_message")) {

                log_message("error", "Lemonade\\AssetsFactory\\JSMinifier: {$e->getMessage()}");
            }

            return "";
        }

    }

    /**
     * @param string|null $typ
     * @return string
     */
    protected function getDirectory(string $typ = null): string
    {

        return match ($typ) {
            default => sprintf("%s%s", $this->appDirectory, $this->compiled),
            "css" => sprintf("%s%s", $this->appDirectory, $this->cssDir),
            "js" => sprintf("%s%s", $this->appDirectory, $this->jsDir),
        };

    }

    /**
     * Vykresleni css
     * @param string $directory
     * @param array $files
     * @return string
     */
    public static function createCss(string $directory, array $files = []): string
    {

        $finder     = new Finder();
        $collection = new FileCollection($directory);
        $factory    = new AssetsFactory(appTyp: AssetsFactory::TYP_CSS, appDirectory: $directory, appFinder: $finder, appCollection: $collection, files: $files);

        return $factory->_cssMinifier();
    }

    /**
     * Vykresleni Js
     * @param string $directory
     * @param array $files
     * @return string
     */
    public static function createJs(string $directory, array $files = []): string
    {

        $finder     = new Finder();
        $collection = new FileCollection($directory);
        $factory    = new AssetsFactory(appTyp: AssetsFactory::TYP_JS, appDirectory: $directory, appFinder: $finder, appCollection: $collection, files: $files);

        return $factory->_jsMinifier();
    }

}