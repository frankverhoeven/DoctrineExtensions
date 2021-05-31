<?php
declare(strict_types=1);

namespace Gedmo\Sluggable\Mapping\Driver;

use Doctrine\Persistence\Mapping\ClassMetadata;
use Gedmo\Exception\InvalidMappingException;
use Gedmo\Mapping\Annotation\Slug;
use Gedmo\Mapping\Annotation\SlugHandler;
use Gedmo\Mapping\Annotation\SlugHandlerOption;
use Gedmo\Mapping\Driver;

final class Attribute extends Driver\AbstractAttributeDriver
{
    /**
     * @var list<string>
     */
    protected $validTypes = [
        'string',
        'text',
        'integer',
        'int',
        'datetime',
        'datetimetz',
        'citext',
    ];

    public function readExtendedMetadata($meta, array &$config): void
    {
        $class = $this->getMetaReflectionClass($meta);

        // property annotations
        foreach ($class->getProperties() as $property) {
            if (
                isset($meta->associationMappings[$property->name]['inherited']) ||
                ($meta->isMappedSuperclass && !$property->isPrivate()) ||
                $meta->isInheritedField($property->name)
            ) {
                continue;
            }

            $this->retrieveSlug($meta, $config, $property, '');
        }

        // Embedded entity
        if (\property_exists($meta, 'embeddedClasses') && $meta->embeddedClasses) {
            foreach ($meta->embeddedClasses as $propertyName => $embeddedClassInfo) {
                $embeddedClass = new \ReflectionClass($embeddedClassInfo['class']);

                foreach ($embeddedClass->getProperties() as $embeddedProperty) {
                    $this->retrieveSlug($meta, $config, $embeddedProperty, $propertyName);
                }
            }
        }
    }

    private function retrieveSlug(ClassMetadata $meta, array &$config, \ReflectionProperty $property, string $fieldNamePrefix): void
    {
        $fieldName = $fieldNamePrefix ? ($fieldNamePrefix.'.'.$property->getName()) : $property->getName();
        $attributes = $property->getAttributes(Slug::class);

        if (false === $attribute = \reset($attributes)) {
            return;
        }

        if (!$meta->hasField($fieldName)) {
            throw new InvalidMappingException("Unable to find slug [{$fieldName}] as mapped property in entity - {$meta->name}");
        }

        if (!$this->isValidField($meta, $fieldName)) {
            throw new InvalidMappingException("Cannot use field - [{$fieldName}] for slug storage, type is not valid and must be 'string' or 'text' in class - {$meta->name}");
        }

        $slug = $attribute->newInstance();
        \assert($slug instanceof Slug);

        $handlers = [];
        foreach ($slug->handlers as $handler) {
            if (!$handler instanceof SlugHandler) {
                throw new InvalidMappingException("SlugHandler: {$handler} should be instance of SlugHandler annotation in entity - {$meta->name}");
            }

            if ('' === $handler->class) {
                throw new InvalidMappingException("SlugHandler class: {$handler->class} should be a valid class name in entity - {$meta->name}");
            }

            $class = $handler->class;
            $handlers[$class] = [];

            foreach ((array) $handler->options as $option) {
                if (!$option instanceof SlugHandlerOption) {
                    throw new InvalidMappingException("SlugHandlerOption: {$option} should be instance of SlugHandlerOption annotation in entity - {$meta->name}");
                }
                if ('' === $option->name) {
                    throw new InvalidMappingException("SlugHandlerOption name: {$option->name} should be valid name in entity - {$meta->name}");
                }
                $handlers[$class][$option->name] = $option->value;
            }

            $class::validate($handlers[$class], $meta);
        }

        // process slug fields
        if (empty($slug->fields)) {
            throw new InvalidMappingException("Slug must contain at least one field for slug generation in class - {$meta->name}");
        }

        foreach ($slug->fields as $slugField) {
            $slugFieldWithPrefix = $fieldNamePrefix ? ($fieldNamePrefix.'.'.$slugField) : $slugField;
            if (!$meta->hasField($slugFieldWithPrefix)) {
                throw new InvalidMappingException("Unable to find slug [{$slugFieldWithPrefix}] as mapped property in entity - {$meta->name}");
            }

            if (!$this->isValidField($meta, $slugFieldWithPrefix)) {
                throw new InvalidMappingException("Cannot use field - [{$slugFieldWithPrefix}] for slug storage, type is not valid and must be 'string' or 'text' in class - {$meta->name}");
            }
        }

        if (!$slug->unique && !empty($meta->identifier) && $meta->isIdentifier($fieldName)) {
            throw new InvalidMappingException("Identifier field - [{$fieldName}] slug must be unique in order to maintain primary key in class - {$meta->name}");
        }

        if (false === $slug->unique && $slug->unique_base) {
            throw new InvalidMappingException("Slug annotation [unique_base] can not be set if unique is unset or 'false'");
        }

        if ($slug->unique_base && !$meta->hasField($slug->unique_base) && !$meta->hasAssociation($slug->unique_base)) {
            throw new InvalidMappingException("Unable to find [{$slug->unique_base}] as mapped property in entity - {$meta->name}");
        }

        $sluggableFields = [];
        foreach ($slug->fields as $field) {
            $sluggableFields[] = $fieldNamePrefix ? ($fieldNamePrefix.'.'.$field) : $field;
        }

        // set all options
        $config['slugs'][$fieldName] = [
            'fields' => $sluggableFields,
            'slug' => $fieldName,
            'style' => $slug->style,
            'dateFormat' => $slug->dateFormat,
            'updatable' => $slug->updatable,
            'unique' => $slug->unique,
            'unique_base' => $slug->unique_base,
            'separator' => $slug->separator,
            'prefix' => $slug->prefix,
            'suffix' => $slug->suffix,
            'handlers' => $handlers,
        ];
    }
}
