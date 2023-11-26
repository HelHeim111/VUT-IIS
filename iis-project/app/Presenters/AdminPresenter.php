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

    // Handle create user form submission
    public function createUserFormSucceeded(Form $form, \stdClass $values): void
    {
        // Check if the username already exists
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
            'admin' => 'Administrátor',
            'broker' => 'Broker'
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
    
    // Update the username and role
    $dataToUpdate = [
        'username' => $values->username,
        'role' => $values->role,
    ];

    // Check if a new password was provided
    if (!empty($values->password)) {
        // Hash and update the password
        $dataToUpdate['password'] = $this->passwords->hash($values->password);
    }

    // Update the user's data
    $user->update($dataToUpdate);

    $this->flashMessage('Uživatelské údaje byly aktualizovány.', 'success');
    // Redirect to Admin:dashboard after update
    $this->redirect('Admin:dashboard');
}
    
}
