# Employee Management System

A web-based Employee Management System developed with PHP, MySQL, MySQLi, Bootstrap and JavaScript as a Computer Engineering graduation project.

## Technologies
- HTML5, CSS3, Bootstrap 5
- JavaScript
- PHP with MySQLi
- MySQL
- Apache/XAMPP

## Main Features
- Login/logout with session control
- Role-based access structure
- Employee CRUD
- Department CRUD
- Role CRUD
- Work record tracking
- Employee document upload
- Reports with JOIN queries
- MySQL trigger and stored procedure
- Server-side regex validation
- 3NF relational database design

## Setup with XAMPP
1. Copy `employee-management-system` folder into `xampp/htdocs/`.
2. Open phpMyAdmin.
3. Import `database/employee_management.sql`.
4. Open `http://localhost/employee-management-system/`.
5. Demo Users

Administrator:
    E-mail: admin@ems.local
    Password: admin123

Manager:
    E-mail: manager@ems.local
    Password: manager123

Employee:
    E-mail: employee@ems.local
    Password: employee123

## Requirement Checklist
- Separate CSS file: `assets/css/style.css`
- JavaScript file: `assets/js/main.js`
- PHP form processing: store/update/delete files
- MySQLi API: `config/database.php`
- CRUD: employees, departments, roles, work records, documents
- Trigger: employee insert/update/delete log triggers
- Stored procedure: `sp_department_employee_summary()`
- JOIN operations: dashboard, employee list, work records, documents, reports/join_report.php
- Form elements: text, textarea, radio, checkbox, select, multiple select, file input, button
