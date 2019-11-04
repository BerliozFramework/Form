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

namespace Berlioz\Form\Type;

use Berlioz\Form\View\ViewInterface;

/**
 * Class File
 *
 * @package Berlioz\Form\Type
 */
class File extends AbstractType
{
    /**
     * Is multiple?
     *
     * @return bool
     */
    protected function isMultiple(): bool
    {
        $attributes = $this->getOption('attributes', []);

        return in_array($attributes['multiple'] ?? false, [true, 'multiple']);
    }

    /**
     * @inheritdoc
     */
    public function getType(): string
    {
        return 'file';
    }

    /////////////////
    /// ID & NAME ///
    /////////////////

    /**
     * @inheritdoc
     */
    public function getFormName(): ?string
    {
        // Multiple?
        if ($this->isMultiple()) {
            return sprintf('%s[]', parent::getFormName());
        }

        return parent::getFormName();
    }

    /////////////
    /// VALUE ///
    /////////////

    /**
     * @inheritdoc
     */
    public function getValue()
    {
        $value = parent::getValue();

        if (!$this->isMultiple()) {
            return $value;
        }

        if (is_array($value)) {
            return $value;
        }

        return array_filter([$value]);
    }

    /////////////
    /// BUILD ///
    /////////////

    /**
     * @inheritdoc
     */
    public function build()
    {
        parent::build();

        if (!is_null($form = $this->getForm())) {
            $formAttributes = $form->getOption('attributes', []);
            $formAttributes['enctype'] = 'multipart/form-data';
            $form->setOption('attributes', $formAttributes);
        }
    }

    /**
     * @inheritdoc
     */
    public function buildView(): ViewInterface
    {
        $view = parent::buildView();
        $view->mergeVars(['value' => '']);

        return $view;
    }
}