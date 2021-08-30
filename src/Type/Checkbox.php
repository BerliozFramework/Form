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

declare(strict_types=1);

namespace Berlioz\Form\Type;

use Berlioz\Form\Transformer\CheckboxTransformer;
use Berlioz\Form\View\ViewInterface;

/**
 * Class Checkbox.
 */
class Checkbox extends AbstractType
{
    const DEFAULT_TRANSFORMER = CheckboxTransformer::class;

    /**
     * @inheritdoc
     */
    public function getType(): string
    {
        return 'checkbox';
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
        $attributes['checked'] = $this->getValue() == $this->getOption('default_value', 'on');

        $view->mergeVars(
            [
                'attributes' => $attributes,
                'value' => $this->getOption('default_value', 'on'),
            ]
        );

        return $view;
    }
}