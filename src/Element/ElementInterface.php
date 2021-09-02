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

use Berlioz\Form\Exception\FormException;
use Berlioz\Form\Exception\ValidatorException;
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
     * @return mixed
     */
    public function getOption(string $name, mixed $default = null, bool $inherit = false): mixed;

    /**
     * Set option.
     *
     * @param string $name
     * @param mixed $value
     */
    public function setOption(string $name, mixed $value): void;

    /**
     * Is required?
     *
     * @return bool
     */
    public function isRequired(): bool;

    /**
     * Is disabled?
     *
     * @return bool
     */
    public function isDisabled(): bool;

    /**
     * Is read only?
     *
     * @return bool
     */
    public function isReadonly(): bool;

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
     * @param ElementInterface|null $parent
     */
    public function setParent(?ElementInterface $parent): void;

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
     * @return mixed
     */
    public function getValue(): mixed;

    /**
     * Get final value.
     * Value for user application.
     *
     * Returns null if no value found.
     *
     * @return mixed
     */
    public function getFinalValue(): mixed;

    /**
     * Set value.
     * Value from user application.
     *
     * @param mixed $value
     *
     * @throws FormException If given value is invalid
     */
    public function setValue(mixed $value): void;

    /**
     * Submit value.
     * Value from user submission.
     *
     * @param mixed $value
     *
     * @throws FormException If given value is invalid
     */
    public function submitValue(mixed $value): void;

    /////////////
    /// BUILD ///
    /////////////

    /**
     * Build form.
     *
     * @return void
     * @throws ValidatorException
     */
    public function build(): void;

    /**
     * Build view, returns array variables for templating.
     *
     * @return ViewInterface
     */
    public function buildView(): ViewInterface;
}