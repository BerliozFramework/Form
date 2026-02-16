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

use Berlioz\Form\Element\ElementInterface;
use Berlioz\Form\Group;

class CompositeType extends Group implements TypeInterface
{
    public function __construct(array $options = [], ElementInterface ...$element)
    {
        parent::__construct($options);
        $this->addElement(...$element);
    }

    /////////////
    /// VALUE ///
    /////////////

    /**
     * @inheritDoc
     */
    public function setValue(mixed $value): void
    {
        $value = $this->getTransformer()->toForm($value, $this);
        parent::setValue($value);
    }

    /**
     * @inheritDoc
     */
    public function getFinalValue(): mixed
    {
        $value = parent::getFinalValue();

        return $this->getTransformer()->fromForm($value, $this);
    }
}