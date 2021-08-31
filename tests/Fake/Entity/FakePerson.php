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

use ArrayObject;
use DateTime;

class FakePerson
{
    /** @var string Last name */
    private $last_name;
    /** @var string First name */
    private $first_name;
    /** @var string Sex */
    private $sex;
    /** @var \DateTime Birthday */
    private $birthday;
    /** @var \Berlioz\Form\Tests\Fake\Entity\FakeAddress[] Addresses */
    private $addresses = [];
    /** @var \ArrayObject Hobbies */
    private $hobbies;
    /** @var \Berlioz\Form\Tests\Fake\Entity\FakeJob|null Job */
    private $job;

    /**
     * @return string|null
     */
    public function getLastName(): ?string
    {
        return $this->last_name;
    }

    /**
     * @param string|null $last_name
     *
     * @return FakePerson
     */
    public function setLastName(?string $last_name): FakePerson
    {
        $this->last_name = $last_name;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getFirstName(): ?string
    {
        return $this->first_name;
    }

    /**
     * @param string|null $first_name
     *
     * @return FakePerson
     */
    public function setFirstName(?string $first_name): FakePerson
    {
        $this->first_name = $first_name;

        return $this;
    }

    /**
     * @return string
     */
    public function getSex(): string
    {
        return $this->sex;
    }

    /**
     * @param string $sex
     *
     * @return FakePerson
     */
    public function setSex(string $sex): FakePerson
    {
        $this->sex = $sex;

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getBirthday(): ?DateTime
    {
        return $this->birthday;
    }

    /**
     * @param \DateTime|null $birthday
     *
     * @return FakePerson
     */
    public function setBirthday(?DateTime $birthday): FakePerson
    {
        $this->birthday = $birthday;

        return $this;
    }

    /**
     * @return \Berlioz\Form\Tests\Fake\Entity\FakeAddress[]
     */
    public function getAddresses(): array
    {
        return $this->addresses;
    }

    /**
     * @param \Berlioz\Form\Tests\Fake\Entity\FakeAddress[] $addresses
     *
     * @return FakePerson
     */
    public function setAddresses(array $addresses): FakePerson
    {
        $this->addresses = $addresses;

        return $this;
    }

    /**
     * @return \ArrayObject|null
     */
    public function getHobbies(): ?ArrayObject
    {
        return $this->hobbies;
    }

    /**
     * @param \ArrayObject $hobbies
     *
     * @return FakePerson
     */
    public function setHobbies(ArrayObject $hobbies): FakePerson
    {
        $this->hobbies = $hobbies;

        return $this;
    }

    /**
     * @return \Berlioz\Form\Tests\Fake\Entity\FakeJob|null
     */
    public function getJob(): ?FakeJob
    {
        return $this->job;
    }

    /**
     * @param \Berlioz\Form\Tests\Fake\Entity\FakeJob|null $job
     *
     * @return FakePerson
     */
    public function setJob(?FakeJob $job): FakePerson
    {
        $this->job = $job;

        return $this;
    }
}