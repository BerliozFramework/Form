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
use Berlioz\Form\Validator\Constraint\NotEmptyConstraint;

class NotEmptyValidator extends AbstractValidator implements ValidatorInterface
{
    /**
     * NotEmptyValidator constructor.
     *
     * @param string $constraint Constraint class
     *
     * @throws \Berlioz\Form\Exception\ValidatorException
     */
    public function __construct(string $constraint = NotEmptyConstraint::class)
    {
        parent::__construct($constraint);
    }

    /**
     * @inheritdoc
     */
    public function validate(ElementInterface $element): array
    {
        $value = $element->getValue(true);

        // String?
        if (is_string($value)) {
            $value = trim((string) $element->getValue(true));

            if (mb_strlen($value) == 0) {
                return [new $this->constraint(['string' => (string) $element->getValue()])];
            }
        }

        // Array
        if (is_array($value)) {
            if (count($value) == 0) {
                return [new $this->constraint()];
            }
        }

        return [];
    }
}