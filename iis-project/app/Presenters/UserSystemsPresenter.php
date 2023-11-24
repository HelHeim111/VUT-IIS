<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;
use Nette\Database\Context;

class UserSystemsPresenter extends Nette\Application\UI\Presenter
{
    private $database;

    public function __construct(Context $database)
    {
        parent::__construct();
        $this->database = $database;
    }
    public function renderDefault()
    {
         if (!$this->getUser()->isLoggedIn()) {
            $this->redirect('signin:default');
        }

        $userId = $this->getUser()->getId();
        $user = $this->database->table('Users')->get($userId);

        $this->template->user = $user;
        $this->template->userSystems = $user->related('UserSystems');

    }

}
