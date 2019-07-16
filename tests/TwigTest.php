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

use Berlioz\Form\Collection;
use Berlioz\Form\Form;
use Berlioz\Form\Group;
use Berlioz\Form\Tests\Fake\Entity\FakePerson;
use Berlioz\Form\Tests\Fake\FakeForm;
use Berlioz\Form\TwigExtension;
use Berlioz\Form\Type\Checkbox;
use Berlioz\Form\Type\Date;
use Berlioz\Form\Type\Text;
use Berlioz\Form\Type\TextArea;
use Twig\Environment;
use Twig\Loader\ChainLoader;
use Twig\Loader\FilesystemLoader;

class TwigTest extends AbstractFormTest
{
    /** @var \Twig\Environment Twig */
    private $twig;
    /** @var \Berlioz\Form\TwigExtension Twig extension */
    private $twigExtension;

    protected function getTwig()
    {
        if (is_null($this->twig)) {
            // Twig
            $loader = new ChainLoader();
            $loader->addLoader($fileLoader = new FilesystemLoader([], realpath(__DIR__ . '/Fake/Twig')));
            $this->twig = new Environment($loader);
            $this->twig->addExtension($this->twigExtension = new TwigExtension($this->twig));
            $fileLoader->addPath(realpath(__DIR__ . '/../resources'), 'Berlioz-Form');
        }

        return $this->twig;
    }

    protected function getTwigExtension(): TwigExtension
    {
        $this->getTwig();

        return $this->twigExtension;
    }

    public function testFunctionStart()
    {
        $entity = new FakePerson();
        $form = $this->getForm($entity);

        $this->assertEquals(
            '<form name="person" method="post" action="">',
            $this->getTwigExtension()->functionFormStart($form->buildView())
        );

        $form->setOption('action', '/foo.html');

        $this->assertEquals(
            '<form name="person" method="post" action="/foo.html">',
            $this->getTwigExtension()->functionFormStart($form->buildView())
        );
    }

    public function testFunctionEnd()
    {
        $entity = new FakePerson();
        $form = $this->getForm($entity);

        $this->assertEquals(
            '</form>',
            $this->getTwigExtension()->functionFormEnd($form->buildView())
        );
    }

    public function testFunctionLabel()
    {
        $form = new Form('foo');
        $form->add('name', Text::class, ['label' => 'Name']);

        $this->assertEquals(
            '<label for="foo_name">Name</label>',
            $this->getTwigExtension()->functionFormLabel($form['name']->buildView())
        );
    }

    public function testFunctionWidget()
    {
        // Address group
        $address = new Group();
        $address
            ->add('address', Text::class, ['label' => 'Address'])
            ->add('zip', Text::class, ['label' => 'Zip code']);

        // Form
        $form = new FakeForm('foo');
        $form->add('name', Text::class, ['label' => 'Name']);
        $form->add('birthday', Date::class, ['label' => 'Birthday']);
        $form->add('text', TextArea::class, ['label' => 'Presentation']);
        $form->add('check', Checkbox::class, ['label' => 'Acceptation']);
        $form->add('addresses', Collection::class, ['prototype' => $address]);

        // With "required" default option
        $this->assertEquals(
            '<input type="text" id="foo_name" name="foo[name]" required="required" />',
            $this->getTwigExtension()->functionFormWidget($form['name']->buildView())
        );

        // Without "required" default option
        $form['name']->setOption('required', false);
        $this->assertEquals(
            '<input type="text" id="foo_name" name="foo[name]" />',
            $this->getTwigExtension()->functionFormWidget($form['name']->buildView())
        );

        // With default value
        $form->setSubmitted(false);
        $form['name']->setValue('Berlioz');
        $this->assertEquals(
            '<input type="text" id="foo_name" name="foo[name]" value="Berlioz" />',
            $this->getTwigExtension()->functionFormWidget($form['name']->buildView())
        );

        // Date time
        $form->setSubmitted(true);
        $form['birthday']->submitValue('1980-01-01');
        $this->assertEquals(
            '<input type="date" id="foo_birthday" name="foo[birthday]" required="required" value="1980-01-01" />',
            $this->getTwigExtension()->functionFormWidget($form['birthday']->buildView())
        );

        // Text area
        $form->setSubmitted(true);
        $form['text']->submitValue('Foo bar');
        $this->assertEquals(
            '<textarea id="foo_text" name="foo[text]" required="required">Foo bar</textarea>',
            $this->getTwigExtension()->functionFormWidget($form['text']->buildView())
        );

        // Checkbox
        $form->setSubmitted(true);
        $form['check']->submitValue('on');
        $this->assertEquals(
            '<label>
    <input type="checkbox" id="foo_check" name="foo[check]" required="required" checked="on" value="on" />
    Acceptation
  </label>',
            $this->getTwigExtension()->functionFormWidget($form['check']->buildView())
        );
    }

    public function testFunctionRow()
    {
        $form = new FakeForm('foo');
        $form->add('name', Text::class, ['label' => 'Name']);
        $form->add('birthday', Date::class, ['label' => 'Birthday']);
        $form->add('text', TextArea::class, ['label' => 'Presentation']);
        $form->add('check', Checkbox::class, ['label' => 'Acceptation']);

        // Text
        $this->assertEquals(
            '      <label for="foo_name">Name</label>
    <input type="text" id="foo_name" name="foo[name]" required="required" />
  ',
            $this->getTwigExtension()->functionFormRow($form['name']->buildView())
        );


        // Text area
        $form->setSubmitted(true);
        $form['text']->submitValue('Foo bar');
        $this->assertEquals(
            '      <label for="foo_text">Presentation</label>
    <textarea id="foo_text" name="foo[text]" required="required">Foo bar</textarea>
  ',
            $this->getTwigExtension()->functionFormRow($form['text']->buildView())
        );

        // Checkbox
        $form->setSubmitted(true);
        $form['check']->submitValue('on');
        $this->assertEquals(
            '<label>
    <input type="checkbox" id="foo_check" name="foo[check]" required="required" checked="on" value="on" />
    Acceptation
  </label>
  ',
            $this->getTwigExtension()->functionFormRow($form['check']->buildView())
        );
    }
}
