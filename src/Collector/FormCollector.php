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

namespace Berlioz\Form\Collector;

use Berlioz\Form\Element\ElementInterface;
use Berlioz\Form\Form;

class FormCollector extends GroupCollector
{
    /**
     * FormHydrator constructor.
     *
     * @param \Berlioz\Form\Form $form
     */
    public function __construct(Form $form)
    {
        parent::__construct($form);
    }

    /**
     * @inheritdoc
     */
    protected function getSubMapped(ElementInterface $element, object $mapped): object
    {
        if (!$element instanceof Form) {
            parent::getSubMapped($element, $mapped);
        }

        return $mapped;
    }
}