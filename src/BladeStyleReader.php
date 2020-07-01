<?php

namespace PhpAssets\Css\Blade;

use Illuminate\Support\Str;
use PhpAssets\Css\ReaderInterface;
use Illuminate\Support\Facades\File;
use Illuminate\View\Compilers\ComponentTagCompiler;

class BladeStyleReader implements ReaderInterface
{
    /**
     * Blade tag finder instance.
     *
     * @var BladeTagFinder
     */
    protected $finder;

    /**
     * Create new BladeStyleReader instance.
     *
     * @param BladeTagFinder $finder
     */
    public function __construct(BladeTagFinder $finder)
    {
        $this->finder = $finder;
    }

    /**
     * Get CSS extension language.
     *
     * @param string $path
     * @return string
     */
    public function lang($path)
    {
        $tag = $this->finder->find(File::get($path), 'x-style');

        if (!$tag) {
            return 'css';
        }

        if (!$tag->attributes->has('lang')) {
            return 'css';
        }

        return $tag->attributes['lang'];
    }

    /**
     * Get raw CSS string.
     *
     * @param string $path
     * @return string
     */
    public function raw($path)
    {
        $tag = $this->finder->find(File::get($path), 'x-style');

        if (!$tag) {
            return;
        }

        return $tag->content;
    }
}
