<?php
namespace Amin\Event\Helpers;
class Env
{
    /**
     * Load and parse the .env file.
     *
     * @param string $path Path to the .env file
     * @throws Exception If .env file is not found
     */
    public static function load(string $path)
    {
        if (!file_exists($path)) {
            throw new \Exception(".env file not found at $path");
        }
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            preg_match("/([^#]+)\=(.*)/",$line,$matches);
            if(isset($matches[2])){ 
                putenv(trim($line)); 
            } 
        }
    }

    /**
     * Get an environment variable value.
     *
     * @param string $key The environment variable key
     * @param mixed $default Default value if key does not exist
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        return $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key) ?? $default;
    }
}
