<?php
// app/presenters/UserSystemsPresenter.php

namespace App\Presenters;

use Nette;
use Nette\Security\User;
use Nette\Database\Context;
use Nette\Application\UI\Form;

class UserSystemsPresenter extends BasePresenter
{
    private $user;
    private $database;

    public function __construct(User $user, Context $database)
    {
        parent::__construct($user, $database);
        $this->user = $user;
        $this->database = $database;
    }

    public function renderDefault()
    {
        $userId = $this->getUser()->getId();
        $user = $this->database->table('Users')->get($userId);
        $this->template->user = $user;

        if ($this->getUser()->isLoggedIn()) {
            $userSystems = $this->database->table('UserSystems')
                ->where('user_id', $userId)
                ->fetchAll();
            $userCreatedSystems = $this->database->table('Systems')
                ->where('admin_id', $userId)
                ->fetchAll();

            $this->template->userSystems = $userSystems;
            $this->template->userCreatedSystems = $userCreatedSystems;
        } else {
            $this->template->userSystems = [];
            $this->template->userCreatedSystems = [];
        }
    }

    protected function createComponentCreateSystemForm(): Form
    {
        $form = new Form();
        $form->addText('system_name', 'System Name')->setRequired();
        $form->addText('system_description', 'System Description')->setRequired();
        $form->addSubmit('create', 'Create System');
        $form->onSuccess[] = [$this, 'createSystemFormSucceeded'];
        return $form;
    }

    public function createSystemFormSucceeded(Form $form, array $values): void
    {
        $userId = $this->getUser()->getId();
        $system = $this->database->table('Systems')->insert([
            'system_name' => $values['system_name'],
            'system_description' => $values['system_description'],
            'admin_id' => $userId,
        ]);

        $this->flashMessage('System created successfully.', 'success');
        $this->redirect('UserSystems:default');
    }

    public function handleCreateSystem(): void
    {
        $this->redirect('createComponentCreateSystemForm');
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
        $this->redirect('UserSystems:default');
    }

}
