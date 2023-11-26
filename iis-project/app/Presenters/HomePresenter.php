<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;
use Nette\Database\Context;

class HomePresenter extends Nette\Application\UI\Presenter
{
    private $database;

    public function __construct(Context $database)
    {
        $this->database = $database;
    }

    public function renderDefault()
    {
        $systems = $this->database->table('Systems')->fetchAll();

        // Pass the $systems variable to the template
        $this->template->systems = $systems;
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