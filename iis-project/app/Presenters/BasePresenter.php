<?php

namespace App\Presenters;

use Nette;
use Nette\Security\User;

abstract class BasePresenter extends Nette\Application\UI\Presenter
{
    /** @var User */
    private $user;
    private $database;

    public function __construct(User $user, Nette\Database\Context $database)
    {
        parent::__construct();
        $this->user = $user;
        $this->database = $database;
    }

    protected function startup()
    {
        parent::startup();

        if ($this->user->isLoggedIn()) {
            $userId = $this->user->getId();
            $userRole = $this->user->getIdentity()->getRoles()[0];

            // Fetch the user record from the database
            $userRecordRole = $this->database->table('Users')->get($userId)->role;
        
            if ($userRole !== $userRecordRole) {
                $this->user->logout(true);
            }
        }
    }
}
