# CrimeManagementSystem

A web-based application for managing crime records, FIRs, police stations, and user profiles. This system streamlines the process of crime reporting, analysis, and management for administrators, police, and users.

## Features

- FIR submission and approval workflow
- Criminal record management
- Police station management
- User authentication (admin, police, user)
- Activity logs and analysis
- Profile management for all roles
- Secure session handling

## Technologies Used

- PHP (Backend)
- MySQL (Database)
- HTML, CSS, JavaScript (Frontend)

## Setup Instructions

1. Clone the repository:
   ```
   git clone https://github.com/yourusername/CrimeManagementSystem.git
   ```
2. Import the `db_crime.sql` database into MySQL.
3. Update database credentials in `conn.php`.
4. Place the project files in your web server directory (e.g., `htdocs` for XAMPP).
5. Access the application via your browser.

## File Structure

- `addCriminal.php`, `addpolice.php`, `addpolicestation.php`: Add new records
- `manageCriminal.php`, `managePolice.php`, `managePoliceStation.php`: Manage records
- `FIRForm.php`, `userFIR.php`: FIR submission
- `approvedFir.php`, `rejectedFir.php`, `sentFir.php`: FIR status management
- `crime_analysis.php`: Crime data analysis
- `myprofileadmin.php`, `myprofilepolice.php`, `myprofileuser.php`: Profile pages
- `uploads/`: Uploaded images and documents
- `conn.php`: Database connection
- `session.php`: Session management

## Screenshots

Add screenshots of your application in the `Img/` folder and reference them here.

