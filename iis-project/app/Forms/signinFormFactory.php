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
        $form->addText('username', 'Uživatelské jméno')
            ->setRequired(true);
        $form->addPassword('password', 'Heslo:')
            ->setRequired(true);
        $form->addSubmit('send', 'Přihlásit se')
            ->setHtmlAttribute('class', 'submit');
        $form->onSuccess[] = function (Form $form, \stdClass $values) use ($onSuccess): void{
            try {
                $this->user->setExpiration('2 hours');
                $this->user->login($values->username, $values->password);
            } catch (Nette\Security\AuthenticationException $e) {
                $form->addError("Nespravné uživatelské jméno nebo heslo.");
                return;
            }
          $onSuccess();
        };
        return $form;
    }
}

