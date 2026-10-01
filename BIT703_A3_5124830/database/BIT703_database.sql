DROP TABLE IF EXISTS `audit_log`;

DROP TABLE IF EXISTS `users`;

DROP TABLE IF EXISTS `access_levels`;

CREATE TABLE
    IF NOT EXISTS `access_levels` (
        `access_level_id` int NOT NULL,
        `access_level_name` varchar(254) NOT NULL,
        PRIMARY KEY (`access_level_id`)
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

INSERT INTO
    `access_levels` (`access_level_id`, `access_level_name`)
VALUES
    (1, 'Admin'),
    (2, 'Manager'),
    (3, 'Salesperson'),
    (4, 'Customer');

CREATE TABLE
    IF NOT EXISTS `users` (
        `user_id` int NOT NULL AUTO_INCREMENT,
        `first_name` varchar(50) NOT NULL,
        `last_name` varchar(50) NOT NULL,
        `email` varchar(254) NOT NULL,
        `password` varchar(255) NOT NULL,
        `access_level_id` int NOT NULL,
        `is_active` boolean NOT NULL DEFAULT true,
        PRIMARY KEY (`user_id`),
        UNIQUE KEY `email` (`email`),
        CONSTRAINT `fk_access_level` FOREIGN KEY (`access_level_id`) REFERENCES `access_levels` (`access_level_id`)
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

INSERT INTO
    `users` (
        `user_id`,
        `first_name`,
        `last_name`,
        `email`,
        `password`,
        `access_level_id`,
        `is_active`
    )
VALUES
    (
        1,
        "Amanda",
        "Admin",
        "amanda.admin@adventuregear.com",
        "$2y$10$jZhvcA/QVy5FIsdTnj27POn5LHt80w.xGhD2B8GK2KzxMtqAR6RLe",
        1,
        true
    ),
    (
        2,
        "Morris",
        "Manager",
        "morris.manager@adventuregear.com",
        "$2y$10$jZhvcA/QVy5FIsdTnj27POn5LHt80w.xGhD2B8GK2KzxMtqAR6RLe",
        2,
        true
    ),
    (
        3,
        "Sammy",
        "Salesperson",
        "sammy.salesperson@adventuregear.com",
        "$2y$10$jZhvcA/QVy5FIsdTnj27POn5LHt80w.xGhD2B8GK2KzxMtqAR6RLe",
        3,
        true
    ),
    (
        4,
        "Cathy",
        "Customer",
        "cathy.customer@customer.com",
        "$2y$10$jZhvcA/QVy5FIsdTnj27POn5LHt80w.xGhD2B8GK2KzxMtqAR6RLe",
        4,
        true
    );

CREATE TABLE
    IF NOT EXISTS `audit_log` (
        `log_id` int NOT NULL AUTO_INCREMENT,
        `action_user_name` varchar(101) DEFAULT NULL,
        `action` varchar(255) NOT NULL,
        `target_user_name` varchar(101) DEFAULT NULL,
        `details` varchar(255) DEFAULT NULL,
        `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`log_id`)
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;