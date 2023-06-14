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

use Berlioz\Form\Element\TraversableElementInterface;
use Berlioz\Form\Exception\ValidatorException;
use Berlioz\Form\Validator\Constraint\BasicConstraint;
use Berlioz\Form\Validator\Constraint\ConstraintInterface;
use Closure;

trait ValidatorHandlerTrait
{
    private array $validators = [];
    private array $invalidated = [];
    private array $constraints = [];

    /**
     * Is valid?
     *
     * @return bool
     * @throws ValidatorException
     */
    public function isValid(): bool
    {
        $this->constraints = [];

        /** @var ValidatorInterface|Closure $validator */
        foreach ($this->validators as $validator) {
            if ($validator instanceof Closure) {
                if (true !== ($result = $validator->call($this, $this))) {
                    array_push($this->constraints, new BasicConstraint(message: (is_string($result) ? $result : null)));
                }
                continue;
            }

            array_push($this->constraints, ...$validator->validate($this));
        }

        if ($this instanceof TraversableElementInterface) {
            $childrenValid = true;

            /** @var ValidatorHandlerInterface $element */
            foreach ($this as $element) {
                if (false === $element->isValid()) {
                    $childrenValid = false;
                }
            }

            return $childrenValid && empty($this->constraints);
        }

        return empty($this->constraints);
    }

    /**
     * @inheritDoc
     */
    public function addValidator(ValidatorInterface|Closure ...$validator): void
    {
        $validator = array_filter(
            $validator,
            fn($validator) => $validator instanceof Closure || false === $this->hasValidator($validator::class)
        );
        array_push($this->validators, ...$validator);
    }

    /**
     * @inheritDoc
     */
    public function hasValidator(ValidatorInterface|string $class): bool
    {
        if ($class instanceof ValidatorInterface) {
            $class = $class::class;
        }

        foreach ($this->validators as $validator) {
            if (is_a($validator, $class, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Reset validators.
     */
    public function resetValidators(): void
    {
        $this->validators = [];
    }

    /**
     * Get not respected constraints.
     *
     * @return ConstraintInterface[]
     */
    public function getConstraints(): array
    {
        return array_merge($this->constraints, $this->invalidated);
    }

    /**
     * Reset constraints.
     */
    public function resetConstraints(): void
    {
        $this->constraints = [];
    }

    /**
     * @inheritDoc
     */
    public function invalid(ConstraintInterface ...$constraint): void
    {
        array_push($this->invalidated, ...$constraint);
    }
}