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

use Berlioz\Form\Element\ElementInterface;
use Berlioz\Form\Validator\Constraint\LengthConstraint;

/**
 * Class LengthValidator.
 *
 * @package Berlioz\Form\Validator
 */
class LengthValidator extends AbstractValidator implements ValidatorInterface
{
    /**
     * LengthValidator constructor.
     *
     * @param string $constraint Constraint class
     *
     * @throws \Berlioz\Form\Exception\ValidatorException
     */
    public function __construct(string $constraint = LengthConstraint::class)
    {
        parent::__construct($constraint);
    }

    /**
     * @inheritdoc
     */
    public function validate(ElementInterface $element): array
    {
        $value = trim((string)$element->getValue());
        $valueLength = mb_strlen($value);
        $attributes = $element->getOption('attributes', []);
        $minLength = $attributes['minlength'] ?? 0;
        $maxLength = $attributes['maxlength'] ?? null;

        if (is_null($value) || $value == '') {
            return [];
        }

        if (($valueLength < $minLength) || (!is_null($maxLength) && $valueLength > $maxLength)) {
            return [
                new $this->constraint(
                    [
                        'maxlength' => $maxLength,
                        'minlength' => $minLength,
                    ]
                ),
            ];
        }

        return [];
    }
}