<?php
/*
 * This file is part of Berlioz framework.
 *
 * @license   https://opensource.org/licenses/MIT MIT License
 * @copyright 2024 Ronan GIRON
 * @author    Ronan GIRON <https://github.com/ElGigi>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code, to the root.
 */

declare(strict_types=1);

namespace Berlioz\Form\Type;

use Berlioz\Form\Element\AbstractElement;
use Berlioz\Form\Validator\NotEmptyValidator;
use Berlioz\Form\View\BasicView;
use Berlioz\Form\View\ViewInterface;

class RawType extends AbstractElement implements TypeInterface
{
    protected mixed $submittedValue = null;
    protected mixed $value = null;

    /**
     * __clone() magic method.
     */
    public function __clone()
    {
        $this->value = null;
    }

    /////////////
    /// VALUE ///
    /////////////

    /**
     * @inheritDoc
     */
    public function getValue(): mixed
    {
        if (true === $this->isSubmitted()) {
            return $this->submittedValue;
        }

        return $this->value;
    }

    /**
     * @inheritDoc
     */
    public function setValue(mixed $value): void
    {
        $this->value = $this->getTransformer()->toForm($value, $this);
    }

    /**
     * @inheritDoc
     */
    public function submitValue(mixed $value): void
    {
        $this->submitted = true;
        $this->submittedValue = $value;
    }

    /**
     * @inheritDoc
     */
    public function build(): void
    {
        // Not empty validator
        if ($this->hasValidator(NotEmptyValidator::class) === false) {
            $this->addValidator(new NotEmptyValidator());
        }
    }

    /**
     * @inheritDoc
     */
    public function buildView(): ViewInterface
    {
        return new BasicView($this);
    }
}
