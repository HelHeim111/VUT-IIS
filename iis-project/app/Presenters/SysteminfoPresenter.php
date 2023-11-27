<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;
use Nette\Security\User;
use Nette\Database\Context;

class SysteminfoPresenter extends BasePresenter
{
    private $database;
    private $user;
    private $system;

    public function __construct(User $user, Context $database)
    {
        parent::__construct($user, $database);
        $this->user = $user;
        $this->database = $database;
    }

    public function renderDefault(int $systemId): void
    {
        $system = $this->database->table('Systems')->get($systemId);
        $this->template->systemId = $systemId;
        if (!$system) {
            $this->flashMessage('System nenalezen.', 'error');
            $this->redirect('Home:default');
        }
        $this->template->system = $system;
        $userSystems = $this->database->table('UserSystems')
            ->where('system_id', $systemId)
            ->fetchAll();

        $this->template->systemOwner = $this->database->table('Users')->get($system->admin_id);
        
        $this->template->users = [];
        foreach ($userSystems as $userSystem) {
            $this->template->users[] = $this->database->table('Users')
                ->get($userSystem->user_id);
        }

        $deviceSystems = $this->database->table('DeviceSystem')
        ->where('system_id', $systemId)
        ->fetchAll();

        $this->template->devices = [];
        foreach ($deviceSystems as $deviceSystem) {
            $this->template->devices[] = $this->database->table('Devices')
                ->get($deviceSystem->device_id);
        }
    }

    public function renderCreateDeviceType(int $systemId): void
    {
        $this->template->systemId = $systemId;

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

    protected function createComponentAddUserForm(): Form
    {
        $form = new Form;
        $form->setHtmlAttribute('class', 'ajax');
        $systemId = $this->getParameter('systemId');
        
        $existingUserIds = $this->database->table('UserSystems')
            ->where('system_id', $this->getParameter('systemId'))
            ->fetchPairs('user_id', 'user_id');
        $existingUserIds[] = $this->database->table('Systems')
            ->get($this->getParameter('systemId'))->admin_id;

        // Fetch usernames excluding the ones in the existingUserIds array
        $userNames = $this->database->table('Users')
            ->where('user_id NOT IN ?', $existingUserIds)
            ->fetchPairs('user_id', 'username');

        $form->addSelect('username', 'Uživatel:')
            ->setRequired('Prosím vyberte uživatele.')
            ->setItems($userNames)
            ->setPrompt('Vyberte uživatele');

        
        $form->addHidden('systemId', $systemId);

        $form->addSubmit('create', 'Přidat uživatele');

        $form->onSuccess[] = [$this, 'addUserFormSucceeded'];

        return $form;
    }

    public function addUserFormSucceeded(Form $form, array $values): void
    {
        $userId = $values['username'];
        $user = $this->database->table('Users')->get($userId);
        $systemId = $values['systemId'];
        $system = $this->database->table('Systems')->get($systemId);

        if ($user && $systemId) {
            $this->database->table('UserSystems')->insert([
                'user_id' => $user->user_id,
                'system_id' => $system,
            ]);

            $this->flashMessage('Uživatel byl úspěšně pridan.', 'success');
            if ($this->isAjax()) {
                $this->payload->success = true;
                $this->redrawControl();
            } else {
                $this->redirect('this');
            }
        }
        else {
            $this->flashMessage('Toto uživatelské ID neexistuje.', 'error');
            if ($this->isAjax()) {
                $this->payload->error = true;
                $this->redrawControl('flashMessages');
            } else {
                $this->redirect('this');
            }
            return;
        }

    }

    public function actionDelete(int $systemId, int $userId): void
    {
        $this->database->table('UserSystems')
            ->where('user_id', $userId)
            ->where('system_id', $systemId)
            ->delete();

        $this->flashMessage('Uživatel byl odstraněn ze systému.', 'success');
        $this->redirect('Systeminfo:default', $systemId);
    }

    public function renderShowDevices(int $systemId): void
    {
        $this->template->systemId = $systemId;

        $devices = $this->database->table('DeviceSystem')
            ->where('system_id', $systemId)
            ->fetchAll();
    
        $deviceDetails = [];
        foreach ($devices as $deviceSystem) {
            $device = $this->database->table('Devices')
                ->get($deviceSystem->device_id);
    
            $parameters = $this->database->table('DeviceParameters')
                ->where('device_id', $device->device_id)
                ->fetchAll();
    
            $paramDetails = [];
            foreach ($parameters as $param) {
                $parameter = $this->database->table('Parameters')
                ->get($param->parameter_id);

                $paramDetails[] = [
                    'parameter_name' => $parameter->parameter_name,
                    'parameter_value' => $parameter->parameter_value
                ];
            }
    
            $deviceDetails[] = [
                'device_id' => $device->device_id,
                'device_type' => $device->device_type,
                'description' => $device->description,
                'parameters' => $paramDetails
            ];
        }
    
        $this->template->devices = $deviceDetails;
    }

    public function actionDeleteDevice(int $systemId, int $deviceId): void
    {
        // Perform the deletion
        $this->database->table('DeviceSystem')->where('device_id', $deviceId)->delete();
        $this->database->table('DeviceParameters')->where('device_id', $deviceId)->delete();
        $this->database->table('Devices')->where('device_id', $deviceId)->delete();
    
        $this->flashMessage('Zařízení bylo odstraněno.', 'success');
        $this->redirect('Systeminfo:showDevices', $systemId);
    }    

    protected function createComponentCreateDeviceType(): Form
    {
        $form = new Form;
        $systemId = $this->getParameter('systemId');
        $paramTypes = $this->database->table('ParameterTypes')->fetchPairs('parameter_type_id', 'type_name');

        $form->addText('device_name', 'Název zařízení:')
                ->setRequired('Prosím zadejte název zařízení.')
                ->setHtmlAttribute('placeholder', 'Prosím zadejte název');

        $form->addText('device_description', 'Popis zařízení:')
                ->setHtmlAttribute('placeholder', 'Prosím zadejte popis');
            
        $form->addText('device_type_name', 'Název typu zařízení:')
                ->setRequired('Prosím zadejte název typu zařízení.')
                ->setHtmlAttribute('placeholder', 'Prosím zadejte název typu');

        $form->addText('device_type_description', 'Popis typu zařízení:')
                ->setHtmlAttribute('placeholder', 'Prosím zadejte popis typu');

        // $form->addText('parameter_name', 'Název parametru:')
        //         ->setRequired('Prosím zadejte název parametru.')
        //         ->setHtmlAttribute('placeholder', 'Prosím zadejte název');
        
        // $parameters = $this->database->table('Parameters')->fetchPairs('parameter_id', 'parameter_name');
        
        $form->addMultiSelect('parameter_types', 'Typy parametrů:')
                ->setItems($paramTypes)
                ->setRequired('Prosím vyberte jeden typ parameteru nebo vytvořte nový.');

        // $form->addText('parameter_value', 'Hodnota parametru:')
        //         ->setHtmlAttribute('placeholder', 'Prosím zadejte hodnotu');


        $form->addHidden('systemId', $systemId);

        $form->addSubmit('create', 'Přidat zařízení');

        $form->onSuccess[] = [$this, 'createDeviceTypeFormSucceeded'];

        return $form;
    }

    public function createDeviceTypeFormSucceeded(Form $form, array $values): void
    {
        $systemId = $values['systemId'];

        // Process the form data and save it to the database
        $deviceTypeName = $values['device_type_name'];
        $deviceTypeDescription = $values['device_type_description'];
    
        // Create a new device type
        $deviceTypeRow = $this->database->table('DeviceTypes')->insert([
            'type_name' => $deviceTypeName,
            'description' => $deviceTypeDescription,
        ]);
            
        $deviceTypeId = $deviceTypeRow->getPrimary();
        // Create a new device
        $deviceRow = $this->database->table('Devices')->insert([
            'device_type' => $values['device_name'], // Assuming device_type column is required
            'device_type_id' => $deviceTypeId,
            'description' => $values['device_description'],
            'user_id' => $this->user->getId(),
        ]);
        
        $deviceId = $deviceRow->getPrimary();

        // Associate selected parameters with the device
        foreach ($values['parameter_types'] as $parameterTypeId) {
            // Fetch the type name
            $typeName = $this->database->table('ParameterTypes')
                ->get($parameterTypeId)->type_name;
        
            // Fetch the latest parameter for this type and extract the number
            $lastParameter = $this->database->table('Parameters')
                ->where('parameter_type_id', $parameterTypeId)
                ->order('parameter_id DESC')
                ->limit(1)
                ->fetch();
        
            $nextNumber = 1;
            if ($lastParameter) {
                $matches = [];
                preg_match('/(\d+)$/', $lastParameter->parameter_name, $matches);
                $nextNumber = isset($matches[1]) ? ((int) $matches[1]) + 1 : 1;
            }
        
            // Format the parameter name
            $parameterName = sprintf('%s_%02d', $typeName, $nextNumber);
        
            // Insert the new parameter
            $insertedParameter = $this->database->table('Parameters')->insert([
                'parameter_name' => $parameterName,
                'parameter_type_id' => $parameterTypeId,
            ]);

            $lastInsertedParameterId = $insertedParameter->getPrimary();

            $this->database->table('DeviceTypeParameterType')->insert([
                'device_type_id' => $deviceTypeId,
                'parameter_type_id' => $parameterTypeId,
            ]);

            $this->database->table('DeviceParameters')->insert([
                'device_id' => $deviceId,
                'parameter_id' => $lastInsertedParameterId,
            ]);
        }
        
        $this->database->table('DeviceSystem')->insert([
            'device_id' => $deviceId,
            'system_id' => $systemId,
        ]);

        $this->flashMessage('Zařízení bylo úspěšně přidáno.', 'success');
        $this->redirect('Systeminfo:showDevices', $systemId);
    }

    public function actionEditDevice(int $systemId, int $deviceId): void
    {
        // Make sure the device exists
        $device = $this->database->table('Devices')->get($deviceId);
        if (!$device) {
            $this->flashMessage('Zařízení nenalezeno.', 'error');
            $this->redirect('Systeminfo:default');
        }
    
        $this->template->device = $device;
        $this->template->systemId = $systemId;
    }

    protected function createComponentEditDeviceForm(): Form
    {
        $form = new Form;
        $systemId = $this->getParameter('systemId');
        // Assuming you pass the device ID to the form in some way (e.g., query parameter)
        $deviceId = $this->getParameter('deviceId');
        $device = $this->database->table('Devices')->get($deviceId);

        $form->addText('device_name', 'Název zařízení')
            ->setDefaultValue($device->device_type)
            ->setRequired('Prosím zadejte název zařízení.');

        $form->addTextArea('device_description', 'Popis')
            ->setDefaultValue($device->description);

        // Add parameters dynamically based on the device
        $parameters = $this->database->table('DeviceParameters')
                        ->where('device_id', $deviceId)
                        ->fetchAll();

        foreach ($parameters as $param) {
            $pararam = $this->database->table('Parameters')->get($param->parameter_id);
            $form->addText('param_' . $param->parameter_id, 'Parameter: ' . $pararam->parameter_name)
                ->setDefaultValue($pararam->parameter_value);
        }
        
        $form->addHidden('systemId', $systemId);
        $form->addSubmit('submit', 'Uložit změny');
        $form->onSuccess[] = [$this, 'editDeviceFormSucceeded'];

        return $form;
    }

    public function editDeviceFormSucceeded(Form $form, array $values): void
    {
        $deviceId = $this->getParameter('deviceId');
        $systemId = $values['systemId'];
        
        $this->database->table('Devices')
            ->where('device_id', $deviceId)
            ->update([
                'device_type' => $values['device_name'],
                'description' => $values['device_description'],
            ]);
    
        // Update parameters
        foreach ($values as $key => $value) {
            if (strpos($key, 'param_') === 0) {
                $paramId = substr($key, 6);
                $this->database->table('Parameters')
                    ->where('parameter_id', $paramId)
                    ->update(['parameter_value' => $value]);
            }
        }
    
        $this->flashMessage('Zařízení bylo úspěšně aktualizováno.', 'success');
        $this->redirect('Systeminfo:showDevices', $systemId);
    }

    public function KPI(float $value, string $operator, float $deviceValue): bool
    {
        if (!$value) {
            $value = 0;
        }
        switch ($operator) {
            case '<':
                return $deviceValue < $value;
            case '>':
                return $deviceValue > $value;
            case '=':
                return $deviceValue == $value;
            case '>=':
                return $deviceValue >= $value;
            case '<=':
                return $deviceValue <= $value;
            default:
                throw new \InvalidArgumentException("Invalid operator");
        }
    }

    protected function createComponentKpiForm(): Form
    {
        $form = new Form;
        $form->addText('value', 'Hodnota:')
              ->setRequired('Prosím zadejte hodnotu.')
              ->addRule(Form::FLOAT, 'Hodnota musí být číslo.');
    
        $form->addSelect('operator', 'Operátor:', ['<' => 'Menší', '>' => 'Větší', '=' => 'Rovno', '>=' => 'Menší nebo rovno', '<=' => 'Větší nebo rovno'])
             ->setRequired('Prosím vyberte operátor.');
    
        $form->addSubmit('submit', 'Zkontrolovat');
        $form->onSuccess[] = [$this, 'kpiFormSucceeded'];
        
        return $form;
    }

    public function kpiFormSucceeded(Form $form, \stdClass $values): void
    {
        $systemId = $this->getParameter('systemId');
        $devices = $this->database->table('DeviceSystem')
                    ->where('system_id', $systemId)
                    ->fetchAll();
        
        $kpiResults = [];
        foreach ($devices as $deviceSystem) {
            $device = $this->database->table('Devices')
                        ->get($deviceSystem->device_id);
    
            $parameters = $this->database->table('DeviceParameters')
                            ->where('device_id', $device->device_id)
                            ->fetchAll();
    
            foreach ($parameters as $param) {
                $parameter = $this->database->table('Parameters')
                                ->get($param->parameter_id);
                $kpiResults[$parameter->parameter_id] = $this->KPI($values->value, $values->operator, $parameter->parameter_value);
            }
        }
    
        $this->template->kpiResults = $kpiResults;
        $this->template->kpiValue = $values->value;
        $this->template->kpiOperator = $values->operator;
        $this->redrawControl('deviceList');
    }     

}