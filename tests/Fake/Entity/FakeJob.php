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

namespace Berlioz\Form\Tests\Fake\Entity;

use DateTime;

class FakeJob
{
    /** @var string|null Job title */
    private $title;
    /** @var string|null Company */
    private $company;
    /** @var \DateTime|null Entry date */
    private $entry_date;
    /** @var \Berlioz\Form\Tests\Fake\Entity\FakeAddress|null Company address */
    private $address;

    /**
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param string|null $title
     *
     * @return FakeJob
     */
    public function setTitle(?string $title): FakeJob
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCompany(): ?string
    {
        return $this->company;
    }

    /**
     * @param string|null $company
     *
     * @return FakeJob
     */
    public function setCompany(?string $company): FakeJob
    {
        $this->company = $company;

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getEntryDate(): ?DateTime
    {
        return $this->entry_date;
    }

    /**
     * @param \DateTime|null $entry_date
     *
     * @return FakeJob
     */
    public function setEntryDate(?DateTime $entry_date): FakeJob
    {
        $this->entry_date = $entry_date;

        return $this;
    }

    /**
     * @return \Berlioz\Form\Tests\Fake\Entity\FakeAddress|null
     */
    public function getAddress(): ?FakeAddress
    {
        return $this->address;
    }

    /**
     * @param \Berlioz\Form\Tests\Fake\Entity\FakeAddress|null $address
     *
     * @return FakeJob
     */
    public function setAddress(?FakeAddress $address): FakeJob
    {
        $this->address = $address;

        return $this;
    }
}