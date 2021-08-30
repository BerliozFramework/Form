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

namespace Berlioz\Form\Element;

use Berlioz\Form\Collection;
use Berlioz\Form\Exception\FormException;
use Berlioz\Form\Form;
use Berlioz\Form\Group;
use Berlioz\Form\Validator\ValidatorHandlerInterface;
use Berlioz\Form\Validator\ValidatorHandlerTrait;
use Berlioz\Form\Transformer\DefaultTransformer;
use Berlioz\Form\Transformer\TransformerInterface;

abstract class AbstractElement implements ElementInterface, ValidatorHandlerInterface
{
    const DEFAULT_TRANSFORMER = DefaultTransformer::class;
    const DEFAULT_VALIDATORS = [];
    use ValidatorHandlerTrait;
    /** @var array Options */
    protected $options;
    /** @var \Berlioz\Form\Transformer\TransformerInterface Transformer */
    protected $transformer;
    /** @var \Berlioz\Form\Element\ElementInterface Parent element */
    protected $parent;

    /**
     * Element constructor.
     *
     * @param array $options Options
     */
    public function __construct(array $options = [])
    {
        $this->options = $options;

        // Transformer?
        if (isset($this->options['transformer'])) {
            $this->setTransformer($this->options['transformer']);
            unset($this->options['transformer']);
        }

        // Validators?
        if (isset($this->options['validators'])) {
            if (!is_array($this->options['validators'])) {
                $this->options['validators'] = [$this->options['validators']];
            }

            foreach ($this->options['validators'] as $validator) {
                $this->addValidator($validator);
            }

            unset($this->options['validators']);
        }
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
        if (is_null($parent = $this->getParent())) {
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
     * @return string|null
     */
    public function getFormName(): ?string
    {
        $parent = $this->getParent();

        if (is_null($parent)) {
            return $this->getName();
        }

        // Parent collection?
        if ($parent instanceof Collection) {
            return sprintf('%s[%s]', $parent->getFormName(), $parent->indexOf($this));
        }

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
     * @return mixed|null
     * @throws \ReflectionException
     */
    public function getMapped()
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

        return b_get_property_value($mapped, $this->getName());
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
     * @param mixed $value
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
     * @return \Berlioz\Form\Element\ElementInterface|null
     */
    public function getParent(): ?ElementInterface
    {
        return $this->parent;
    }

    /**
     * Set parent.
     *
     * @param \Berlioz\Form\Element\ElementInterface|null $parent
     *
     * @return static
     */
    public function setParent(?ElementInterface $parent)
    {
        $this->parent = $parent;

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

    /////////////////
    /// CALLBACKS ///
    /////////////////

    /**
     * Call callback.
     *
     * @param string $type
     * @param mixed ...$args
     *
     * @return \Berlioz\Form\Element\AbstractElement
     * @throws \Berlioz\Form\Exception\FormException
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
        if (is_null($this->transformer)) {
            $defaultTransformer = static::DEFAULT_TRANSFORMER;
            $this->transformer = new $defaultTransformer();
        }

        return $this->transformer;
    }

    /**
     * Set transformer.
     *
     * @param \Berlioz\Form\Transformer\TransformerInterface $transformer
     *
     * @return $this
     */
    public function setTransformer(TransformerInterface $transformer)
    {
        $this->transformer = $transformer;

        return $this;
    }

    /////////////
    /// VALUE ///
    /////////////

    /**
     * @inheritdoc
     */
    public function getFinalValue()
    {
        return $this->getTransformer()->fromForm($this->getValue(), $this);
    }
}