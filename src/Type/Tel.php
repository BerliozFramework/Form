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

/**
 * Class Tel.
 *
 * @package Berlioz\Form\Type
 */
class Tel extends Text
{
    /**
     * @inheritdoc
     */
    public function getType(): string
    {
        return 'tel';
    }
}