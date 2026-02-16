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

use Berlioz\Form\Element\ElementInterface;
use Berlioz\Form\Exception\ValidatorException;
use Berlioz\Form\Validator\Constraint\IntervalConstraint;

class IntervalValidator extends AbstractValidator implements ValidatorInterface
{
    /**
     * IntervalValidator constructor.
     *
     * @param string $constraint Constraint class
     *
     * @throws ValidatorException
     */
    public function __construct(string $constraint = IntervalConstraint::class)
    {
        parent::__construct($constraint);
    }

    /**
     * @inheritDoc
     */
    public function validate(ElementInterface $element): array
    {
        $value = $element->getValue();
        $attributes = $element->getOption('attributes', []);
        $minValue = $attributes['min'] ?? null;
        $maxValue = $attributes['max'] ?? null;

        if ($value == '') {
            return [];
        }

        if ((null !== $minValue && (string)$value < (string)$minValue) ||
            (null !== $maxValue && (string)$value > (string)$maxValue)) {
            return [
                new $this->constraint(
                    [
                        'value' => $value,
                        'max' => $maxValue,
                        'min' => $minValue,
                    ]
                ),
            ];
        }

        return [];
    }
}