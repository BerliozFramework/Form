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
use Berlioz\Form\Tests\Fake\FakeForm;
use Berlioz\Form\Tests\Transformer\ChoiceTransformer;
use Berlioz\Form\Type\Choice;

class ChoiceTransformerTest extends AbstractFormTest
{
    protected function getForm(): Form
    {
        $form = new FakeForm('test');
        $form->add(
            'my_choice',
            Choice::class,
            [
                'multiple' => true,
                'choices' => [
                    'Foo' => 'foo',
                    'Bar' => 'bar',
                ],
                'choice_transformer' => new ChoiceTransformer(),
            ]
        );

        return $form;
    }

    public function testChoiceWithTransformer()
    {
        $form = $this->getForm();
        $form->handle(
            $this->getServerRequest(
                [
                    'test' => [
                        'my_choice' => [
                            'foo',
                        ],
                    ],
                ]
            )
        );

        $this->assertEquals(['my_choice' => ['foo']], $form->getValue());
    }

    public function testChoiceWithTransformerAndAdditionalData()
    {
        $form = $this->getForm();
        $form->handle(
            $this->getServerRequest(
                [
                    'test' => [
                        'my_choice' => [
                            'foo',
                            'test',
                            'test2',
                        ],
                    ],
                ]
            )
        );

        $this->assertEquals(['my_choice' => ['foo', 'test']], $form->getValue());

        $view = $form['my_choice']->buildView();
        $this->assertArrayHasKey('test', $view->getVar('choices'));
    }
}