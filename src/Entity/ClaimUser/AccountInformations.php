<?php

namespace App\Entity\ClaimUser;

use App\Controller\ProfileController;
use ApiPlatform\Metadata\Get;
// use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\QueryParameter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface; 
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Validator\Constraints as Assert;



/**
 * AccountInformations
 *
 * @ORM\Table(name="account_informations", uniqueConstraints={@ORM\UniqueConstraint(name="email_address_UNIQUE", columns={"email_address"}), @ORM\UniqueConstraint(name="users_id_UNIQUE", columns={"users_id"})})
 * @ORM\Entity
 */

#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/api/profile',
            controller: ProfileController::class . '::getAllProfile',
            parameters: [ 
                'userId' => new QueryParameter()
            ]
        ), 
        new Post(
            uriTemplate: '/api/profile/upload',
            controller: ProfileController::class . '::uploadProfileImage',
            deserialize: false,
            name: 'upload_profile_image'
        )   
    ],
)]
class AccountInformations implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="business_name", type="string", length=150, nullable=false)
     */
    private $businessName;

    /**
     * @var string
     *
     * @ORM\Column(name="business_registration_number", type="string", length=150, nullable=false)
     */
    private $businessRegistrationNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="business_address", type="string", length=250, nullable=false)
     */
    private $businessAddress;

    /**
     * @var string
     *
     * @ORM\Column(name="city", type="string", length=45, nullable=false)
     */
    private $city;

    /**
     * @var string
     *
     * @ORM\Column(name="postal_code", type="string", length=45, nullable=false)
     */
    private $postalCode;

    /**
     * @var string
     *
     * @ORM\Column(name="phone_number", type="string", length=100, nullable=false)
     */
    private $phoneNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="email_address", type="string", length=255, nullable=false)
     */
    private $emailAddress;

    /**
     * @var string|null
     *
     * @ORM\Column(name="password", type="string", length=250, nullable=false)
     */
    private $password;

    /**
     * @var string|null
     *
     * @ORM\Column(name="website", type="string", length=150, nullable=true)
     */
    private $website;

    /**
     * @var \Users
     *
     * @ORM\ManyToOne(targetEntity="Users")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="users_id", referencedColumnName="id")
     * })
     */
    private $users;

    /**
     * @var string
     *
     * @ORM\Column(name="backup_email", type="string", length=255, nullable=false)
     */
    private $backupEmail;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_of_birth", type="datetime", nullable=false, options={"default"="CURRENT_TIMESTAMP"})
     */
    private $dateOfBirth;

    /**
     * @var string|null
     *
     * @ORM\Column(name="nic", type="string", length=50, nullable=true)
     */
    private $nic;

    /**
     * @var string|null
     *
     * @ORM\Column(name="country_of_nationality", type="string", length=50, nullable=true)
     */
    private $countryOfNationality;

    /**
     * @var string|null
     *
     * @ORM\Column(name="home_number", type="string", length=50, nullable=true)
     */
    private $homeNumber;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="kyc", type="datetime", nullable=false, options={"default"="CURRENT_TIMESTAMP"})
     */
    private $kyc;

    /**
     * @var string|null
     *
     * @ORM\Column(name="profile_image", type="string", length=100, nullable=true)
     */
    private $profileImage;


    /**
     * Mot de passe en clair temporaire
     * Non stocké en base
    */
    #[Assert\NotBlank(message: "Le mot de passe est obligatoire.")]
    #[Assert\Length(min: 8, minMessage: "Le mot de passe doit contenir au moins 8 caractères.")]
    #[Assert\Regex(
        pattern: "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).+$/",
        message: "Le mot de passe doit contenir au moins une minuscule, une majuscule, un chiffre et un caractère spécial."
    )]
    private ?string $plainPassword = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBusinessName(): ?string
    {
        return $this->businessName;
    }

    public function setBusinessName(string $businessName): static
    {
        $this->businessName = $businessName;

        return $this;
    }

    public function getBusinessRegistrationNumber(): ?string
    {
        return $this->businessRegistrationNumber;
    }

    public function setBusinessRegistrationNumber(string $businessRegistrationNumber): static
    {
        $this->businessRegistrationNumber = $businessRegistrationNumber;

        return $this;
    }

    public function getBusinessAddress(): ?string
    {
        return $this->businessAddress;
    }

    public function setBusinessAddress(string $businessAddress): static
    {
        $this->businessAddress = $businessAddress;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(string $postalCode): static
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(string $phoneNumber): static
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    public function getEmailAddress(): ?string
    {
        return $this->emailAddress;
    }

    public function setEmailAddress(string $emailAddress): static
    {
        $this->emailAddress = $emailAddress;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function setWebsite(?string $website): static
    {
        $this->website = $website;

        return $this;
    }

    public function getUsers(): ?Users
    {
        return $this->users;
    }

    public function setUsers(?Users $users): static
    {
        $this->users = $users;

        return $this;
    }

    public function getRoles(): array
    {
        if ($this->users && method_exists($this->users, 'getRoleCodes')) {
            return $this->users->getRoleCodes();
        }

        return ['ROLE_USER'];
    }

    public function eraseCredentials()
    {   
        // If you store any temporary, sensitive data on the user, clear it here
    }

    public function getUserIdentifier(): string
    {
        // Return the unique identifier for the user (e.g. email)
        return $this->emailAddress;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $plainPassword): self
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    public function getBackupEmail(): ?string
    {
        return $this->backupEmail;
    }

    public function setBackupEmail(string $backupEmail): static
    {
        $this->backupEmail = $backupEmail;

        return $this;
    }

     public function getDateOfBirth(): ?\DateTime
    {
        return $this->dateOfBirth;
    }

    public function setDateOfBirth(\DateTime $dateOfBirth): self
    {
        $this->dateOfBirth = $dateOfBirth;
        return $this;
    }

    public function getNic(): ?string
    {
        return $this->nic;
    }

    public function setNic(string $nic): static
    {
        $this->nic = $nic;

        return $this;
    }

    public function getCountryOfNationality(): ?string
    {
        return $this->countryOfNationality;
    }

    public function setCountryOfNationality(string $countryOfNationality): static
    {
        $this->countryOfNationality = $countryOfNationality;

        return $this;
    }

    public function getHomeNumber(): ?string
    {
        return $this->homeNumber;
    }

    public function setHomeNumber(string $homeNUmber): static
    {
        $this->homeNumber = $homeNUmber;

        return $this;
    }

     public function getKyc(): ?\DateTime
    {
        return $this->kyc;
    }

    public function setKyc(\DateTime $kyc): self
    {
        $this->dateOfBirth = $kyc;
        return $this;
    }

    
    public function getProfileImage(): ?string
    {
        return $this->profileImage;
    }

    public function setProfileImage(?string $profileImage): self
    {
        $this->profileImage = $profileImage;
        return $this;
    }
}
