<?php

namespace Berlioz\Form\Type;


use Berlioz\Form\FormType;
use Berlioz\Form\FormValidation;

class Date extends FormType
{
    const TYPE = 'date';
    /** @var string Field format */
    private $format;

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
     * Get value.
     *
     * @return int
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
