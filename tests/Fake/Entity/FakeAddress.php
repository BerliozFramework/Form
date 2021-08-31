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

class FakeAddress
{
    /** @var string|null Address */
    private $address;
    /** @var string|null Address next */
    private $address_next;
    /** @var string|null Zip code */
    private $zip_code;
    /** @var string|null City */
    private $city;
    /** @var string|null Country */
    private $country;

    /**
     * @return string
     */
    public function getAddress(): ?string
    {
        return $this->address;
    }

    /**
     * @param string|null $address
     *
     * @return FakeAddress
     */
    public function setAddress(?string $address): FakeAddress
    {
        $this->address = $address;

        return $this;
    }

    /**
     * @return string
     */
    public function getAddressNext(): ?string
    {
        return $this->address_next;
    }

    /**
     * @param string|null $address_next
     *
     * @return FakeAddress
     */
    public function setAddressNext(?string $address_next): FakeAddress
    {
        $this->address_next = $address_next;

        return $this;
    }

    /**
     * @return string
     */
    public function getZipCode(): ?string
    {
        return $this->zip_code;
    }

    /**
     * @param string|null $zip_code
     *
     * @return FakeAddress
     */
    public function setZipCode(?string $zip_code): FakeAddress
    {
        $this->zip_code = $zip_code;

        return $this;
    }

    /**
     * @return string
     */
    public function getCity(): ?string
    {
        return $this->city;
    }

    /**
     * @param string|null $city
     *
     * @return FakeAddress
     */
    public function setCity(?string $city): FakeAddress
    {
        $this->city = $city;

        return $this;
    }

    /**
     * @return string
     */
    public function getCountry(): ?string
    {
        return $this->country;
    }

    /**
     * @param string|null $country
     *
     * @return FakeAddress
     */
    public function setCountry(?string $country): FakeAddress
    {
        $this->country = $country;

        return $this;
    }
}