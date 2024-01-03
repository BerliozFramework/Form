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
use Berlioz\Form\DataProvider\FormDataProvider;
use Berlioz\Form\Exception\FormException;
use Berlioz\Form\Hydrator\FormHydrator;
use Berlioz\Form\View\TraversableView;
use Psr\Http\Message\ServerRequestInterface;

class Form extends Group
{
    protected FormDataProvider $dataProvider;
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
    public function __construct(string $name, ?object $mapped = null, array $options = [])
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

        $this->setDataProvider($options['dataProvider'] ?? new FormDataProvider());
        $this->mapObject($mapped);
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

    ////////////////
    /// HANDLING ///
    ////////////////

    /**
     * Set a data provider.
     *
     * @param FormDataProvider $dataProvider
     *
     * @return void
     */
    public function setDataProvider(FormDataProvider $dataProvider): void
    {
        $this->dataProvider = $dataProvider;
    }

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

        if (strtolower($request->getMethod()) === strtolower($this->getOption('method'))) {
            $submittedData = $this->dataProvider->handle($request, $this);
            $this->submitted = $submittedData !== false;

            if (true === $this->submitted) {
                $this->submittedData = $submittedData ?: [];
                $this->submitValue($this->submittedData);

                // Hydrate mapped object
                $hydrator = new FormHydrator($this);
                $hydrator->hydrate();
            }
        }
    }
}