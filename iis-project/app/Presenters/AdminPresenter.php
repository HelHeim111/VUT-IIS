<?php

namespace App\Presenters;

use Nette;
use Nette\Database\Context;
use Nette\Application\UI\Form;
use Nette\Security\Passwords;

class AdminPresenter extends Nette\Application\UI\Presenter
{
    private $database;
    private $passwords;

    public function __construct(Context $database, Passwords $passwords)
    {
        $this->database = $database;
        $this->passwords = $passwords;
    }

    public function actionDashboard()
    {
        // Check if the user is logged in and has the 'admin' role
        if (!$this->user->isLoggedIn() || !$this->user->isInRole('admin')) {
            // If not, redirect to the sign-in page or another appropriate page
            $this->flashMessage('Přístup odepřen. Pouze administrátoři mohou vstoupit.', 'error');
            if ($this->user->isLoggedIn()) {
                $this->redirect('Home:default');
            } else {
                $this->redirect('Signin:default');
            }
        }
    }

    // Renders the dashboard template
    public function renderDashboard()
    {
        $this->template->users = $this->database->table('Users')->fetchAll();
    }

    public function renderEditUserForm()
    {
        $this->template->form = $this['editUserForm'];
    }

    // Action to delete a user
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

    // Action to display edit form
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

    // Create user form component
    protected function createComponentCreateUserForm(): Form
    {
        $form = new Form;
        $form->addText('username', 'Uživatelské jméno:')
                ->setRequired('Prosím zadejte uživatelské jméno.');

        $form->addPassword('password', 'Heslo:')
                ->setRequired('Prosím zadejte heslo.');

        $form->addSelect('role', 'Role:', [
            'user' => 'Uživatel',
            'admin' => 'Administrátor'
        ])->setRequired('Prosím vyberte roli.');

        $form->addSubmit('create', 'Vytvořit uživatele');
        $form->onSuccess[] = [$this, 'createUserFormSucceeded'];

        return $form;
    }

    // Handle create user form submission
    public function createUserFormSucceeded(Form $form, \stdClass $values): void
    {
        // Check if the username already exists
        $existingUser = $this->database->table('Users')->where('username', $values->username)->fetch();
        if ($existingUser) {
            $form->addError('Uživatelské jméno již existuje.');
            return;
        }
        
        $this->database->table('Users')->insert([
            'username' => $values->username,
            'password' => $this->passwords->hash($values->password),
            'role' => $values->role,
        ]);

        $this->flashMessage('Uživatel byl úspěšně vytvořen.', 'success');
        $this->redirect('Admin:dashboard');
    }

    // Edit user form component
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
            'admin' => 'Administrátor'
        ])->setDefaultValue($user->role)
            ->setRequired('Prosím vyberte roli.');

        $form->addSubmit('save', 'Uložit změny');
        $form->onSuccess[] = [$this, 'editUserFormSucceeded'];

        return $form;
    }

    // Handle edit user form submission
    public function editUserFormSucceeded(Form $form, \stdClass $values): void
    {
        $userId = $this->getParameter('userId');
        $user = $this->database->table('Users')->get($userId);

        $dataToUpdate = [
            'username' => $values->username,
            'role' => $values->role,
        ];

        if (!empty($values->password)) {
            $dataToUpdate['password'] = Passwords::hash($values->password);
        }

        $user->update($dataToUpdate);
        $this->flashMessage('Uživatelské údaje byly aktualizovány.', 'success');
        $this->redirect('Admin:dashboard');
    } 
}
