<?php
/*
 * This file is part of Berlioz framework.
 *
 * @license   https://opensource.org/licenses/MIT MIT License
 * @copyright 2021 Ronan GIRON
 * @author    Ronan GIRON <https://github.com/ElGigi>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code, to the root.
 */

declare(strict_types=1);

namespace Berlioz\Form;

use Berlioz\Form\Collector\FormCollector;
use Berlioz\Form\Exception\FormException;
use Berlioz\Form\Hydrator\FormHydrator;
use Berlioz\Form\View\TraversableView;
use Psr\Http\Message\ServerRequestInterface;

class Form extends Group
{
    /** @var bool Submitted? */
    protected bool $submitted = false;
    /** @var array Submitted data */
    protected array $submittedData = [];

    /**
     * Form constructor.
     *
     * @param string $name Name of form
     * @param object|null $mapped Mapped object
     * @param array $options Options
     *
     * @throws FormException
     */
    public function __construct(string $name, object $mapped = null, array $options = [])
    {
        $options['name'] = $name;
        $options = array_replace_recursive(
            [
                'method' => 'post',
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
     * @inheritDoc
     */
    public function buildView(): TraversableView
    {
        $view = parent::buildView();

        if (null === $this->getParent()) {
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
        }

        return $view;
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
     * @param ServerRequestInterface $request
     *
     * @throws FormException
     */
    public function handle(ServerRequestInterface $request)
    {
        // Build
        $this->build();

        $this->submitted = false;

        // Collect mapped object
        $collector = new FormCollector($this);
        $this->setValue($collector->collect());

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
                $hydrator = new FormHydrator($this);
                $hydrator->hydrate();
            }
        }
    }
}