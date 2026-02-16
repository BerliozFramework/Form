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

declare(strict_types=1);

namespace Berlioz\Form\DataProvider;

use Berlioz\Form\Form;
use Psr\Http\Message\ServerRequestInterface;

interface DataProviderInterface
{
    /**
     * Handle server request and return data.
     *
     * @param ServerRequestInterface $request
     * @param Form $form
     *
     * @return array|false
     */
    public function handle(ServerRequestInterface $request, Form $form): array|false;
}