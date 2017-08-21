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


class FormValidation
{
    /** @var callable Callable function */
    private $callable;
    /** @var string[] Errors */
    private $errors;

    /**
     * FormValidation constructor.
     *
     * @param callable $validation Function to valid a form element
     */
    public function __construct(callable $validation)
    {
        $this->errors = [];
        $this->callable = $validation;
    }

    /**
     * Get errors.
     *
     * @return string[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Add error.
     *
     * @param string $error
     */
    public function addError(string $error)
    {
        $this->errors[] = $error;
    }

    /**
     * Valid ?
     *
     * @param \Berlioz\Form\FormElement $formElement Form element
     *
     * @return bool
     */
    public function valid(FormElement $formElement): bool
    {
        return call_user_func($this->callable, $this, $formElement) == true;
    }
}