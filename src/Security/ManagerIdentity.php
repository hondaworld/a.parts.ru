<?php


namespace App\Security;


use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class ManagerIdentity implements UserInterface, EquatableInterface
{
    private $managerID;
    private $login;
    private $password_admin;
    private $firstname;
    private $name;
    private $photo;
    private $isHide;
    private $isManager;
    private $isAdmin;
    private $settings_admin;
    private $sections;
    private static $SUPER_ADMIN_ID = 1;
    private array $roles;

    public function __construct(
        int $managerID,
        string $login,
        string $password_admin,
        string $firstname,
        string $name,
        string $photo,
        bool $isHide,
        bool $isManager,
        bool $isAdmin,
        string $settings_admin,
        array $sections
    )
    {
        $this->managerID = $managerID;
        $this->login = $login;
        $this->password_admin = $password_admin;
        $this->firstname = $firstname;
        $this->name = $name;
        $this->photo = $photo;
        $this->isHide = $isHide;
        $this->isManager = $isManager;
        $this->isAdmin = $isAdmin;
        $this->settings_admin = $settings_admin;
        $this->sections = $sections;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->managerID;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return $this->login;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        //$roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles = [];
        if ($this->isManager || $this->isAdmin)
            $roles[] = 'ROLE_USER';

        if ($this->isAdmin)
            $roles[] = 'ROLE_ADMIN';

        if ($this->managerID == self::$SUPER_ADMIN_ID) {
            $roles[] = 'ROLE_ADMIN';
            $roles[] = 'ROLE_SUPER_ADMIN';
        }

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }


    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return $this->password_admin;
    }

    public function setPassword(string $password): self
    {
        $this->password_admin = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->isHide == 0;
    }

    /**
     * @return bool
     */
    public function isManager(): bool
    {
        return $this->isManager == 1 || $this->isAdmin();
    }

    /**
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->isAdmin == 1;
    }

    public function isEqualTo(UserInterface $user): bool
    {
        if (!$user instanceof self) {
            return false;
        }

        return $user->isActive() && $user->isManager();
    }

    /**
     * @return string
     */
    public function getFirstname(): string
    {
        return $this->firstname;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getRole(): string
    {
        if ($this->managerID == self::$SUPER_ADMIN_ID) return 'Суперадмин';
        if ($this->isAdmin) return 'Админ';
        return 'Менеджер';
    }

    /**
     * @return string
     */
    public function getPhoto(): string
    {
        return $this->photo ? 'uploads/manager/' . $this->photo : 'images/avatars/avatar.jpg';
    }

    public function getSettings(): array
    {
        return $this->settings_admin ? json_decode($this->settings_admin, true) : [];
    }

    public function getSections(): array
    {
        return $this->sections;
    }

    public function getActionByEntity(string $entity, string $attribute): bool
    {
        if (!$this->sections) return true;

        foreach ($this->sections as $section) {
            if ($section['entity'] == $entity) {
                if (isset($section['actions'][$attribute])) {
                    if ($section['actions'][$attribute] == 1 || in_array('ROLE_ADMIN', $this->getRoles()))
                        return true;
                    else
                        return false;
                }
            }
        }

        return true;
    }

}