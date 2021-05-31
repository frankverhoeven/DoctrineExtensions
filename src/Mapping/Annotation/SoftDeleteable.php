<?php

namespace Gedmo\Mapping\Annotation;

use Attribute;
use Doctrine\Common\Annotations\Annotation;

/**
 * Group annotation for SoftDeleteable extension
 *
 * @author Gustavo Falco <comfortablynumb84@gmail.com>
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 *
 * @Annotation
 * @Target("CLASS")
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class SoftDeleteable
{
    /** @var string */
    public $fieldName;

    /** @var bool */
    public $timeAware;

    /** @var bool */
    public $hardDelete;

    public function __construct(
        string $fieldName = 'deletedAt',
        bool $timeAware = false,
        bool $hardDelete = true
    ) {
        $this->fieldName = $fieldName;
        $this->timeAware = $timeAware;
        $this->hardDelete = $hardDelete;
    }
}
