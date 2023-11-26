<?php

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;
use Nette\Security\Passwords;
use Nette\Database\Context;
use Nette\Security\User;

class SignupPresenter extends BasePresenter
{
    private $passwords;
    private $database;
    private $user;

    public function __construct(User $user, Passwords $passwords, Context $database)
    {   
        parent::__construct($user, $database);
        $this->user = $user;
        $this->passwords = $passwords;
        $this->database = $database;
    }

    protected function createComponentSignupForm(): Form
    {
        $form = new Form;

        // Add form fields
        $form->addText('username', 'Uživatelské jméno:')
            ->setRequired('Prosím zadejte uživatelské jméno.');

        $form->addPassword('password', 'Heslo:')
            ->setRequired('Prosím zadejte heslo.');

        $form->addPassword('passwordVerify', 'Potvrďte heslo:')
            ->setRequired('Prosím zadejte heslo znovu.')
            ->addRule(Form::EQUAL, 'Hesla se neshodují.', $form['password']);

        $form->addSubmit('register', 'Registrovat');

        // Define what happens after form is submitted
        $form->onSuccess[] = [$this, 'signupFormSucceeded'];

        return $form;
    }

    public function signupFormSucceeded(Form $form, \stdClass $values): void
    {
        // Process user registration
        if ($values->password !== $values->passwordVerify) {
            $this->flashMessage("Hesla se neshodují.", "error");
            return;
        }

        // Check if the username already exists
        $existingUser = $this->database->table('Users')->where('username', $values->username)->fetch();
        if ($existingUser) {
            $form->addError('Uživatelské jméno již existuje.');
            return;
        }

        // Hash the password
        $hashedPassword = $this->passwords->hash($values->password);

        // Save the new user to the database
        $this->database->table('Users')->insert([
            'username' => $values->username,
            'password' => $hashedPassword,
            'role' => 'user',
        ]);

        $this->flashMessage("Registrace byla úspěšná. Nyní se můžete přihlásit.", "success");
        $this->redirect('Signin:default'); // Redirect to the login page
    }
}
