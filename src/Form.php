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

namespace Berlioz\Form;

use Berlioz\Form\Collector\FormCollector;
use Berlioz\Form\Exception\FormException;
use Berlioz\Form\Hydrator\FormHydrator;
use Berlioz\Form\View\ViewInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class Form.
 *
 * @package Berlioz\Form
 */
class Form extends Group
{
    /** @var object|array|null Mapped object or array */
    protected $mapped;
    /** @var bool Submitted? */
    protected $submitted = false;
    /** @var array Submitted data */
    protected $submittedData = [];

    /**
     * Form constructor.
     *
     * @param string $name Name of form
     * @param object|null $mapped Mapped object
     * @param array $options Options
     *
     * @throws \Berlioz\Form\Exception\FormException
     */
    public function __construct(string $name, object $mapped = null, array $options = [])
    {
        $options['name'] = $name;
        $options = array_replace_recursive(
            [
                'method' => 'post',
                'mapped' => !is_null($mapped),
                'required' => true,
            ],
            $options
        );

        parent::__construct($options);

        $this->mapObject($mapped);
        $this->submitted = false;
    }

    /////////////
    /// BUILD ///
    /////////////

    /**
     * @inheritdoc
     */
    public function buildView(): ViewInterface
    {
        $view = parent::buildView();
        $view->mergeVars(
            [
                'type' => is_null($this->getParent()) ? 'rootform' : 'form',
                'id' => $this->getId(),
                'name' => $this->getFormName(),
                'method' => $this->getOption('method'),
                'action' => $this->getOption('action'),
                'submitted' => $this->isSubmitted(),
                'valid' => $this->isValid(),
                'attributes' => $this->getOption('attributes', []),
            ]
        );

        return $view;
    }

    ///////////////
    /// MAPPING ///
    ///////////////

    /**
     * Map an object.
     *
     * @param object|null $object
     *
     * @throws \Berlioz\Form\Exception\FormException
     */
    public function mapObject($object = null)
    {
        if (!is_object($object) && !is_null($object)) {
            throw new FormException(sprintf('Parameter given must be an object, "%s" given', gettype($object)));
        }

        $this->mapped = $object;
    }

    /**
     * Get mapped object.
     *
     * @return object|null
     */
    public function getMappedObject()
    {
        return $this->mapped;
    }

    //////////////////
    /// SUBMISSION ///
    //////////////////

    /**
     * Is submitted?
     *
     * @return bool
     */
    public function isSubmitted(): bool
    {
        return $this->submitted;
    }

    ////////////////
    /// HANDLING ///
    ////////////////

    /**
     * Handle form.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @throws \Berlioz\Form\Exception\FormException
     */
    public function handle(ServerRequestInterface $request)
    {
        // Build
        $this->build();

        $this->submitted = false;

        // Get mapped object
        $mappedObject = $this->getMappedObject();

        // Collect mapped object
        if (!is_null($mappedObject)) {
            $collector = new FormCollector($this);
            $this->setValue($collector->collect($mappedObject));
        }

        if (mb_strtolower($request->getMethod()) === mb_strtolower($this->getOption('method'))) {
            switch (mb_strtolower($request->getMethod())) {
                case 'get':
                    $submittedData = $request->getQueryParams();
                    break;
                case 'post':
                    if (!is_array($parsedBody = $request->getParsedBody())) {
                        $parsedBody = [];
                    }

                    $submittedData = array_replace_recursive($parsedBody, $request->getUploadedFiles());
                    break;
                default:
                    $submittedData = [];
            }

            if (($this->submitted = array_key_exists($this->getName(), $submittedData)) !== false) {
                $this->submittedData = $submittedData[$this->getName()];
                $this->submitValue($this->submittedData);

                // Hydrate mapped object
                if (!is_null($mappedObject)) {
                    $hydrator = new FormHydrator($this);
                    $hydrator->hydrate($mappedObject);
                }
            }
        }
    }
}