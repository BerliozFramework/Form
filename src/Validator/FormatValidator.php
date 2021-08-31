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
use Berlioz\Form\Validator\Constraint\FormatConstraint;

class FormatValidator extends AbstractValidator implements ValidatorInterface
{
    /**
     * FormatValidator constructor.
     *
     * @param string $format Format (REGEX)
     * @param string $constraint Constraint class
     *
     * @throws ValidatorException
     */
    public function __construct(private string $format, string $constraint = FormatConstraint::class)
    {
        parent::__construct($constraint);
    }

    /**
     * @inheritDoc
     */
    public function validate(ElementInterface $element): array
    {
        $value = $element->getValue();
        $constraints = [];

        if (empty($value)) {
            return [];
        }

        if (preg_match($this->format, $value) !== 1) {
            $constraints[] = new $this->constraint(['format' => $this->format]);
        }

        return $constraints;
    }
}