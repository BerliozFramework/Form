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

declare(strict_types=1);

namespace Berlioz\Form\Validator;

use Berlioz\Form\Exception\ValidatorException;
use Berlioz\Form\Validator\Constraint\FormatConstraint;

/**
 * Class EmailFormatValidator.
 */
class EmailFormatValidator extends FormatValidator
{
    const FORMAT = '(?(DEFINE)(?<email>[a-zA-Z0-9.!#$%&’*+\/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*))';
    const FORMAT_SINGLE = '/' . self::FORMAT . '^\g<email>$/';
    const FORMAT_MULTIPLE = '/' . self::FORMAT . '^\g<email>(,\g<email>)+$/';

    /**
     * EmailFormatValidator constructor.
     *
     * @param bool $multiple
     * @param string $constraint
     *
     * @throws ValidatorException
     */
    public function __construct(bool $multiple = false, string $constraint = FormatConstraint::class)
    {
        parent::__construct(!$multiple ? static::FORMAT_SINGLE : static::FORMAT_MULTIPLE, $constraint);
    }
}