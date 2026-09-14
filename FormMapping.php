<?php
/*
 * This file is part of Berlioz framework.
 *
 * @license   https://opensource.org/licenses/MIT MIT License
 * @copyright 2026 Ronan GIRON
 * @author    Ronan GIRON <https://github.com/ElGigi>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code, to the root.
 */

declare(strict_types=1);

namespace Berlioz\Form;

use Closure;

/**
 * Class FormMapping.
 *
 * Strategy describing how a form element reads a value from and writes a value to a mapped object.
 *
 * The `$get` closure receives the mapped object (and an `$exists` reference) and returns the value.
 * The `$set` closure receives the mapped object and the value, and returns whether the write succeeded.
 */
final readonly class FormMapping
{
    /**
     * FormMapping constructor.
     *
     * @param Closure $get fn(object $mapped, ?bool &$exists = null): mixed
     * @param Closure $set fn(object $mapped, mixed $value): bool
     */
    public function __construct(
        private Closure $get,
        private Closure $set,
    ) {
    }

    /**
     * Create a mapping for a simple property name.
     *
     * Reads and writes the value through the Berlioz property accessors (getter/setter, public
     * property or magic methods). The `$exists` reference reflects whether an accessor was found,
     * allowing callers to distinguish a missing property from a `null` value.
     *
     * @param string $property
     *
     * @return self
     */
    public static function forProperty(string $property): self
    {
        return new self(
            get: fn(object $mapped, ?bool &$exists = null): mixed
                => b_get_property_value($mapped, $property, $exists),
            set: fn(object $mapped, mixed $value): bool
                => b_set_property_value($mapped, $property, $value),
        );
    }

    /**
     * Get value from mapped object.
     *
     * @param object $mapped
     * @param bool|null $exists
     *
     * @return mixed
     */
    public function get(object $mapped, ?bool &$exists = null): mixed
    {
        $exists = true;

        return ($this->get)($mapped, $exists);
    }

    /**
     * Set value on mapped object.
     *
     * @param object $mapped
     * @param mixed $value
     *
     * @return bool True if the value has been written
     */
    public function set(object $mapped, mixed $value): bool
    {
        return false !== ($this->set)($mapped, $value);
    }
}
