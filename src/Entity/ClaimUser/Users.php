<?php

namespace App\Entity\ClaimUser;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\QueryParameter;
use App\Controller\GetUserProfileController;
use App\Controller\UpdateUserSecurityController;
use App\Controller\UpdateUserWebsiteController;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Users
 *
 * @ORM\Table(name="users", uniqueConstraints={@ORM\UniqueConstraint(name="id_UNIQUE", columns={"id"})})
 * @ORM\Entity
 */
#[ApiResource(
    operations: [
        // Insertion utilisateur
        new Post(
            uriTemplate: '/api/insert',
            controller: GetUserProfileController::class . '::inserUser'
        ), 
        new Post(
            uriTemplate: '/api/auth/send-invite',
            controller: GetUserProfileController::class . '::sendInvite',
            parameters: [ 
                'email'   => new QueryParameter()
            ]
        ), 
        // Verify-link-first-login
        new Post(
            uriTemplate: '/api/auth/verify-link-first-login',
            controller: GetUserProfileController::class . '::verifyLinkFirstLogin',
            parameters: [ 
                'token'   => new QueryParameter()
            ]
        ),
        // Profile utilisateur
        new Get(
            uriTemplate: '/api/profile_claim',
            controller: GetUserProfileController::class . '::__invoke',
            parameters: [ 'email' => new QueryParameter()]
        ), 
        // Utilisateur filtré par rôle 
        new Get(
            uriTemplate: '/api/user_by_role',
            controller: GetUserProfileController::class . '::getUserByRole',
            parameters: [ 'role_id' => new QueryParameter()]
        ),
        // Modification website
        new Patch(
            uriTemplate: '/api/profile/website',
            controller: UpdateUserWebsiteController::class,
            parameters: [ 
                'email'   => new QueryParameter(),  
                'newWebsite'     => new QueryParameter()
            ]
        ),
        // Modification administrative
        new Patch(
            uriTemplate: '/api/profile/administrative',
            controller: GetUserProfileController::class . '::updateAdminSetting',
            parameters: [ 
                'email'                 => new QueryParameter(),  
                'primaryContactName'    => new QueryParameter(),
                'primaryContactPost'    => new QueryParameter(),
                'notification'          => new QueryParameter(),
                'methodNames'           => new QueryParameter(),
            ]
        ),
        // Modification security
        new Patch(
            uriTemplate: '/api/profile/security',
            controller: UpdateUserSecurityController::class,
            parameters: [ 
                'email'             => new QueryParameter(),  
                'newPassword'       => new QueryParameter(),
                'newBackupEmail'    => new QueryParameter(),
            ]
            ),
        // forgot-password
        new Post(
            uriTemplate: '/api/auth/forgot-password',
            controller: GetUserProfileController::class . '::forgotPassword',
            parameters: [ 
                'email'   => new QueryParameter()
            ]
        ),
        // Verify-reset-token
        new Post(
            uriTemplate: '/api/auth/verify-reset-token',
            controller: GetUserProfileController::class . '::verifyResetPassword',
            parameters: [ 
                'token'   => new QueryParameter()
            ]
        ),
        // Reset-password
        new Patch(
            uriTemplate: '/api/auth/reset-password',
            controller: GetUserProfileController::class . '::updateUserPassword',
            parameters: [ 
                'email'   => new QueryParameter(),  
                'newPassword'    => new QueryParameter(),
            ]
        )
    ]
)]
class Users
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
     * @var \DateTime|null
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=true, options={"default"="CURRENT_TIMESTAMP"})
     */
    private $createdAt = 'CURRENT_TIMESTAMP';

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="updated_at", type="datetime", nullable=true, options={"default"="CURRENT_TIMESTAMP"})
     */
    private $updatedAt = 'CURRENT_TIMESTAMP';

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="Roles", inversedBy="users")
     * @ORM\JoinTable(name="user_roles",
     *   joinColumns={
     *     @ORM\JoinColumn(name="users_id", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="roles_id", referencedColumnName="id")
     *   }
     * )
     */
    private $roles = array();

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->roles = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTime $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTime $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return Collection<int, Roles>
     */
    public function getRoles(): Collection
    {
        return $this->roles;
    }

    public function addRole(Roles $role): static
    {
        if (!$this->roles->contains($role)) {
            $this->roles->add($role);
        }

        return $this;
    }

    public function removeRole(Roles $role): static
    {
        $this->roles->removeElement($role);

        return $this;
    }

    public function getRoleCodes(): array
    {
        return $this->roles->map(fn ($role) => $role->getRoleCode())->toArray();
    }

}
