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

declare(strict_types=1);

namespace Berlioz\Form\Type;

use Berlioz\Form\Transformer\CheckboxTransformer;
use Berlioz\Form\View\ViewInterface;

class Checkbox extends AbstractType
{
    public function __construct(array $options = [])
    {
        parent::__construct(
            array_replace(
                ['transformer' => new CheckboxTransformer()],
                $options
            )
        );
    }

    /**
     * @inheritDoc
     */
    public function getType(): string
    {
        return 'checkbox';
    }

    /////////////
    /// BUILD ///
    /////////////

    /**
     * @inheritDoc
     */
    public function buildView(): ViewInterface
    {
        $view = parent::buildView();
        $attributes = $this->getOption('attributes', []);

        $view->mergeVars(
            [
                'attributes' => $attributes,
                'value' => $this->getOption('default_value', 'on'),
            ]
        );

        return $view;
    }
}