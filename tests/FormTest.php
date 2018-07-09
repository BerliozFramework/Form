<?php
/**
 * This file is part of Berlioz framework.
 *
 * @license   https://opensource.org/licenses/MIT MIT License
 * @copyright 2017 Ronan GIRON
 * @author    Ronan GIRON <https://github.com/ElGigi>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code, to the root.
 */

namespace Berlioz\Form\Tests;

use Berlioz\Form\Collection;
use Berlioz\Form\Form;
use Berlioz\Form\Group;
use Berlioz\Form\Propagator;
use Berlioz\Form\Tests\Data\Entity;
use Berlioz\Form\Tests\Data\EntityAddress;
use Berlioz\Form\Tests\Data\EntityHobby;
use Berlioz\Form\Type\Text;
use PHPUnit\Framework\TestCase;

class FormTest extends TestCase
{
    private function getForm($mapped = null)
    {
        return
            (new Form('form', $mapped, ['required' => false]))
                ->add('firstname', Text::class)
                ->add('lastname', Text::class)
                ->add('address',
                      (new Group(['data_type' => EntityAddress::class]))
                          ->add('address', Text::class)
                          ->add('address_next', Text::class)
                          ->add('postal_code', Text::class)
                          ->add('city', Text::class))
                ->add('hobbies',
                      new Collection(['prototype' =>
                                          (new Group(['data_type' => EntityHobby::class]))
                                              ->add('name', Text::class)]))
                ->add('tags',
                      new Collection(['prototype' => Text::class]),
                      ['mapped' => true]);
    }

    public function test()
    {
        $entity = new Entity;
        $entity->setLastname('Giron');
        $entity->setTags(['tag1', 'tag2', 'tag3']);
        $form = $this->getForm($entity);

        $form->setValue(['lastname'  => 'Giron',
                         'firstname' => 'Ronan',
                         'address'   => ['address'     => '1 rue Théodore Botrel',
                                         'postal_code' => '44360',
                                         'city'        => 'Saint Etienne de Montluc'],
                         'hobbies'   => [['name' => 'Movies'], ['name' => 'Dogs'], ['name' => 'Friends']],
                         'tags'      => ['tag1', 'tag2']]);

        $propagator = new Propagator($form);
        $propagator->propagate();

        var_dump($entity);
    }
}
