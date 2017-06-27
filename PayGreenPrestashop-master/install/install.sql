CREATE TABLE IF NOT EXISTS `%%PREFIX%%paygreen_buttons` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `label` VARCHAR(100) NULL,
  `image` VARCHAR(45) NULL,
  `height` INT NULL,
  `position` INT NULL,
  `displayType` VARCHAR(45) NULL DEFAULT 'default',
  `nbPayment` INT NOT NULL DEFAULT 1,
  `perCentPayment` INT NULL,
  `minAmount` DECIMAL(10,2) NULL,
  `maxAmount` DECIMAL(10,2) NULL,
  `executedAt` INT NULL DEFAULT 0,
  PRIMARY KEY (`id`)) DEFAULT charset=utf8;
