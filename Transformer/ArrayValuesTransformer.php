<?php

declare(strict_types=1);

namespace Berlioz\Form\Transformer;

use Berlioz\Form\Element\ElementInterface;

class ArrayValuesTransformer implements TransformerInterface
{
    /**
     * @inheritDoc
     */
    public function toForm(mixed $data, ElementInterface $element): mixed
    {
        return $data;
    }

    /**
     * @inheritDoc
     */
    public function fromForm(mixed $data, ElementInterface $element): mixed
    {
        if (empty($data)) {
            return null;
        }

        return array_values($data);
    }
}
