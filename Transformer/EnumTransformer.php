<?php

declare(strict_types=1);

namespace Berlioz\Form\Transformer;

use BackedEnum;
use Berlioz\Form\Element\ElementInterface;
use ValueError;

class EnumTransformer implements TransformerInterface
{
    public function __construct(private string $class)
    {
        if (false === is_a($this->class, BackedEnum::class, true)) {
            throw new ValueError('Enum must be a PHP 8.1 backed enum type');
        }
    }

    /**
     * @inheritDoc
     */
    public function fromForm($data, ElementInterface $element): BackedEnum|array|null
    {
        if (is_array($data)) {
            return array_filter(
                array_map(
                    fn($value) => ($this->class)::tryFrom($data ?? ''),
                    $data,
                )
            );
        }

        $enum = ($this->class)::tryFrom($data ?? '');

        if (empty($enum)) {
            return null;
        }

        return $enum;
    }

    /**
     * @inheritDoc
     */
    public function toForm($data, ElementInterface $element): mixed
    {
        if (is_array($data)) {
            return array_map(
                fn($value) => $value->value,
                array_filter($data, fn($value) => $value instanceof $this->class),
            );
        }

        if ($data instanceof $this->class) {
            return $data->value;
        }

        return null;
    }
}
