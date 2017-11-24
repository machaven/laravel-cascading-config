<?php

namespace Machaven\LaravelCascadingConfig;

use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;

class CascadingConfigProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $config = $this->getConfigFiles();

        if ($config === false) {
            return;
        }

        foreach ($config as $key => $new) {
            $original = config($key, null);

            if (null !== $original) {
                $merged = array_replace_recursive($original, $new);
                config([$key => $merged]);

                continue;
            }

            if (null === $original) {
                config([$key => $new]);
            }
        }
    }

    /**
     * Scan config directory for environment and return an array of all configs.
     *
     * @return array|bool
     */
    private function getConfigFiles()
    {
        $env = App::environment();
        $path = config_path() . '/' . $env . '/';
        $config = [];

        if (!is_dir($path)) {
            return false;
        }

        foreach (scandir($path, SCANDIR_SORT_ASCENDING) as $filename) {
            $filePath = $path . '/' . $filename;
            $namespace = substr($filename, 0, -4);
            if (preg_match("@(\.php)$@", $filename)) {
                $config[$namespace] = include $filePath;
            }
        }

        return $config;
    }
}
