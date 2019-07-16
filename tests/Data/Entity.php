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

namespace Berlioz\Form\Tests\Data;

class Entity
{
    private $firstname;
    private $lastname;
    private $address;
    private $hobbies;
    private $tags;

    public function getFirstname()
    {
        return $this->firstname;
    }

    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;
    }

    public function getLastname()
    {
        return $this->lastname;
    }

    public function setLastname($lastname)
    {
        $this->lastname = $lastname;
    }

    public function getAddress(): ?EntityAddress
    {
        return $this->address;
    }

    public function setAddress(EntityAddress $address)
    {
        $this->address = $address;
    }

    public function getHobbies()
    {
        return $this->hobbies ?? [];
    }

    public function setHobbies(array $hobbies)
    {
        $this->hobbies = $hobbies;
    }

    public function getTags()
    {
        return $this->tags ?? [];
    }

    public function setTags(array $tags)
    {
        $this->tags = $tags;
    }
}