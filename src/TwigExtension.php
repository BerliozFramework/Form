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

namespace Berlioz\Form;

use Berlioz\Form\Exception\AlreadyInsertedException;
use Berlioz\Form\View\BasicView;
use Berlioz\Form\View\TraversableView;
use Berlioz\Form\View\ViewInterface;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

if (class_exists('Twig\\Extension\\AbstractExtension', true)) {
    /**
     * Class TwigExtension.
     *
     * @package Berlioz\Form
     */
    class TwigExtension extends AbstractExtension
    {
        const DEFAULT_TPL = '@Berlioz-Form/default.html.twig';
        /** @var \Twig\Environment Twig */
        private $twig;

        /**
         * TwigExtension constructor.
         *
         * @param \Twig\Environment $twig
         */
        public function __construct(Environment $twig)
        {
            $this->twig = $twig;
        }

        /**
         * Get twig.
         *
         * @return \Twig\Environment
         */
        private function getTwig(): Environment
        {
            return $this->twig;
        }

        /**
         * Render.
         *
         * @param string $blockType
         * @param \Berlioz\Form\View\ViewInterface $formView
         * @param array $options
         *
         * @return string
         * @throws \Throwable Twig error
         * @throws \Twig\Error\Error
         */
        private function render(string $blockType, ViewInterface $formView, array $options = []): string
        {
            $template = $this->getTwig()->load($formView->getRender() ?? self::DEFAULT_TPL);

            // Variables
            $variables = $formView->getVars();
            $variables = array_replace_recursive($variables, $options);
            $variables['form'] = $formView;

            $specificBlockType = sprintf('%s_%s', $formView->getVar('type', 'form'), $blockType);

            if ($template->hasBlock($specificBlockType, $variables)) {
                return $template->renderBlock($specificBlockType, $variables);
            }

            return $template->renderBlock(sprintf('form_%s', $blockType), $variables);
        }

        /**
         * Returns a list of functions to add to the existing list.
         *
         * @return \Twig\TwigFunction[]
         */
        public function getFunctions()
        {
            $functions = [];

            // Forms
            $functions[] = new TwigFunction('form_render', [$this, 'functionFormRender'], ['is_safe' => ['html']]);
            $functions[] = new TwigFunction('form_start', [$this, 'functionFormStart'], ['is_safe' => ['html']]);
            $functions[] = new TwigFunction('form_end', [$this, 'functionFormEnd'], ['is_safe' => ['html']]);
            $functions[] = new TwigFunction('form_errors', [$this, 'functionFormErrors'], ['is_safe' => ['html']]);
            $functions[] = new TwigFunction('form_rest', [$this, 'functionFormRest'], ['is_safe' => ['html']]);
            $functions[] = new TwigFunction('form_label', [$this, 'functionFormLabel'], ['is_safe' => ['html']]);
            $functions[] = new TwigFunction('form_widget', [$this, 'functionFormWidget'], ['is_safe' => ['html']]);
            $functions[] = new TwigFunction('form_row', [$this, 'functionFormRow'], ['is_safe' => ['html']]);

            return $functions;
        }

        /**
         * Function form render
         *
         * @param \Berlioz\Form\View\ViewInterface $formView Form view
         * @param string|array $templateFileName Template file
         *
         * @return string
         */
        public function functionFormRender(ViewInterface $formView, $templateFileName): string
        {
            $formView->setRender($templateFileName);

            return '';
        }

        /**
         * Function form start
         *
         * @param \Berlioz\Form\View\BasicView $formView Form view
         * @param array $options Options
         *
         * @return string
         * @throws \Throwable Twig error
         * @throws \Twig\Error\Error
         */
        public function functionFormStart(BasicView $formView, array $options = []): string
        {
            return $this->render('start', $formView, $options);
        }

        /**
         * Function form end
         *
         * @param \Berlioz\Form\View\BasicView $formView Form view
         * @param array $options Options
         *
         * @return string
         * @throws \Throwable Twig error
         * @throws \Twig\Error\Error
         */
        public function functionFormEnd(BasicView $formView, array $options = []): string
        {
            return $this->render('end', $formView, $options);
        }

        /**
         * Function form errors
         *
         * @param \Berlioz\Form\View\ViewInterface $formView Form view
         * @param array $options Options
         *
         * @return string
         * @throws \Throwable Twig error
         * @throws \Twig\Error\Error
         */
        public function functionFormErrors(ViewInterface $formView, array $options = []): string
        {
            return $this->render('errors', $formView, $options);
        }

        /**
         * Function form label
         *
         * @param \Berlioz\Form\View\BasicView $formView Form view
         * @param array $options Options
         *
         * @return string
         * @throws \Throwable Twig error
         * @throws \Twig\Error\Error
         */
        public function functionFormLabel(BasicView $formView, array $options = []): string
        {
            return $this->render('label', $formView, $options);
        }

        /**
         * Function form widget
         *
         * @param \Berlioz\Form\View\ViewInterface $formView Form view
         * @param array $options Options
         *
         * @return string
         * @throws \Berlioz\Form\Exception\AlreadyInsertedException
         * @throws \Throwable Twig error
         * @throws \Twig\Error\Error
         */
        public function functionFormWidget(ViewInterface $formView, array $options = []): string
        {
            if ($formView->isInserted()) {
                throw new AlreadyInsertedException(sprintf('Element "%s" of form has already inserted', $formView->getVar('name', 'Unknown')));
            }

            // Set inserted
            $formView->setInserted();

            return $this->render('widget', $formView, $options);
        }

        /**
         * Function form row
         *
         * @param \Berlioz\Form\View\ViewInterface $formView Form view
         * @param array $options Options
         *
         * @return string
         * @throws \Berlioz\Form\Exception\AlreadyInsertedException
         * @throws \Throwable Twig error
         * @throws \Twig\Error\Error
         */
        public function functionFormRow(ViewInterface $formView, array $options = []): string
        {
            if ($formView->isInserted()) {
                throw new AlreadyInsertedException(sprintf('Element "%s" of form has already inserted', $formView->getVar('name', 'Unknown')));
            }

            return $this->render('row', $formView, $options);
        }

        /**
         * Function form rest.
         *
         * @param \Berlioz\Form\View\TraversableView $formView Form view
         * @param array $options Options
         *
         * @return string
         * @throws \Berlioz\Form\Exception\AlreadyInsertedException
         * @throws \Throwable Twig error
         * @throws \Twig\Error\Error
         */
        public function functionFormRest(TraversableView $formView, array $options = []): string
        {
            $rendering = '';

            /** @var \Berlioz\Form\View\ViewInterface $aFormView */
            foreach ($formView as $aFormView) {
                if ($aFormView->isInserted()) {
                    continue;
                }

                $rendering .= $this->functionFormRow($aFormView, $options);
            }

            return $rendering;
        }
    }
}