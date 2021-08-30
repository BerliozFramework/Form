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

namespace Berlioz\Form\Collector;

use Berlioz\Form\Element\ElementInterface;
use Berlioz\Form\Exception\CollectorException;
use Berlioz\Form\Type\TypeInterface;
use Exception;

/**
 * Class TypeCollector.
 *
 * @package Berlioz\Form\Collector
 */
class TypeCollector extends AbstractCollector
{
    /** @var \Berlioz\Form\Type\TypeInterface Type */
    private $type;

    /**
     * TypeCollector constructor.
     *
     * @param \Berlioz\Form\Type\TypeInterface $type
     */
    public function __construct(TypeInterface $type)
    {
        $this->type = $type;
    }

    /**
     * @inheritdoc
     * @return \Berlioz\Form\Type\TypeInterface
     */
    public function getElement(): ElementInterface
    {
        return $this->type;
    }

    /**
     * @inheritdoc
     */
    public function collect($mapped = null)
    {
        if (null === $mapped) {
            return null;
        }

        $propertyName = $this->type->getName();

        try {
            $exists = false;
            $value = b_get_property_value($mapped, $propertyName, $exists);

            if (!$exists) {
                throw new CollectorException(sprintf('Unable to find getter method of "%s" property in mapped object "%s"', $propertyName, get_class($mapped)));
            }

            return $value;
        } catch (CollectorException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new CollectorException(sprintf('Unable to find property getter of "%s" on object "%s"', $propertyName, get_class($mapped)), 0, $e);
        }
    }
}