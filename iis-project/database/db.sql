--------    CREATE TABLES    ---------

CREATE TABLE 'Users' (
    'user_id' INT PRIMARY KEY AUTO_INCREMENT,
    'username' VARCHAR(32) NOT NULL,
    'password' VARCHAR(32) NOT NULL,
    'role' NOT NULL
);

CREATE TABLE 'Devices' (
    'device_id' INT PRIMARY KEY AUTO_INCREMENT,
    'device_name' VARCHAR(32) NOT NULL,
    'device_type_id' INT,
    'description' VARCHAR(128),
    'user_id' INT,
    CONSTRAINT 'fk_device_type' FOREIGN KEY ('device_type_id') REFERENCES 'DeviceTypes'('device_type_id') ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT 'fk_user' FOREIGN KEY ('user_id') REFERENCES 'Users'('user_id') ON DELETE CASCADE ON UPDATE CASCADE
);


CREATE TABLE 'DeviceTypes' (
    'device_type_id' INT PRIMARY KEY AUTO_INCREMENT,
    'type_name' VARCHAR(32) NOT NULL,
    'description' VARCHAR(128)
);

CREATE TABLE 'Parameters' (
    'parameter_id' INT PRIMARY KEY AUTO_INCREMENT,
    'parameter_name' VARCHAR(32) NOT NULL,
    'parameter_values' VARCHAR(128) NOT NULL
);

CREATE TABLE 'Systems' (
    'system_id' INT PRIMARY KEY AUTO_INCREMENT,
    'system_name' VARCHAR(32) NOT NULL,
    'system_description' VARCHAR(128),
    'admin_id' INT,
    CONSTRAINT 'fk_admin' FOREIGN KEY ('admin_id') REFERENCES 'Users'('user_id') ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE 'KPIs' (
    'kpi_id' INT PRIMARY KEY AUTO_INCREMENT,
    'kpi_name' VARCHAR(50) NOT NULL,
    'parameter_id' INT,
    'output' INT,
    CONSTRAINT 'fk_parameter' FOREIGN KEY ('parameter_id') REFERENCES 'Parameters'('parameter_id') ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE 'DeviceTypesParametrs' (
    'device_type_id' INT NOT NULL,
    'parameter_id' INT NOT NULL,
    CONSTRAINT 'pk_dtp' PRIMARY KEY ('device_type_id', 'parameter_id'),
    CONSTRAINT 'fk_device_type' FOREIGN KEY ('device_type_id') REFERENCES 'DeviceTypes'('device_type_id') ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT 'fk_parameter' FOREIGN KEY ('parameter_id') REFERENCES 'Parameters'('parameter_id') ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE 'UserSystems' (
    'user_id' INT,
    'system_id' INT,
    CONSTRAINT 'pk_us' PRIMARY KEY ('user_id', 'system_id'),
    CONSTRAINT 'fk_user' FOREIGN KEY ('user_id') REFERENCES 'Users'('user_id') ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT 'fk_system'FOREIGN KEY ('system_id') REFERENCES 'Systems'('system_id') ON DELETE CASCADE ON UPDATE CASCADE
);
