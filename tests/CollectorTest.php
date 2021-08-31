<?php
/*
 * This file is part of Berlioz framework.
 *
 * @license   https://opensource.org/licenses/MIT MIT License
 * @copyright 2021 Ronan GIRON
 * @author    Ronan GIRON <https://github.com/ElGigi>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code, to the root.
 */

namespace Berlioz\Form\Tests;

use ArrayObject;
use Berlioz\Form\Collector\FormCollector;
use Berlioz\Form\Collector\GroupCollector;
use Berlioz\Form\Exception\CollectorException;
use Berlioz\Form\Exception\FormException;
use Berlioz\Form\Form;
use Berlioz\Form\Group;
use Berlioz\Form\Tests\Fake\Entity\FakeAddress;
use Berlioz\Form\Tests\Fake\Entity\FakeJob;
use Berlioz\Form\Tests\Fake\Entity\FakePerson;
use Berlioz\Form\Tests\Fake\FakeForm;
use Berlioz\Form\Type\Text;
use DateTime;

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
                $job = (new FakeJob())
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
        $form = $this->getFormTest($person);

        $collector = new FormCollector($form);
        $collected = $collector->collect($person);

        $this->assertSame($job, $form['job']->getMapped());
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

        $formCollector = new FormCollector($form);
        $formCollected = $formCollector->collect();

        $groupCollector = new GroupCollector($group);
        $groupCollected = $groupCollector->collect();

        $this->assertEquals(
            [
                'input3' =>
                    [
                        'last_name' => 'Bar',
                        'first_name' => 'Foo',
                    ],
            ],
            $formCollected
        );
        $this->assertEquals(
            [
                'last_name' => 'Bar',
                'first_name' => 'Foo',
            ],
            $groupCollected
        );
    }

    /**
     * Test with nope map element. (Have to pass without exception)
     *
     * @throws CollectorException
     * @throws FormException
     */
    public function testWithNoneMapProperty()
    {
        $person = new FakePerson();
        $person
            ->setLastName('Berlioz')
            ->setFirstName('Hector');

        $form = new Form('test', $person);
        $form
            ->add('last_name', Text::class)
            ->add('first_name', Text::class)
            ->add('nick_name', Text::class, [
                'mapped' => false
            ]);

        $formCollector = new FormCollector($form);

        $this->assertEquals(
            [
                'last_name' => 'Berlioz',
                'first_name' => 'Hector',
            ],
            $formCollector->collect()
        );
    }
}
