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

namespace Berlioz\Form\Tests\Type;

use Berlioz\Form\Element\AbstractElement;
use Berlioz\Form\Form;
use Berlioz\Form\Group;
use Berlioz\Form\Tests\Fake\FakeTransformer;
use Berlioz\Form\Tests\Fake\FakeType;
use Berlioz\Form\Tests\Fake\FakeForm;
use Berlioz\Form\Transformer\DefaultTransformer;
use Berlioz\Form\Type\AbstractType;
use Berlioz\Form\Validator\NotEmptyValidator;
use Berlioz\Form\View\BasicView;
use PHPUnit\Framework\TestCase;

class AbstractTypeTest extends TestCase
{
    public function test__construct()
    {
        $type = new FakeType();

        $this->assertInstanceOf(AbstractType::class, $type);
        $this->assertInstanceOf(AbstractElement::class, $type);
    }

    public function test__clone()
    {
        $type = new FakeType();
        $type->setValue('test');

        $this->assertSame('test', $type->getValue());
        $clonedType = clone $type;
        $this->assertNull($clonedType->getValue());
    }

    public function test__debugInfo()
    {
        $type = new FakeType();
        $debugInfo = $type->__debugInfo();

        $this->assertArrayHasKey('name', $debugInfo);
        $this->assertArrayHasKey('value', $debugInfo);
        $this->assertArrayHasKey('parent', $debugInfo);
        $this->assertArrayHasKey('options', $debugInfo);
        $this->assertArrayHasKey('constraints', $debugInfo);
    }

    public function testGetSetOption()
    {
        $type = new FakeType();

        $type->setOption('foo', 'bar');
        $this->assertSame('bar', $type->getOption('foo'));
        $this->assertSame('bar', $type->getOption('foo', 'test'));
        $this->assertNull($type->getOption('bar'));
        $this->assertSame('test', $type->getOption('bar', 'test'));
    }

    public function testGetOptions()
    {
        $form = new FakeForm('foo');
        $type = new FakeType(['name' => 'bar']);
        $type->setParent($form);

        $this->assertEquals(
            [
                'id' => 'foo_bar',
                'name' => 'foo[bar]',
                'parent' => $form,
                'this' => $type,
            ],
            $type->getOptions()
        );
    }

    public function testGetType()
    {
        $type = new FakeType();
        $this->assertSame('fake', $type->getType());
    }

    public function testGetId()
    {
        $type = new FakeType(['name' => 'bar']);
        $this->assertSame('bar', $type->getId());

        $form = new FakeForm('foo');
        $type->setParent($form);
        $this->assertSame('foo_bar', $type->getId());
    }

    public function testGetName()
    {
        $type = new FakeType(['name' => 'bar']);
        $this->assertSame('bar', $type->getName());

        $form = new FakeForm('foo');
        $type->setParent($form);
        $this->assertSame('bar', $type->getName());
    }

    public function testGetFormName()
    {
        $type = new FakeType(['name' => 'bar']);
        $this->assertSame('bar', $type->getFormName());

        $form = new FakeForm('foo');
        $type->setParent($form);
        $this->assertSame('foo[bar]', $type->getFormName());
    }

    public function testGetSetParent()
    {
        $form = new FakeForm('foo');
        $group = new Group();
        $group->setParent($form);
        $type = new FakeType();
        $type->setParent($group);

        $this->assertSame($group, $type->getParent());
        $this->assertSame($form, $group->getParent());
        $this->assertNull($form->getParent());
    }

    public function testGetForm()
    {
        $form = new FakeForm('foo');
        $group = new Group();
        $group->setParent($form);
        $type = new FakeType();
        $type->setParent($group);

        $this->assertSame($form, $type->getForm());
    }

    public function testGetSetTransformer()
    {
        $type = new FakeType();

        $this->assertInstanceOf(DefaultTransformer::class, $type->getTransformer());
        $type->setTransformer(new FakeTransformer());
        $this->assertInstanceOf(FakeTransformer::class, $type->getTransformer());
    }

    public function testValue()
    {
        $type = new FakeType(['name' => 'bar']);
        $type->submitValue('bar');

        $this->assertNull($type->getValue());

        $type->setValue('test');
        $this->assertSame('test', $type->getValue());

        $form = new FakeForm('foo');
        $type->setParent($form);
        $this->assertSame('test', $type->getValue());
        $form->setSubmitted(true);
        $this->assertSame('bar', $type->getValue());
    }

    public function testGetConstraints()
    {
        $type = new FakeType(['name' => 'bar']);
        $this->assertEquals([], $type->getConstraints());
    }

    public function testBuildView()
    {
        $type = new FakeType(['name' => 'bar']);
        $view = $type->buildView();

        $this->assertInstanceOf(BasicView::class, $view);
    }

    public function testIsValid()
    {
        $form = new FakeForm('foo');
        $form->setSubmitted(true);
        $type = new FakeType(['name' => 'bar']);
        $type->setParent($form);
        $type->addValidator(new NotEmptyValidator());

        $this->assertFalse($type->isValid());

        $type->submitValue('');
        $this->assertFalse($type->isValid());

        $type->submitValue('bar');
        $this->assertTrue($type->isValid());
    }

    public function testValidator()
    {
        $type = new FakeType(['name' => 'bar']);

        $this->assertFalse($type->hasValidator(NotEmptyValidator::class));

        $type->addValidator(new NotEmptyValidator());
        $this->assertNotFalse($type->hasValidator(NotEmptyValidator::class));
    }
}
