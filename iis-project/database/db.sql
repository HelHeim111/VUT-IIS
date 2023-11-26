DROP TABLE IF EXISTS `DeviceSystem`;
DROP TABLE IF EXISTS `UserSystems`;
DROP TABLE IF EXISTS `DeviceTypesParameters`;
DROP TABLE IF EXISTS `Devices`;
DROP TABLE IF EXISTS `KPIs`;
DROP TABLE IF EXISTS `Systems`;
DROP TABLE IF EXISTS `Parameters`;
DROP TABLE IF EXISTS `DeviceTypes`;
DROP TABLE IF EXISTS `Users`;

CREATE TABLE `Users` (
    `user_id` INT PRIMARY KEY AUTO_INCREMENT,
    `username` VARCHAR(32) NOT NULL,
    `password` VARCHAR(128) NOT NULL,
    `role` VARCHAR(32) NOT NULL
);

CREATE TABLE `DeviceTypes` (
    `device_type_id` INT PRIMARY KEY AUTO_INCREMENT,
    `type_name` VARCHAR(32) NOT NULL,
    `description` VARCHAR(128)
);

CREATE TABLE `Parameters` (
    `parameter_id` INT PRIMARY KEY AUTO_INCREMENT,
    `parameter_name` VARCHAR(32) NOT NULL,
    `parameter_values` VARCHAR(128) NOT NULL
);


CREATE TABLE `KPIs` (
    `kpi_id` INT PRIMARY KEY AUTO_INCREMENT,
    `kpi_name` VARCHAR(50) NOT NULL,
    `parameter_id` INT,
    `output` INT,
    CONSTRAINT `fk_parameter` FOREIGN KEY (`parameter_id`) REFERENCES `Parameters`(`parameter_id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE `Devices` (
    `device_id` INT PRIMARY KEY AUTO_INCREMENT,
    `device_name` VARCHAR(32) NOT NULL,
    `device_type_id` INT,
    `description` VARCHAR(128),
    `user_id` INT,
    `kpi_id` INT,
    CONSTRAINT `fk_devices_device_type` FOREIGN KEY (`device_type_id`) REFERENCES `DeviceTypes`(`device_type_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_devices_user` FOREIGN KEY (`user_id`) REFERENCES `Users`(`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_devices_kpi` FOREIGN KEY (`kpi_id`) REFERENCES `KPIs`(`kpi_id`) ON DELETE CASCADE ON UPDATE CASCADE
);


CREATE TABLE `Systems` (
    `system_id` INT PRIMARY KEY AUTO_INCREMENT,
    `system_name` VARCHAR(32) NOT NULL,
    `system_description` VARCHAR(128),
    `admin_id` INT,
    CONSTRAINT `fk_admin` FOREIGN KEY (`admin_id`) REFERENCES `Users`(`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE `DeviceTypesParameters` (
    `device_type_id` INT NOT NULL,
    `parameter_id` INT NOT NULL,
    CONSTRAINT `pk_dtp` PRIMARY KEY (`device_type_id`, `parameter_id`),
    CONSTRAINT `fk_dtp_device_type` FOREIGN KEY (`device_type_id`) REFERENCES `DeviceTypes`(`device_type_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_dtp_parameter` FOREIGN KEY (`parameter_id`) REFERENCES `Parameters`(`parameter_id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE `UserSystems` (
    `user_id` INT,
    `system_id` INT,
    CONSTRAINT `pk_us` PRIMARY KEY (`user_id`, `system_id`),
    CONSTRAINT `fk_user` FOREIGN KEY (`user_id`) REFERENCES `Users`(`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_system` FOREIGN KEY (`system_id`) REFERENCES `Systems`(`system_id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE `DeviceSystem` (
    `device_id` INT NOT NULL,
    `system_id` INT NOT NULL,
    CONSTRAINT `pk_device_system` PRIMARY KEY (`device_id`, `system_id`),
    CONSTRAINT `fk_device_system_device` FOREIGN KEY (`device_id`) REFERENCES `Devices`(`device_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_device_system_system` FOREIGN KEY (`system_id`) REFERENCES `Systems`(`system_id`) ON DELETE CASCADE ON UPDATE CASCADE
);

INSERT INTO `Users` (username, password, role) VALUES ('admin', '$2y$10$eStqgge8bpW6nMQ3kV6vOu1K1YuvZ83vAGACECiWBjaJBC2UKX4Ly', 'admin');
INSERT INTO `Users` (username, password, role) VALUES ('Abobus', '$2y$10$BhhhsySPfuOxQMXQUd8ceeeJwm3DiM89TMtXVablnYxWn359I4CKG', 'user');
INSERT INTO `Users` (username, password, role) VALUES ('Amogus', '$2y$10$qJHIxAuax0ML3oBriP4f3OL.oF8TKKUNjJB7ORKQRFSQU6DcqMhRq', 'user');
INSERT INTO `Users` (username, password, role) VALUES ('NewUser1', '$2y$10$SomeHashValue', 'user');
INSERT INTO `Users` (username, password, role) VALUES ('NewUser2', '$2y$10$AnotherHashValue', 'user');

INSERT INTO `DeviceTypes` (type_name, description) VALUES ('Thermometer', 'Measures temperature');
INSERT INTO `DeviceTypes` (type_name, description) VALUES ('Humidity Sensor', 'Measures humidity levels');

INSERT INTO `Parameters` (parameter_name, parameter_values) VALUES ('Temperature', 'Celsius, Fahrenheit');
INSERT INTO `Parameters` (parameter_name, parameter_values) VALUES ('Humidity', 'Percentage');

INSERT INTO `Systems` (system_name, system_description, admin_id) VALUES ('SmartHome', 'Home automation system', 2);
INSERT INTO `Systems` (system_name, system_description, admin_id) VALUES ('OfficeMonitor', 'Office environment monitoring', 3);

INSERT INTO `KPIs` (kpi_name, parameter_id, output) VALUES ('Temperature_OK', 1, 1);
INSERT INTO `KPIs` (kpi_name, parameter_id, output) VALUES ('Humidity_OK', 2, 1);

INSERT INTO `Devices` (device_name, device_type_id, description, user_id, kpi_id) VALUES ('LivingRoomThermometer', 1, 'Measures temperature in the living room', 2, 1);
INSERT INTO `Devices` (device_name, device_type_id, description, user_id, kpi_id) VALUES ('OfficeHumiditySensor', 2, 'Monitors humidity in the office', 3, 2);
INSERT INTO `Devices` (device_name, device_type_id, description, user_id, kpi_id) VALUES ('KitchenThermometer', 1, 'Measures temperature in the kitchen', 2, 1);
INSERT INTO `Devices` (device_name, device_type_id, description, user_id, kpi_id) VALUES ('LivingRoomHumiditySensor', 2, 'Monitors humidity in the living room', 3, 2);


INSERT INTO `DeviceTypesParameters` (device_type_id, parameter_id) VALUES (1, 1);
INSERT INTO `DeviceTypesParameters` (device_type_id, parameter_id) VALUES (2, 2);

INSERT INTO `UserSystems` (user_id, system_id) VALUES (2, 2);
INSERT INTO `UserSystems` (user_id, system_id) VALUES (3, 1);
INSERT INTO `UserSystems` (user_id, system_id) VALUES (4, 1);


INSERT INTO `DeviceSystem` (`device_id`, `system_id`) VALUES (1, 1);
INSERT INTO `DeviceSystem` (`device_id`, `system_id`) VALUES (2, 2);
INSERT INTO `DeviceSystem` (`device_id`, `system_id`) VALUES (3, 1);
INSERT INTO `DeviceSystem` (`device_id`, `system_id`) VALUES (4, 2);