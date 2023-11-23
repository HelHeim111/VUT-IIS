<?php

namespace App\Presenters;

use Nette;

class SignoutPresenter extends Nette\Application\UI\Presenter
{
    public function actionDefault()
    {
        $this->getUser()->logout();
        $this->flashMessage('Byli jste úspěšně odhlášeni.');
        $this->redirect('Home:default');
    }
}
