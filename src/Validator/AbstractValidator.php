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

use Berlioz\Form\Exception\ValidatorException;

abstract class AbstractValidator implements ValidatorInterface
{
    /** @var string Constraint class */
    protected $constraint;

    /**
     * AbstractValidator constructor.
     *
     * @param string $constraint Constraint class
     *
     * @throws \Berlioz\Form\Exception\ValidatorException
     */
    public function __construct(string $constraint)
    {
        if (is_a($constraint, ValidatorInterface::class, true)) {
            throw new ValidatorException(sprintf('Constraint must be implements %s class', ValidatorInterface::class));
        }

        $this->constraint = $constraint;
    }
}