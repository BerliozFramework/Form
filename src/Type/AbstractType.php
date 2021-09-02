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

namespace Berlioz\Form\Type;

use Berlioz\Form\Element\AbstractElement;
use Berlioz\Form\Validator\FormatValidator;
use Berlioz\Form\Validator\NotEmptyValidator;
use Berlioz\Form\View\BasicView;
use Berlioz\Form\View\ViewInterface;

abstract class AbstractType extends AbstractElement implements SimpleTypeInterface
{
    protected bool $submitted = false;
    protected mixed $submittedValue = null;
    protected mixed $value = null;

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
            'parent' => $this->getParent()?->getName(),
            'options' => $this->options,
            'constraints' => $this->getConstraints(),
        ];
    }

    /////////////
    /// VALUE ///
    /////////////

    /**
     * @inheritDoc
     */
    public function getValue(): mixed
    {
        if (true === $this->getForm()?->isSubmitted()) {
            return $this->submittedValue;
        }

        return $this->value;
    }

    /**
     * @inheritDoc
     */
    public function submitValue(mixed $value): void
    {
        $this->submitted = true;
        $this->submittedValue = $value;
    }

    /**
     * @inheritDoc
     */
    public function setValue(mixed $value): void
    {
        $this->value = $this->getTransformer()->toForm($value, $this);
    }

    ///////////////
    /// OPTIONS ///
    ///////////////

    /**
     * @inheritDoc
     */
    public function isRequired(): bool
    {
        return $this->getOption('attributes.required', parent::isRequired());
    }

    /**
     * @inheritDoc
     */
    public function isDisabled(): bool
    {
        return $this->getOption('attributes.disabled', parent::isDisabled());
    }

    /**
     * @inheritDoc
     */
    public function isReadonly(): bool
    {
        return $this->getOption('attributes.readonly', parent::isReadonly());
    }

    /////////////
    /// BUILD ///
    /////////////

    /**
     * @inheritDoc
     */
    public function build(): void
    {
        // Pattern validator
        if ($pattern = $this->getOption('attributes.pattern')) {
            $this->addValidator(new FormatValidator(sprintf('#^%s$#', str_replace('#', '\\#', $pattern))));
        }

        // Not empty validator
        if ($this->hasValidator(NotEmptyValidator::class) === false) {
            $this->addValidator(new NotEmptyValidator());
        }
    }

    /**
     * @inheritDoc
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
                'required' => $this->isRequired(),
                'disabled' => $this->isDisabled(),
                'readonly' => $this->isReadonly(),
                'attributes' => $this->getOption('attributes', []),
            ]
        );
    }
}