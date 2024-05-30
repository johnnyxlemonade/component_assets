<?php declare(strict_types=1);

namespace Lemonade\Assets;

use Lemonade\Assets\Interfaces\NamingConventionInterface;
use function count;
use function implode;
use function md5;
use function pathinfo;
use function substr;

final class NameConvention implements NamingConventionInterface
{


    /**
     * @param string $prefix
     * @param string $suffix
     */
    public function __construct(
        protected readonly string $prefix,
        protected readonly string $suffix)
    {

    }

    /**
     * Css
     * @return NameConvention
     */
    public static function createCssConvention(): NameConvention
    {

        return new NameConvention(prefix: "loader-", suffix: ".css");
    }

    /**
     * Js
     * @return NameConvention
     */
    public static function createJsConvention(): NameConvention
    {

        return new NameConvention(prefix: "loader-", suffix: ".js");
    }

    /**
     * @return string
     */
    public function getPrefix(): string
    {

        return $this->prefix;
    }

    /**
     * @return string
     */
    public function getSuffix(): string
    {
        return $this->suffix;
    }

    /**
     * @param array $files
     * @param Compiler $compiler
     * @return string
     */
    public function getFilename(array $files, Compiler $compiler): string
    {

        $name = $this->createHash(files: $files, compiler: $compiler);

        if (count($files) === 1) {

            $name .= "-" . pathinfo(path: $files[0], flags: PATHINFO_FILENAME);
        }

        return $this->prefix . $name . $this->suffix;
    }

    /**
     * @param array $files
     * @param Compiler $compiler
     * @return string
     */
    protected function createHash(array $files, Compiler $compiler): string
    {

        return substr(md5(implode("|", $files)), 0, 12) . "-" . substr(md5((string) $compiler->getLastModified()), 0, 12);
    }
}
    