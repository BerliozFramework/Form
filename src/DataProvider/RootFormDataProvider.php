<?php

declare(strict_types=1);

namespace Berlioz\Form\DataProvider;

use Berlioz\Form\Form;
use Psr\Http\Message\ServerRequestInterface;

class RootFormDataProvider extends FormDataProvider
{
    /**
     * @inheritDoc
     */
    public function handle(ServerRequestInterface $request, Form $form): array|false
    {
        return $this->getSubmittedData($request);
    }
}