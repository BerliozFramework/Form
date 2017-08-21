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


use Berlioz\Core\OptionList;

interface FormElementInterface
{
    /**
     * Get name.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Get full qualified name.
     *
     * @param string $separator Separator
     *
     * @return string
     */
    public function getFullQualifiedName(string $separator = '.'): string;

    /**
     * Get form name.
     *
     * @return string
     */
    public function getFormName(): string;

    /**
     * Set name.
     *
     * @param string $name
     */
    public function setName(string $name): void;

    /**
     * Has parent ?
     *
     * @return bool
     */
    public function hasParent(): bool;

    /**
     * Get parent.
     *
     * @return \Berlioz\Form\Form
     */
    public function getParent(): Form;

    /**
     * Get main parent.
     *
     * @return \Berlioz\Form\Form
     */
    public function getMainParent(): Form;

    /**
     * Set parent.
     *
     * @param \Berlioz\Form\Form $parent
     */
    public function setParent(Form $parent): void;

    /**
     * Get options.
     *
     * @return \Berlioz\Core\OptionList
     */
    public function getOptions(): OptionList;

    /**
     * Has template filename ?
     *
     * @return bool
     */
    public function hasTemplateFilename(): bool;

    /**
     * Get template filename.
     *
     * @return string
     */
    public function getTemplateFilename(): string;

    /**
     * Set template filename.
     *
     * @param string $templateFilename
     */
    public function setTemplateFilename(string $templateFilename): void;
}