<?php declare(strict_types=1);

namespace Lemonade\Assets;

use Lemonade\Assets\Exception\InvalidArgumentException;
use Lemonade\Assets\Interfaces\FileCollectionInterface;
use Lemonade\Assets\Interfaces\NamingConventionInterface;
use StdClass;

final class Compiler
{

    /**
     * @var string
     */
    protected string $outputDir = "";

    /**
     * @var bool
     */
    protected bool $joinFiles = true;

    /**
     * @var array
     */
    protected array $filters = [];
    /**
     * @var bool
     */
    protected bool $checkLastModified = TRUE;

    /**
     * @param FileCollectionInterface $collection
     * @param NamingConventionInterface $convention
     * @param string $outputDir
     * @throws InvalidArgumentException
     */
    public function __construct(protected readonly FileCollectionInterface $collection, protected readonly NamingConventionInterface $convention, string $outputDir)
    {

        $tempPath = Path::normalize(path: $outputDir);

        if (!is_dir($tempPath) && !@mkdir($tempPath, 0777, true) && !is_dir($tempPath)) {

            throw new InvalidArgumentException("Cant create directory '$tempPath'");
        }

        if (!is_writable($tempPath)) {

            throw new InvalidArgumentException("Directory '$tempPath' is not writeable.");
        }

        $this->outputDir = $tempPath;

    }

    /**
     * Create compiler with predefined css output naming convention
     * @param FileCollectionInterface $files
     * @param string $outputDir
     * @return Compiler
     * @throws InvalidArgumentException
     */
    public static function createCssCompiler(FileCollectionInterface $files, string $outputDir): Compiler
    {

        return new Compiler(collection: $files, convention: NameConvention::createCssConvention(), outputDir: $outputDir);
    }

    /**
     * Create compiler with predefined javascript output naming convention
     *
     * @param FileCollectionInterface $files
     * @param string $outputDir
     * @return Compiler
     * @throws InvalidArgumentException
     */
    public static function createJsCompiler(FileCollectionInterface $files, string $outputDir): Compiler
    {

        return new Compiler(collection: $files, convention: NameConvention::createJsConvention(), outputDir: $outputDir);
    }

    /**
     * @param array|null $files
     * @return int|mixed
     */
    public function getLastModified(array $files = null): mixed
    {

        if ($files === null) {

            $files = $this->collection->getFiles();
        }

        $modified = 0;

        foreach ($files as $file) {

            $modified = max($modified, filemtime($file));
        }

        return $modified;
    }

    /**
     * @param array|null $files
     * @return mixed|string
     */
    public function getContent(array $files = null): mixed
    {

        if ($files === null) {
            $files = $this->collection->getFiles();
        }

        // obsah
        $content = "";
        foreach ($files as $file) {

            $content .= PHP_EOL . $this->loadFile($file);
        }

        // filtry
        foreach ($this->filters as $filter) {

            $content = call_user_func($filter, $content, $this);
        }

        return $content;
    }

    /**
     * @param bool $ifModified
     * @return array|object[]|StdClass[]
     */
    public function generate(bool $ifModified = true): array
    {

        $files = $this->collection->getFiles();

        if (!count($files)) {

            return [];
        }

        if ($this->joinFiles) {

            $watch = ($this->checkLastModified ? array_unique(array_merge($files, $this->collection->getWatchFiles())) : []);

            return [

                $this->generateFiles($files, $ifModified, $watch)
            ];

        } else {

            $arr = [];

            foreach ($files as $file) {

                $watch = ($this->checkLastModified ? array_unique(array_merge([$file], $this->collection->getWatchFiles())) : []);
                $arr[] = $this->generateFiles(array($file), $ifModified, $watch);
            }

            return $arr;
        }
    }

    /**
     * @param array $files
     * @param bool $ifModified
     * @param array $watchFiles
     * @return object
     */
    protected function generateFiles(array $files, bool $ifModified, array $watchFiles = array()): object
    {

        $name = $this->convention->getFilename(files: $files, compiler: $this);
        $path = $this->outputDir . "/" . $name;
        $lastModified = $this->checkLastModified ? $this->getLastModified($watchFiles) : 0;

        if (!$ifModified || !file_exists($path) || $lastModified > filemtime($path)) {

            $outPath = in_array("safe", stream_get_wrappers()) ? "safe://" . $path : $path;

            file_put_contents($outPath, $this->getContent($files));
        }

        return (object) [
            "file" => $name,
            "path" => $this->outputDir,
            "time" => $lastModified,
            "source" => $files,
        ];
    }

    /**
     * @param $file
     * @return string
     */
    protected function loadFile($file): string
    {

        $content = file_get_contents($file);

        if($content !== false) {

            return $content;

        } else {

            return "";
        }
    }

    /**
     * @param $filter
     * @return void
     */
    public function addFilter($filter): void
    {

        if (is_callable($filter)) {

            $this->filters[] = $filter;
        }


    }

}