<?php

namespace Berlioz\Form\Tests;

use ArrayObject;
use Berlioz\Form\Collection;
use Berlioz\Form\Collector\FormCollector;
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

class CollectorTest extends AbstractFormTest
{
    public function testCollect()
    {
        $person = new FakePerson();
        $person
            ->setLastName('Giron')
            ->setFirstName('Ronan')
            ->setSex('male')
            ->setBirthday(new DateTime('1980-01-01'))
            ->setAddresses(
                [
                    (new FakeAddress())
                        ->setAddress('2 avenue Paris')
                        ->setAddressNext('BP 12345')
                        ->setZipCode('75001')
                        ->setCity('Paris')
                        ->setCountry('FR'),
                    (new FakeAddress())
                        ->setAddress('3 avenue Paris')
                        ->setAddressNext('BP 12345')
                        ->setZipCode('75002')
                        ->setCity('Paris')
                        ->setCountry('FR'),
                ]
            )
            ->setHobbies(new ArrayObject(['pony', 'swimming pool']))
            ->setJob(
                (new FakeJob())
                    ->setTitle('Developer')
                    ->setCompany('Berlioz')
                    ->setAddress(
                        (new FakeAddress())
                            ->setAddress('1 avenue Paris')
                            ->setAddressNext('BP 12345')
                            ->setZipCode('75000')
                            ->setCity('Paris')
                            ->setCountry('FR')
                    )
            );
        $form = $this->getForm($person);

        $collector = new FormCollector($form);
        $collected = $collector->collect($person);

        $this->assertEquals(
            [
                'last_name' => 'Giron',
                'first_name' => 'Ronan',
                'sex' => 'male',
                'birthday' => new DateTime('1980-01-01'),
                'addresses' => [
                    [
                        'address' => '2 avenue Paris',
                        'address_next' => 'BP 12345',
                        'zip_code' => '75001',
                        'city' => 'Paris',
                        'country' => 'FR',
                    ],
                    [
                        'address' => '3 avenue Paris',
                        'address_next' => 'BP 12345',
                        'zip_code' => '75002',
                        'city' => 'Paris',
                        'country' => 'FR',
                    ],
                ],
                'hobbies' => ['pony', 'swimming pool'],
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
                    'entry_date' => null,
                ],
            ],
            $collected
        );
    }
}
