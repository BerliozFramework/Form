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

namespace Berlioz\Form\Transformer;

use Berlioz\Form\Element\ElementInterface;

class JsonTransformer implements TransformerInterface
{
    public function __construct(
        protected int $encodeFlags = 0,
        protected int $decodeFlags = 0,
        protected int $encodeDepth = 512,
        protected int $decodeDepth = 512,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function toForm(mixed $data, ElementInterface $element): mixed
    {
        if (null === $data) {
            return null;
        }

        if (true === is_array($data)) {
            return $data;
        }

        return json_decode($data, true, $this->decodeDepth, $this->decodeFlags);
    }

    /**
     * @inheritDoc
     */
    public function fromForm(mixed $data, ElementInterface $element): ?string
    {
        if (true === is_array($data)) {
            if (true === $element->getOption('null_if_empty', false, true)) {
                $data = array_filter($data, fn($value) => null !== $value);

                if (empty($data)) {
                    $data = null;
                }
            }
        }

        if (null === $data) {
            return null;
        }

        return json_encode($data, $this->encodeFlags, $this->encodeDepth);
    }
}