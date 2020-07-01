<?php

namespace PhpAssets\Css\Blade;

use Illuminate\Support\Str;
use Illuminate\View\Compilers\ComponentTagCompiler;

class BladeTagFinder
{
    /**
     * ComponentTagCompiler instance.
     *
     * @var ComponentTagCompiler
     */
    protected $componentTagCompiler;

    /**
     * Create new BladeStyleReader instance.
     */
    public function __construct()
    {
        $this->componentTagCompiler = new ComponentTagCompiler;
    }

    /**
     * Find tags.
     *
     * @param string $value
     * @param string $tag
     * @return string
     */
    public function find(string $value, $tag)
    {
        $tags = $this->getTags($value);

        if (!$tags->has($tag)) {
            return;
        }

        return $tags[$tag];
    }

    /**
     * Get tags.
     *
     * @param string $value
     * @return void
     * 
     * @see \Illuminate\View\Compilers\ComponentTagCompiler
     */
    public function getTags(string $value)
    {
        $pattern = "/
            <
                \s*
                ([\w\-\:\.]*)
                (?<attributes>
                    (?:
                        \s+
                        [\w\-:.@]+
                        (
                            =
                            (?:
                                \\\"[^\\\"]*\\\"
                                |
                                \'[^\']*\'
                                |
                                [^\'\\\"=<>]+
                            )
                        )
                    ?)*
                    \s*
                )
                (?<![\/=\-])
            >
        /x";

        preg_match_all($pattern, $value, $matches);

        return collect($matches[1])->mapWithKeys(function ($tag, $key) use ($value, $matches) {

            return [$tag => (object) [
                'attributes' => $this->getAttributesFromAttributeString($matches['attributes'][$key]),
                'content' => $this->getTagContent($value, $matches[0][$key], $matches[1][$key])
            ]];
        });
    }

    protected function getTagContent($value, $fullTag, $tagName)
    {
        return Str::between($value, $fullTag, "</{$tagName}>");
    }

    /**
     * Get attributes from string.
     *
     * @param string $attributeString
     * @return void
     * 
     * @see \Illuminate\View\Compilers\ComponentTagCompiler
     */
    protected function getAttributesFromAttributeString($attributeString)
    {
        $pattern = '/
            (?<attribute>[\w\-:.@]+)
            (
                =
                (?<value>
                    (
                        \"[^\"]+\"
                        |
                        \\\'[^\\\']+\\\'
                        |
                        [^\s>]+
                    )
                )
            )?
        /x';

        if (!preg_match_all($pattern, $attributeString, $matches, PREG_SET_ORDER)) {
            return [];
        }

        return collect($matches)->mapWithKeys(function ($match) {
            $attribute = $match['attribute'];
            $value = $match['value'] ?? null;

            if (is_null($value)) {
                $value = 'true';

                $attribute = Str::start($attribute, 'bind:');
            }

            $value = $this->componentTagCompiler->stripQuotes($value);

            return [$attribute => $value];
        });
    }
}
