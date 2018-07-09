<?php
/**
 * This file is part of Berlioz framework.
 *
 * @license   https://opensource.org/licenses/MIT MIT License
 * @copyright 2017 Ronan GIRON
 * @author    Ronan GIRON <https://github.com/ElGigi>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code, to the root.
 */

namespace Berlioz\Form\Validator;

use Berlioz\Form\ElementInterface;

interface ValidatorInterface
{
    /**
     * ValidatorInterface constructor.
     *
     * @param string $constraint Constraint class
     */
    public function __construct(string $constraint);

    /**
     * Validate.
     *
     * @param \Berlioz\Form\ElementInterface $value
     *
     * @return \Berlioz\Form\Validator\Constraint\ConstraintInterface[]
     * @throws \Berlioz\Form\Exception\ValidatorException
     */
    public function validate(ElementInterface $value): array;
}