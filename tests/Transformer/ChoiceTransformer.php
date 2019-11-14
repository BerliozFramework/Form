<?php
/**
 * This file is part of Berlioz framework.
 *
 * @license   https://opensource.org/licenses/MIT MIT License
 * @copyright 2017 Ronan GIRON
 * @author    Ronan GIRON <https://github.com/ElGigi>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code, to the root.
 */

namespace Berlioz\Form\Tests\Transformer;

use Berlioz\Form\Element\ElementInterface;
use Berlioz\Form\Transformer\ChoiceTransformerInterface;
use Berlioz\Form\Type\ChoiceValue;

class ChoiceTransformer implements ChoiceTransformerInterface
{
    const ADDITIONAL_CHOICES = ['Test' => 'test'];

    /**
     * @inheritDoc
     */
    public function fromForm(array $choices, ElementInterface $element): array
    {
        $choices = array_intersect($choices, array_values(static::ADDITIONAL_CHOICES));
        $choices =
            array_map(
                function ($choice) {
                    if(($key = array_search($choice, static::ADDITIONAL_CHOICES)) === false) {
                        return null;
                    }

                    $choiceValue = new ChoiceValue();
                    $choiceValue->setLabel($key);
                    $choiceValue->setValue($choice);

                    return $choiceValue;
                },
                $choices
            );

        return array_filter($choices);
    }
}