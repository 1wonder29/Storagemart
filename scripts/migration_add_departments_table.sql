-- Creates tbldepartments so department options are admin-editable instead of
-- hardcoded in the Add/Edit Account view files. Seeded with the current list
-- so nothing changes for existing employees.

CREATE TABLE `tbldepartments` (
  `department_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(50) NOT NULL,
  `label` VARCHAR(150) NOT NULL,
  PRIMARY KEY (`department_id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `tbldepartments` (`code`, `label`) VALUES
  ('IT', 'Information Technology'),
  ('Sales', 'Sales'),
  ('Purchasing', 'Purchasing'),
  ('Accounting', 'Accounting'),
  ('HRMD', 'HRMD & Admin Department'),
  ('Marketing', 'Marketing'),
  ('Compliance', 'Corporate Compliance'),
  ('Operations', 'Operations'),
  ('Digital Marketing', 'Digital Marketing'),
  ('Construction', 'Construction');
