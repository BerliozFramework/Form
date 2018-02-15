<?php

namespace Berlioz\Form\Type;

use Berlioz\Form\FormType;
use Berlioz\Form\FormValidation;

/**
 * Class Date field type for Berlioz form
 *
 * @package Berlioz\Form\Type
 */
class Date extends FormType
{
    const TYPE = 'date';

    /**
     * Email constructor.
     *
     * @param string $name    Name
     * @param array  $options Options
     */
    public function __construct(string $name, array $options = [])
    {
        $this->getOptions()->setOptions(['format' => 'Y\m\d H:i:s']);

        parent::__construct($name, $options);

        // Validation
        $this->addValidation(new FormValidation([$this, 'validation']));
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
        $fOptions = parent::getTemplateData($options);

        if ($fOptions['value'] instanceof \DateTime) {
            $format = $this->getOptions()->get('format');
            $fOptions['value'] = $fOptions['value']->format($format);
        }

        return $fOptions;
    }

    /**
     * Get value.
     *
     * @return bool|\DateTime
     */
    public function getValue()
    {
        $value = parent::getValue();
        $format = $this->getOptions()->get('format');

        return \DateTime::createFromFormat($format, $value);
    }

    /**
     * Validation.
     *
     * @return bool
     */
    public function validation()
    {
        return $this->getValue() instanceof \DateTime;
    }
}
