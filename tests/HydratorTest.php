<?php

namespace Berlioz\Form\Tests;

use ArrayObject;
use Berlioz\Form\Collection;
use Berlioz\Form\Form;
use Berlioz\Form\Group;
use Berlioz\Form\Hydrator\FormHydrator;
use Berlioz\Form\Tests\Fake\Entity\FakeAddress;
use Berlioz\Form\Tests\Fake\Entity\FakeJob;
use Berlioz\Form\Tests\Fake\Entity\FakePerson;
use Berlioz\Form\Tests\Fake\FakeForm;
use Berlioz\Form\Transformer\DateTimeTransformer;
use Berlioz\Form\Type\Date;
use Berlioz\Form\Type\Text;
use DateTime;
use PHPUnit\Framework\TestCase;

class HydratorTest extends AbstractFormTest
{
    public function testHydrate()
    {
        $person = new FakePerson();
        $form = $this->getForm($person);
        $form->setSubmitted(true);
        $form->submitValue(
            [
                'last_name' => 'Giron',
                'first_name' => 'Ronan',
                'sex' => 'male',
                'birthday' => '1980-01-01',
                'addresses' => [
                    [
                        'address' => '2 avenue Paris',
                        'address_next' => 'BP 12345',
                        'zip_code' => '75001',
                        'city' => 'Paris',
                        'country' => 'FR',
                    ],
                ],
                'hobbies' => [
                    'pony',
                    'swimming pool',
                ],
                'job' => [
                    'title' => 'Developer',
                    'company' => 'Berlioz',
                    'address' => [
                        'address' => '1 avenue Paris',
                        'address_next' => 'BP 12345',
                        'zip_code' => '75000',
                        'city' => 'Paris',
                        'country' => 'FR',
                    ],
                ],
            ]
        );

        $hydrator = new FormHydrator($form);
        $hydrator->hydrate($person);

        $this->assertEquals('Giron', $person->getLastName());
        $this->assertEquals('Ronan', $person->getFirstName());
        $this->assertEquals(new DateTime('1980-01-01'), $person->getBirthday());
        $this->assertInstanceOf(ArrayObject::class, $person->getHobbies());
        $this->assertInstanceOf(FakeJob::class, $person->getJob());
        $this->assertEquals('Developer', $person->getJob()->getTitle());
        $this->assertEquals('Berlioz', $person->getJob()->getCompany());
        $this->assertInstanceOf(FakeAddress::class, $person->getJob()->getAddress());
        $this->assertEquals('1 avenue Paris', $person->getJob()->getAddress()->getAddress());
        $this->assertEquals('BP 12345', $person->getJob()->getAddress()->getAddressNext());
        $this->assertEquals('75000', $person->getJob()->getAddress()->getZipCode());
        $this->assertEquals('Paris', $person->getJob()->getAddress()->getCity());
        $this->assertEquals('FR', $person->getJob()->getAddress()->getCountry());
    }
}
