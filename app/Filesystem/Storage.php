<?php

namespace App\Filesystem;

use RuntimeException;

final class Storage
{
    private string $basePath;

    public function __construct()
    {
        $config = require __DIR__ . '/../../config/config.php';
        $this->basePath = $config['storage']['path'];
    }
        /**
     * Verifica que una ruta esté dentro del storage.
     * Lanza excepción si intenta salirse.
     */
    public function assertInsideStorage(string $path): void
    {
        $real = realpath($path);
        $realBase = realpath($this->basePath);

        if ($real === false) {
            // El archivo no existe. Verificamos la ruta normalizada igual.
            $real = $this->normalizePath($path);
            $realBase = $this->normalizePath($this->basePath);
        }

        if (!str_starts_with($real, $realBase)) {
            throw new RuntimeException("Ruta fuera del storage: $path");
        }
    }
        /**
     * Normaliza una ruta sin comprobar que exista.
     * Resuelve . y .. manualmente.
     */
    private function normalizePath(string $path): string
    {
        $path = str_replace('\\', '/', $path);
        $parts = explode('/', $path);
        $result = [];

        foreach ($parts as $part) {
            if ($part === '' || $part === '.') {
                continue;
            }
            if ($part === '..') {
                array_pop($result);
                continue;
            }
            $result[] = $part;
        }

        return '/' . implode('/', $result);
    }
        /**
     * Crea una carpeta en disco. Lanza excepción si falla.
     */
    public function createFolder(string $physicalPath): void
    {
        $this->assertInsideStorage($physicalPath);

        if (is_dir($physicalPath)) {
            throw new RuntimeException("La carpeta ya existe: $physicalPath");
        }

        if (!mkdir($physicalPath, 0755, true)) {
            throw new RuntimeException("No se pudo crear la carpeta: $physicalPath");
        }
    }
        /**
     * Escribe un archivo. Devuelve el número de bytes escritos.
     */
    public function writeFile(string $physicalPath, string $content): int
    {
        $this->assertInsideStorage($physicalPath);

        $dir = dirname($physicalPath);
        if (!is_dir($dir)) {
            throw new RuntimeException("La carpeta padre no existe: $dir");
        }

        $bytes = file_put_contents($physicalPath, $content);
        if ($bytes === false) {
            throw new RuntimeException("No se pudo escribir el archivo: $physicalPath");
        }

        return $bytes;
    }
        /**
     * Borra un archivo o carpeta (recursivo).
     */
    public function delete(string $physicalPath): void
    {
        $this->assertInsideStorage($physicalPath);

        if (!file_exists($physicalPath)) {
            throw new RuntimeException("No existe: $physicalPath");
        }

        if (is_file($physicalPath)) {
            if (!unlink($physicalPath)) {
                throw new RuntimeException("No se pudo borrar el archivo: $physicalPath");
            }
            return;
        }

        if (is_dir($physicalPath)) {
            $this->deleteDirectory($physicalPath);
        }
    }

    /**
     * Borra una carpeta y todo su contenido recursivamente.
     */
    private function deleteDirectory(string $dir): void
    {
        $items = scandir($dir);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $path = $dir . '/' . $item;
            if (is_dir($path)) {
                $this->deleteDirectory($path);
            } else {
                unlink($path);
            }
        }
        rmdir($dir);
    }
        /**
     * Mueve o renombra un archivo/carpeta.
     */
    public function move(string $from, string $to): void
    {
        $this->assertInsideStorage($from);
        $this->assertInsideStorage($to);

        if (!file_exists($from)) {
            throw new RuntimeException("No existe el origen: $from");
        }

        if (file_exists($to)) {
            throw new RuntimeException("Ya existe el destino: $to");
        }

        $dir = dirname($to);
        if (!is_dir($dir)) {
            throw new RuntimeException("La carpeta destino no existe: $dir");
        }

        if (!rename($from, $to)) {
            throw new RuntimeException("No se pudo mover: $from → $to");
        }
    }
        /**
     * Verifica si una ruta existe en disco.
     */
    public function exists(string $physicalPath): bool
    {
        return file_exists($physicalPath);
    }
}