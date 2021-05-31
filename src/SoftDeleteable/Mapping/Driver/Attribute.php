<?php
declare(strict_types=1);

namespace Gedmo\SoftDeleteable\Mapping\Driver;

use Gedmo\Mapping\Annotation\SoftDeleteable;
use Gedmo\Mapping\Driver\AbstractAttributeDriver;

final class Attribute extends AbstractAttributeDriver
{
    /**
     * List of types which are valid for extension
     *
     * @var list<string>
     */
    protected $validTypes = [
        'date',
        'date_immutable',
        'time',
        'time_immutable',
        'datetime',
        'datetime_immutable',
        'datetimetz',
        'datetimetz_immutable',
        'timestamp',
    ];

    public function readExtendedMetadata($meta, array &$config): void
    {
        $class = $this->getMetaReflectionClass($meta);
        $attributes = $class->getAttributes(SoftDeleteable::class);

        if (false === $attribute = \reset($attributes)) {
            return;
        }

        $softDeletable = $attribute->newInstance();
        \assert($softDeletable instanceof SoftDeleteable);

        $this->isValidField($meta, $softDeletable->fieldName);

        $config['softDeleteable'] = true;
        $config['fieldName'] = $softDeletable->fieldName;
        $config['timeAware'] = $softDeletable->timeAware;
        $config['hardDelete'] = $softDeletable->hardDelete;

        $this->validateFullMetadata($meta, $config);
    }
}
