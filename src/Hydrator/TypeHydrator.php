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

namespace Berlioz\Form\Hydrator;

use Berlioz\Form\Element\ElementInterface;
use Berlioz\Form\Exception\HydratorException;
use Berlioz\Form\Type\AbstractType;
use Exception;

/**
 * Class TypeHydrator.
 *
 * @package Berlioz\Form\Hydrator
 */
class TypeHydrator extends AbstractHydrator
{
    /** @var \Berlioz\Form\Type\AbstractType Type */
    private $type;

    /**
     * TypeHydrator constructor.
     *
     * @param \Berlioz\Form\Type\AbstractType $type
     */
    public function __construct(AbstractType $type)
    {
        $this->type = $type;
    }

    /**
     * @inheritdoc
     */
    public function getElement(): ElementInterface
    {
        return $this->type;
    }

    /**
     * @inheritdoc
     */
    public function hydrate(&$mapped)
    {
        $propertyName = $this->type->getName();
        $value = $this->type->getFinalValue();

        try {
            if (!b_set_property_value($mapped, $propertyName, $value)) {
                throw new HydratorException(sprintf('Unable to set property "%s" on object "%s"', $propertyName, get_class($mapped)));
            }
        } catch (HydratorException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new HydratorException(sprintf('Unable to find property setter of "%s" on object "%s"', $propertyName, get_class($mapped)), 0, $e);
        }
    }
}