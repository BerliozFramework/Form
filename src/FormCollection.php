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


use Berlioz\Core\Exception\BerliozException;

class FormCollection extends FormElement implements FormTraversableInterface, \ArrayAccess
{
    /** @var \Berlioz\Form\FormElement[] List */
    private $list;
    /** @var \Berlioz\Form\FormElement Element */
    private $element;

    /**
     * FormCollection constructor.
     *
     * @param \Berlioz\Form\FormElement $element Form element
     * @param array                     $options Options
     *
     * @throws \Berlioz\Core\Exception\BerliozException If try to add a collection into this collection
     */
    public function __construct(FormElement $element, array $options = [])
    {
        if (!is_a($element, get_class())) {
            $this->element = $element;
        } else {
            throw new BerliozException('You can not add a collection into another collection');
        }
    }

    /**
     * Get form name.
     *
     * @return string
     */
    public function getFormName(): string
    {
        $formName = sprintf('[%s][]', $this->getName());

        if ($this->hasParent()) {
            $formName = $this->getParent()->getFormName() . $formName;
        }

        return $formName;
    }

    /**
     * Get element.
     *
     * @return \Berlioz\Form\FormElement
     */
    public function getElement(): FormElement
    {
        return $this->element;
    }

    /**
     * Retrieve an external iterator.
     *
     * @return \Traversable
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->list);
    }

    /**
     * Whether a form element exists.
     *
     * @param string $offset Form element offset in collection
     *
     * @return bool
     */
    public function offsetExists($offset)
    {
        $offset = explode('.', $offset, 2);

        if (isset($this->list[$offset[0]])) {
            $formElement = $this->list[$offset[0]];

            if (count($offset) == 2) {
                if ($formElement instanceof FormTraversableInterface) {
                    return $formElement->offsetExists($offset[1]);
                } else {
                    return false;
                }
            } else {
                return true;
            }
        } else {
            return false;
        }
    }

    /**
     * Get a form element.
     *
     * @param string $offset Form element offset in collection
     *
     * @return \Berlioz\Form\FormElement
     * @throws \Berlioz\Core\Exception\BerliozException If element doesn't exists
     */
    public function offsetGet($offset)
    {
        $offset = explode('.', $offset, 2);

        if ($this->offsetExists($offset[0])) {
            $formElement = $this->list[$offset[0]];

            if (count($offset) == 2) {
                if ($formElement instanceof FormTraversableInterface) {
                    return $formElement->offsetGet($offset[1]);
                } else {
                    throw new BerliozException(sprintf('Element "%s[%d]" is not a traversable element', $this->getElement()->getName(), $offset[0]));
                }
            } else {
                return $formElement;
            }
        } else {
            throw new BerliozException(sprintf('Element "%s[%d]" does not exists', $this->getElement()->getName(), $offset[0]));
        }
    }

    /**
     * Add a form element.
     *
     * @param string                    $offset Form element offset in collection
     * @param \Berlioz\Form\FormElement $value  The value to set.
     *
     * @return void
     * @throws \Berlioz\Core\Exception\BerliozException If element is not an acceptable object
     * @throws \Berlioz\Core\Exception\BerliozException If the name of element is not valid
     * @throws \Berlioz\Core\Exception\BerliozException If an another element already exists with the same name
     */
    public function offsetSet($offset, $value)
    {
        if ($value instanceof FormElement) {
            if (preg_match('/^\d+$/', $offset) == 1) {
                if (!isset($this->list[$value->getName()])) {
                    $this->list[$value->getName()] = $value;
                    $value->setParent($this->getParent());
                } else {
                    throw new BerliozException(sprintf('An element named "%s" already exists', $value->getName()));
                }
            } else {
                throw new BerliozException(sprintf('"%s" is not a valid name', $offset));
            }
        } else {
            throw new BerliozException('Only class whose implements \Berlioz\Core\Form\FormElement interface can be added');
        }
    }

    /**
     * Remove a form element.
     *
     * @param string $offset Form element offset in collection
     *
     * @return void
     */
    public function offsetUnset($offset)
    {
        $this->list[$offset]->setParent(null);
        unset($this->list[$offset]);
    }
}