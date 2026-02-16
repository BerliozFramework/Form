<?php
/*
 * This file is part of Berlioz framework.
 *
 * @license   https://opensource.org/licenses/MIT MIT License
 * @copyright 2021 Ronan GIRON
 * @author    Ronan GIRON <https://github.com/ElGigi>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code, to the root.
 */

declare(strict_types=1);

namespace Berlioz\Form\Type;

class ChoiceValue
{
    private string|int|float $label;
    private mixed $value;
    private mixed $finalValue;
    private ?string $group;
    private array $attributes = [];
    private bool $preferred = false;
    private bool $selected = false;

    /**
     * Get label.
     *
     * @return string|int|float
     */
    public function getLabel(): string|int|float
    {
        return $this->label;
    }

    /**
     * Set label.
     *
     * @param string|int|float $label
     */
    public function setLabel(string|int|float $label): void
    {
        $this->label = $label;
    }

    /**
     * Get value.
     *
     * @return mixed
     */
    public function getValue(): mixed
    {
        return $this->value ?? null;
    }

    /**
     * Set value.
     *
     * @param mixed $value
     */
    public function setValue(mixed $value): void
    {
        $this->value = $value;
    }

    /**
     * Get final value.
     *
     * @return mixed
     */
    public function getFinalValue(): mixed
    {
        return $this->finalValue ?? null;
    }

    /**
     * Set final value.
     *
     * @param mixed $value
     */
    public function setFinalValue(mixed $value): void
    {
        $this->finalValue = $value;
    }

    /**
     * Get group.
     *
     * @return string|null
     */
    public function getGroup(): ?string
    {
        return $this->group ?? null;
    }

    /**
     * Set group.
     *
     * @param string|null $group
     */
    public function setGroup(?string $group): void
    {
        $this->group = $group;
    }

    /**
     * Get attributes.
     *
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Get attribute.
     *
     * @param string $name Attribute name
     * @param null $default Default value if not exists
     *
     * @return mixed
     */
    public function getAttribute(string $name, mixed $default = null): mixed
    {
        if (array_key_exists($name, $this->attributes)) {
            return $this->attributes[$name];
        }

        return $default;
    }

    /**
     * Set attributes.
     *
     * @param array $attributes
     */
    public function setAttributes(array $attributes): void
    {
        $this->attributes = $attributes;
    }

    /**
     * Is selected?
     *
     * @return bool
     */
    public function isSelected(): bool
    {
        return $this->selected;
    }

    /**
     * Set selected.
     *
     * @param bool $selected
     */
    public function setSelected(bool $selected): void
    {
        $this->selected = $selected;
    }

    /**
     * Is preferred?
     *
     * @return bool
     */
    public function isPreferred(): bool
    {
        return $this->preferred;
    }

    /**
     * Set preferred.
     *
     * @param bool $preferred
     */
    public function setPreferred(bool $preferred): void
    {
        $this->preferred = $preferred;
    }
}