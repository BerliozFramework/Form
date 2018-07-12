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

namespace Berlioz\Form;


use Berlioz\Core\Services\Template\TemplateInterface;

class TwigExtension extends \Twig_Extension
{
    /** @var \Berlioz\Core\Services\Template\TemplateInterface Template engine */
    private $templateEngine;

    /**
     * TwigExtension constructor
     *
     * @param \Berlioz\Core\Services\Template\TemplateInterface $templateEngine Template engine
     */
    public function __construct(TemplateInterface $templateEngine)
    {
        $this->templateEngine = $templateEngine;
        $this->getTemplateEngine()->registerPath(__DIR__ , 'Berlioz-Form');
    }

    /**
     * Get template engine
     *
     * @return \Berlioz\Core\Services\Template\TemplateInterface
     */
    public function getTemplateEngine(): TemplateInterface
    {
        return $this->templateEngine;
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return \Twig_Function[]
     */
    public function getFunctions()
    {
        $functions = [];

        // Forms
        $functions[] = new \Twig_Function('form_render', [$this, 'functionFormRender'], ['is_safe' => ['html']]);
        $functions[] = new \Twig_Function('form_start', [$this, 'functionFormStart'], ['is_safe' => ['html']]);
        $functions[] = new \Twig_Function('form_end', [$this, 'functionFormEnd'], ['is_safe' => ['html']]);
        $functions[] = new \Twig_Function('form_errors', [$this, 'functionFormErrors'], ['is_safe' => ['html']]);
        $functions[] = new \Twig_Function('form_rst', [$this, 'functionFormRst'], ['is_safe' => ['html']]);
        $functions[] = new \Twig_Function('form_rows', [$this, 'functionFormRows'], ['is_safe' => ['html']]);
        $functions[] = new \Twig_Function('form_label', [$this, 'functionFormLabel'], ['is_safe' => ['html']]);
        $functions[] = new \Twig_Function('form_widget', [$this, 'functionFormWidget'], ['is_safe' => ['html']]);
        $functions[] = new \Twig_Function('form_row', [$this, 'functionFormRow'], ['is_safe' => ['html']]);

        return $functions;
    }

    /**
     * Function form render
     *
     * @param \Berlioz\Form\FormElement $formElement      Form element
     * @param string|array              $templateFileName Template file
     *
     * @return string
     */
    public function functionFormRender(FormElement $formElement, $templateFileName): string
    {
        $formElement->setTemplateFilename($templateFileName);

        return '';
    }

    /**
     * Function form start
     *
     * @param \Berlioz\Form\Form $form    Form
     * @param array              $options Options
     *
     * @return string
     */
    public function functionFormStart(Form $form, array $options = []): string
    {
        if ($this->getTemplateEngine()->hasBlock($form->getTemplateFilename(), 'form_start')) {
            return $this->getTemplateEngine()
                        ->renderBlock($form->getTemplateFilename(),
                                      'form_start',
                                      $form->getTemplateData($options));
        } else {
            return '';
        }
    }

    /**
     * Function form end
     *
     * @param \Berlioz\Form\Form $form Form
     *
     * @return string
     */
    public function functionFormEnd(Form $form): string
    {
        if ($this->getTemplateEngine()->hasBlock($form->getTemplateFilename(), 'form_end')) {
            return $this->getTemplateEngine()
                        ->renderBlock($form->getTemplateFilename(),
                                      'form_end',
                                      $form->getTemplateData());
        } else {
            return '';
        }
    }

    /**
     * Function form errors
     *
     * @param \Berlioz\Form\Form $form Form
     *
     * @return string
     */
    public function functionFormErrors(Form $form): string
    {
        if ($this->getTemplateEngine()->hasBlock($form->getTemplateFilename(), 'form_errors')) {
            return $this->getTemplateEngine()
                        ->renderBlock($form->getTemplateFilename(),
                                      'form_errors',
                                      $form->getTemplateData());
        } else {
            return '';
        }
    }

    /**
     * Function form rows
     *
     * @param \Berlioz\Form\Form $form    Form
     * @param array              $options Options
     *
     * @return string
     */
    public function functionFormRows(Form $form, array $options = []): string
    {
        if ($this->getTemplateEngine()->hasBlock($form->getTemplateFilename(), 'form_rows')) {
            return $this->getTemplateEngine()
                        ->renderBlock($form->getTemplateFilename(),
                                      'form_rows',
                                      $form->getTemplateData($options));
        } else {
            return '';
        }
    }

    /**
     * Function form label
     *
     * @param \Berlioz\Form\FormType $formType Form type
     * @param array                  $options  Options
     *
     * @return string
     */
    public function functionFormLabel(FormType $formType, array $options = []): string
    {
        if ($this->getTemplateEngine()->hasBlock($formType->getTemplateFilename(), 'form_label')) {
            return $this->getTemplateEngine()
                        ->renderBlock($formType->getTemplateFilename(),
                                      'form_label',
                                      $formType->getTemplateData($options));
        } else {
            return '';
        }
    }

    /**
     * Function form widget
     *
     * @param \Berlioz\Form\FormType $formType Form type
     * @param array                  $options  Options
     *
     * @return string
     */
    public function functionFormWidget(FormType $formType, array $options = []): string
    {
        if ($this->getTemplateEngine()->hasBlock($formType->getTemplateFilename(), 'form_widget')) {
            // Set inserted
            $formType->setInserted(true);

            return $this->getTemplateEngine()
                        ->renderBlock($formType->getTemplateFilename(),
                                      'form_widget',
                                      $formType->getTemplateData($options));
        } else {
            return '';
        }
    }

    /**
     * Function form row
     *
     * @param \Berlioz\Form\FormType $formType Form type
     * @param array                  $options  Options
     *
     * @return string
     */
    public function functionFormRow(FormType $formType, array $options = []): string
    {
        if ($this->getTemplateEngine()->hasBlock($formType->getTemplateFilename(), 'form_row')) {
            // Set inserted
            $formType->setInserted(true);

            return $this->getTemplateEngine()
                        ->renderBlock($formType->getTemplateFilename(),
                                      'form_row',
                                      $formType->getTemplateData($options));
        } else {
            return '';
        }
    }
}
