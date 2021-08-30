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

/**
 * Class Time.
 *
 * @package Berlioz\Form\Type
 */
class Time extends AbstractType
{
    public function __construct(array $options = [])
    {
        $options = array_replace(['format' => 'H:i:s'], $options);
        parent::__construct($options);
    }

    /**
     * @inheritdoc
     */
    public function getType(): string
    {
        return 'time';
    }
}