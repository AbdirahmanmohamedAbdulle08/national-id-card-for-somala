# National ID Card System for Somalia

A digital system for registering citizens and issuing national ID cards for Somalia. Built with **PHP**, **MySQL**, and **XAMPP**.

---

## 📋 Features

- ✅ Citizen registration and management
- ✅ National ID card generation
- ✅ QR code / barcode support
- ✅ Admin dashboard with statistics
- ✅ Search and verification of citizens
- ✅ Secure authentication (password hashing)
- ✅ Responsive design (Bootstrap)
- ✅ Print-ready ID card layout

---

## 🛠️ Tech Stack

| Layer | Technology |
|-------|------------|
| Backend | PHP 8.x |
| Database | MySQL / MariaDB |
| Server | Apache (XAMPP) |
| Frontend | HTML5, CSS3, Bootstrap 5, JavaScript |
| Version Control | Git & GitHub |

---

## ⚙️ Requirements

- [XAMPP](https://www.apachefriends.org/) (or WAMP/MAMP)
- PHP 8.0 or higher
- MySQL 5.7 or higher
- A modern browser (Chrome, Firefox, Edge)
- Git (optional)

---

## 🚀 Installation

### 1. Clone the repository

```bash
git clone https://github.com/AbdirahmanmohamedAbdulle08/national-id-card-for-somala.git

2. Copy the folder to XAMPP
Move the project folder to:

text
C:\xampp\htdocs\nic_system
3. Start XAMPP
Open XAMPP Control Panel and start:

✅ Apache

✅ MySQL

4. Create the Database
Open your browser and go to: http://localhost/phpmyadmin

Create a new database named: nic_db

Select the database → Import → upload the .sql file inside the database/ folder

Click Go

5. Configure the database connection
Open config/database.php (or includes/db.php) and verify:

php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "nic_db";
6. Open the project
In your browser, go to:

text
http://localhost/nic_system
Usage
Admin — login → Dashboard → Register citizens → Generate ID cards → View reports

Search — search by ID number or name to verify a citizen

Print — generate a print-ready ID card

🤝 Contributing
Fork the repository

Create a new branch: git checkout -b feature/feature-name

Commit: git commit -m "Add feature"

Push: git push origin feature/feature-name

Open a Pull Request

📝 License
This project is licensed under the MIT License — you are free to use, modify, and distribute it.

👤 Author
Abdirahman Mohamed Abdulle

GitHub: @AbdirahmanmohamedAbdulle08

Gmail:AbdirahmanmohamedAbdulle08@gmail.com

Repo: national-id-card-for-somala

📞 Support
If you have any questions or issues, open a new Issue on GitHub:

👉 https://github.com/AbdirahmanmohamedAbdulle08/national-id-card-for-somala/issues
