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

namespace Berlioz\Form\Validator;

use Berlioz\Form\Element\ElementInterface;
use Berlioz\Form\Exception\ValidatorException;
use Berlioz\Form\Type\MultipleTypeInterface;
use Berlioz\Form\Validator\Constraint\FileFormatConstraint;
use Berlioz\Http\Message\UploadedFile;

class FileFormatValidator extends AbstractValidator implements ValidatorInterface
{
    /**
     * LengthValidator constructor.
     *
     * @param string $constraint Constraint class
     *
     * @throws ValidatorException
     */
    public function __construct(string $constraint = FileFormatConstraint::class)
    {
        parent::__construct($constraint);
    }

    /**
     * @inheritDoc
     */
    public function validate(ElementInterface $element): array
    {
        $constraints = [];
        $multiple = $element instanceof MultipleTypeInterface && $element->isMultiple();
        $accept = $element->getOption('attributes.accept', []);

        if (empty($accept)) {
            return $constraints;
        }

        $accept = explode(',', $accept);
        $accept = array_map('trim', $accept);
        $accept = array_map('strtolower', $accept);

        $files = $element->getValue();
        if (false === $multiple) {
            $files = [$files];
        }

        /** @var UploadedFile $file */
        foreach ($files as $file) {
            $mime = $file->getMediaType() ?? 'application/octet-stream';
            $extension = null;
            if (false !== ($extensionPos = strrpos($file->getClientFilename(), '.'))) {
                $extension = strtolower(substr($file->getClientFilename(), $extensionPos + 1));
            }

            foreach ($accept as $acceptValue) {
                switch (true) {
                    // Extension
                    case str_starts_with($acceptValue, '.'):
                        if (substr($acceptValue, 1) == $extension) {
                            continue(3);
                        }
                        break;
                    // Mime
                    default:
                        $acceptValue = str_replace('\*', '.+', preg_quote($acceptValue, '#'));
                        if (1 === preg_match('#' . $acceptValue . '#i', $mime)) {
                            continue(3);
                        }

                }
            }

            $constraints[] =
                new $this->constraint(
                    [
                        'filename' => $file->getClientFilename(),
                        'actual' => $mime,
                        'expected' => implode(', ', $accept),
                    ]
                );
        }

        return $constraints;
    }
}