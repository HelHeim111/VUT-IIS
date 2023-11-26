<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;
use Nette\Database\Context;
use Nette\Security\User;

class HomePresenter extends BasePresenter
{
    private $database;
    private $user;

    public function __construct(User $user, Nette\Database\Context $database)
    {
        parent::__construct($user, $database);
        $this->database = $database;
        $this->user = $user;
    }

    public function renderDefault()
    {
        $systems = $this->database->table('Systems')->fetchAll();

        // Pass the $systems variable to the template
        $this->template->systems = $systems;
        $this->template->user= $this->user;
    }

    public function actionDelete($systemId): void
    {
        $system = $this->database->table('Systems')->get($systemId);
        if ($system) {
            $system->delete();
            $this->flashMessage('System byl úspěšně smazán.', 'success');
        } else {
            $this->flashMessage('System nenalezen.', 'error');
        }
        $this->redirect('Home:default');
    }
}