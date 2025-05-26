<?php

declare(strict_types=1);

namespace Berlioz\Form\Transformer;

use Berlioz\Form\Element\ElementInterface;
use Closure;

class ArrayFilterTransformer implements TransformerInterface
{
    public function __construct(private ?Closure $callback = null)
    {
    }

    /**
     * @inheritDoc
     */
    public function toForm(mixed $data, ElementInterface $element): mixed
    {
        if (empty($data)) {
            return [];
        }

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

        if (null !== $this->callback) {
            return array_filter($data, $this->callback);
        }

        return array_filter($data, fn($value) => $value === false || !empty($value));
    }
}
