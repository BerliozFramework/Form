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

namespace Berlioz\Form\Type;

use Berlioz\Form\View\ViewInterface;

class Checkbox extends AbstractType
{
    /**
     * @inheritdoc
     */
    public function getType(): string
    {
        return 'checkbox';
    }

    /////////////
    /// VALUE ///
    /////////////

    /**
     * @inheritdoc
     */
    public function getValue(bool $raw = false)
    {
        $value = parent::getValue($raw);

        // Transformer
        if (!$raw && is_null($transformer = $this->getTransformer())) {
            if (is_null($this->getOption('default_value', null))) {
                return in_array(parent::getValue(true),
                                [$this->getOption('default_value', 'on'), 'true', true],
                                true);
            } else {
                return parent::getValue() ?: null;
            }
        }

        return $value;
    }

    /////////////
    /// BUILD ///
    /////////////

    /**
     * @inheritdoc
     */
    public function buildView(): ViewInterface
    {
        $view = parent::buildView();
        $attributes = $this->getOption('attributes', []);
        $attributes['checked'] = in_array($this->getValue(true),
                                          [$this->getOption('default_value', 'on'), 'true', true],
                                          true);

        $view->mergeVars(['attributes' => $attributes,
                          'value'      => $this->getOption('default_value', 'on')]);

        return $view;
    }
}