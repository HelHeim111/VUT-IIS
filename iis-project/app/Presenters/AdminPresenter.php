<?php

namespace App\Presenters;

use Nette;
use Nette\Database\Context;
use Nette\Application\UI\Form;
use Nette\Security\Passwords;
use Nette\Security\User;

class AdminPresenter extends BasePresenter
{
    private $database;
    private $passwords;
    private $user;

    public function __construct(User $user, Context $database, Passwords $passwords)
    {
        parent::__construct($user, $database);
        $this->database = $database;
        $this->user = $user;
        $this->passwords = $passwords;
    }

    public function actionDashboard()
    {
        if (!$this->user->isLoggedIn() || !$this->user->isInRole('admin')) {
            $this->flashMessage('Přístup odepřen. Pouze administrátoři mohou vstoupit.', 'error');
            if ($this->user->isLoggedIn()) {
                $this->redirect('Home:default');
            } else {
                $this->redirect('Signin:default');
            }
        }
    }

    public function renderDashboard()
    {
        $users = $this->database->table('Users');

        if (!$users) {
            $this->flashMessage('Uzivatele nenalezene.', 'error');
            $this->redirect('Home:default');
        }

        $this->template->users = $users;
        $this->template->user = $this->user;
    }

    public function renderEditUserForm()
    {
        $this->template->form = $this['editUserForm'];
    }

    public function actionDelete($userId)
    {
        $user = $this->database->table('Users')->get($userId);
        if ($user) {
            $user->delete();
            $this->flashMessage('Uživatel byl úspěšně smazán.', 'success');
        } else {
            $this->flashMessage('Uživatel nenalezen.', 'error');
        }
        $this->redirect('Admin:dashboard');
    }

    public function actionEditUserForm($userId)
    {
        $user = $this->database->table('Users')->get($userId);
        if (!$user) {
            $this->flashMessage('Uživatel nenalezen.', 'error');
            $this->redirect('Admin:dashboard');
        }

        $form = $this['editUserForm'];
        $form->setDefaults($user->toArray());
    }    

    protected function createComponentCreateUserForm(): Form
    {
        $form = new Form;
        $form->setHtmlAttribute('class', 'ajax');
        $form->addText('username', 'Uživatelské jméno:')
                ->setRequired('Prosím zadejte uživatelské jméno.')
                ->setHtmlAttribute('placeholder', 'Prosím zadejte jméno');

        $form->addPassword('password', 'Heslo:')
                ->setRequired('Prosím zadejte heslo.')
                ->setHtmlAttribute('placeholder', 'Prosím zadejte heslo');

        $form->addSelect('role', 'Role:', [
            'user' => 'Uživatel',
            'admin' => 'Administrátor',
            'broker' => 'Broker'
        ]);

        $form->addSubmit('create', 'Vytvořit uživatele');
        $form->onSuccess[] = [$this, 'createUserFormSucceeded'];

        return $form;
    }

    public function createUserFormSucceeded(Form $form, \stdClass $values): void
    {
        $existingUser = $this->database->table('Users')->where('username', $values->username)->fetch();
        if ($existingUser) {
            $this->flashMessage('Uživatelské jméno již existuje.', 'error');
            if ($this->isAjax()) {
                $this->payload->error = true;
                $this->redrawControl('flashMessages');
            } else {
                $this->redirect('this');
            }
            return;
        }
        
        $this->database->table('Users')->insert([
            'username' => $values->username,
            'password' => $this->passwords->hash($values->password),
            'role' => $values->role,
        ]);

        $this->flashMessage('Uživatel byl úspěšně vytvořen.', 'success');
        if ($this->isAjax()) {
            $this->payload->success = true;
            $this->redrawControl();
        } else {
            $this->redirect('this');
        }
    }

    protected function createComponentEditUserForm(): Form
    {
        $form = new Form;
        
        $userId = $this->getParameter('userId');
        $user = $this->database->table('Users')->get($userId);

        $form->addText('username', 'Uživatelské jméno:')
                ->setDefaultValue($user->username)
                ->setRequired('Prosím zadejte uživatelské jméno.');

        $form->addPassword('password', 'Heslo:')
                ->setRequired(false);

        $form->addSelect('role', 'Role:', [
            'user' => 'Uživatel',
            'admin' => 'Administrátor',
            'broker' => 'Broker'
        ])->setDefaultValue($user->role)
            ->setRequired('Prosím vyberte roli.');

        $form->addSubmit('save', 'Uložit změny');
        $form->onSuccess[] = [$this, 'editUserFormSucceeded'];

        return $form;
    }

public function editUserFormSucceeded(Form $form, \stdClass $values): void
{
    $userId = $this->getParameter('userId');
    $user = $this->database->table('Users')->get($userId);
    
    $dataToUpdate = [
        'username' => $values->username,
        'role' => $values->role,
    ];

    if (!empty($values->password)) {
        $dataToUpdate['password'] = $this->passwords->hash($values->password);
    }

    $user->update($dataToUpdate);

    $this->flashMessage('Uživatelské údaje byly aktualizovány.', 'success');
    $this->redirect('Admin:dashboard');
}
    
}
