Project Overview

This is a simple web-based banking system built using PHP, MySQL, and Bootstrap. It allows users to signup, login, create bank accounts, and perform transactions such as credit and debit. An admin panel is included to manage and monitor all users, their accounts, and transactions.

The application is designed for learning purposes to demonstrate session management, database interactions, and CRUD operations in PHP.

Features
1. User Functionality

Signup: New users can create an account with a username, email, and password. Passwords are securely hashed using password_hash.

Login: Existing users can log in with their email and password. Sessions are used to maintain login state.

Dashboard (Welcome Page): After login, users are redirected to their dashboard where they can see their account information.

Account Management:

Users can create bank accounts with an account name and initial balance.

Initial deposit is automatically recorded as a credit transaction.

Transactions:

Users can credit (deposit) or debit (withdraw) money from their accounts.

Each transaction is recorded with type, amount, description, balance after transaction, and timestamp.

Transaction History: Users can see a table of all transactions associated with their accounts.

2. Admin Functionality

Admin Login: Admin can log in using predefined credentials.

Dashboard:

Admin can see all registered users.

For each user, admin can view all accounts and balances.

Admin can view all transaction histories for each account.

Security: Admin panel is only accessible after login and has a logout feature.

Database Structure

The application uses MySQL with the following tables:

users
| Column | Type | Description |
|--------|------|-------------|
| id | INT | Primary Key, Auto Increment |
| username | VARCHAR | Username of the user |
| email | VARCHAR | Email of the user |
| password | VARCHAR | Hashed password |

accounts
| Column | Type | Description |
|--------|------|-------------|
| id | INT | Primary Key, Auto Increment |
| user_id | INT | Foreign key to users table |
| account_name | VARCHAR | Name of the bank account |
| balance | DECIMAL | Current balance |

transactions
| Column | Type | Description |
|--------|------|-------------|
| id | INT | Primary Key, Auto Increment |
| account_id | INT | Foreign key to accounts table |
| type | ENUM | 'credit' or 'debit' |
| amount | DECIMAL | Transaction amount |
| description | VARCHAR | Description of transaction |
| balance_after | DECIMAL | Balance after transaction |
| created_at | TIMESTAMP | Timestamp of transaction |

How It Works

User Registration & Login

New users register via the signup page.

Passwords are hashed before storing in the database.

On login, credentials are verified and a session is created.

Account Creation

After login, users can create a bank account.

If an initial balance is entered, it is automatically stored as a credit transaction in the transactions table.

Performing Transactions

Users can credit or debit money using the transaction form.

Validations include:

Positive amount required

Cannot debit more than the current balance

Each transaction updates the accounts table and logs in the transactions table.

Admin Panel

Admin logs in using predefined credentials.

Admin can view all users, their accounts, and transaction history in a structured layout.

The admin panel is styled for easy readability and navigation.

Logout

Both users and admin can logout which destroys their session and redirects to the login page.

Technologies Used

Frontend: HTML, CSS, Bootstrap

Backend: PHP, MySQL

Database: MySQL / MariaDB

Session Management: PHP $_SESSION to track logged-in users

Password Security: password_hash() and password_verify()
