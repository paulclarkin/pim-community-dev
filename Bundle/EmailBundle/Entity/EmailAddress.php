<?php

namespace Oro\Bundle\EmailBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Email Address
 *
 * @ORM\Table(name="oro_email_address",
 *      uniqueConstraints={@ORM\UniqueConstraint(name="oro_email_address_uq", columns={"email_address"})},
 *      indexes={@ORM\Index(name="oro_email_address_idx", columns={"email_address"})})
 * @ORM\Entity
 */
class EmailAddress
{
    // TODO: This is a temporary stub and it must be deleted after our implementation of flexible entity is finished
    public $owner;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="email_address", type="string", length=255)
     */
    protected $emailAddress;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get a 'pure' email address.
     * It means that if the full email is "John Smith" <john@example.com> the email address is john@example.com
     *
     * @return string
     */
    public function getEmailAddress()
    {
        return $this->emailAddress;
    }

    /**
     * Set a 'pure' email address.
     * It means that if the full email is "John Smith" <john@example.com> the email address is john@example.com
     *
     * @param string $emailAddress
     * @return $this
     */
    public function setEmailAddress($emailAddress)
    {
        $this->emailAddress = $emailAddress;

        return $this;
    }
}
