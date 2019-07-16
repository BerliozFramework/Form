<?php
/**
 * This file is part of Berlioz framework.
 *
 * @license   https://opensource.org/licenses/MIT MIT License
 * @copyright 2019 Ronan GIRON
 * @author    Ronan GIRON <https://github.com/ElGigi>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code, to the root.
 */

namespace Berlioz\Form\Transformer;

use Berlioz\Form\Element\ElementInterface;

class CheckboxTransformer implements TransformerInterface
{
    /**
     * @inheritdoc
     */
    public function toForm($data, ElementInterface $element)
    {
        if ($element->getOption('default_value', 'on') == 'on') {
            return $data == 'on';
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function fromForm($data, ElementInterface $element)
    {
        $defaultValue = $element->getOption('default_value', 'on');

        if ($defaultValue == 'on' || is_bool($defaultValue)) {
            if ($data == 'on' || $data == true) {
                return true;
            }

            return false;
        }

        return $data;
    }
}