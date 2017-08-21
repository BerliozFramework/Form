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


use Berlioz\Core\App;
use Berlioz\Core\Exception\RuntimeException;
use Berlioz\Core\ExtensionInterface;
use Berlioz\Core\Services\Template\DefaultEngine;

class BerliozExtension implements ExtensionInterface
{
    /** @var \Berlioz\Core\Services\Template\TemplateInterface Template engine */
    private $templateEngine;

    /**
     * @inheritdoc
     * @throws \Berlioz\Core\Exception\RuntimeException if not a good templating service
     */
    public function init(App $app): void
    {
        if ($app->hasService('templating')) {
            $this->templateEngine = $app->getService('templating');

            if ($this->templateEngine instanceof DefaultEngine) {
                $this->templateEngine->getTwig()->addExtension(new TwigExtension($this->templateEngine));
            } else {
                throw new RuntimeException('Unable to init plugin without \Berlioz\Core\Services\Template\DefaultEngine templating service.');
            }
        } else {
            throw new RuntimeException('Unable to init plugin without templating service.');
        }
    }

    /**
     * @inheritdoc
     */
    public function isInitialized(): bool
    {
        return !is_null($this->templateEngine);
    }
}