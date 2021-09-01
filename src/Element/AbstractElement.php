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

namespace Berlioz\Form\Element;

use Berlioz\Form\Collection;
use Berlioz\Form\Exception\FormException;
use Berlioz\Form\Form;
use Berlioz\Form\Group;
use Berlioz\Form\Transformer\DefaultTransformer;
use Berlioz\Form\Transformer\TransformerInterface;
use Berlioz\Form\Validator\ValidatorHandlerInterface;
use Berlioz\Form\Validator\ValidatorHandlerTrait;
use Exception;

abstract class AbstractElement implements ElementInterface, ValidatorHandlerInterface
{
    use ValidatorHandlerTrait;

    protected TransformerInterface $transformer;
    protected ?ElementInterface $parent = null;

    /**
     * Element constructor.
     *
     * @param array $options Options
     */
    public function __construct(
        protected array $options = [],
    ) {
        $this->addValidator(...($options['validators'] ?? []));
        $this->transformer = $options['transformer'] ?? new DefaultTransformer();
    }

    /////////////////
    /// ID & NAME ///
    /////////////////

    /**
     * Get id.
     *
     * @return string|null
     */
    public function getId(): ?string
    {
        if (null === ($parent = $this->getParent())) {
            return $this->getName();
        }

        if ($parent instanceof Collection) {
            return sprintf('%s_%s', $parent->getId(), $parent->indexOf($this));
        }

        if (null === $this->getName()) {
            return $parent->getId();
        }

        return sprintf('%s_%s', $parent->getId(), $this->getName());
    }

    /**
     * @inheritDoc
     */
    public function getName(): ?string
    {
        return $this->getOption('name');
    }

    /**
     * Get form name.
     *
     * @return string|null
     */
    public function getFormName(): ?string
    {
        // No parent
        if (null === ($parent = $this->getParent())) {
            return $this->getName();
        }

        // Parent is a collection?
        if ($parent instanceof Collection) {
            return sprintf('%s[%s]', $parent->getFormName(), $parent->indexOf($this));
        }

        // No name
        if (null === $this->getName()) {
            return $parent->getFormName();
        }

        return sprintf('%s[%s]', $parent->getFormName(), $this->getName());
    }

    ///////////////
    /// MAPPING ///
    ///////////////

    /**
     * Get mapped.
     *
     * @return mixed
     * @throws FormException
     */
    public function getMapped(): mixed
    {
        // Element has mapped element
        if ($this instanceof Group) {
            if (null !== $this->getMappedObject()) {
                return $this->getMappedObject();
            }
        }

        // No parent, so no mapped element
        if (null === ($parent = $this->getParent())) {
            return null;
        }

        // Get mapped element of parent
        if (null === ($mapped = $parent->getMapped())) {
            return null;
        }

        // If parent is collection, so it's iterable mapped
        if ($parent instanceof Collection) {
            if (!is_iterable($mapped)) {
                return null;
            }

            if (isset($mapped[$parent->indexOf($this)])) {
                return $mapped[$parent->indexOf($this)];
            }

            return null;
        }

        try {
            return b_get_property_value($mapped, $this->getName());
        } catch (Exception $exception) {
            throw new FormException(
                sprintf('Unable to get value of "%s" input', $this->getName()),
                previous: $exception
            );
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
        return
            array_replace_recursive(
                $this->options,
                [
                    'id' => $this->getId(),
                    'name' => $this->getFormName(),
                    'parent' => $this->getParent(),
                    'this' => $this,
                ]
            );
    }

    /**
     * Get option.
     *
     * @param string $name
     * @param mixed $default Default value
     * @param bool $inherit Inherit option? (default: false)
     *
     * @return mixed
     */
    public function getOption(string $name, mixed $default = null, bool $inherit = false): mixed
    {
        if (null !== ($value = b_array_traverse_get($this->options, $name))) {
            return $value;
        }

        if (true === $inherit) {
            return $this->parent?->getOption($name, $default, $inherit) ?? $default;
        }

        return $default;
    }

    /**
     * @inheritDoc
     */
    public function setOption(string $name, mixed $value): void
    {
        b_array_traverse_set($this->options, $name, $value);
    }

    ///////////////
    /// PARENTS ///
    ///////////////

    /**
     * @inheritDoc
     */
    public function getParent(): ?ElementInterface
    {
        return $this->parent;
    }

    /**
     * @inheritDoc
     */
    public function setParent(?ElementInterface $parent): void
    {
        $this->parent = $parent;
    }

    /**
     * Get form.
     *
     * @return Form|null
     */
    public function getForm(): ?Form
    {
        $parent = $this;

        do {
            if (null !== ($parent->getParent())) {
                continue;
            }

            if ($parent instanceof Form) {
                return $parent;
            }
        } while (null !== ($parent = $parent->getParent()));

        return null;
    }

    /////////////////
    /// CALLBACKS ///
    /////////////////

    /**
     * Call callback.
     *
     * @param string $type
     * @param mixed ...$args
     *
     * @return AbstractElement
     * @throws FormException
     */
    protected function callCallback(string $type, ...$args): AbstractElement
    {
        if (is_array($allCallbacks = $this->getOption('callbacks'))) {
            if (isset($allCallbacks[$type]) && !empty($callbacks = $allCallbacks[$type])) {
                if (!is_array($callbacks)) {
                    $callbacks = [$callbacks];
                }

                foreach ($callbacks as $callback) {
                    if (!is_callable($callback)) {
                        throw new FormException(
                            sprintf('Callback "%s" must be a callable or an array of callable', $type)
                        );
                    }

                    call_user_func_array($callback, $args);
                }
            }
        }

        return $this;
    }

    ///////////////////
    /// TRANSFORMER ///
    ///////////////////

    /**
     * Get transformer.
     *
     * @return TransformerInterface
     */
    public function getTransformer(): TransformerInterface
    {
        return $this->transformer;
    }

    /**
     * Set transformer.
     *
     * @param TransformerInterface $transformer
     */
    public function setTransformer(TransformerInterface $transformer): void
    {
        $this->transformer = $transformer;
    }

    /////////////
    /// VALUE ///
    /////////////

    /**
     * @inheritDoc
     */
    public function getFinalValue(): mixed
    {
        return $this->getTransformer()->fromForm($this->getValue(), $this);
    }
}