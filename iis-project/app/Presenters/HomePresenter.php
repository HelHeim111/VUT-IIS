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
}