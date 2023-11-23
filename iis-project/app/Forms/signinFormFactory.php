<?php

declare(strict_types=1);

namespace App\Forms;

use Nette;
use Nette\Application\UI\Form;
use Nette\Security\User;

final class SigninFormFactory
{
    use Nette\SmartObject;

    private $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function create(callable $onSuccess): Form
    {
        $form = new Form();
        $form->addText('username', 'Uživatelské jméno:')
            ->setRequired('Prosím zadejte uživatelské jméno.');
        $form->addPassword('password', 'Heslo:')
            ->setRequired('Prosím zadejte heslo.');
        $form->addSubmit('send', 'Přihlásit se')
            ->setHtmlAttribute('class', 'submit');

        $form->onSuccess[] = function (Form $form, \stdClass $values) use ($onSuccess): void {
            try {
                $this->user->setExpiration('2 hours');
                $this->user->login($values->username, $values->password);
                $role = $this->user->getIdentity()->role;
                $onSuccess($role);
            } catch (Nette\Security\AuthenticationException $e) {
                $form->addError('Nesprávné uživatelské jméno nebo heslo.');
                return;
            }
        };

        return $form;
    }
}
