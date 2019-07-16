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

namespace Berlioz\Form\Type;

use Berlioz\Form\Transformer\NumberTransformer;

/**
 * Class Number.
 *
 * @package Berlioz\Form\Type
 */
class Number extends AbstractType
{
    /**
     * Number constructor.
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->setTransformer(new NumberTransformer());
        parent::__construct($options);
    }

    /**
     * @inheritdoc
     */
    public function getType(): string
    {
        return 'number';
    }

    /**
     * @inheritdoc
     */
    public function getValue()
    {
        $value = parent::getValue();

        if (is_null($value) || $value == '') {
            return null;
        }

        return floatval($value);
    }
}