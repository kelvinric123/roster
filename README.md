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

### Role-Based Access

Each staff type has specific access permissions within the system:
- Admins can manage all system aspects
- Specialist doctors can view and sometimes manage department rosters
- Medical officers and nurses can primarily view their own schedules
- Team leaders have additional permissions to manage their team members' schedules

## Roster Type and Shift Logic

The system supports multiple roster types to accommodate different scheduling needs:

### Roster Types

1. **Regular Shift Roster**
   - Standard shift-based scheduling
   - Configurable shift patterns (morning, evening, night)
   - Support for rotation patterns and shift swaps
   - Visual calendar view with color-coded shift assignments

2. **On-Call Roster**
   - Scheduling for after-hours emergency coverage
   - Rotation-based assignment to ensure fair distribution
   - Integration with regular shift schedules to prevent overlaps
   - Support for primary and backup on-call assignments

### Shift Settings

For each department and staff type, shift settings can be customized:
- Shift durations (e.g., 8-hour, 12-hour shifts)
- Start and end times for different shift types
- Required minimum staff counts per shift
- Special shift types for weekends or holidays

## Team Leader Logic

Team leaders play a crucial role in the roster management hierarchy:

### Team Leader Functionality

- **Assignment**: Each department can assign team leaders for different staff types
- **Responsibilities**: Team leaders can review, approve, and adjust schedules
- **Oversight**: They can monitor staff distribution and workload balance
- **Approvals**: Leave requests and shift swap approvals flow through team leaders
- **Reports**: Access to departmental statistics and staff performance metrics

### Team Leader Assignment

- Team leaders are typically selected from senior staff (specialist doctors or senior nurses)
- They have department-specific oversight responsibilities
- Assignment is managed through the team leader management interface
- One staff member can be a team leader for multiple departments if needed

## System Architecture

The system follows a modular design pattern:

### Core Components

1. **Department Management**
   - Creation and configuration of hospital departments
   - Setting department-specific roster requirements

2. **Staff Management**
   - Staff profiles and qualification tracking
   - Work history and specialization records
   - Availability and leave management

3. **Roster Creation and Management**
   - Interactive calendar interfaces
   - Drag-and-drop shift assignment
   - Conflict detection and resolution
   - Schedule template creation and reuse

4. **Notification System**
   - Alerts for new schedule assignments
   - Reminders for upcoming shifts
   - Notifications for schedule changes or approval requests

5. **Reporting and Analytics**
   - Staff utilization reports
   - Department workload analysis
   - Schedule fairness metrics
   - Historical data comparison

## Getting Started

### Prerequisites

- PHP 8.2 or higher
- MySQL/MariaDB database
- Composer
- Node.js and NPM

### Installation

1. Clone the repository
```bash
git clone https://github.com/kelvinric123/roster.git
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
