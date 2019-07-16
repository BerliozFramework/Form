<?php

namespace Berlioz\Form\Tests\Fake;

use Berlioz\Form\Form;

class FakeForm extends Form
{
    public function setSubmitted(bool $submitted): FakeForm
    {
        $this->submitted = $submitted;

        return $this;
    }
}