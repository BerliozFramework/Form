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

declare(strict_types=1);

namespace Berlioz\Form\Validator;

use Berlioz\Form\Element\ElementInterface;
use Berlioz\Form\Element\TraversableElementInterface;
use Berlioz\Form\Exception\ValidatorException;
use Berlioz\Form\Validator\Constraint\BasicConstraint;
use Berlioz\Form\Validator\Constraint\ConstraintInterface;

/**
 * Trait ValidatorHandlerTrait.
 *
 * @package Berlioz\Form\Validator
 */
trait ValidatorHandlerTrait
{
    /** @var \Berlioz\Form\Validator\ValidatorInterface[] Validators */
    protected $validators = [];
    /** @var \Berlioz\Form\Validator\Constraint\ConstraintInterface[] Constraints */
    protected $invalidated = [];
    /** @var \Berlioz\Form\Validator\Constraint\ConstraintInterface[] Constraints */
    protected $constraints = [];

    /**
     * Is valid?
     *
     * @return bool
     * @throws \Berlioz\Form\Exception\ValidatorException
     */
    public function isValid(): bool
    {
        if (!$this instanceof ElementInterface) {
            throw new ValidatorException(sprintf('Trait must be used with "%s" objects', ElementInterface::class));
        }

        $this->constraints = [];

        foreach ($this->validators as $validator) {
            $constraints = [];

            if (is_callable($validator)) {
                if ($result = call_user_func($validator, $this) !== true) {
                    $constraints = [new BasicConstraint([], (is_string($result) ? $result : null))];
                }
            } else {
                if (!(is_object($validator) && is_a($validator, ValidatorInterface::class))) {
                    throw new ValidatorException(sprintf('Validators must be a callback or a valid "%s" class, "%s" given', ValidatorInterface::class, gettype($validator)));
                }

                /** @var \Berlioz\Form\Validator\ValidatorInterface $validator */
                /** @var \Berlioz\Form\Element\ElementInterface $this */
                $constraints = $validator->validate($this);
            }

            $this->constraints = array_merge($this->constraints, $constraints);
        }

        if ($this instanceof TraversableElementInterface) {
            $childrenValid = true;

            /** @var \Berlioz\Form\Validator\ValidatorHandlerInterface $element */
            foreach ($this as $element) {
                if (!$element->isValid()) {
                    $childrenValid = false;
                }
            }

            return $childrenValid && empty($this->getConstraints());
        }

        return empty($this->getConstraints());
    }

    /**
     * Add validator.
     *
     * @param \Berlioz\Form\Validator\ValidatorInterface $validator
     *
     * @return static
     */
    public function addValidator(ValidatorInterface $validator)
    {
        if (($key = $this->hasValidator(get_class($validator))) !== false) {
            unset($this->validators[$key]);
        }

        $this->validators[] = $validator;

        return $this;
    }

    /**
     * Has validator?
     *
     * @param string $validatorClass
     *
     * @return mixed|false
     */
    public function hasValidator(string $validatorClass)
    {
        foreach ($this->validators as $key => $validator) {
            if (is_a($validator, $validatorClass)) {
                return $key;
            }
        }

        return false;
    }

    /**
     * Reset validators.
     *
     * @return static
     */
    public function resetValidators()
    {
        $this->validators = [];

        return $this;
    }

    /**
     * Get not respected constraints.
     *
     * @return \Berlioz\Form\Validator\Constraint\ConstraintInterface[]
     */
    public function getConstraints(): array
    {
        return array_merge($this->constraints, $this->invalidated);
    }

    /**
     * Reset constraints.
     *
     * @return static
     */
    public function resetConstraints()
    {
        $this->constraints = [];

        return $this;
    }

    /**
     * Invalid.
     *
     * @param \Berlioz\Form\Validator\Constraint\ConstraintInterface $constraint
     *
     * @return static
     */
    public function invalid(ConstraintInterface $constraint)
    {
        $this->invalidated[] = $constraint;

        return $this;
    }
}