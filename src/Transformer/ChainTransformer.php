<?php

declare(strict_types=1);

namespace Berlioz\Form\Transformer;

use Berlioz\Form\Element\ElementInterface;

class ChainTransformer implements TransformerInterface
{
    private array $transformers;

    public function __construct(TransformerInterface ...$transformers)
    {
        $this->transformers = $transformers;
    }

    /**
     * @inheritDoc
     */
    public function toForm(mixed $data, ElementInterface $element): mixed
    {
        foreach (array_reverse($this->transformers) as $transformer) {
            $data = $transformer->toForm($data, $element);
        }

        return $data;
    }

    /**
     * @inheritDoc
     */
    public function fromForm(mixed $data, ElementInterface $element): mixed
    {
        foreach ($this->transformers as $transformer) {
            $data = $transformer->fromForm($data, $element);
        }

        return $data;
    }
}
