<?php

namespace Gedmo\Mapping\Annotation;

use Attribute;
use Doctrine\Common\Annotations\Annotation;

/**
 * Slug annotation for Sluggable behavioral extension
 *
 * @Annotation
 * @Target("PROPERTY")
 *
 * @author Gediminas Morkevicius <gediminas.morkevicius@gmail.com>
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class Slug
{
    /** @var list<string> @Required */
    public $fields;
    /** @var bool */
    public $updatable;
    /** @var 'default'|'camel' */
    public $style;
    /** @var bool */
    public $unique;
    /** @var string */
    public $unique_base;
    /** @var string */
    public $separator;
    /** @var string */
    public $prefix;
    /** @var string */
    public $suffix;
    /** @var list<SlugHandler> */
    public $handlers;
    /** @var string */
    public $dateFormat;

    /**
     * @param list<string> $fields
     * @param 'default'|'camel' $style
     * @param list<SlugHandler> $handlers
     */
    public function __construct(
        array $fields,
        bool $updatable = true,
        string $style = 'default',
        bool $unique = true,
        ?string $unique_base = null,
        string $separator = '-',
        string $prefix = '',
        string $suffix = '',
        array $handlers = [],
        string $dateFormat = 'Y-m-d-H:i'
    ) {
        $this->fields = $fields;
        $this->updatable = $updatable;
        $this->style = $style;
        $this->unique = $unique;
        $this->unique_base = $unique_base;
        $this->separator = $separator;
        $this->prefix = $prefix;
        $this->suffix = $suffix;
        $this->handlers = $handlers;
        $this->dateFormat = $dateFormat;
    }
}
