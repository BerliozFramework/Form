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

use Berlioz\Form\Exception\FormException;
use Berlioz\Form\Form;
use Berlioz\Form\View\ViewInterface;

interface ElementInterface
{
    /**
     * Get id.
     *
     * @return string|null
     */
    public function getId(): ?string;

    /**
     * Get name.
     *
     * @return string|null
     */
    public function getName(): ?string;

    /**
     * Get form name.
     *
     * @return string|null
     */
    public function getFormName(): ?string;

    ///////////////
    /// OPTIONS ///
    ///////////////

    /**
     * Get options.
     *
     * @return array
     */
    public function getOptions(): array;

    /**
     * Get option.
     *
     * @param string $name
     * @param mixed $default Default value
     * @param bool $inherit Inherit option? (default: false)
     *
     * @return mixed|null
     */
    public function getOption(string $name, $default = null, bool $inherit = false);

    /**
     * Set option.
     *
     * @param string $name
     * @param mixed $value
     *
     * @return ElementInterface
     */
    public function setOption(string $name, $value);

    ///////////////
    /// PARENTS ///
    ///////////////

    /**
     * Get parent.
     *
     * @return ElementInterface|null
     */
    public function getParent(): ?ElementInterface;

    /**
     * Set parent.
     *
     * @param ElementInterface $parent
     *
     * @return static
     */
    public function setParent(ElementInterface $parent);

    /**
     * Get form.
     *
     * @return Form|null
     */
    public function getForm(): ?Form;

    /////////////
    /// VALUE ///
    /////////////

    /**
     * Get value.
     * Value for form.
     *
     * Returns null if no value found.
     *
     * @return mixed|null
     */
    public function getValue();

    /**
     * Get final value.
     * Value for user application.
     *
     * Returns null if no value found.
     *
     * @return mixed|null
     */
    public function getFinalValue();

    /**
     * Set value.
     * Value from user application.
     *
     * @param mixed $value
     *
     * @return static
     * @throws FormException If given value is invalid
     */
    public function setValue($value);

    /**
     * Submit value.
     * Value from user submission.
     *
     * @param mixed $value
     *
     * @return static
     * @throws FormException If given value is invalid
     */
    public function submitValue($value);

    /////////////
    /// BUILD ///
    /////////////

    /**
     * Build form.
     *
     * @return void
     */
    public function build();

    /**
     * Build view, returns array variables for templating.
     *
     * @return ViewInterface
     */
    public function buildView(): ViewInterface;
}