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

namespace Berlioz\Form\Type;

use Berlioz\Form\Element\AbstractElement;
use Berlioz\Form\Group;

abstract class AbstractCompositeType extends Group implements TypeInterface
{
    /////////////
    /// VALUE ///
    /////////////

    /**
     * @inheritDoc
     */
    public function getFinalValue(): array
    {
        return AbstractElement::getFinalValue();
    }
}