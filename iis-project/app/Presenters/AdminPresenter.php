<?php

namespace App\Presenters;

use Nette;

class AdminPresenter extends Nette\Application\UI\Presenter
{
    // Called before rendering the dashboard template
    public function actionDashboard()
    {
        // Check if the user is logged in and has the 'admin' role
        if (!$this->user->isLoggedIn() || !$this->user->isInRole('admin')) {
            // If not, redirect to the sign-in page or another appropriate page
            $this->flashMessage('Přístup odepřen. Pouze administrátoři mohou vstoupit.', 'error');
            if ($this->user->isLoggedIn()) {
                $this->redirect('Home:default');
            } else {
                $this->redirect('Signin:default');
            }
        }

        // Additional logic for the dashboard can be placed here
    }

    // Renders the dashboard template
    public function renderDashboard()
    {
        // Pass data to the template, if needed
    }
}
