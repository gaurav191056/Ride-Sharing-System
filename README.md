# 🚗 RideShare — PHP & MySQL Ride Sharing System

![PHP](https://img.shields.io/badge/PHP-8.x-777BB4?style=flat-square&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=flat-square&logo=mysql&logoColor=white)
![License](https://img.shields.io/badge/License-MIT-green?style=flat-square)
![Status](https://img.shields.io/badge/Status-Active-brightgreen?style=flat-square)

A full-stack ride sharing web application built with PHP and MySQL. Supports user registration, ride booking, real-time driver acceptance, ride status tracking, and a rating system.

---

## 📋 Table of Contents

- [Features](#-features)
- [Tech Stack](#-tech-stack)
- [Project Structure](#-project-structure)
- [Database Schema](#-database-schema)
- [System Flowchart](#-system-flowchart)
- [Flow Description](#-flow-description)
- [Getting Started](#-getting-started)
- [API / Page Reference](#-page-reference)
- [Security Considerations](#-security-considerations)
- [Future Improvements](#-future-improvements)

---

## ✨ Features

- **User Authentication** — Register, login, session management, and logout
- **Ride Booking** — Book rides with pickup/drop location and ride type selection
- **Driver Panel** — Drivers view and accept pending ride requests
- **Ride Status Tracking** — Live status flow: `pending → accepted → ongoing → completed`
- **Fare & Wallet** — Auto-calculated fare stored in the wallet table
- **Ride History** — Users can view all past and current rides
- **Ratings & Reviews** — Users rate drivers post-ride; driver average rating updated
- **Profile Management** — Users and drivers manage their profile data

---

## 🛠 Tech Stack

| Layer      | Technology          |
|------------|---------------------|
| Frontend   | HTML5, CSS3         |
| Backend    | PHP 8.x             |
| Database   | MySQL 8.0           |
| Server     | Apache / Nginx      |
| Sessions   | PHP native sessions |

---

## 📁 Project Structure

```
rideshare/
├── register.html          # Registration form UI
├── register.php           # Handles user registration logic
├── login.html             # Login form UI
├── login.php              # Authenticates user credentials
├── dashboard.php          # Main user dashboard after login
├── book_ride.html         # Ride booking form UI
├── book_ride.php          # Processes ride booking request
├── driver_panel.php       # Driver dashboard — lists pending rides
├── accept_ride.php        # Driver accepts a specific ride
├── update_ride_status.php # Updates ride: accepted → ongoing → completed
├── view_rides.php         # User ride history viewer
├── rate_ride.php          # Post-ride rating and review submission
├── profile.php            # User/driver profile management
└── logout.php             # Clears session and logs out user
```

---

## 🗃 Database Schema

### `users`
| Column       | Type         | Description              |
|--------------|--------------|--------------------------|
| id           | INT PK AI    | Unique user ID           |
| name         | VARCHAR(100) | Full name                |
| email        | VARCHAR(100) | Email (unique)           |
| password     | VARCHAR(255) | Hashed password          |
| phone        | VARCHAR(20)  | Contact number           |
| created_at   | TIMESTAMP    | Registration timestamp   |

### `drivers`
| Column       | Type         | Description              |
|--------------|--------------|--------------------------|
| id           | INT PK AI    | Unique driver ID         |
| user_id      | INT FK       | References users.id      |
| license_no   | VARCHAR(50)  | Driver's license number  |
| is_available | TINYINT(1)   | 1 = available, 0 = busy  |
| rating       | DECIMAL(3,2) | Average rating           |

### `vehicles`
| Column       | Type         | Description              |
|--------------|--------------|--------------------------|
| id           | INT PK AI    | Vehicle ID               |
| driver_id    | INT FK       | References drivers.id    |
| make         | VARCHAR(50)  | Car make                 |
| model        | VARCHAR(50)  | Car model                |
| plate_no     | VARCHAR(20)  | License plate            |
| type         | VARCHAR(30)  | Economy / Premium / etc  |

### `rides`
| Column         | Type         | Description                              |
|----------------|--------------|------------------------------------------|
| id             | INT PK AI    | Ride ID                                  |
| user_id        | INT FK       | Passenger (references users.id)          |
| driver_id      | INT FK       | Assigned driver (references drivers.id)  |
| pickup         | VARCHAR(255) | Pickup location                          |
| destination    | VARCHAR(255) | Drop-off location                        |
| status         | ENUM         | pending / accepted / ongoing / completed |
| fare           | DECIMAL(8,2) | Calculated fare                          |
| created_at     | TIMESTAMP    | Booking time                             |
| completed_at   | TIMESTAMP    | Completion time                          |

### `reviews`
| Column     | Type         | Description              |
|------------|--------------|--------------------------|
| id         | INT PK AI    | Review ID                |
| ride_id    | INT FK       | References rides.id      |
| user_id    | INT FK       | Reviewer                 |
| driver_id  | INT FK       | Reviewed driver          |
| rating     | TINYINT      | 1–5 stars                |
| comment    | TEXT         | Written review           |
| created_at | TIMESTAMP    | Review timestamp         |

### `wallet`
| Column       | Type         | Description              |
|--------------|--------------|--------------------------|
| id           | INT PK AI    | Wallet entry ID          |
| user_id      | INT FK       | References users.id      |
| ride_id      | INT FK       | References rides.id      |
| amount       | DECIMAL(8,2) | Transaction amount       |
| type         | ENUM         | debit / credit           |
| created_at   | TIMESTAMP    | Transaction time         |

---

## 🔄 System Flowchart

![Flowchart](rideshare_system_flowchart.svg)

## 📖 Flow Description

### 1. User Registration / Login

1. A new visitor lands on **register.html** and submits their details via **register.php**, which hashes the password and inserts a row into the `users` table.
2. Returning users go directly to **login.html**. Credentials are checked in **login.php**; on success a session is created and the user is redirected to **dashboard.php**.
3. Failed logins redirect back to the login form.

### 2. Ride Booking

1. From **dashboard.php**, the user navigates to **book_ride.html** and enters pickup location, drop-off, and ride type.
2. **book_ride.php** checks for available drivers. If none are found, the user is notified and can retry.
3. On success, a new row is inserted into the `rides` table with `status = pending`.

### 3. Driver Accepting a Ride

1. **driver_panel.php** queries pending rides and presents them to the driver.
2. The driver clicks "Accept" which calls **accept_ride.php**, updating `status = accepted` and linking the driver to the ride.
3. If the driver declines or skips, the ride remains pending for the next driver.

### 4. Ride Status Updates

| Step         | File                    | Status Change            |
|--------------|-------------------------|--------------------------|
| Accept ride  | `accept_ride.php`       | `pending → accepted`     |
| Start trip   | `update_ride_status.php`| `accepted → ongoing`     |
| End trip     | `update_ride_status.php`| `ongoing → completed`    |

### 5. Ride Completion

1. On completion, fare is calculated and a debit entry is created in the `wallet` table.
2. The ride appears in **view_rides.php** under the user's history.

### 6. Rating & Review

1. After a completed ride, the user is prompted in **rate_ride.php** to submit a 1–5 star rating and an optional comment.
2. The review is saved to the `reviews` table and the driver's average rating in `drivers` is updated.
3. The user can then visit **profile.php** or return to the dashboard for another booking.

---

## ⚙️ ER Diagram 
![WhatsApp Image 2026-04-14 at 22 17 57](https://github.com/user-attachments/assets/c35c4731-4053-401f-971d-190dc4f42965)



## 🚀 Getting Started

### Prerequisites

- PHP 8.x
- MySQL 8.0
- Apache or Nginx (XAMPP / WAMP recommended for local dev)

### Installation

```bash
# 1. Clone the repository
git clone https://github.com/your-username/rideshare.git
cd rideshare

# 2. Import the database schema
mysql -u root -p < database/schema.sql

# 3. Configure DB connection
cp config.sample.php config.php
# Edit config.php with your DB credentials

# 4. Start your local server and open in browser
# http://localhost/rideshare/login.html
```

---

## 📄 Page Reference

| File                   | Role    | Description                                  |
|------------------------|---------|----------------------------------------------|
| `register.html/.php`   | Public  | New user registration                        |
| `login.html/.php`      | Public  | Credential-based login                       |
| `dashboard.php`        | User    | Main hub after login                         |
| `book_ride.html/.php`  | User    | Ride booking form and processing             |
| `driver_panel.php`     | Driver  | Pending rides list for drivers               |
| `accept_ride.php`      | Driver  | Accepts a specific ride                      |
| `update_ride_status.php`| Driver | Moves ride through status stages             |
| `view_rides.php`       | User    | Ride history list                            |
| `rate_ride.php`        | User    | Submit post-ride rating and review           |
| `profile.php`          | Both    | View and edit profile details                |
| `logout.php`           | Both    | Destroy session and redirect to login        |

---

## 🔐 Security Considerations

- Passwords are stored using `password_hash()` with `PASSWORD_BCRYPT`
- All user input should be sanitized using `mysqli_real_escape_string()` or prepared statements
- Session tokens are regenerated on login to prevent session fixation
- Role-based access: driver-only pages check session role before rendering
- HTTPS is strongly recommended in production

---

## 🗺 Future Improvements

- [ ] Real-time ride tracking with WebSockets or polling
- [ ] Google Maps API integration for route display and fare estimation
- [ ] Push notifications for ride status changes
- [ ] OTP-based phone number verification
- [ ] Admin dashboard for platform analytics
- [ ] Payment gateway integration (Stripe / Razorpay)
- [ ] Driver location broadcasting via GPS API
- [ ] Mobile-responsive redesign with Tailwind CSS

---

## 📜 License

This project is licensed under the [MIT License](LICENSE).

---

> Built with PHP & MySQL · Designed for learning and extensibility
