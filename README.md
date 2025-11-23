# facebook-simple-clone
📌 Mini Social Network Login System + CRUD

Backend Development with PHP — Level 6, Year 2 (RP Tumba College)

This project is a simplified social-network-style authentication system built using PHP, HTML, CSS, and MySQL. It includes user sign-up, sign-in, mocked Google login, UI mockups, form validation, and finally redirects to a CRUD application.

📚 1. Project Overview

The system is inspired by [Your Chosen Platform → e.g., Facebook, Instagram, X, TikTok, LinkedIn, etc.].
It features a clean interface consistent with the design language of that platform.

The main purpose of the project is to demonstrate:

UI/UX design skills

Backend implementation in PHP

Form validation

Authentication logic

Integration with an existing CRUD project

Basic security practices

🎨 2. UI Mockups

UI mockups were designed using [Your Tool: Figma / Canva / Adobe XD / Pencil / Hand-drawn].
Mockups included:

Sign Up Page

Sign In Page

Dashboard / Home Page

📎 Screenshots and mockup images are included in the repository under the folder:
/ui-mockups/

🧑‍💻 3. Features Implemented
✔️ Sign Up Page

Username

Email

Password

Confirm Password

✔️ Validation Rules

No empty fields

Valid email format

Password ≥ 6 characters

Password confirmation must match

All errors displayed with styled messages

✔️ Sign In Page

Login using email or username + password

Error messages for:

Empty fields

Wrong password

Account not found

✔️ Mock Google Login

Button: “Login with Google”

Simulated login (no real API)

Automatically redirects to Dashboard

✔️ Redirect to CRUD

After successful login (normal or Google), the user is redirected to an existing CRUD project:
[Specify: e.g., Student CRUD / Product CRUD / Employee CRUD / Posts CRUD]

✔️ Platform-Inspired Design

The look and feel imitates the chosen social platform through:

Color palette

Typography

Rounded buttons / layout

Error message style



🛢️ 5. Database

A simple MySQL table named users:


📎 Database export included under:
/database/facebook.sql


📄 7. One-Page Report Summary

Platform Chosen:
Your selected platform (e.g., “Inspired by Instagram”).

Features Implemented:

UI mockups

Sign up

Sign in

Form validation

Mock Google login

Redirect to CRUD

Challenges Faced:

Handling PHP form validation

Matching UI to platform style

Maintaining session state after login

Connecting login system with CRUD project

🚀 How to Run the Project

Clone the repository:

git clone https://github.com/docile-imbereyemaso/facebook-simple-clone


Import database:

Go to phpMyAdmin

Create a database

Import sql/database.sql

Configure database connection in:

config/db.php


Run using a local server such as:

XAMPP



