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

namespace Berlioz\Form\View;

use Berlioz\Form\Element\ElementInterface;
use Berlioz\Form\Exception\FormException;

/**
 * Class BasicView.
 *
 * @package Berlioz\Form\View
 */
class BasicView implements ViewInterface
{
    /** @var \Berlioz\Form\Element\ElementInterface Source element */
    private $src;
    /** @var \Berlioz\Form\View\ViewInterface Parent view */
    private $parentView;
    /** @var string Render template */
    private $render;
    /** @var array Options */
    private $variables = [];
    /** @var bool Inserted? */
    private $inserted = false;

    /**
     * BasicView constructor.
     *
     * @param \Berlioz\Form\Element\ElementInterface $src
     * @param array $variables
     */
    public function __construct(ElementInterface $src, array $variables = [])
    {
        $this->src = $src;
        $this->variables = $variables;
    }

    /**
     * @inheritdoc
     */
    public function getSrcType(): string
    {
        return get_class($this->src);
    }

    /**
     * @inheritdoc
     */
    public function setParentView(TraversableView $parentView)
    {
        $this->parentView = $parentView;

        return $this;
    }

    /////////////////
    /// VARIABLES ///
    /////////////////

    /**
     * __isset() PHP magic method to test if variable exists.
     *
     * @param string $name
     *
     * @return bool
     */
    public function __isset(string $name): bool
    {
        return array_key_exists($name, $this->variables);
    }

    /**
     * Get variable.
     *
     * @param string $name
     *
     * @return mixed
     * @throws \Berlioz\Form\Exception\FormException
     */
    public function __get(string $name)
    {
        if (!$this->__isset($name)) {
            throw new FormException(sprintf('Variable "%s" doest not exists', $name));
        }

        return $this->variables[$name];
    }

    /**
     * @inheritdoc
     */
    public function getVar(string $name, $default = null)
    {
        return $this->variables[$name] ?? $default;
    }

    /**
     * @inheritdoc
     */
    public function getVars(): array
    {
        return $this->variables ?? [];
    }

    /**
     * @inheritdoc
     */
    public function mergeVars(array $variables)
    {
        $this->variables = array_merge($this->variables, $variables);

        return $this;
    }

    //////////////
    /// RENDER ///
    //////////////

    /**
     * @inheritdoc
     */
    public function getRender(): ?string
    {
        if (!empty($this->render)) {
            return $this->render;
        }

        if (!is_null($this->parentView)) {
            return $this->parentView->getRender();
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function setRender(?string $value)
    {
        $this->render = $value;

        return $this;
    }

    /////////////////
    /// INSERTION ///
    /////////////////

    /**
     * Is inserted?
     *
     * @return bool
     */
    public function isInserted(): bool
    {
        return $this->inserted;
    }

    /**
     * Set inserted.
     *
     * @param bool $inserted
     *
     * @return static
     */
    public function setInserted(bool $inserted = true)
    {
        $this->inserted = $inserted;

        return $this;
    }
}