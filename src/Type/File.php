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

use Berlioz\Form\Form;
use Berlioz\Form\FormType;

class File extends FormType
{
    const TYPE = 'file';

    /**
     * @inheritdoc
     */
    public function setParent(Form $parent): void
    {
        parent::setParent($parent);

        // Set 'enctype' attribute to the form element for file type
        $mainParentOptions = $parent->getMainParent()->getOptions();
        $mainParentOptions->set('attributes',
                                array_merge($mainParentOptions->get('attributes') ?? [],
                                            ['enctype' => 'multipart/form-data']));
    }
}