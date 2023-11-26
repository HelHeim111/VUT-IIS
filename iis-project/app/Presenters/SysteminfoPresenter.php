<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;

class SysteminfoPresenter extends Nette\Application\UI\Presenter
{
    private $database;

    public function __construct(Nette\Database\Context $database)
    {
        parent::__construct();
        $this->database = $database;
    }

    public function renderDefault(int $systemId): void
    {
        $system = $this->database->table('Systems')->get($systemId);
        if (!$system) {
            $this->flashMessage('System nenalezen.', 'error');
            $this->redirect('Home:default');
        }
        $this->template->system = $system;
    
        $userSystems = $this->database->table('UserSystems')
            ->where('system_id', $systemId)
            ->fetchAll();
    
        $this->template->users = [];
        foreach ($userSystems as $userSystem) {
            $this->template->users[] = $this->database->table('Users')
                ->get($userSystem->user_id);
        }
    }

    protected function createComponentSystemEditForm(): Form
    {
        $form = new Form;
        $form->setHtmlAttribute('class', 'ajax');
        $form->addText('system_name', 'Název systému');
        $form->addTextArea('system_description', 'Popis');
        $form->addSubmit('submit', 'Uložit změny');
        $form->onSuccess[] = [$this, 'systemEditFormSucceeded'];
    
        return $form;
    }     

    public function systemEditFormSucceeded(Form $form, \stdClass $values): void
    {
        $systemId = $this->getParameter('systemId');
        $system = $this->database->table('Systems')->get($systemId);
        
        if (!$system) {
            $this->flashMessage('Systém nenalezen.', 'error');
            if ($this->isAjax()) {
                $this->payload->error = true;
                $this->redrawControl('flashMessages');
            } else {
                $this->redirect('this');
            }
            return;
        }
    
        $system->update([
            'system_name' => $values->system_name,
            'system_description' => $values->system_description,
        ]);
    
        $this->flashMessage('Systém byl úspěšně aktualizován.', 'success');
        if ($this->isAjax()) {
            $this->payload->success = true;
            $this->redrawControl();
        } else {
            $this->redirect('this');
        }
    }    

}