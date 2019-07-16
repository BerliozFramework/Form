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

namespace Berlioz\Form\Type;

use Berlioz\Form\Element;
use Berlioz\Form\Transformer;
use Berlioz\Form\Validator\NotEmptyValidator;
use Berlioz\Form\View\BasicView;
use Berlioz\Form\View\ViewInterface;

abstract class AbstractType extends Element
{
    /** @var mixed Default value */
    protected $submittedValue;
    /** @var mixed Value */
    protected $value;

    /**
     * AbstractType constructor.
     *
     * @param array $options Options
     */
    public function __construct(array $options = [])
    {
        parent::__construct(array_replace_recursive(['label' => false], $options));
    }

    /**
     * __clone() magic method.
     */
    public function __clone()
    {
        $this->value = null;
    }

    /**
     * __debugInfo() magic method.
     *
     * @return array
     */
    public function __debugInfo(): array
    {
        return [
            'name' => $this->getName(),
            'value' => $this->getValue(),
            'parent' => $this->getParent() ? $this->getParent()->getName() : null,
            'options' => $this->options,
            'constraints' => $this->getConstraints(),
        ];
    }

    /**
     * Get type.
     *
     * @return string
     */
    abstract public function getType(): string;

    /**
     * @inheritdoc
     */
    public function getOptions(): array
    {
        if (is_null($value = $this->getValue())) {
            $value = $this->getOption('value');
        }

        return array_replace_recursive(
            parent::getOptions(),
            [
                'type' => $this->getType(),
                'value' => $value,
            ]
        );
    }

    /////////////
    /// VALUE ///
    /////////////

    /**
     * @inheritdoc
     */
    public function getValue(bool $raw = false)
    {
        if (!$raw && !is_null($transformer = $this->getTransformer())) {
            return $transformer->fromForm($this->getRawValue());
        }

        return $this->getRawValue();
    }

    /**
     * Get raw value.
     *
     * @return mixed
     */
    protected function getRawValue()
    {
        if ($this->getOption('readonly', false, true)) {
            return $this->value;
        }

        if (!is_null($this->getForm()) && $this->getForm()->isSubmitted()) {
            return $this->submittedValue;
        }

        if (!is_null($this->value)) {
            return $this->value;
        }

        return $this->getOption('value');
    }

    /**
     * @inheritdoc
     */
    public function setValue($value, bool $submitted = false)
    {
        if ($submitted) {
            $this->submittedValue = $value;
        } else {
            if (!is_null($transformer = $this->getTransformer())) {
                $value = $transformer->toForm($value);
            }

            $this->value = $value;
        }

        return $this;
    }

    /**
     * Get transformer.
     *
     * @return \Berlioz\Form\Transformer|null
     */
    protected function getTransformer(): ?Transformer
    {
        if (!is_null($transformer = $this->getOption('transformer'))) {
            if ($transformer instanceof Transformer) {
                return $transformer;
            }
        }

        return null;
    }

    /////////////
    /// BUILD ///
    /////////////

    /**
     * @inheritdoc
     * @throws \Berlioz\Form\Exception\ValidatorException
     */
    public function build()
    {
        // Validator
        if ($this->getOption('required', false, true)) {
            if ($this->hasValidator(NotEmptyValidator::class) === false) {
                $this->options['validators'][] = new NotEmptyValidator;
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function buildView(): ViewInterface
    {
        return new BasicView(
            $this,
            [
                'type' => $this->getType(),
                'id' => $this->getId(),
                'name' => $this->getFormName(),
                'label' => $this->getOption('label', false),
                'label_attributes' => $this->getOption('label_attributes', []),
                'helper' => $this->getOption('helper', false),
                'value' => $this->getRawValue(),
                'errors' => $this->getConstraints(),
                'required' => $this->getOption('required', false, true),
                'disabled' => $this->getOption('disabled', false, true),
                'readonly' => $this->getOption('readonly', false, true),
                'attributes' => $this->getOption('attributes', []),
            ]
        );
    }
}