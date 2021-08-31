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

use Berlioz\Form\Type\Email;
use PHPUnit\Framework\TestCase;

class TypeTest extends TestCase
{
    public function testEmail()
    {
        $type = new Email(['name' => 'email']);
        $type->build();

        $type->setValue('foo@bar.com,bar@foo.com');
        $this->assertEquals('foo@bar.com,bar@foo.com', $type->getValue());
        $this->assertFalse($type->isValid());

        // Add multiple option
        $type->resetValidators();
        $type->resetConstraints();
        $type->setOption('attributes', array_merge($type->getOption('attributes', []), ['multiple' => true]));
        $type->build();

        $this->assertTrue($type->isValid());
    }
}
