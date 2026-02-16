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

class CheckboxTransformer implements TransformerInterface
{
    /**
     * @inheritDoc
     */
    public function toForm(mixed $data, ElementInterface $element): ?string
    {
        $element->setOption(
            'attributes.checked',
            $data == $element->getOption('checked_value', true)
        );

        return $element->getOption('default_value', 'on');
    }

    /**
     * @inheritDoc
     */
    public function fromForm(mixed $data, ElementInterface $element): mixed
    {
        $defaultValue = $element->getOption('default_value', 'on');

        if ($element->isSubmitted() && $data == $defaultValue) {
            return $element->getOption('checked_value', true);
        }

        return $element->getOption('unchecked_value', false);
    }
}