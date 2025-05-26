<?php

declare(strict_types=1);

namespace Berlioz\Form\DataProvider;

use Berlioz\Form\Form;
use Psr\Http\Message\ServerRequestInterface;

class ApiDataProvider extends FormDataProvider
{
    public function __construct(private ?string $mapToElement = null)
    {
    }

    /**
     * @inheritDoc
     */
    public function handle(ServerRequestInterface $request, Form $form): array|false
    {
        if (false === in_array(strtolower($request->getMethod()), ['post', 'put'])) {
            return false;
        }

        $parsedBody = $request->getParsedBody();
        if (is_object($parsedBody)) {
            $parsedBody = json_decode(
                json_encode($parsedBody),
                associative: true,
                flags: JSON_THROW_ON_ERROR
            );

            false === is_array($parsedBody) && $parsedBody = [];
        }

        if (null !== $this->mapToElement) {
            $parsedBody = [$this->mapToElement => $parsedBody];
        }

        return array_replace_recursive(
            $parsedBody ?: [],
            $request->getUploadedFiles() ?? []
        );
    }
}