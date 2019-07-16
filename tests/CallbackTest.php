<?php
/**
 * This file is part of Berlioz framework.
 *
 * @license   https://opensource.org/licenses/MIT MIT License
 * @copyright 2019 Ronan GIRON
 * @author    Ronan GIRON <https://github.com/ElGigi>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code, to the root.
 */

namespace Berlioz\Form\Tests;

use ArrayObject;
use Berlioz\Form\Collection;
use Berlioz\Form\Tests\Fake\FakeForm;
use Berlioz\Form\Type\Text;
use PHPUnit\Framework\TestCase;

class CallbackTest extends TestCase
{
    public function testCollection()
    {
        $callbackAddCalled = null;
        $callbackRemoveCalled = null;

        $form = new FakeForm('foo');
        $form
            ->add(
                'collection',
                new Collection(
                    [
                        'data_type' => ArrayObject::class,
                        'prototype' => new Text(),
                        'callbacks' => [
                            'add' => function ($collection, $element) use (&$callbackAddCalled) {
                                $callbackAddCalled = $element->getValue();
                            },
                            'remove' => function ($collection, $element) use (&$callbackRemoveCalled) {
                                $callbackRemoveCalled = $element->getValue();
                            },
                        ],
                    ]
                )
            );

        $form
            ->setValue(
                [
                    'collection' => [
                        0 => 'Foo',
                        1 => 'Foo 2',
                        2 => 'Bar',
                        3 => 'Bar 2',
                    ],
                ]
            )
            ->setSubmitted(true)
            ->submitValue(
                [
                    'collection' => [
                        0 => 'Foo',
                        2 => 'Bar',
                        3 => 'Bar 2',
                        4 => 'Foo 3',
                    ],
                ]
            );

        $this->assertEquals('Foo 3', $callbackAddCalled);
        $this->assertEquals('Foo 2', $callbackRemoveCalled);
    }
}