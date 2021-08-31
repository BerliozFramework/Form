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

namespace Berlioz\Form\View;

use Berlioz\Form\Element\ElementInterface;
use Berlioz\Form\Exception\FormException;

class BasicView implements ViewInterface
{
    private ?ViewInterface $parentView = null;
    private ?string $render = null;
    private bool $inserted = false;

    /**
     * BasicView constructor.
     *
     * @param ElementInterface $src
     * @param array $variables
     */
    public function __construct(
        private ElementInterface $src,
        private array $variables = [],
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getMapped(): ?object
    {
        if (null === $this->src->getForm()) {
            return null;
        }

        return $this->src->getForm()->getMappedObject();
    }

//    /**
//     * @inheritDoc
//     */
//    public function getSrcType(): string
//    {
//        return $this->src::class;
//    }

    /**
     * @inheritDoc
     */
    public function setParentView(TraversableView $parentView): void
    {
        $this->parentView = $parentView;
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
     * @throws FormException
     */
    public function __get(string $name)
    {
        if (!$this->__isset($name)) {
            throw new FormException(sprintf('Variable "%s" doest not exists', $name));
        }

        return $this->variables[$name];
    }

    /**
     * @inheritDoc
     */
    public function getVar(string $name, mixed $default = null): mixed
    {
        return $this->variables[$name] ?? $default;
    }

    /**
     * @inheritDoc
     */
    public function getVars(): array
    {
        return $this->variables ?? [];
    }

    /**
     * @inheritDoc
     */
    public function mergeVars(array $variables): void
    {
        $this->variables = array_replace_recursive($this->variables, $variables);
    }

    //////////////
    /// RENDER ///
    //////////////

    /**
     * @inheritDoc
     */
    public function getRender(): ?string
    {
        return $this->render ?: $this->parentView?->getRender() ?: null;
    }

    /**
     * @inheritDoc
     */
    public function setRender(?string $value): void
    {
        $this->render = $value;
    }

    /////////////////
    /// INSERTION ///
    /////////////////

    /**
     * @inheritDoc
     */
    public function isInserted(): bool
    {
        return $this->inserted;
    }

    /**
     * @inheritDoc
     */
    public function setInserted(bool $inserted = true): void
    {
        $this->inserted = $inserted;
    }
}