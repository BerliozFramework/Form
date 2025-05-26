<?php

declare(strict_types=1);

namespace Berlioz\Form\Transformer;

use Berlioz\Form\Element\ElementInterface;
use Closure;

class CallbackTransformer implements TransformerInterface
{
    public function __construct(
        private ?Closure $toFormCallback = null,
        private ?Closure $fromFormCallback = null,
    ) {

    }

    /**
     * @inheritDoc
     */
    public function toForm(mixed $data, ElementInterface $element): mixed
    {
        if (null === $this->toFormCallback) {
            return $data;
        }

        return ($this->toFormCallback)($data, $element);
    }

    /**
     * @inheritDoc
     */
    public function fromForm(mixed $data, ElementInterface $element): mixed
    {
        if (null === $this->fromFormCallback) {
            return $data;
        }

        return ($this->fromFormCallback)($data, $element);
    }
}
