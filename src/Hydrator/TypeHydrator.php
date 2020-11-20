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
use Berlioz\Form\Type\TypeInterface;
use Exception;

/**
 * Class TypeHydrator.
 *
 * @package Berlioz\Form\Hydrator
 */
class TypeHydrator extends AbstractHydrator
{
    /** @var \Berlioz\Form\Type\TypeInterface Type */
    private $type;

    /**
     * TypeHydrator constructor.
     *
     * @param \Berlioz\Form\Type\TypeInterface $type
     */
    public function __construct(TypeInterface $type)
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
    public function hydrate(&$mapped = null)
    {
        if (null === $mapped) {
            return;
        }

        if (!$this->type->getOption('mapped', true, true)) {
            return;
        }

        if ($this->type->getOption('disabled', false, true)) {
            return;
        }

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