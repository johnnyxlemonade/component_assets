<?php declare(strict_types=1);

namespace Lemonade\Assets;

/**
 * Třída pro ukládání a načítání integrity hashů do/z souborové cache.
 */
final class IntegrityStorage
{

    protected const DEFAULT_CACHE_DIR = 'application/cache/0/cache';
    protected const DEFAULT_FILENAME = 'assets_storage_integrity.cache';
    protected const DEFAULT_TTL = 7200;

    /** @var self|null Singleton instance */
    private static ?self $instance = null;

    /** @var string Cesta k souboru cache */
    private string $cacheFile;

    /** @var array<string, array{integrity: string, mtime: int, stored_at: int}> Cache dat s cestou jako klíčem */
    private array $data = [];

    /** @var int Časová platnost hashů v sekundách (TTL) */
    private int $ttl;

    /**
     * Vrací singleton instanci třídy IntegrityStorage.
     * Pokud ještě instance neexistuje, vytvoří ji.
     *
     * @param string $cacheDir Cesta k adresáři s cache
     * @param string $filename Název souboru cache
     * @param int $ttl Časová platnost (TTL) v sekundách
     * @return self
     */
    public static function getInstance(
        string $cacheDir = self::DEFAULT_CACHE_DIR,
        string $filename = self::DEFAULT_FILENAME,
        int $ttl = self::DEFAULT_TTL
    ): self {
        if (!isset(self::$instance)) {
            self::$instance = new self($cacheDir, $filename, $ttl);
        }

        return self::$instance;
    }

    /**
     * Vytvoří novou instanci a načte cache. TTL lze volitelně změnit.
     *
     * @param string $cacheDir Cesta k adresáři s cache
     * @param string $filename Název souboru cache
     * @param int $ttl Časová platnost (Time-To-Live) hashů v sekundách
     */
    public function __construct(
        protected readonly string $cacheDir = self::DEFAULT_CACHE_DIR,
        protected readonly string $filename = self::DEFAULT_FILENAME,
        int $ttl = self::DEFAULT_TTL
    ) {
        $this->ttl = $ttl;
        $this->cacheFile = rtrim($this->cacheDir, '/') . '/' . $this->filename;
        $this->load();
    }

    /**
     * Zjistí, zda cache obsahuje platný záznam pro daný soubor.
     * Neprovádí načtení integrity ani invalidaci.
     *
     * @param string $filePath Absolutní cesta k souboru
     * @return bool True, pokud je záznam přítomen a platný (soubor existuje a TTL nevypršel)
     */
    public function has(string $filePath): bool
    {
        if (!isset($this->data[$filePath])) {
            return false;
        }

        $entry = $this->data[$filePath];

        if (!file_exists($filePath) || filemtime($filePath) !== $entry['mtime']) {
            unset($this->data[$filePath]);
            return false;
        }

        if (time() - ($entry['stored_at'] ?? 0) > $this->ttl) {
            unset($this->data[$filePath]);
            return false;
        }

        return true;
    }

    /**
     * Vrátí integrity hash, pokud je v cache a soubor se nezměnil a je v limitu TTL.
     *
     * @param string $filePath Absolutní cesta k souboru
     * @return string|null Integrity hash nebo null, pokud není v cache nebo soubor se změnil
     */
    public function get(string $filePath): ?string
    {
        if (!$this->has($filePath)) {
            return null;
        }

        return $this->data[$filePath]['integrity'];
    }

    /**
     * Uloží integrity hash a mtime daného souboru do cache.
     *
     * @param string $filePath Absolutní cesta k souboru
     * @param string $integrity Spočítaný integrity hash (např. "sha384-...")
     * @return void
     */
    public function set(string $filePath, string $integrity): void
    {
        $this->data[$filePath] = [
            'integrity' => $integrity,
            'mtime' => filemtime($filePath),
            'stored_at' => time(),
        ];

        $this->save();
    }

    /**
     * Odstraní z cache danou cestu k souboru, pokud existuje.
     *
     * @param string $filePath Absolutní cesta k souboru
     * @return void
     */
    public function delete(string $filePath): void
    {
        if (isset($this->data[$filePath])) {
            unset($this->data[$filePath]);
            $this->save();
        }
    }

    /**
     * Invalidače všech hodnot v cache (vyprázdnění).
     *
     * @return void
     */
    public function clear(): void
    {
        $this->data = [];
        $this->save();
    }

    /**
     * Načte data z cache souboru, pokud existuje.
     *
     * @return void
     */
    private function load(): void
    {
        if (file_exists($this->cacheFile)) {
            $json = file_get_contents($this->cacheFile);
            $this->data = json_decode($json, true) ?: [];
        }
    }

    /**
     * Uloží aktuální data do cache souboru jako JSON.
     *
     * @return void
     */
    private function save(): void
    {
        @file_put_contents($this->cacheFile, json_encode($this->data, JSON_UNESCAPED_SLASHES));
    }
}