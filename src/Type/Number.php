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
    const DEFAULT_TRANSFORMER = NumberTransformer::class;

    /**
     * @inheritdoc
     */
    public function getType(): string
    {
        return 'number';
    }
}