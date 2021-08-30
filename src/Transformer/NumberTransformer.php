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

declare(strict_types=1);

namespace Berlioz\Form\Transformer;

use Berlioz\Form\Element\ElementInterface;

class NumberTransformer implements TransformerInterface
{
    /**
     * @inheritdoc
     */
    public function toForm($data, ElementInterface $element)
    {
        if (is_null($data) || $data === '') {
            return null;
        }

        return floatval($data);
    }

    /**
     * @inheritdoc
     */
    public function fromForm($data, ElementInterface $element)
    {
        return $this->toForm($data, $element);
    }
}