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


interface FormTraversableInterface extends FormElementInterface, \IteratorAggregate
{
    /**
     * Check if a form element exists.
     *
     * @param string $name Name of form element
     *
     * @return bool
     */
    public function __isset(string $name): bool;

    /**
     * Get a form element.
     *
     * @param string $name Name of form element
     *
     * @return \Berlioz\Form\FormElement
     */
    public function __get(string $name): FormElement;
}