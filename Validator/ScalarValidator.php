<?php
/*
 * This file is part of Berlioz framework.
 *
 * @license   https://opensource.org/licenses/MIT MIT License
 * @copyright 2026 Ronan GIRON
 * @author    Ronan GIRON <https://github.com/ElGigi>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code, to the root.
 */

declare(strict_types=1);

namespace Berlioz\Form\Validator;

use Berlioz\Form\Element\ElementInterface;
use Berlioz\Form\Exception\ValidatorException;
use Berlioz\Form\Validator\Constraint\ScalarConstraint;

/**
 * Class ScalarValidator.
 *
 * Rejects non-scalar values (arrays, objects) submitted to a simple type.
 */
class ScalarValidator extends AbstractValidator implements ValidatorInterface
{
    /**
     * ScalarValidator constructor.
     *
     * @param string $constraint Constraint class
     *
     * @throws ValidatorException
     */
    public function __construct(string $constraint = ScalarConstraint::class)
    {
        parent::__construct($constraint);
    }

    /**
     * Is accepted value?
     *
     * @param mixed $value
     *
     * @return bool
     */
    public static function isAccepted(mixed $value): bool
    {
        return null === $value || is_scalar($value);
    }

    /**
     * @inheritDoc
     */
    public function validate(ElementInterface $element): array
    {
        if (true === static::isAccepted($element->getValue())) {
            return [];
        }

        return [new $this->constraint(['name' => $element->getName()])];
    }
}
