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

namespace Berlioz\Form\Validator;

use Berlioz\Form\Validator\Constraint\ConstraintInterface;

/**
 * Interface ValidatorHandlerInterface.
 */
interface ValidatorHandlerInterface
{
    /**
     * Is valid?
     *
     * @return bool
     */
    public function isValid(): bool;

    /**
     * Add validator.
     *
     * @param ValidatorInterface ...$validator
     */
    public function addValidator(ValidatorInterface ...$validator): void;

    /**
     * Has validator?
     *
     * @param ValidatorInterface|string $class
     *
     * @return bool
     */
    public function hasValidator(ValidatorInterface|string $class): bool;

    /**
     * Get not respected constraints.
     *
     * @return ConstraintInterface[]
     */
    public function getConstraints(): array;

    /**
     * Invalid.
     *
     * @param ConstraintInterface ...$constraint
     */
    public function invalid(ConstraintInterface ...$constraint): void;
}