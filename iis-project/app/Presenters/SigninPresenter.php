<?php
declare(strict_types=1);

namespace App\Presenters;

use Nette;
use App\Forms;
use Nette\Application\UI\Form;


final class SigninPresenter extends Nette\Application\UI\Presenter
{
    public $backlink = '';

    private $signinFactory;

    public function __construct(Forms\SigninFormFactory $signinFactory)
    {
        parent::__construct();
        $this->signinFactory = $signinFactory;
    }

    protected function createComponentSigninForm(): Form
    {
        return $this->signinFactory->create(function (): void {
            $this->restoreRequest($this->backlink);
            $this->redirect('Home:default');
        });
    }

    public function processForm(Form $form, array $values) : void
    {
        $this->redirect('Home:default');
    }
}