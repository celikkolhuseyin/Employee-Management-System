CREATE DATABASE IF NOT EXISTS employee_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE employee_management;

DROP TABLE IF EXISTS employee_logs;
DROP TABLE IF EXISTS employee_documents;
DROP TABLE IF EXISTS work_records;
DROP TABLE IF EXISTS employee_roles;
DROP TABLE IF EXISTS employees;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS roles;
DROP TABLE IF EXISTS departments;

CREATE TABLE departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(120) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    user_type ENUM('Administrator','Manager','Employee') NOT NULL DEFAULT 'Employee',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE employees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_code VARCHAR(20) NOT NULL UNIQUE,
    first_name VARCHAR(80) NOT NULL,
    last_name VARCHAR(80) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL,
    gender ENUM('Male','Female','Other') NOT NULL,
    birth_date DATE,
    hire_date DATE NOT NULL,
    salary DECIMAL(10,2) NOT NULL DEFAULT 0,
    address TEXT,
    department_id INT NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_employee_department FOREIGN KEY (department_id) REFERENCES departments(id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE employee_roles (
    employee_id INT NOT NULL,
    role_id INT NOT NULL,
    PRIMARY KEY (employee_id, role_id),
    CONSTRAINT fk_employee_roles_employee FOREIGN KEY (employee_id) REFERENCES employees(id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_employee_roles_role FOREIGN KEY (role_id) REFERENCES roles(id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE work_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    work_date DATE NOT NULL,
    status ENUM('Present','Remote','On Leave','Sick Leave','Absent') NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_work_employee FOREIGN KEY (employee_id) REFERENCES employees(id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE employee_documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    document_type ENUM('CV','Contract','Certificate','Other') NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_document_employee FOREIGN KEY (employee_id) REFERENCES employees(id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE employee_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NULL,
    action_type VARCHAR(30) NOT NULL,
    log_message VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

DELIMITER $$
CREATE TRIGGER trg_employee_after_insert
AFTER INSERT ON employees
FOR EACH ROW
BEGIN
    INSERT INTO employee_logs(employee_id, action_type, log_message)
    VALUES(NEW.id, 'INSERT', CONCAT('Employee added: ', NEW.first_name, ' ', NEW.last_name));
END$$

CREATE TRIGGER trg_employee_after_update
AFTER UPDATE ON employees
FOR EACH ROW
BEGIN
    INSERT INTO employee_logs(employee_id, action_type, log_message)
    VALUES(NEW.id, 'UPDATE', CONCAT('Employee updated: ', NEW.first_name, ' ', NEW.last_name));
END$$

CREATE TRIGGER trg_employee_before_delete
BEFORE DELETE ON employees
FOR EACH ROW
BEGIN
    INSERT INTO employee_logs(employee_id, action_type, log_message)
    VALUES(OLD.id, 'DELETE', CONCAT('Employee deleted: ', OLD.first_name, ' ', OLD.last_name));
END$$

CREATE PROCEDURE sp_department_employee_summary()
BEGIN
    SELECT d.id, d.name AS department_name, COUNT(e.id) AS employee_count,
           COALESCE(AVG(e.salary), 0) AS average_salary
    FROM departments d
    LEFT JOIN employees e ON e.department_id = d.id AND e.is_active = 1
    GROUP BY d.id, d.name
    ORDER BY d.name;
END$$
DELIMITER ;

INSERT INTO departments(name, description) VALUES
('Human Resources', 'Responsible for employee relations and recruitment.'),
('Information Technology', 'Manages software, hardware and system operations.'),
('Finance', 'Handles accounting and payroll processes.'),
('Operations', 'Coordinates daily organizational workflows.');

INSERT INTO roles(name, description) VALUES
('Administrator', 'Full system access.'),
('Manager', 'Can review employees and reports.'),
('Software Developer', 'Develops software systems.'),
('HR Specialist', 'Manages HR processes.'),
('Accountant', 'Handles financial records.');

INSERT INTO users(full_name,email,password,user_type) VALUES
('System Administrator','admin@ems.local','$2y$12$LAvUKZ9Tq/XfDOW9Jx1R6uvPgBBQFm4gTorrNvl26P4AE4g.lNvli','Administrator'),
('Demo Manager','manager@ems.local','$2y$12$mBe17F.qh4NeVizjyBAh3eIaiHwLzxJ5XVdb38owVZ50KibEjxCem','Manager'),
('Demo Employee','employee@ems.local','$2y$12$aDr90ZoY5d/sr2swog9XVulNkK3Lm1oCGl1Ru1Gm8OMJQwRUpRV9.','Employee');

INSERT INTO employees(employee_code, first_name, last_name, email, phone, gender, birth_date, hire_date, salary, address, department_id, is_active) VALUES
('EMP001','Ahmet','Yilmaz','ahmet.yilmaz@ems.local','05551234567','Male','1995-03-12','2023-01-10',42000,'Adana, Turkey',2,1),
('EMP002','Ayse','Demir','ayse.demir@ems.local','05557654321','Female','1997-07-08','2022-09-05',39000,'Mersin, Turkey',1,1),
('EMP003','Mehmet','Kara','mehmet.kara@ems.local','05559876543','Male','1992-11-20','2021-06-15',45000,'Gaziantep, Turkey',3,1);

INSERT INTO employee_roles(employee_id, role_id) VALUES
(1,3),(2,4),(3,5);

INSERT INTO work_records(employee_id, work_date, status, notes) VALUES
(1, CURDATE(), 'Present', 'On-site work'),
(2, CURDATE(), 'Remote', 'Remote work'),
(3, CURDATE(), 'On Leave', 'Annual leave');
