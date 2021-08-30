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

use Berlioz\Form\Validator\EmailFormatValidator;
use Berlioz\Form\Validator\FormatValidator;

/**
 * Class Email
 *
 * @package Berlioz\Form\Type
 */
class Email extends Text
{
    /**
     * @inheritdoc
     */
    public function getType(): string
    {
        return 'email';
    }

    /////////////
    /// BUILD ///
    /////////////

    /**
     * @inheritdoc
     * @throws \Berlioz\Form\Exception\ValidatorException
     */
    public function build()
    {
        parent::build();

        // Format validator
        if ($this->hasValidator(FormatValidator::class) === false) {
            $this->addValidator(new EmailFormatValidator($this->isMultiple()));
        }
    }
}