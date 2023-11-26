<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;
use Nette\Security\User;
use Nette\Database\Context;

class SysteminfoPresenter extends BasePresenter
{
    private $database;
    private $user;
    private $system;

    public function __construct(User $user, Context $database)
    {
        parent::__construct($user, $database);
        $this->user = $user;
        $this->database = $database;
    }

    public function renderDefault(int $systemId): void
    {
        $system = $this->database->table('Systems')->get($systemId);
        $this->template->systemId = $systemId;
        if (!$system) {
            $this->flashMessage('System nenalezen.', 'error');
            $this->redirect('Home:default');
        }
        $this->template->system = $system;
        $userSystems = $this->database->table('UserSystems')
            ->where('system_id', $systemId)
            ->fetchAll();

        $this->template->systemOwner = $this->database->table('Users')->get($system->admin_id);
        
        $this->template->users = [];
        foreach ($userSystems as $userSystem) {
            $this->template->users[] = $this->database->table('Users')
                ->get($userSystem->user_id);
        }

        $deviceSystems = $this->database->table('DeviceSystem')
        ->where('system_id', $systemId)
        ->fetchAll();

        $this->template->devices = [];
        foreach ($deviceSystems as $deviceSystem) {
            $this->template->devices[] = $this->database->table('Devices')
                ->get($deviceSystem->device_id);
        }
    }

    protected function createComponentSystemEditForm(): Form
    {
        $form = new Form;
        $form->setHtmlAttribute('class', 'ajax');
        $form->addText('system_name', 'Název systému');
        $form->addTextArea('system_description', 'Popis');
        $form->addSubmit('submit', 'Uložit změny');
        $form->onSuccess[] = [$this, 'systemEditFormSucceeded'];
    
        return $form;
    }     

    public function systemEditFormSucceeded(Form $form, \stdClass $values): void
    {
        $systemId = $this->getParameter('systemId');
        $system = $this->database->table('Systems')->get($systemId);
        
        if (!$system) {
            $this->flashMessage('Systém nenalezen.', 'error');
            if ($this->isAjax()) {
                $this->payload->error = true;
                $this->redrawControl('flashMessages');
            } else {
                $this->redirect('this');
            }
            return;
        }
    
        $system->update([
            'system_name' => $values->system_name,
            'system_description' => $values->system_description,
        ]);
    
        $this->flashMessage('Systém byl úspěšně aktualizován.', 'success');
        if ($this->isAjax()) {
            $this->payload->success = true;
            $this->redrawControl();
        } else {
            $this->redirect('this');
        }
    }    

    protected function createComponentAddUserForm(): Form
    {
        $form = new Form;
        $form->setHtmlAttribute('class', 'ajax');
        $systemId = $this->getParameter('systemId');
        
        $existingUserIds = $this->database->table('UserSystems')
            ->where('system_id', $this->getParameter('systemId'))
            ->fetchPairs('user_id', 'user_id');
        $existingUserIds[] = $this->database->table('Systems')
            ->get($this->getParameter('systemId'))->admin_id;

        // Fetch usernames excluding the ones in the existingUserIds array
        $userNames = $this->database->table('Users')
            ->where('user_id NOT IN ?', $existingUserIds)
            ->fetchPairs('user_id', 'username');

        $form->addSelect('username', 'Uživatel:')
            ->setRequired('Prosím vyberte uživatele.')
            ->setItems($userNames)
            ->setPrompt('Vyberte uživatele');

        
        $form->addHidden('systemId', $systemId);

        $form->addSubmit('create', 'Přidat uživatele');

        $form->onSuccess[] = [$this, 'addUserFormSucceeded'];

        return $form;
    }

    public function addUserFormSucceeded(Form $form, array $values): void
    {
        $userId = $values['username'];
        $user = $this->database->table('Users')->get($userId);
        $systemId = $values['systemId'];
        $system = $this->database->table('Systems')->get($systemId);

        if ($user && $systemId) {
            $this->database->table('UserSystems')->insert([
                'user_id' => $user->user_id,
                'system_id' => $system,
            ]);

            $this->flashMessage('Uživatel byl úspěšně pridan.', 'success');
            if ($this->isAjax()) {
                $this->payload->success = true;
                $this->redrawControl();
            } else {
                $this->redirect('this');
            }
        }
        else {
            $this->flashMessage('Toto uživatelské ID neexistuje.', 'error');
            if ($this->isAjax()) {
                $this->payload->error = true;
                $this->redrawControl('flashMessages');
            } else {
                $this->redirect('this');
            }
            return;
        }

    }

    public function actionDelete(int $systemId, int $userId): void
    {
        $this->database->table('UserSystems')
            ->where('user_id', $userId)
            ->where('system_id', $systemId)
            ->delete();

        $this->flashMessage('Uživatel byl odstraněn ze systému.', 'success');
        $this->redirect('Systeminfo:default', $systemId);
    }

}