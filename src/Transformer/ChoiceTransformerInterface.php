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

declare(strict_types=1);

namespace Berlioz\Form\Transformer;

use Berlioz\Form\Element\ElementInterface;
use Berlioz\Form\Type\ChoiceValue;

interface ChoiceTransformerInterface
{
    /**
     * Transform unknown choice values to ChoiceValue objects to add.
     *
     * @param array $choices
     * @param ElementInterface $element
     *
     * @return ChoiceValue[]
     */
    public function toForm(array $choices, ElementInterface $element): array;
}