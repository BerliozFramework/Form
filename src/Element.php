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

namespace Berlioz\Form;

use Berlioz\Form\Exception\ValidatorException;
use Berlioz\Form\Validator\Constraint\BasicConstraint;
use Berlioz\Form\Validator\ValidatorInterface;

abstract class Element implements ElementInterface
{
    /** @var array Options */
    protected $options;
    /** @var \Berlioz\Form\ElementInterface Parent element */
    protected $parentElement;
    /** @var \Berlioz\Form\Validator\Constraint\ConstraintInterface[] */
    protected $constraints;

    /**
     * Element constructor.
     *
     * @param array $options Options
     */
    public function __construct(array $options = [])
    {
        $this->options = $options;
    }

    /**
     * Get id.
     *
     * @return string
     */
    public function getId(): string
    {
        if (is_null($parent = $this->getParent())) {
            return $this->getName();
        } else {
            if ($parent instanceof Collection) {
                return sprintf('%s_%s', $parent->getId(), $parent->indexOf($this));
            } else {
                return sprintf('%s_%s', $parent->getId(), $this->getName());
            }
        }
    }

    /**
     * Get name.
     *
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->getOption('name');
    }

    /**
     * Get form name.
     *
     * @return string
     */
    public function getFormName(): string
    {
        if (is_null($parent = $this->getParent())) {
            return $this->getName();
        } else {
            if ($parent instanceof Collection) {
                return sprintf('%s[%s]', $parent->getFormName(), $parent->indexOf($this));
            } else {
                return sprintf('%s[%s]', $parent->getFormName(), $this->getName());
            }
        }
    }

    ///////////////
    /// OPTIONS ///
    ///////////////

    /**
     * Get options.
     *
     * @return array
     */
    public function getOptions(): array
    {
        return array_replace_recursive($this->options,
                                       ['id'     => $this->getId(),
                                        'name'   => $this->getFormName(),
                                        'parent' => $this->getParent(),
                                        'form'   => $this]);
    }

    /**
     * Get option.
     *
     * @param string $name
     * @param mixed  $default Default value
     * @param bool   $inherit Inherit option? (default: false)
     *
     * @return mixed|null
     */
    public function getOption(string $name, $default = null, bool $inherit = false)
    {
        if (isset($this->options[$name]) || $inherit === false || is_null($this->getParent())) {
            return $this->options[$name] ?? $default;
        } else {
            return $this->getParent()->getOption($name, $default, $inherit);
        }
    }

    /**
     * Set option.
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return static
     */
    public function setOption(string $name, $value)
    {
        $this->options[$name] = $value;

        return $this;
    }

    ///////////////
    /// PARENTS ///
    ///////////////

    /**
     * Get parent.
     *
     * @return \Berlioz\Form\ElementInterface|null
     */
    public function getParent(): ?ElementInterface
    {
        return $this->parentElement;
    }

    /**
     * Set parent.
     *
     * @param \Berlioz\Form\ElementInterface $parent
     *
     * @return static
     */
    public function setParent(ElementInterface $parent)
    {
        $this->parentElement = $parent;

        return $this;
    }

    /**
     * Get form.
     *
     * @return \Berlioz\Form\Form|null
     */
    public function getForm(): ?Form
    {
        $parent = $this;

        do {
            if (!is_null($parent) && is_null($parent->getParent()) && $parent instanceof Form) {
                return $parent;
            }
        } while (!is_null($parent = $parent->getParent()));

        return null;
    }

    //////////////////
    /// VALIDATION ///
    //////////////////

    /**
     * Is valid?
     *
     * @return bool
     * @throws \Berlioz\Form\Exception\ValidatorException
     */
    public function isValid(): bool
    {
        foreach ($this->getOption('validators', []) as $validator) {
            $constraints = [];

            if (is_callable($validator)) {
                if ($result = call_user_func($validator, $this) !== true) {
                    $constraints = [new BasicConstraint([], (is_string($result) ? $result : null))];
                }
            } else {
                if (is_object($validator) && is_a($validator, ValidatorInterface::class)) {
                    /** @var \Berlioz\Form\Validator\ValidatorInterface $validator */
                    $constraints = $validator->validate($this);
                } else {
                    throw new ValidatorException(sprintf('Validators must be a callback or a valid "%s" class, "%s" given', ValidatorInterface::class, gettype($validator)));
                }
            }

            $this->constraints = array_merge($this->constraints ?? [], $constraints);
        }

        if ($this instanceof TraversableElementInterface) {
            $childrenValid = true;

            /** @var \Berlioz\Form\ElementInterface $element */
            foreach ($this as $element) {
                if (!$element->isValid()) {
                    $childrenValid = false;
                }
            }

            return $childrenValid && empty($this->constraints);
        }

        return empty($this->constraints);
    }

    /**
     * Add validator.
     *
     * @param \Berlioz\Form\Validator\ValidatorInterface $validator
     *
     * @return $this
     */
    public function addValidator(ValidatorInterface $validator)
    {
        if (($key = $this->hasValidator(get_class($validator))) !== false) {
            unset($this->options['validators'][$key]);
        }

        $this->options['validators'][] = $validator;

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
        foreach ($this->getOption('validators', []) as $key => $validator) {
            if (is_a($validator, $validatorClass)) {
                return $key;
            }
        }

        return false;
    }

    /**
     * Get not respected constraints.
     *
     * @return \Berlioz\Form\Validator\Constraint\ConstraintInterface[]
     */
    public function getConstraints(): array
    {
        return $this->constraints ?? [];
    }
}