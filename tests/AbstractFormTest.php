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
use Berlioz\Form\Collection;
use Berlioz\Form\Form;
use Berlioz\Form\Group;
use Berlioz\Form\Tests\Fake\Entity\FakeAddress;
use Berlioz\Form\Tests\Fake\Entity\FakeJob;
use Berlioz\Form\Tests\Fake\FakeForm;
use Berlioz\Form\Transformer\DateTimeTransformer;
use Berlioz\Form\Type\Choice;
use Berlioz\Form\Type\Date;
use Berlioz\Form\Type\Text;
use Berlioz\Http\Message\ServerRequest;
use Berlioz\Http\Message\Stream;
use Berlioz\Http\Message\Uri;
use PHPUnit\Framework\TestCase;

abstract class AbstractFormTest extends TestCase
{
    protected function getFormTest(object $mapped): Form
    {
        $address = new Group(['data_type' => FakeAddress::class]);
        $address
            ->add('address', Text::class)
            ->add('address_next', Text::class)
            ->add('zip_code', Text::class)
            ->add('city', Text::class)
            ->add('country', Text::class);

        // Job group
        $job = new Group(['data_type' => FakeJob::class]);
        $job
            ->add('title', Text::class)
            ->add('company', Text::class)
            ->add(
                'entry_date',
                Date::class,
                ['transformer' => new DateTimeTransformer()]
            )
            ->add('address', clone $address, ['data_type' => FakeAddress::class]);

        // Person
        $form = new FakeForm('person', $mapped);
        $form
            ->add('last_name', Text::class)
            ->add('first_name', Text::class)
            ->add('sex', Choice::class, ['choices' => ['Male' => 'male', 'Female' => 'female']])
            ->add(
                'birthday',
                Date::class,
                ['transformer' => new DateTimeTransformer()]
            )
            ->add(
                'addresses',
                new Collection(
                    [
                        'data_type' => ArrayObject::class,
                        'prototype' => clone $address,
                        'callbacks' => [
                            'delete' => function () {
                                print 'Callback deletion';
                            },
                        ],
                    ]
                )
            )
            ->add(
                'hobbies',
                new Collection(
                    [
                        'data_type' => ArrayObject::class,
                        'prototype' => new Text(),
                    ]
                )
            )
            ->add('job', clone $job);

        return $form;
    }

    protected function getServerRequest(array $postData = []): ServerRequest
    {
        $_POST = $postData;

        $serverRequest =
            new ServerRequest(
                'POST',
                new Uri(
                    'https',
                    'getberlioz.com'
                ),
                ['Content-Type' => 'multipart/form-data'],
                [],
                [],
                new Stream()
            );

        return $serverRequest;
    }
}