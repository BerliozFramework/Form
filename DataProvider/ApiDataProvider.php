<?php
/*
 * This file is part of Berlioz framework.
 *
 * @license   https://opensource.org/licenses/MIT MIT License
 * @copyright 2026 Ronan GIRON
 * @author    Ronan GIRON <https://github.com/ElGigi>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code, to the root.
 */

declare(strict_types=1);

namespace Berlioz\Form\DataProvider;

use Berlioz\Form\Form;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class ApiDataProvider.
 */
class ApiDataProvider extends FormDataProvider
{
    /**
     * ApiDataProvider constructor.
     *
     * @param string|null $mapToElement Name of the element receiving the whole parsed body
     * @param bool|null $strictJson Preserve JSON types: only the root object is converted to an array, nested
     *                              objects stay `stdClass` and arrays stay arrays. Not passing `true` is
     *                              deprecated since 3.4; this will be the only behavior in 4.0.
     */
    public function __construct(
        private readonly ?string $mapToElement = null,
        private readonly ?bool $strictJson = null,
    ) {
        if (true !== $this->strictJson) {
            trigger_error(
                'Not passing "strictJson: true" to ' . static::class . ' is deprecated since berlioz/form 3.4; '
                . 'nested JSON objects will be kept as stdClass in v4.',
                E_USER_DEPRECATED,
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function handle(ServerRequestInterface $request, Form $form): array|false
    {
        if (false === in_array(strtolower($request->getMethod()), ['delete', 'patch', 'post', 'put'])) {
            return false;
        }

        $parsedBody = $request->getParsedBody();
        if (is_object($parsedBody)) {
            $parsedBody = match ($this->strictJson) {
                true => get_object_vars($parsedBody),
                default => json_decode(
                    json_encode($parsedBody),
                    associative: true,
                    flags: JSON_THROW_ON_ERROR
                ),
            };
        }

        // A scalar JSON root cannot be submitted to a form
        if (null !== $parsedBody && false === is_array($parsedBody)) {
            $parsedBody = [];
        }

        if (null !== $this->mapToElement) {
            $parsedBody = [$this->mapToElement => $parsedBody];
        }

        return array_replace_recursive(
            $parsedBody ?? [],
            $request->getUploadedFiles() ?? []
        );
    }
}
