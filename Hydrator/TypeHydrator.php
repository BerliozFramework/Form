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

namespace Berlioz\Form\Hydrator;

use Berlioz\Form\Exception\HydratorException;
use Berlioz\Form\Type\TypeInterface;
use Exception;

class TypeHydrator extends AbstractHydrator
{
    /**
     * TypeHydrator constructor.
     *
     * @param TypeInterface $type
     */
    public function __construct(private readonly TypeInterface $type)
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
    public function hydrate(mixed &$mapped = null): void
    {
        if (null === $mapped) {
            return;
        }

        if ($this->type->getOption('disabled', false, true)) {
            return;
        }

        if ($this->type->getOption('readonly', false, true)) {
            return;
        }

        if (false === $this->type->isSubmitted()) {
            return;
        }

        // Not mapped
        if (null === ($mapping = $this->type->getMapping())) {
            return;
        }

        $value = $this->type->getFinalValue();

        try {
            if (!$mapping->set($mapped, $value)) {
                throw new HydratorException(
                    sprintf('Unable to set mapped value of "%s" on object "%s"', $this->type->getName(), $mapped::class)
                );
            }
        } catch (HydratorException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new HydratorException(
                sprintf('Unable to set mapped value of "%s" on object "%s"', $this->type->getName(), $mapped::class),
                0,
                $e
            );
        }
    }
}