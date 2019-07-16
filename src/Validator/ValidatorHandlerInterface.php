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

namespace Berlioz\Form\Validator;

/**
 * Interface ValidatorHandlerInterface.
 *
 * @package Berlioz\Form\Validator
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
     * @param \Berlioz\Form\Validator\ValidatorInterface $validator
     *
     * @return $this
     */
    public function addValidator(ValidatorInterface $validator);

    /**
     * Has validator?
     *
     * @param string $validatorClass
     *
     * @return mixed|false
     */
    public function hasValidator(string $validatorClass);

    /**
     * Get not respected constraints.
     *
     * @return \Berlioz\Form\Validator\Constraint\ConstraintInterface[]
     */
    public function getConstraints(): array;
}