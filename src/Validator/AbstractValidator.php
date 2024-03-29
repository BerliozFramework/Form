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

use Berlioz\Form\Exception\ValidatorException;
use Berlioz\Form\Validator\Constraint\ConstraintInterface;

abstract class AbstractValidator implements ValidatorInterface
{
    /**
     * AbstractValidator constructor.
     *
     * @param string $constraint Constraint class
     *
     * @throws ValidatorException
     */
    public function __construct(protected string $constraint)
    {
        if (!is_a($constraint, ConstraintInterface::class, true)) {
            throw new ValidatorException(sprintf('Constraint must be implements %s class', ConstraintInterface::class));
        }
    }
}