# Hospital Roster Management System

A comprehensive solution for managing staff schedules, shifts, and on-call rotations in a hospital setting. This system is designed to streamline the roster creation and management process for medical departments, providing clear visibility and efficient scheduling for healthcare facilities.

## System Overview

The Hospital Roster Management System is built to handle the complex scheduling needs of healthcare institutions. It supports various staff types, departments, and scheduling patterns, with the flexibility to accommodate both regular shifts and on-call duties.

### Key Features

- **Multi-Department Support**: Manage schedules across different hospital departments
- **Staff Type Categorization**: Different roster views and rules for various medical staff categories
- **Shift and On-Call Management**: Support for both regular shift schedules and on-call rotations
- **Team Leader Oversight**: Hierarchical management with team leader assignment and responsibilities
- **Holiday Integration**: Automatic consideration of public holidays in scheduling
- **Statistical Overview**: Visual dashboards displaying staff distribution and scheduling metrics

## Staff Role Logic

The system implements a hierarchical staff role structure that accommodates the different levels of healthcare professionals:

### Staff Types

1. **Specialist Doctor** (`specialist_doctor`)
   - Senior medical professionals with specialized expertise
   - Can be assigned to both regular shifts and on-call duties
   - May serve as team leaders for their respective departments

2. **Medical Officer** (`medical_officer`) 
   - Mid-level doctors who handle general patient care
   - Primary workforce for regular shift coverage
   - Can be assigned to on-call rotations when necessary

3. **Houseman Officer** (`houseman_officer`)
   - Junior doctors in training
   - Typically assigned to supervised shifts
   - May have rotation-specific scheduling requirements

4. **Nurse** (`nurse`)
   - Nursing staff with various experience levels
   - Primarily works in assigned shifts with specific nursing units
   - Different levels: Junior Nurse, Senior Nurse, Head Nurse

5. **Admin** (`admin`)
   - System administrators with full access to all functionality
   - Can manage all departments, staff types, and rosters
   - Responsible for system configuration and management

### Creating New Departments

Only administrators can create new departments:

1. Log in with administrator credentials
2. Navigate to Departments section
3. Click on "Create New Department"
4. Fill in the required information:
   - **Name**: Full department name
   - **Code**: Short unique department code (up to 10 characters)
   - **Description**: Optional department description
5. Click "Save" to create the department

### Creating New Staff Members

Staff members can be created by administrators or department leaders (for their own departments):

1. Navigate to Staff section
2. Click on "Add New Staff Member"
3. Complete the staff profile form:
   - **Name**: Full name of the staff member
   - **Email**: Email address (used for login)
   - **Phone**: Contact number
   - **Type**: Select from available staff types
   - **Department**: Assign to a specific department
   - **Joining Date**: Start date at the facility
   - **Notes**: Optional additional information
4. Submit the form to create the staff profile
   - A user account will be automatically created with the default password: `qmed.asia`

## Roster Management

### Creating Department Rosters

The system supports flexible roster creation for each department and staff type:

1. Navigate to the Roster Management section
2. Select the department and staff type to create a roster for
3. Choose the roster type (shift-based or on-call)
4. Configure schedule settings specific to the department's needs
5. Create and manage the roster through the interactive calendar interface

### Managing Team Leaders

Team leaders have special roster management privileges for their departments:

1. Navigate to Team Leaders section
2. Assign team leaders to departments by selecting staff members
3. Set leader effective dates and responsibilities
4. Team leaders will have access to manage their team's schedules

## Getting Started

### Prerequisites

- PHP 8.2 or higher
- MySQL/MariaDB database
- Composer
- Node.js and NPM

### Installation

1. Clone the repository
```bash
git clone https://github.com/qmed-asia/roster.git
```

2. Install PHP dependencies
```bash
composer install
```

3. Install JavaScript dependencies
```bash
npm install
```

4. Copy the environment file and configure your database
```bash
cp .env.example .env
# Edit .env file with your database credentials
```

5. Generate application key
```bash
php artisan key:generate
```

6. Run database migrations and seed initial data
```bash
php artisan migrate --seed
```

7. Build frontend assets
```bash
npm run build
```

8. Start the development server
```bash
php artisan serve
```

### Default Login

After installation, you can log in with the following admin account:
- Email: drtai@qmed.asia
- Password: qmed.asia

## License

This project is proprietary software developed for hospital roster management.

## Acknowledgments

This project is developed and maintained by QMed.Asia.
