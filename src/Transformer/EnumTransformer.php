<?php

declare(strict_types=1);

namespace Berlioz\Form\Transformer;

use BackedEnum;
use Berlioz\Form\Element\ElementInterface;
use LogicException;
use UnitEnum;

class EnumTransformer implements TransformerInterface
{
    public function __construct(private string $class)
    {
        class_exists(BackedEnum::class) || throw new LogicException('PHP 8.1 required to use Enum transformer');
        is_a($this->class, BackedEnum::class, true) || throw new LogicException('Class must implement BackedEnum');
    }

    /**
     * @inheritDoc
     */
    public function fromForm($data, ElementInterface $element): UnitEnum|array|null
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
