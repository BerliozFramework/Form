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

class FormType extends FormElement
{
    const TYPE = '';
    /** @var bool Is inserted ? */
    private $inserted;

    /**
     * FormType constructor.
     *
     * @param string $name    Name
     * @param array  $options Options
     */
    public function __construct(string $name, array $options = [])
    {
        // Set name
        $this->setName($name);

        // Options
        $this->getOptions()
             ->setOptions(['required'    => true,
                           'disabled'    => false,
                           'readonly'    => false,
                           'trim'        => true,
                           'data'        => null,
                           'empty_data'  => null,
                           'pattern'     => null,
                           'placeholder' => null,
                           'attributes'  => []])
             ->setOptions($options);
    }

    /**
     * Get id.
     *
     * @return string
     */
    public function getId(): string
    {
        if ($this->getOptions()->is_empty('id')) {
            return $this->getFullQualifiedName('_');
        } else {
            return $this->getOptions()->get('id');
        }
    }

    /**
     * Get form name.
     *
     * @return string
     */
    public function getFormName(): string
    {
        $formName = sprintf('[%s]', $this->getName());

        if ($this->hasParent()) {
            $formName = $this->getParent()->getFormName() . $formName;
        }

        return $formName;
    }

    /**
     * Get type.
     *
     * @return string
     * @throws \Berlioz\Core\Exception\BerliozException If not valid type name
     */
    public function getType()
    {
        if (preg_match('/^[a-z]\w+$/', static::TYPE)) {
            return static::TYPE;
        } else {
            throw new BerliozException(sprintf('Invalid type name for FormType class "%s"', get_class($this)));
        }
    }

    /**
     * Get default value.
     *
     * @return mixed
     */
    public function getDefaultValue()
    {
        $defaultValue = null;
        $mainForm = $this->getMainParent();
        $fullQualifiedName = explode('.', $this->getFullQualifiedName());
        array_shift($fullQualifiedName);

        $exists = false;
        // From mapped object
        if (is_null($mainForm->getMapped()) ||
            (empty($defaultValue = b_array_traverse($mainForm->getMapped(), $fullQualifiedName, $exists)) && $exists === false)
        ) {
            $defaultValue = $this->getOptions()->get('data');
        }

        return $defaultValue;
    }

    /**
     * Get value.
     *
     * @return mixed
     * @throws \Berlioz\Core\Exception\BerliozException If invalid "transformer" option
     */
    public function getValue()
    {
        $value = null;
        $mainForm = $this->getMainParent();
        $fullQualifiedName = explode('.', $this->getFullQualifiedName());
        array_shift($fullQualifiedName);

        // From form data
        if (!$mainForm->isSubmitted()) {
            $value = $this->getDefaultValue();
        } else {
            $exists = false;
            if (empty($value = b_array_traverse($mainForm->getData(), $fullQualifiedName, $exists)) && $exists === false) {
                $value = $this->getOptions()->get('data');
            }
        }

        return $value;
    }

    /**
     * Get template data.
     *
     * @param array $options Options
     *
     * @return array
     */
    public function getTemplateData(array $options = []): array
    {
        $fOptions = ['form'        => $this,
                     'type'        => $this->getType(),
                     'id'          => $this->getId(),
                     'name'        => $this->getName(),
                     'full_name'   => $this->getFormName(),
                     'value'       => $this->getValue(),
                     'placeholder' => $this->getOptions()->get('placeholder'),
                     //'data'       => $this->getDataAttribute($this->getOptions()->get('data')),
                     'label'       => $this->getOptions()->get('label'),
                     // Attributes
                     'required'    => $this->getOptions()->get('required') == true,
                     'disabled'    => $this->getOptions()->get('disabled') == true,
                     'readonly'    => $this->getOptions()->get('readonly') == true,
                     'attributes'  => $this->getOptions()->get('attributes') ?? []];

        $fOptions = b_array_merge_recursive($options, $fOptions);

        return $fOptions;
    }

    /**
     * Is inserted ?
     *
     * @return bool
     */
    public function isInserted(): bool
    {
        return $this->inserted;
    }

    /**
     * Set inserted.
     *
     * @param bool $inserted
     */
    public function setInserted(bool $inserted)
    {
        $this->inserted = $inserted;
    }

    public function validation()
    {
        foreach ($this->getValidations() as $validation) {
            $validation->valid($this);
        }
    }
}