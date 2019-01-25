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

use Berlioz\Form\Exception\FormException;
use Berlioz\Form\View\ViewInterface;
use Psr\Http\Message\ServerRequestInterface;

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
     * @param string      $name    Name of form
     * @param object|null $mapped  Mapped object
     * @param array       $options Options
     *
     * @throws \Berlioz\Form\Exception\FormException
     */
    public function __construct(string $name, $mapped = null, array $options = [])
    {
        $options['name'] = $name;
        $options = array_replace_recursive(['method'   => 'post',
                                            'mapped'   => !is_null($mapped),
                                            'required' => true],
                                           $options);

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
        $view->mergeVars(['type'       => is_null($this->getParent()) ? 'rootform' : 'form',
                          'id'         => $this->getId(),
                          'name'       => $this->getFormName(),
                          'method'     => $this->getOption('method'),
                          'action'     => $this->getOption('action'),
                          'attributes' => $this->getOption('attributes', [])]);

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

    /**
     * Get submitted data.
     *
     * @return array
     * @throws \Berlioz\Form\Exception\FormException
     */
    public function getSubmittedData(): array
    {
        if (!$this->isSubmitted()) {
            throw new FormException(sprintf('Form "%s" is not submitted', $this->getName()));
        }

        return $this->submittedData;
    }

    /**
     * Set submitted data.
     *
     * @param array $data  Data
     * @param bool  $merge Merge with already submitted data? If no, replace values.
     *
     * @return static
     */
    public function setSubmittedData(array $data, bool $merge = false): Form
    {
        if ($merge) {
            $this->submittedData = array_replace_recursive($this->submittedData ?? [], $data);
        } else {
            $this->submittedData = $data;
        }

        return $this;
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
     * @throws \Berlioz\Form\Exception\PropagationException
     */
    public function handle(ServerRequestInterface $request)
    {
        // Build
        $this->build();

        $this->submitted = false;

        // Collect data
        $collector = new Collector($this);
        $collector->collect();

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
                $this->setValue($this->submittedData, true);

                // Propagate data
                $propagator = new Propagator($this);
                $propagator->propagate();
            }
        }
    }
}