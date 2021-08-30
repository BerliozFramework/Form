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

namespace Berlioz\Form\View;

/**
 * Interface ViewInterface.
 *
 * @package Berlioz\Form\View
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
     * Get source form element class name.
     *
     * @return string
     */
    public function getSrcType(): string;

    /**
     * Set parent view.
     *
     * @param \Berlioz\Form\View\TraversableView $parentView
     *
     * @return static
     */
    public function setParentView(TraversableView $parentView);

    /**
     * Get variable.
     *
     * @param string $name
     * @param mixed $default Default returned value
     *
     * @return mixed
     */
    public function getVar(string $name, $default = null);

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
     *
     * @return static
     */
    public function setRender(?string $value);

    /**
     * Merge variables.
     *
     * @param array $variables
     *
     * @return static
     */
    public function mergeVars(array $variables);

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
     *
     * @return static
     */
    public function setInserted(bool $inserted = true);
}