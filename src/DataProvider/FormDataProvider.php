<?php
/*
 * This file is part of Berlioz framework.
 *
 * @license   https://opensource.org/licenses/MIT MIT License
 * @copyright 2023 Ronan GIRON
 * @author    Ronan GIRON <https://github.com/ElGigi>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code, to the root.
 */

namespace Berlioz\Form\DataProvider;

use Berlioz\Form\Form;
use Psr\Http\Message\ServerRequestInterface;

class FormDataProvider implements DataProviderInterface
{
    /**
     * Get all submitted data.
     *
     * @param ServerRequestInterface $request
     *
     * @return array
     */
    protected function getSubmittedData(ServerRequestInterface $request): array
    {
        switch (strtolower($request->getMethod())) {
            case 'get':
                return $request->getQueryParams();
            case 'post':
                $parsedBody = $request->getParsedBody();
                false === is_array($parsedBody) && $parsedBody = [];

                return array_replace_recursive(
                    $parsedBody ?: [],
                    $request->getUploadedFiles()
                );
            default:
                return [];
        }
    }

    /**
     * @inheritDoc
     */
    public function handle(ServerRequestInterface $request, Form $form): array|false
    {
        $submittedData = $this->getSubmittedData($request);

        if (false === array_key_exists($form->getName(), $submittedData)) {
            return false;
        }

        return $submittedData[$form->getName()];
    }
}