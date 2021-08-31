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

namespace Berlioz\Form\Tests\Fake;

use Berlioz\Form\Form;

class FakeForm extends Form
{
    public function setSubmitted(bool $submitted): FakeForm
    {
        $this->submitted = $submitted;

        return $this;
    }
}