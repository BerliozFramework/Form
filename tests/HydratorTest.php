<?php

namespace Berlioz\Form\Tests;

use ArrayObject;
use Berlioz\Form\Collection;
use Berlioz\Form\Group;
use Berlioz\Form\Hydrator\FormHydrator;
use Berlioz\Form\Tests\Fake\Entity\FakeAddress;
use Berlioz\Form\Tests\Fake\Entity\FakeJob;
use Berlioz\Form\Tests\Fake\Entity\FakePerson;
use Berlioz\Form\Tests\Fake\FakeForm;
use Berlioz\Form\Type\Text;
use DateTime;

class HydratorTest extends AbstractFormTest
{
    public function testHydrate()
    {
        $person = new FakePerson();
        $form = $this->getFormTest($person);
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

    public function testHydrateCollectionWithAbstractType()
    {
        $person = new FakePerson();
        $person->setHobbies(new \ArrayObject(['pony', 'swimming pool', 'tennis', 'basket']));

        $form = new FakeForm('person', $person);
        $form
            ->add(
                'hobbies',
                new Collection(
                    [
                        'data_type' => ArrayObject::class,
                        'prototype' => new Text(),
                    ]
                )
            );

        $form->setSubmitted(true);
        $form->submitValue(
            [
                'hobbies' => [
                    0 => 'pony',
                    2 => 'tennis',
                    4 => 'football',
                ],
            ]
        );

        $hydrator = new FormHydrator($form);
        $hydrator->hydrate($person);

        $this->assertInstanceOf(ArrayObject::class, $person->getHobbies());
        $this->assertEquals(['pony', 'tennis', 'football'], array_values($person->getHobbies()->getArrayCopy()));
    }

    public function testHydrateCollectionWithGroup()
    {
        $person = new FakePerson();
        $person
            ->setAddresses(
                [
                    $address1 = (new FakeAddress())
                        ->setAddress('2 avenue Paris')
                        ->setAddressNext('BP 12345')
                        ->setZipCode('75001')
                        ->setCity('Paris')
                        ->setCountry('FR'),
                    $address2 = (new FakeAddress())
                        ->setAddress('3 avenue Paris')
                        ->setAddressNext('BP 12345')
                        ->setZipCode('75002')
                        ->setCity('Paris')
                        ->setCountry('FR'),
                    $address3 = (new FakeAddress())
                        ->setAddress('4 avenue Paris')
                        ->setAddressNext('BP 12345')
                        ->setZipCode('75002')
                        ->setCity('Paris')
                        ->setCountry('FR'),
                ]
            );

        // Form
        $address = new Group(['data_type' => FakeAddress::class]);
        $address
            ->add('address', Text::class)
            ->add('address_next', Text::class)
            ->add('zip_code', Text::class)
            ->add('city', Text::class)
            ->add('country', Text::class);

        $form = new FakeForm('person', $person);
        $form
            ->add(
                'addresses',
                new Collection(
                    [
                        'data_type' => ArrayObject::class,
                        'prototype' => clone $address,
                    ]
                )
            );

        $form->setSubmitted(true);
        $form->submitValue(
            [
                'addresses' => [
                    0 => [
                        'address' => '2 avenue Paris',
                        'address_next' => 'BP 12345',
                        'zip_code' => '75001',
                        'city' => 'Paris',
                        'country' => 'FR',
                    ],
                    2 => [
                        'address' => '4 avenue Paris',
                        'address_next' => 'BP 12345',
                        'zip_code' => '75002',
                        'city' => 'Paris',
                        'country' => 'FR',
                    ],
                ],
            ]
        );

        $hydrator = new FormHydrator($form);
        $hydrator->hydrate($person);

        $this->assertEquals([0 => $address1, 2 => $address3], $person->getAddresses());
    }

    public function testHydrateCollectionWithGroupEmpty()
    {
        $person = new FakePerson();
        $person
            ->setAddresses(
                [
                    (new FakeAddress())
                        ->setAddress('2 avenue Paris')
                        ->setAddressNext('BP 12345')
                        ->setZipCode('75001')
                        ->setCity('Paris')
                        ->setCountry('FR'),
                ]
            );

        // Form
        $address = new Group(['data_type' => FakeAddress::class]);
        $address
            ->add('address', Text::class)
            ->add('address_next', Text::class)
            ->add('zip_code', Text::class)
            ->add('city', Text::class)
            ->add('country', Text::class);

        $form = new FakeForm('person', $person);
        $form
            ->add(
                'addresses',
                new Collection(
                    [
                        'data_type' => ArrayObject::class,
                        'prototype' => clone $address,
                    ]
                )
            );

        $form->setSubmitted(true);
        $form->submitValue([]);

        $hydrator = new FormHydrator($form);
        $hydrator->hydrate($person);

        $this->assertEquals([], $person->getAddresses());
    }

    public function testPartialMapped()
    {
        $person = new FakePerson();
        $person
            ->setLastName('Bar')
            ->setFirstName('Foo');

        $group = new Group();
        $group->mapObject($person);
        $group
            ->add('last_name', Text::class)
            ->add('first_name', Text::class);

        $form = new FakeForm('test');
        $form
            ->add('input1', Text::class)
            ->add('input2', Text::class)
            ->add('input3', $group);

        $form->setSubmitted(true);
        $form->submitValue(
            [
                'input1' => 'Foo',
                'input2' => 'Bar',
                'input3' => ['last_name' => 'Baz', 'first_name' => 'Qux']
            ]
        );

        $hydrator = new FormHydrator($form);
        $hydrator->hydrate();

        $this->assertEquals('Baz', $person->getLastName());
        $this->assertEquals('Qux', $person->getFirstName());
        $this->assertSame($person, $group->getMapped());
    }
}
