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


use Berlioz\Core\Exception\BerliozException;
use Berlioz\Core\OptionList;

abstract class FormElement implements FormElementInterface
{
    /** @var string Name */
    private $name;
    /** @var \Berlioz\Form\Form Parent */
    private $parent;
    /** @var OptionList Options */
    private $options;
    /** @var string Template of form elements */
    private $templateFilename;
    /** @var \Berlioz\Form\FormValidation[] Validations */
    private $validations;

    /**
     * Get name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get full qualified name.
     *
     * @param string $separator Separator
     *
     * @return string
     */
    public function getFullQualifiedName(string $separator = '.'): string
    {
        $fqName = $this->getName();

        if ($this->hasParent()) {
            $fqName = $this->getParent()->getName() . $separator . $fqName;
        }

        return $fqName;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @throws \Berlioz\Core\Exception\BerliozException If name is not acceptable
     */
    public function setName(string $name): void
    {
        if (preg_match('/^\w[\w\d_\-]*$/i', $name) == 1) {
            $this->name = $name;
        } else {
            throw new BerliozException(sprintf('"%s" is not a valid name', $name));
        }
    }

    /**
     * Has parent ?
     *
     * @return bool
     */
    public function hasParent(): bool
    {
        return !is_null($this->parent);
    }

    /**
     * Get parent.
     *
     * @return \Berlioz\Form\Form
     */
    public function getParent(): Form
    {
        return $this->parent;
    }

    /**
     * Get main parent.
     *
     * @return \Berlioz\Form\Form
     * @throws \Berlioz\Core\Exception\BerliozException If no parent form defined
     */
    public function getMainParent(): Form
    {
        if ($this->hasParent()) {
            return $this->getParent()->getMainParent();
        } else {
            if ($this instanceof Form) {
                return $this;
            } else {
                throw new BerliozException('No parent form defined');
            }
        }
    }

    /**
     * Set parent.
     *
     * @param \Berlioz\Form\Form $parent
     */
    public function setParent(Form $parent): void
    {
        $this->parent = $parent;
    }

    /**
     * Get options.
     *
     * @return \Berlioz\Core\OptionList
     */
    public function getOptions(): OptionList
    {
        if (is_null($this->options)) {
            $this->options = new OptionList();
        }

        return $this->options;
    }

    /**
     * Has template filename ?
     *
     * @return bool
     */
    public function hasTemplateFilename(): bool
    {
        return !empty($this->templateFilename);
    }

    /**
     * Get template filename.
     *
     * @return string
     */
    public function getTemplateFilename(): string
    {
        if ($this->hasTemplateFilename()) {
            return $this->templateFilename;
        } else {
            if ($this->hasParent()) {
                return $this->getParent()->getTemplateFilename();
            } else {
                return '@Berlioz/Form.twig';
            }
        }
    }

    /**
     * Set template filename.
     *
     * @param string $templateFilename
     */
    public function setTemplateFilename(string $templateFilename): void
    {
        $this->templateFilename = $templateFilename;
    }

    /**
     * Get attribute recursively.
     *
     * @param array|null $data
     * @param string     $prefix
     *
     * @return string[]
     */
    protected function getDataAttribute($data = null, string $prefix = ''): array
    {
        $fData = [];

        if (!empty($data) && is_array($data)) {
            foreach ($data as $name => $value) {
                if (is_array($value)) {
                    $fData = array_merge($fData, $this->getDataAttribute($value, $name . '-'));
                } else {
                    $fData[$prefix . $name] = $value;
                }
            }
        }

        return $fData;
    }

    /**
     * Get validations.
     *
     * @return \Berlioz\Form\FormValidation[]
     */
    public function getValidations(): array
    {
        return $this->validations;
    }

    /**
     * Add validation.
     *
     * @param \Berlioz\Form\FormValidation $validation
     */
    public function addValidation(FormValidation $validation)
    {
        $this->validations[] = $validation;
    }
    //abstract public function validation();
}