<?php
class User
{
    public $userId;
    public $firstName;
    public $lastName;
    public $email;
    public $password;
    public $accessLevelId;
    public $accessLevelName;
    public $isActive;

    public function __construct(
        $userId,
        $firstName,
        $lastName,
        $email,
        $password,
        $accessLevelId,
        $isActive,
        $accessLevelName = null
    ) {
        $this->userId = $userId;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->password = $password;
        $this->accessLevelId = $accessLevelId;
        $this->isActive = $isActive;
        $this->accessLevelName = $accessLevelName;
    }
}

?>