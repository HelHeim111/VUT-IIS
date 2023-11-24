<?php
// app/presenters/SysteminfoPresenter.php

declare(strict_types=1);

namespace App\Presenters;

use Nette;
use Nette\Database\Context;

class SysteminfoPresenter extends Nette\Application\UI\Presenter
{
    private $database;

    // Inject the database context in the constructor
    public function __construct(Context $database)
    {
        parent::__construct();
        $this->database = $database;
    }
    public function renderDefault($id)
    {
        // Fetch data for the system with ID $id from the database
        $systemData = $this->database->table('Systems')->get($id);

        // Pass data to the template
        $this->template->system = $systemData;
    }

}
