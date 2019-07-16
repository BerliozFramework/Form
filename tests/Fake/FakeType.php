<?php

namespace Berlioz\Form\Tests\Fake;

use Berlioz\Form\Type\AbstractType;

class FakeType extends AbstractType
{
    /**
     * Get type.
     *
     * @return string
     */
    public function getType(): string
    {
        return 'fake';
    }
}