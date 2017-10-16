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

use Berlioz\Form\FormType;

class Checkbox extends FormType
{
    const TYPE = 'checkbox';

    /**
     * Choice constructor.
     *
     * @param string $name    Name
     * @param array  $options Options
     */
    public function __construct(string $name, array $options = [])
    {
        // Default required for checkbox (boolean)
        if (!isset($options['required'])) {
            $options['required'] = false;
        }

        parent::__construct($name, $options);
    }

    /**
     * Get template data.
     *
     * @param array $options Options
     *
     * @return array
     */
    public function getTemplateData(array $options = []): array
    {
        $fOptions = parent::getTemplateData($options);
        $fOptions['checked'] = $this->getDefaultValue() == true;

        return $fOptions;
    }

    /**
     * Get value.
     *
     * @return bool
     */
    public function getValue()
    {
        return parent::getValue() == true;
    }
}