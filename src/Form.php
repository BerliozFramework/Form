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
use Berlioz\Core\Http\ServerRequest;

class Form extends FormElement implements FormTraversableInterface
{
    private static $_b_initialized = false;
    /** @var \Berlioz\Form\FormElement[] Elements */
    private $elements;
    /** @var \Berlioz\Core\Http\ServerRequest Server request */
    private $serverRequest;
    /** @var array|false Data of form */
    private $formData;
    /** @var mixed Mapped object */
    private $mapped;

    /**
     * Form constructor.
     *
     * @param string $name    Name
     * @param mixed  $mapped  Mapped object
     * @param array  $options Options
     *
     * @throws \Berlioz\Core\Exception\BerliozException If $mapped parameter is not an object
     */
    public function __construct(string $name, $mapped = null, array $options = [])
    {
        // Mapped object
        if (!is_null($mapped)) {
            if (is_object($mapped)) {
                $this->mapped = $mapped;
            } else {
                throw new BerliozException('$mapped parameter must be an object');
            }
        }

        // Set name
        $this->setName($name);

        // Options
        $this->getOptions()
             ->setOptions(['method' => 'post'])
             ->setOptions($options);

        // Default data to false
        $this->formData = false;
    }

    /**
     * Get form name.
     *
     * @return string
     */
    public function getFormName(): string
    {
        if ($this->hasParent()) {
            return $this->getParent()->getFormName() . sprintf('[%s]', $this->getName());
        } else {
            return $this->getName();
        }
    }

    /**
     * Retrieve an external iterator.
     *
     * @return \Traversable
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->elements);
    }

    /**
     * Check if a form element exists.
     *
     * @param string $name Name of form element
     *
     * @return bool
     */
    public function __isset(string $name): bool
    {
        return isset($this->elements[$name]);
    }

    /**
     * Get a form element.
     *
     * @param string $name Name of form element
     *
     * @return \Berlioz\Form\FormElement
     * @throws \Berlioz\Core\Exception\BerliozException If element doesn't exists
     */
    public function __get(string $name): FormElement
    {
        if (isset($this->elements[$name])) {
            return $this->elements[$name];
        } else {
            throw new BerliozException(sprintf('Element named "%s" does not exists', $name));
        }
    }

    /**
     * Add a form element.
     *
     * @param \Berlioz\Form\FormElement $element Form element
     *
     * @return static
     * @throws \Berlioz\Core\Exception\BerliozException If element is not an acceptable object
     * @throws \Berlioz\Core\Exception\BerliozException If the name of element is not valid
     * @throws \Berlioz\Core\Exception\BerliozException If an another element already exists with the same name
     */
    public function add(FormElement $element)
    {
        if (!isset($this->elements[$element->getName()])) {
            $this->elements[$element->getName()] = $element;
            $element->setParent($this);
        } else {
            throw new BerliozException(sprintf('An element named "%s" already exists', $element->getName()));
        }

        return $this;
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
        return b_array_merge_recursive($options,
                                       ['form'       => $this,
                                        'name'       => $this->getName(),
                                        'full_name'  => $this->getFormName(),
                                        'method'     => $this->getOptions()->get('method') ?? 'post',
                                        'attributes' => $this->getOptions()->get('attributes') ?? []]);
    }

    /**
     * Get mapped object.
     *
     * @return mixed
     */
    public function getMapped()
    {
        return $this->mapped;
    }

    /**
     * Get server request.
     *
     * @return \Berlioz\Core\Http\ServerRequest
     */
    public function getServerRequest()
    {
        return $this->serverRequest;
    }

    /**
     * Get form data.
     *
     * @return array
     * @throws \Berlioz\Core\Exception\BerliozException If it's a sub form
     */
    public function getData(): array
    {
        if (!$this->hasParent()) {
            return $this->formData ?: [];
        } else {
            throw new BerliozException('Unable to do that, it is a sub form, not the main form');
        }
    }

    /**
     * Form is submitted ?
     *
     * @return bool
     * @throws \Berlioz\Core\Exception\BerliozException If it's a sub form
     */
    public function isSubmitted(): bool
    {
        if (!$this->hasParent()) {
            return $this->formData !== false;
        } else {
            throw new BerliozException('Unable to do that, it is a sub form, not the main form');
        }
    }

    /**
     * Form is valid ?
     *
     * @return bool
     * @throws \Berlioz\Core\Exception\BerliozException If it's a sub form
     * @todo Valid form
     */
    public function isValid(): bool
    {
        if (!$this->hasParent()) {
            return $this->formData !== false;
        } else {
            throw new BerliozException('Unable to do that, it is a sub form, not the main form');
        }
    }

    /**
     * Handle.
     *
     * @param \Berlioz\Core\Http\ServerRequest $serverRequest Server request
     *
     * @throws \Berlioz\Core\Exception\BerliozException If it's a sub form
     */
    public function handle(ServerRequest $serverRequest)
    {
        if (!$this->hasParent()) {
            // Save server request
            $this->serverRequest = $serverRequest;

            // Submission data
            switch (mb_strtolower($this->getOptions()->get('method'))) {
                case 'get':
                case 'delete':
                    $queryParams = $serverRequest->getQueryParams();
                    $inputData = $queryParams[$this->getName()] ?? false;
                    break;
                case 'post':
                case 'put':
                    $parsedBody = $serverRequest->getParsedBody();
                    if (is_array($parsedBody)) {
                        $inputData = $parsedBody[$this->getName()] ?? false;
                    } else {
                        $inputData = false;
                    }
                    break;
                default:
                    throw new BerliozException(sprintf('Unknown method "%s" for form submission', $this->getOptions()->get('method')));
            }

            // Treat
            if ($inputData !== false) {
                // Add files upload
                $uploadedFiles = $serverRequest->getUploadedFiles();
                $inputData = array_merge($uploadedFiles[$this->getName()] ?? [], $inputData);

                // Treat data
                $this->formData = $this->getInputData($this, $inputData, $this->mapped);
                $this->mapData($this, $this->mapped);
            } else {
                $this->formData = false;
            }
        } else {
            throw new BerliozException('Unable to do that, it is a sub form, not the main form');
        }
    }

    /**
     * Complete data.
     *
     * @param \Berlioz\Form\FormTraversableInterface $formTraversable
     * @param array                                  $inputData
     * @param object                                 $mapped
     *
     * @return array
     * @throws \Berlioz\Core\Exception\BerliozException If it's a sub form
     */
    private function getInputData(FormTraversableInterface $formTraversable, array &$inputData = [], &$mapped = null)
    {
        if (!$this->hasParent()) {
            $data = [];

            /** @var \Berlioz\Form\FormElementInterface $formElement */
            foreach ($formTraversable as $formElement) {
                $inputDataExists = isset($inputData[$formElement->getName()]);

                if ($formElement instanceof FormTraversableInterface) {
                    // Get object to map
                    if (!is_null($mapped)) {
                        $subMapped = b_property_get($mapped, $formElement->getName());
                    }

                    // Complete data
                    if ($inputDataExists) {
                        $data[$formElement->getName()] = $this->getInputData($formElement, $inputData[$formElement->getName()], $subMapped);
                    } else {
                        $null = [];
                        $data[$formElement->getName()] = $this->getInputData($formElement, $null, $subMapped);
                    }
                } else {
                    if ($formElement instanceof FormCollectionInterface) {
                        // @toto Collection treatment
                    } else {
                        if ($formElement instanceof FormType) {
                            if ($inputDataExists) {
                                // Add data
                                if ($formElement->getOptions()->get('trim') == true && is_string($inputData[$formElement->getName()])) {
                                    if (is_array($inputData[$formElement->getName()])) {
                                        $data[$formElement->getName()] = array_map('trim', $inputData[$formElement->getName()]);
                                    } else {
                                        $data[$formElement->getName()] = trim($inputData[$formElement->getName()]);
                                    }
                                } else {
                                    $data[$formElement->getName()] = $inputData[$formElement->getName()];
                                }
                            } else {
                                // Add data
                                $data[$formElement->getName()] = $formElement->getOptions()->get('empty_data');
                            }

                            // Transformer
                            if (!$formElement->getOptions()->is_empty('transformer')) {
                                if ($formElement->getOptions()->is_string('transformer') && $formElement->getOptions()->is_a('transformer', '\Berlioz\Core\Form\FormTransformer')) {
                                    $transformerClass = $formElement->getOptions()->get('transformer');
                                    /** @var \Berlioz\Form\FormTransformer $transformer */
                                    $transformer = new $transformerClass($data[$formElement->getName()], $formElement->getDefaultValue());
                                    $data[$formElement->getName()] = $transformer->result();
                                } else {
                                    throw new BerliozException(sprintf('Invalid option "transformer" for input named "%s", need to be class name and class must implement \Berlioz\Core\Form\FormTransformer interface.', $formElement->getName()));
                                }
                            }
                        }
                    }
                }
            }

            return $data;
        } else {
            throw new BerliozException('Unable to do that, it is a sub form, not the main form');
        }
    }

    /**
     * Map data to mapped object.
     *
     * @param \Berlioz\Form\FormTraversableInterface $formTraversable
     * @param object                                 $mapped
     *
     * @return void
     * @throws \Berlioz\Core\Exception\BerliozException If it's a sub form
     */
    private function mapData(FormTraversableInterface $formTraversable, &$mapped = null)
    {
        if (!$this->hasParent()) {
            if (!empty($this->getData())) {
                /** @var \Berlioz\Form\FormElementInterface $formElement */
                foreach ($formTraversable as $formElement) {
                    if ($formElement instanceof FormTraversableInterface) {
                        // Get object to map
                        if (!is_null($mapped)) {
                            $subMapped = b_property_get($mapped, $formElement->getName());
                        }

                        // Map data
                        $this->mapData($formElement, $subMapped);
                    } else {
                        if ($formElement instanceof FormCollectionInterface) {
                            // @toto Collection treatment
                        } else {
                            if ($formElement instanceof FormType) {
                                // Update mapped
                                if (!is_null($mapped)) {
                                    $mappedValue = b_property_get($mapped, $formElement->getName());
                                    $value = $formElement->getValue();

                                    // For array or ArrayAccess properties
                                    if (is_array($value) && $mappedValue instanceof \ArrayAccess) {
                                        // Clear array
                                        foreach ($mappedValue as $mKey => $mValue) {
                                            unset($mappedValue[$mKey]);
                                        }

                                        foreach ($value as $fValue) {
                                            $mappedValue[] = $fValue;
                                        }
                                    } else {
                                        b_property_set($mapped, $formElement->getName(), $formElement->getValue());
                                    }
                                }
                            }
                        }
                    }
                }
            }
        } else {
            throw new BerliozException('Unable to do that, it is a sub form, not the main form');
        }
    }
}