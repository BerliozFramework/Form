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
use Berlioz\Form\Hydrator\HydrationQueue;
use Berlioz\Form\View\TraversableView;
use Closure;
use Psr\Http\Message\ServerRequestInterface;

class Form extends Group
{
    protected FormDataProvider $dataProvider;
    /** @var array Submitted data */
    protected array $submittedData = [];
    /** @var bool Submission waiting for an explicit apply() */
    protected bool $staged = false;
    /** @var bool Submission applied on mapped data */
    protected bool $applied = false;
    /** @var Closure[] Callbacks deferred until apply() */
    protected array $deferredCallbacks = [];

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
     * By default, a submission is applied on mapped data immediately, valid or not.
     * With `$apply = false`, mapped data stays untouched and collection callbacks are deferred
     * until an explicit call to {@see Form::apply()}, typically after validation.
     *
     * @param ServerRequestInterface $request
     * @param bool $apply Apply submission on mapped data
     *
     * @throws FormException
     */
    public function handle(ServerRequestInterface $request, bool $apply = true): void
    {
        // Build
        $this->build();
        $this->resetConstraints();

        $this->submitted = false;
        $this->staged = false;
        $this->applied = false;
        $this->deferredCallbacks = [];

        // Collect mapped object
        $collector = new FormCollector($this);
        $this->setValue($collector->collect());

        if (strtolower($request->getMethod()) === strtolower((string)$this->getOption('method'))) {
            $submittedData = $this->dataProvider->handle($request, $this);

            if ($submittedData !== false) {
                $this->submittedData = $submittedData ?: [];
                $this->staged = !$apply;
                $this->submitValue($this->submittedData);

                if (true === $apply) {
                    $this->apply();
                }
            }
        }
    }

    /**
     * Apply submission on mapped data.
     *
     * Every conversion is done before the first write: if one fails, mapped data stays untouched
     * and deferred callbacks are not called. Then deferred collection callbacks are called,
     * and planned writes are committed.
     *
     * Validation is not checked: call {@see Form::isValid()} before, an invalid submission is applied too.
     *
     * @throws FormException If form is not submitted or submission already applied
     */
    public function apply(): void
    {
        if (false === $this->isSubmitted()) {
            throw new FormException('Unable to apply a form not submitted');
        }

        if (true === $this->applied) {
            throw new FormException('Form submission already applied');
        }

        $queue = new HydrationQueue();
        (new FormHydrator($this))->plan(null, $queue);

        $this->applied = true;
        $this->staged = false;

        $callbacks = $this->deferredCallbacks;
        $this->deferredCallbacks = [];
        foreach ($callbacks as $callback) {
            $callback();
        }

        $queue->commit();
    }

    /**
     * Defer a callback until the submission is applied.
     *
     * @param Closure $callback
     *
     * @return bool False if the form does not wait for an apply(), callback must be called immediately
     * @internal
     */
    public function deferCallback(Closure $callback): bool
    {
        if (false === $this->staged) {
            return false;
        }

        $this->deferredCallbacks[] = $callback;

        return true;
    }
}
