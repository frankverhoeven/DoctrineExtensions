<?php

namespace Gedmo\Mapping\Driver;

use Doctrine\Persistence\Mapping\ClassMetadata;
use Gedmo\Mapping\Driver;

/**
 * This is an abstract class to implement common functionality
 * for extension annotation mapping drivers.
 *
 * @author     Derek J. Lambert <dlambert@dereklambert.com>
 * @license    MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
abstract class AbstractAttributeDriver implements Driver
{
    /**
     * List of types which are valid for extension
     *
     * @var list<string>
     */
    protected $validTypes = [];

    /**
     * @var object
     */
    private $originalDriver;

    /**
     * @inheritDoc
     */
    public function setOriginalDriver($driver): void
    {
        $this->originalDriver = $driver;
    }

    /**
     * @return \ReflectionClass<ClassMetadata>
     *
     * @throws \ReflectionException
     */
    public function getMetaReflectionClass(ClassMetadata $meta): \ReflectionClass
    {
        $class = $meta->getReflectionClass();
        if (!$class) {
            // based on recent doctrine 2.3.0-DEV maybe will be fixed in some way
            // this happens when running annotation driver in combination with
            // static reflection services. This is not the nicest fix
            $class = new \ReflectionClass($meta->name);
        }

        return $class;
    }

    /**
     * Checks if $field type is valid
     *
     * @param ClassMetadata $meta
     * @param string $field
     *
     * @return bool
     */
    protected function isValidField(ClassMetadata $meta, string $field): bool
    {
        $mapping = $meta->getFieldMapping($field);

        return $mapping && \in_array($mapping['type'], $this->validTypes, true);
    }

    public function validateFullMetadata(ClassMetadata $meta, array $config)
    {
    }
}
