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

namespace Berlioz\Form\Transformer;

use Berlioz\Form\Element\ElementInterface;

interface ChoiceTransformerInterface
{
    /**
     * Transform unknown choice values to ChoiceValue objects to add.
     *
     * @param array $choices
     * @param \Berlioz\Form\Element\ElementInterface $element
     *
     * @return \Berlioz\Form\Type\ChoiceValue[]
     */
    public function toForm(array $choices, ElementInterface $element): array;
}