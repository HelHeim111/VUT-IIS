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
        $this->template->items = $this->database->table('Users')->limit(10);
    }
}