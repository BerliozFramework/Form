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

namespace Berlioz\Form;

interface Transformer
{
    /**
     * Transform data to form.
     *
     * @param mixed $data
     *
     * @return mixed
     */
    public function toForm($data);

    /**
     * Transform data from form.
     *
     * @param mixed $data
     *
     * @return mixed
     */
    public function fromForm($data);
}