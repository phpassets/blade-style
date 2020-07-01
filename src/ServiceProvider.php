<?php

namespace PhpAssets\Css\Blade;

use PhpAssets\Css\Factory\Factory;
use Illuminate\Support\Facades\File;
use PhpAssets\Css\Factory\Reader\ReaderResolver;
use PhpAssets\Css\Factory\Compiler\CompilerResolver;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;

class ServiceProvider extends LaravelServiceProvider
{
    /**
     * Register applicationo services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerPublishes();

        $this->registerCompiler();
        $this->registerCompilerResolver();

        $this->registerReader();
        $this->registerReaderResolver();

        $this->registerFactory();
    }

    /**
     * Register reader.
     *
     * @return void
     */
    protected function registerReader()
    {
        foreach (config('style.reader') as $extension => $reader) {
            $this->app->singleton($reader);
        }
    }

    /**
     * Register style compiler.
     *
     * @return void
     */
    protected function registerCompiler()
    {
        foreach (config('style.compiler') as $compiler => $abstracts) {
            $this->app->singleton($compiler);
        }
    }

    /**
     * Register compiler resolver.
     *
     * @return void
     */
    protected function registerCompilerResolver()
    {
        $this->app->singleton('style.compiler.resolver', function ($app) {
            $resolver = new CompilerResolver;

            foreach (config('style.compiler') as $binding => $abstracts) {
                foreach ($abstracts as $abstract) {
                    $resolver->register($abstract, function () use ($binding) {
                        return $this->app[$binding];
                    });
                }
            }

            return $resolver;
        });
    }

    /**
     * Register reader resolver.
     *
     * @return void
     */
    protected function registerReaderResolver()
    {
        $this->app->singleton('style.reader.resolver', function ($app) {
            $resolver = new ReaderResolver;

            foreach (config('style.reader') as $extension => $reader) {
                $resolver->register($extension, function () use ($reader) {
                    return $this->app[$reader];
                });
            }

            return $resolver;
        });
    }

    /**
     * Register style factory.
     *
     * @return void
     */
    protected function registerFactory()
    {
        $this->app->singleton('style.factory', function ($app) {
            return new Factory($app['style.compiler.resolver'], $app['style.reader.resolver']);
        });
    }

    /**
     * Register publishes.
     *
     * @return void
     */
    public function registerPublishes()
    {
        $this->publishes([
            __DIR__ . '/../storage/' => storage_path('framework/styles')
        ], 'storage');

        $this->publishes([
            __DIR__ . '/../config/style.php' => config_path('style.php')
        ], 'config');

        $this->mergeConfigFrom(
            __DIR__ . '/../config/style.php',
            'style'
        );

        if (!File::exists(storage_path('framework/styles'))) {
            File::copyDirectory(__DIR__ . '/../storage/', storage_path('framework/styles'));
        }
    }
}
