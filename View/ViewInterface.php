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

/**
 * Interface ViewInterface.
 */
interface ViewInterface
{
    /**
     * Get mapped object.
     *
     * @return object|null
     */
    public function getMapped(): ?object;

    /**
     * Set parent view.
     *
     * @param TraversableView $parentView
     */
    public function setParentView(TraversableView $parentView): void;

    /**
     * Get variable.
     *
     * @param string $name
     * @param mixed $default Default returned value
     *
     * @return mixed
     */
    public function getVar(string $name, mixed $default = null): mixed;

    /**
     * Get all variables.
     *
     * @return array
     */
    public function getVars(): array;

    /**
     * Get render template.
     *
     * @return string|null
     */
    public function getRender(): ?string;

    /**
     * Set render template.
     *
     * @param string|null $value Template
     */
    public function setRender(?string $value): void;

    /**
     * Merge variables.
     *
     * @param array $variables
     */
    public function mergeVars(array $variables): void;

    /**
     * Is inserted?
     *
     * @return bool
     */
    public function isInserted(): bool;

    /**
     * Set inserted.
     *
     * @param bool $inserted
     */
    public function setInserted(bool $inserted = true): void;
}