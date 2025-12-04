Exam Seating Management System:
A simple web-based system to manage exam seating plans.
Built using HTML, CSS, JavaScript, PHP, and MySQL.


🔹 Project Overview
This system allows admins to upload or manually enter student data and automatically generate seating arrangements. Students can check their seat by entering their CMS ID.


🔹 Features
For Students:
Enter CMS ID to view Room, Row, and Column.
Clean and simple lookup page.

For Admin:
Login & registration system
Upload CSV seating file
Add students manually
Edit / delete student entries
MySQL-based data storage


🔹 Tech Stack
Frontend: HTML, CSS, JavaScript
Backend: PHP
Database: MySQL
Tools: XAMPP, VS Code, Git, Figma



🔹 Database Structure

admins:
Field	         Type	                     Notes
id	             INT (PK)               	 Auto increment
full_name	     VARCHAR	                 Admin name
email	         VARCHAR	                 Unique
password	     VARCHAR	                 Hashed password

students:
Field	         Type	                     Notes
id	             INT (PK)	                 Auto increment
cms_id	         VARCHAR	                 Unique
full_name	     VARCHAR	                 Student name
room_no	         VARCHAR	                 Assigned room
seat_row	     INT	                     Row number
seat_col	     INT	                     Column number


🔹 How to Run
Install XAMPP
Move project to:
htdocs/exam-seating/
Import the SQL file into phpMyAdmin
Start Apache + MySQL
Open in browser:
http://localhost/exam-seating/index.html



🔹 Future Improvements
Generate PDF seating plans
Add room-wise grid visualization
Add search + filters for admin
Dark mode 


🔹 License
This project is for educational purposes.



## 🔗 Project Resources

### 🎨 Figma Design  
[![View Figma](https://www.figma.com/proto/G13jrslnDLHz7kTF5YJMqH/Untitled?node-id=1-2&starting-point-node-id=1%3A2)]

### 🎥 YouTube Demo  
[![Watch Video](https://img.shields.io/badge/Watch_on_YouTube-FF0000?style=for-the-badge&logo=youtube&logoColor=white)]

