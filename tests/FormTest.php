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

use Berlioz\Form\Form;
use Berlioz\Form\Tests\Fake\Entity\FakePerson;
use Berlioz\Form\Type\Checkbox;
use Berlioz\Form\Type\Date;
use Berlioz\Form\Type\Text;
use DateTime;

class FormTest extends AbstractFormTest
{
    public function testNotMapped()
    {
        // Form
        $form = new Form('foo');
        $form
            ->add('text1', Text::class)
            ->add(
                'checkbox1',
                Checkbox::class,
                [
                    'default_value' => 'bar',
                    'checked_value' => 'bar'
                ]
            )
            ->add('checkbox2', Checkbox::class);

        // Form not submitted
        $this->assertFalse($form->isSubmitted());
        $this->assertSame(
            [
                'text1' => null,
                'checkbox1' => null,
                'checkbox2' => null,
            ],
            $form->getValue()
        );

        // Form submission
        {
            $form->handle(
                $this->getServerRequest(
                    [
                        'foo' => [
                            'text1' => 'bar',
                            'checkbox1' => 'bar',
                            'checkbox2' => 'on',
                        ],
                    ]
                )
            );
        }

        // Form submitted
        $this->assertTrue($form->isSubmitted());
        $this->assertSame(
            [
                'text1' => 'bar',
                'checkbox1' => 'bar',
                'checkbox2' => 'on',
            ],
            $form->getValue()
        );
        $this->assertSame(
            [
                'text1' => 'bar',
                'checkbox1' => 'bar',
                'checkbox2' => true,
            ],
            $form->getFinalValue()
        );
    }

    public function testMapped()
    {
        // Mapped
        $entity = new FakePerson();

        // Form
        $form = new Form('person', $entity);
        $form
            ->add('last_name', Text::class)
            ->add('first_name', Text::class)
            ->add('birthday', Date::class);

        // Form not submitted
        $this->assertFalse($form->isSubmitted());
        $this->assertSame(
            [
                'last_name' => null,
                'first_name' => null,
                'birthday' => null,
            ],
            $form->getValue()
        );

        // Form submission
        {
            $form->handle(
                $this->getServerRequest(
                    [
                        'person' => [
                            'last_name' => 'Giron',
                            'first_name' => 'Ronan',
                            'birthday' => '1980-01-01',
                        ],
                    ]
                )
            );
        }

        // Form submitted
        $this->assertTrue($form->isSubmitted());
        $this->assertEquals(
            [
                'last_name' => 'Giron',
                'first_name' => 'Ronan',
                'birthday' => '1980-01-01',
            ],
            $form->getValue()
        );
        $this->assertEquals(
            [
                'last_name' => 'Giron',
                'first_name' => 'Ronan',
                'birthday' => new DateTime('1980-01-01'),
            ],
            $form->getFinalValue()
        );
        $this->assertEquals($entity->getLastName(), 'Giron');
        $this->assertEquals($entity->getFirstName(), 'Ronan');
        $this->assertEquals($entity->getBirthday(), new DateTime('1980-01-01'));
    }
}
