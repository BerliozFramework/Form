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

namespace Berlioz\Form\Type;

use Berlioz\Form\Element\AbstractElement;
use Berlioz\Form\Validator\NotEmptyValidator;
use Berlioz\Form\View\BasicView;
use Berlioz\Form\View\ViewInterface;

/**
 * Class AbstractType.
 *
 * @package Berlioz\Form\Type
 */
abstract class AbstractType extends AbstractElement implements SimpleTypeInterface
{
    /** @var bool Submitted? */
    protected $submitted = false;
    /** @var mixed Submitted value */
    protected $submittedValue;
    /** @var mixed Value */
    protected $value;

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

    /////////////
    /// VALUE ///
    /////////////

    /**
     * Is multiple?
     *
     * @return bool
     */
    protected function isMultiple(): bool
    {
        $attributes = $this->getOption('attributes', []);

        return in_array($attributes['multiple'] ?? false, [true, 'multiple']);
    }

    /**
     * @inheritdoc
     */
    public function getValue()
    {
        if ($form = $this->getForm()) {
            if ($form->isSubmitted()) {
                return $this->submittedValue;
            }
        }

        return $this->value;
    }

    /**
     * @inheritdoc
     */
    public function submitValue($value)
    {
        $this->submitted = true;
        $this->submittedValue = $value;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setValue($value)
    {
        $this->value = $this->getTransformer()->toForm($value, $this);

        return $this;
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
        if ($this->hasValidator(NotEmptyValidator::class) === false) {
            $this->addValidator(new NotEmptyValidator());
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
                'value' => $this->getValue(),
                'errors' => $this->getConstraints(),
                'required' => $this->getOption('required', false, true),
                'disabled' => $this->getOption('disabled', false, true),
                'readonly' => $this->getOption('readonly', false, true),
                'attributes' => $this->getOption('attributes', []),
            ]
        );
    }
}