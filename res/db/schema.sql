
# This is a fix for InnoDB in MySQL >= 4.1.x
# It "suspends judgement" for fkey relationships until are tables are set.
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
-- account
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `account`;

CREATE TABLE `account`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `address_id` INTEGER NOT NULL,
    `identifier` VARCHAR(45) NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `unique_account_identifier` (`identifier`),
    INDEX `fk_account_address_idx` (`address_id`),
    CONSTRAINT `fk_account_address`
        FOREIGN KEY (`address_id`)
        REFERENCES `address` (`id`)
        ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- address
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `address`;

CREATE TABLE `address`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `company` VARCHAR(255),
    `firstname` VARCHAR(255),
    `lastname` VARCHAR(255),
    `address` VARCHAR(255),
    `zipcode` VARCHAR(45),
    `city` VARCHAR(255),
    `state` VARCHAR(255),
    `province` VARCHAR(255),
    `country` VARCHAR(2),
    `phone` VARCHAR(255),
    `fax` VARCHAR(255),
    `website` VARCHAR(255),
    `email` VARCHAR(255),
    `vatid` VARCHAR(255),
    PRIMARY KEY (`id`)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- booking
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `booking`;

CREATE TABLE `booking`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `transaction_id` INTEGER NOT NULL,
    `booking_type_id` INTEGER NOT NULL,
    `label` VARCHAR(255),
    `value` INTEGER DEFAULT 0 NOT NULL,
    PRIMARY KEY (`id`),
    INDEX `fk_booking_type_idx` (`booking_type_id`),
    INDEX `fk_booking_transaction1_idx` (`transaction_id`),
    CONSTRAINT `fk_booking_transaction1`
        FOREIGN KEY (`transaction_id`)
        REFERENCES `transaction` (`id`)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT `fk_booking_type`
        FOREIGN KEY (`booking_type_id`)
        REFERENCES `booking_type` (`id`)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- booking_type
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `booking_type`;

CREATE TABLE `booking_type`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `account_id` INTEGER NOT NULL,
    `identifier` VARCHAR(255) NOT NULL,
    `label` VARCHAR(255) NOT NULL,
    `unit` enum('seconds','minutes','hours','halfdays','days','weeks','months','years') NOT NULL,
    `display_unit` enum('seconds','minutes','hours','halfdays','days','weeks','months','years'),
    PRIMARY KEY (`id`),
    UNIQUE INDEX `unique_booking_type_identifier` (`account_id`, `identifier`),
    INDEX `fk_booking_type_account` (`account_id`),
    CONSTRAINT `fk_booking_value_account`
        FOREIGN KEY (`account_id`)
        REFERENCES `account` (`id`)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- clocking
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `clocking`;

CREATE TABLE `clocking`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `creator_id` INTEGER,
    `user_id` INTEGER NOT NULL,
    `type_id` int(11) unsigned NOT NULL,
    `start` DATETIME NOT NULL,
    `end` DATETIME NOT NULL,
    `breaktime` int(11) unsigned DEFAULT 0 NOT NULL,
    `comment` TEXT,
    `approval_status` smallint(5) unsigned DEFAULT 0 NOT NULL,
    `deleted` TINYINT(1) DEFAULT 0 NOT NULL,
    `frozen` TINYINT(1) DEFAULT 0 NOT NULL,
    `creationdate` INTEGER NOT NULL,
    `last_changed` INTEGER NOT NULL,
    PRIMARY KEY (`id`),
    INDEX `fk_clocking_user_idx` (`user_id`),
    INDEX `fk_clocking_type_idx` (`type_id`),
    INDEX `fk_clocking_user1_idx` (`creator_id`),
    INDEX `clocking_start_idx` (`start`),
    INDEX `clocking_end_idx` (`end`),
    CONSTRAINT `fk_clocking_creator`
        FOREIGN KEY (`creator_id`)
        REFERENCES `user` (`id`)
        ON UPDATE CASCADE
        ON DELETE SET NULL,
    CONSTRAINT `fk_clocking_type`
        FOREIGN KEY (`type_id`)
        REFERENCES `clocking_type` (`id`)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT `fk_clocking_user`
        FOREIGN KEY (`user_id`)
        REFERENCES `user` (`id`)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- clocking_type
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `clocking_type`;

CREATE TABLE `clocking_type`
(
    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
    `account_id` INTEGER NOT NULL,
    `identifier` VARCHAR(255) NOT NULL,
    `label` VARCHAR(255) NOT NULL,
    `whole_day` TINYINT(1) DEFAULT 0 NOT NULL,
    `future_grace_time` bigint(19) unsigned,
    `past_grace_time` bigint(19) unsigned,
    `approval_required` TINYINT(1) DEFAULT 0 NOT NULL,
    PRIMARY KEY (`id`,`account_id`),
    UNIQUE INDEX `unique_clocking_type_identifier` (`account_id`, `identifier`),
    INDEX `fk_clocking_type_account1_idx` (`account_id`),
    CONSTRAINT `fk_clocking_type_account1`
        FOREIGN KEY (`account_id`)
        REFERENCES `account` (`id`)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- domain
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `domain`;

CREATE TABLE `domain`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `account_id` INTEGER NOT NULL,
    `address_id` INTEGER,
    `name` VARCHAR(45) NOT NULL,
    `valid` TINYINT(1),
    `description` VARCHAR(255),
    `number` VARCHAR(45),
    PRIMARY KEY (`id`),
    UNIQUE INDEX `unique_domain_name` (`account_id`, `name`, `valid`),
    INDEX `fk_domain_account_idx` (`account_id`),
    INDEX `fk_domain_address_idx` (`address_id`),
    CONSTRAINT `fk_domain_account`
        FOREIGN KEY (`account_id`)
        REFERENCES `account` (`id`)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT `fk_domain_address`
        FOREIGN KEY (`address_id`)
        REFERENCES `address` (`id`)
        ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- holiday
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `holiday`;

CREATE TABLE `holiday`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `account_id` INTEGER NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `date` DATE NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `unique_holiday_date` (`account_id`, `name`, `date`),
    INDEX `fk_vacation_account_idx` (`account_id`),
    CONSTRAINT `fk_holiday_account`
        FOREIGN KEY (`account_id`)
        REFERENCES `account` (`id`)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- holiday_domain
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `holiday_domain`;

CREATE TABLE `holiday_domain`
(
    `holiday_id` INTEGER NOT NULL,
    `domain_id` INTEGER NOT NULL,
    PRIMARY KEY (`holiday_id`,`domain_id`),
    INDEX `fk_holiday_domains_domain_idx` (`domain_id`),
    INDEX `fk_holiday_domains_holiday_idx` (`holiday_id`),
    CONSTRAINT `fk_holiday_domains_domain`
        FOREIGN KEY (`domain_id`)
        REFERENCES `domain` (`id`)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT `fk_holiday_domains_holiday`
        FOREIGN KEY (`holiday_id`)
        REFERENCES `holiday` (`id`)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- plugin
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `plugin`;

CREATE TABLE `plugin`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `account_id` INTEGER NOT NULL,
    `entity` VARCHAR(255) NOT NULL,
    `event` VARCHAR(255) NOT NULL,
    `priority` int(10) unsigned NOT NULL,
    `identifier` VARCHAR(255) NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `code` LONGTEXT NOT NULL,
    `active` TINYINT(11) DEFAULT 1 NOT NULL,
    `interval` INTEGER DEFAULT 0 NOT NULL,
    `start` INTEGER DEFAULT 0 NOT NULL,
    `last_execution_time` BIGINT DEFAULT 0 NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `unique_plugin_priority` (`account_id`, `entity`, `event`, `priority`),
    UNIQUE INDEX `unique_plugin_identifier` (`account_id`, `identifier`),
    INDEX `fk_plugin_account_idx` (`account_id`),
    CONSTRAINT `fk_plugin_account`
        FOREIGN KEY (`account_id`)
        REFERENCES `account` (`id`)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- property
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `property`;

CREATE TABLE `property`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `account_id` INTEGER NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `label` VARCHAR(255),
    `description` TEXT,
    `type` VARCHAR(255) DEFAULT 'string' NOT NULL,
    `default_value` LONGTEXT,
    `fixed` TINYINT(1) DEFAULT 0 NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `name_UNIQUE` (`name`),
    INDEX `fk_property_account1_idx` (`account_id`),
    CONSTRAINT `fk_property_account1`
        FOREIGN KEY (`account_id`)
        REFERENCES `account` (`id`)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- property_value
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `property_value`;

CREATE TABLE `property_value`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `property_id` INTEGER NOT NULL,
    `domain_id` INTEGER,
    `user_id` INTEGER,
    `value` LONGTEXT NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `unique_property_value_property_id` (`property_id`, `domain_id`, `user_id`),
    UNIQUE INDEX `unique_property_value_domain_id` (`property_id`, `domain_id`),
    UNIQUE INDEX `unique_property_value_user_id` (`property_id`, `user_id`),
    INDEX `fk_property_value_setting_idx` (`property_id`),
    INDEX `fk_property_value_user_idx` (`user_id`),
    INDEX `fk_property_value_domain1_idx` (`domain_id`),
    CONSTRAINT `fk_property_value_domain1`
        FOREIGN KEY (`domain_id`)
        REFERENCES `domain` (`id`)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT `fk_property_value_setting`
        FOREIGN KEY (`property_id`)
        REFERENCES `property` (`id`)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT `fk_property_value_user`
        FOREIGN KEY (`user_id`)
        REFERENCES `user` (`id`)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- system_log
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `system_log`;

CREATE TABLE `system_log`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `user_id` INTEGER,
    `index` VARCHAR(255),
    `entity` VARCHAR(255) NOT NULL,
    `service` VARCHAR(255) NOT NULL,
    `code` INTEGER,
    `message` TEXT,
    `data` LONGTEXT,
    PRIMARY KEY (`id`),
    INDEX `fk_system_log_user_idx` (`user_id`),
    CONSTRAINT `fk_system_log_user`
        FOREIGN KEY (`user_id`)
        REFERENCES `user` (`id`)
        ON UPDATE CASCADE
        ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- transaction
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `transaction`;

CREATE TABLE `transaction`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `creator_id` INTEGER,
    `user_id` INTEGER NOT NULL,
    `deleted` TINYINT DEFAULT 0 NOT NULL,
    `start` DATE NOT NULL,
    `end` DATE NOT NULL,
    `creationdate` INTEGER NOT NULL,
    `comment` TEXT,
    PRIMARY KEY (`id`),
    INDEX `fk_transaction_user_idx` (`user_id`),
    INDEX `fk_transaction_user1_idx` (`creator_id`),
    INDEX `transaction_start_idx` (`start`),
    INDEX `transaction_end_idx` (`end`),
    CONSTRAINT `fk_transaction_creator`
        FOREIGN KEY (`creator_id`)
        REFERENCES `user` (`id`)
        ON UPDATE CASCADE
        ON DELETE SET NULL,
    CONSTRAINT `fk_transaction_user`
        FOREIGN KEY (`user_id`)
        REFERENCES `user` (`id`)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- transaction_clocking
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `transaction_clocking`;

CREATE TABLE `transaction_clocking`
(
    `transaction_id` INTEGER NOT NULL,
    `clocking_id` INTEGER NOT NULL,
    PRIMARY KEY (`transaction_id`,`clocking_id`),
    INDEX `fk_transaction_clockings_transaction_idx` (`transaction_id`),
    INDEX `fk_transaction_clockings_clocking_idx` (`clocking_id`),
    CONSTRAINT `fk_transaction_clockings_clocking`
        FOREIGN KEY (`clocking_id`)
        REFERENCES `clocking` (`id`)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT `fk_transaction_clockings_transaction`
        FOREIGN KEY (`transaction_id`)
        REFERENCES `transaction` (`id`)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- user
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `user`;

CREATE TABLE `user`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `account_id` INTEGER NOT NULL,
    `domain_id` INTEGER NOT NULL,
    `deleted` TINYINT DEFAULT 0 NOT NULL,
    `name` VARCHAR(45) NOT NULL,
    `firstname` VARCHAR(255),
    `lastname` VARCHAR(255),
    `phone` VARCHAR(255),
    `manager_of` INTEGER,
    `is_admin` TINYINT DEFAULT 0 NOT NULL,
    `email` VARCHAR(255),
    `password_hash` VARCHAR(255) NOT NULL,
    `number` VARCHAR(45),
    PRIMARY KEY (`id`),
    UNIQUE INDEX `unique_user_name` (`account_id`, `name`),
    INDEX `fk_user_domain_idx` (`domain_id`),
    INDEX `fk_user_account1_idx` (`account_id`),
    CONSTRAINT `fk_user_account1`
        FOREIGN KEY (`account_id`)
        REFERENCES `account` (`id`)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT `fk_user_domain`
        FOREIGN KEY (`domain_id`)
        REFERENCES `domain` (`id`)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB;

# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;

# Insert default data

INSERT INTO `address` (`company`) VALUES ('Your company');
INSERT INTO `account` (`address_id`,`identifier`,`name`) VALUES (1, 'default', 'Default account');
INSERT INTO `domain` (`account_id`,`name`) VALUES (1, 'Default domain');
INSERT INTO `user` (`account_id`,`domain_id`,`name`,`is_admin`,`password_hash`) VALUES (1, 1, 'admin', 1, '["a0bf42b87e1807af64bedb3252e706860c2b9fa2","e0169f4e5b1aa864b8d920e7f2b3077d43b6f3c0"]');

INSERT INTO `booking_type` (`account_id`, `identifier`, `label`, `unit`, `display_unit`)
VALUES
	(1,'flexitime','Gleitzeit','minutes','minutes'),
	(1,'regular','Reguläre AZ','minutes','minutes'),
	(1,'overtime','Überstunden','minutes','minutes'),
	(1,'vacation','Urlaub','halfdays','days'),
	(1,'sick_leave','Krankheit','days','days'),
	(1,'education','Schule / Uni','days','days'),
	(1,'rejected','Abgelehnt','minutes','minutes'),
	(1,'vacation_left_prev','Urlaub für Vorjahr','halfdays','days'),
	(1,'vacation_left','Urlaub verbleibend','halfdays','days'),
	(1,'vacation_left_next','Urlaub für Folgejahr','halfdays','days'),
	(1,'parental_leave','Elternzeit','days','days');


INSERT INTO `clocking_type` (`account_id`, `identifier`, `label`, `whole_day`, `future_grace_time`, `past_grace_time`, `approval_required`)
VALUES
	(1,'regular','Reguläre AZ',0,1,172800,0),
	(1,'reduce_overtime','Ü-Abbau',1,NULL,2419200,1),
	(1,'vacation','Urlaub',1,NULL,2419200,1),
	(1,'sick_leave','Krankheit',1,NULL,2419200,1),
	(1,'education','Schule / Uni',1,NULL,2419200,1),
	(1,'parental_leave','Elternzeit',1,NULL,2419200,1);
