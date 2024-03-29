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

use Berlioz\Form\View\ViewInterface;

class Button extends AbstractType
{
    /**
     * Button constructor.
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        parent::__construct(
            array_replace_recursive(
                [
                    'required' => false,
                    'mapped' => false,
                    'value' => null,
                ],
                $options
            )
        );
    }

    /**
     * @inheritDoc
     */
    public function getType(): string
    {
        return 'button';
    }

    /**
     * Is button clicked?
     *
     * @return bool
     */
    public function isClicked(): bool
    {
        return !is_null($this->getValue());
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
        $view->mergeVars(['value' => $this->getValue() ?? $this->getOption('value')]);

        return $view;
    }
}