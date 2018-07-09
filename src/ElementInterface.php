<?php
/**
 * This file is part of Berlioz framework.
 *
 * @license   https://opensource.org/licenses/MIT MIT License
 * @copyright 2017 Ronan GIRON
 * @author    Ronan GIRON <https://github.com/ElGigi>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code, to the root.
 */

namespace Berlioz\Form;

use Berlioz\Form\Validator\ValidatorInterface;
use Berlioz\Form\View\ViewInterface;

interface ElementInterface
{
    /**
     * Get id.
     *
     * @return string
     */
    public function getId(): string;

    /**
     * Get name.
     *
     * @return string|null
     */
    public function getName(): ?string;

    /**
     * Get form name.
     *
     * @return string
     */
    public function getFormName(): string;

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
     * @param mixed  $default Default value
     * @param bool   $inherit Inherit option? (default: false)
     *
     * @return mixed|null
     */
    public function getOption(string $name, $default = null, bool $inherit = false);

    /**
     * Set option.
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return static
     */
    public function setOption(string $name, $value);

    ///////////////
    /// PARENTS ///
    ///////////////

    /**
     * Get parent.
     *
     * @return \Berlioz\Form\ElementInterface|null
     */
    public function getParent(): ?ElementInterface;

    /**
     * Set parent.
     *
     * @param \Berlioz\Form\ElementInterface $parent
     *
     * @return static
     */
    public function setParent(ElementInterface $parent);

    /**
     * Get form.
     *
     * @return \Berlioz\Form\Form|null
     */
    public function getForm(): ?Form;

    /////////////
    /// VALUE ///
    /////////////

    /**
     * Get value.
     *
     * Returns null if no value found.
     *
     * @param bool $raw Raw value? (default: false)
     *
     * @return mixed|null
     */
    public function getValue(bool $raw = false);

    /**
     * Set value.
     *
     * @param mixed $value
     * @param bool  $submitted Submitted value? (default: false)
     *
     * @return static
     */
    public function setValue($value, bool $submitted = false);

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
     * @return \Berlioz\Form\View\ViewInterface
     */
    public function buildView(): ViewInterface;

    //////////////////
    /// VALIDATION ///
    //////////////////

    /**
     * Is valid?
     *
     * @return bool
     */
    public function isValid(): bool;

    /**
     * Add validator.
     *
     * @param \Berlioz\Form\Validator\ValidatorInterface $validator
     *
     * @return $this
     */
    public function addValidator(ValidatorInterface $validator);

    /**
     * Has validator?
     *
     * @param string $validatorClass
     *
     * @return mixed|false
     */
    public function hasValidator(string $validatorClass);

    /**
     * Get not respected constraints.
     *
     * @return \Berlioz\Form\Validator\Constraint\ConstraintInterface[]
     */
    public function getConstraints(): array;
}