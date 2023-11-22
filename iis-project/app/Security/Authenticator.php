<?php

namespace App\Security;

use Nette;
use Nette\Security as NS;
use Nette\Database\Context;
use Tracy\Debugger;
use Tracy\IBarPanel;

class Authenticator implements NS\IAuthenticator
{
    private $database;

    public function __construct(Context $database)
    {
        // Constructor class, dependencies can be added here, for example, database
        $this->database = $database;
    }

    public function authenticate(array $credentials): NS\IIdentity
    {
        [$username, $password] = $credentials;

        // Query the database to find the user
        $row = $this->database->table('Users') // assuming you have a 'users' table
            ->where('username', $username)
            ->fetch();

        if (!$row) {
            // User not found
            Debugger::log("User not found: $username", Debugger::ERROR);
            throw new NS\AuthenticationException('Uživatel nenalezen.', NS\IAuthenticator::IDENTITY_NOT_FOUND);
        }

        if (!password_verify($password, $row->password)) {
            // Incorrect password
            Debugger::log("Invalid password for user: $username", Debugger::ERROR);
            throw new NS\AuthenticationException('Nesprávné heslo.', NS\IAuthenticator::INVALID_CREDENTIAL);
        }

        // Return the Identity of the user
        return new NS\SimpleIdentity($row->user_id, $row->role, ['username' => $row->username]);
    }
}
