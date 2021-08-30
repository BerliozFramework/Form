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

namespace Berlioz\Form\Collector;

use Berlioz\Form\Form;

class FormCollector extends GroupCollector
{
    /**
     * FormHydrator constructor.
     *
     * @param Form $form
     */
    public function __construct(Form $form)
    {
        parent::__construct($form);
    }
}