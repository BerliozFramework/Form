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

namespace Berlioz\Form\Collector;

use Berlioz\Form\Exception\CollectorException;
use Berlioz\Form\Type\TypeInterface;
use Exception;

class TypeCollector extends AbstractCollector
{
    /**
     * TypeCollector constructor.
     *
     * @param TypeInterface $type
     */
    public function __construct(private TypeInterface $type)
    {
    }

    /**
     * @inheritDoc
     */
    public function getElement(): TypeInterface
    {
        return $this->type;
    }

    /**
     * @inheritDoc
     */
    public function collect(mixed $mapped = null): mixed
    {
        if (null === $mapped) {
            return null;
        }

        $propertyName = $this->type->getOption('mapped');
        if (!is_string($propertyName)) {
            $propertyName = $this->type->getName();
        }

        try {
            $exists = false;
            $value = b_get_property_value($mapped, $propertyName, $exists);

            if (!$exists) {
                throw new CollectorException(
                    sprintf(
                        'Unable to find getter method of "%s" property in mapped object "%s"',
                        $propertyName,
                        get_class($mapped)
                    )
                );
            }

            return $value;
        } catch (CollectorException $exception) {
            throw $exception;
        } catch (Exception $exception) {
            throw new CollectorException(
                          sprintf(
                              'Unable to find property getter of "%s" on object "%s"',
                              $propertyName,
                              get_class($mapped)
                          ),
                previous: $exception
            );
        }
    }
}