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

namespace Berlioz\Form\Collector;

use Berlioz\Form\Collection;
use Berlioz\Form\Element\ElementInterface;
use Berlioz\Form\Exception\CollectorException;
use Berlioz\Form\Type\AbstractType;
use Exception;

/**
 * Class TypeCollector.
 *
 * @package Berlioz\Form\Collector
 */
class TypeCollector extends AbstractCollector
{
    /** @var \Berlioz\Form\Type\AbstractType Type */
    private $type;

    /**
     * TypeCollector constructor.
     *
     * @param \Berlioz\Form\Type\AbstractType $type
     */
    public function __construct(AbstractType $type)
    {
        $this->type = $type;
    }

    /**
     * @inheritdoc
     * @return \Berlioz\Form\Type\AbstractType
     */
    public function getElement(): ElementInterface
    {
        return $this->type;
    }

    /**
     * @inheritdoc
     */
    public function collect($mapped)
    {
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