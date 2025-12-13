
![Logo](https://raw.githubusercontent.com/ImLammerz/VulnCentral/refs/heads/main/img/VC-GreenWide.png)

# VulnCentral
VulnCentral — Centralized Vulnerability Intelligence Platform

VulnCentral is a Centralized Vulnerability Intelligence Platform designed to help security teams collect, correlate, analyze, and manage vulnerability data across multiple systems in one unified dashboard.
## Features

- Intelligent severity analysis with automatic risk classification
- Centralized visibility of vulnerabilities across assets
- Historical scan tracking to measure risk over time
- PDF report management for evidence-based security workflows
- Fast search, filtering, and pagination for large datasets
- Fully responsive, mobile-ready interface
- Role-based access control (Admin & View roles)



## Installation

1) Clone the Repository

```bash
git clone https://github.com/ImLammerz/VulnCentral.git
cd VulnCentral
```
2) Install & Start a Server (XAMPP Recommended)

```bash
Install XAMPP (Apache + MySQL/MariaDB)
Start:
Apache
MySQL

Move the project folder into:
Windows: C:\xampp\htdocs\vulncentral
Linux: /opt/lampp/htdocs/vulncentral
macOS (XAMPP): /Applications/XAMPP/htdocs/vulncentral
```
3) Create Database vc_security
```bash
Open phpMyAdmin:
Go to: https://yoursite/phpmyadmin
Create a new database:
Name: vc_security
Collation: utf8mb4_unicode_ci (recommended)
Then import vc_security.sql
```
4) Configure Database Connection (config.php)
```bash
$host = "yourhost";
$user = "youruser";
$pass = "yourpass";
$db   = "vc_security";
```
5) Run VulnCentral

Open in your browser:
Login: https://vulncentral/login.php
## Screenshots

![App Screenshot](https://raw.githubusercontent.com/ImLammerz/VulnCentral/refs/heads/main/ss/Login.png)

![App Screenshot](https://raw.githubusercontent.com/ImLammerz/VulnCentral/refs/heads/main/ss/Dashboard.png)

![App Screenshot](https://raw.githubusercontent.com/ImLammerz/VulnCentral/refs/heads/main/ss/Administration.png)


## Feedback

If you have any feedback, please reach out to me at sltfalah(@).com

