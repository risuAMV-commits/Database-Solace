# Solace Resort - Hotel Booking System
[![Ask DeepWiki](https://devin.ai/assets/askdeepwiki.png)](https://deepwiki.com/risuAMV-commits/Database-Solace)

Solace Resort is a comprehensive, web-based hotel booking and management system built with PHP and MySQL. It provides a dual-interface platform catering to both customers looking to book rooms and administrators managing the resort's operations. The system features secure user authentication, real-time data dashboards, and dynamic management of rooms, customers, and bookings.

## ✨ Key Features

### 👑 Admin Panel
*   **Secure Dashboard:** A central hub for administrators, protected by a dedicated login.
*   **Real-time Analytics:** Interactive dashboard with live-updating statistics and charts for total customers, room occupancy, booking statuses, and more.
*   **Customer Management:** View all registered customers, manage their VIP status, and remove accounts.
*   **Room Management:** Add, edit, and delete room listings with details like room type, pricing, description, category (Regular/VIP), and image uploads.
*   **Booking Management:** A comprehensive overview of all bookings. Admins can view details, edit dates or assigned rooms, and cancel reservations.
*   **Dynamic Search & Filtering:** Easily search and filter through customers and bookings to find specific records.

### 👥 Customer Portal
*   **User Authentication:** Secure customer registration and login system with password reset functionality.
*   **Personalized Dashboard:** A welcoming dashboard for logged-in users, showcasing available rooms.
*   **Room Browsing:** Customers can browse through a catalog of available rooms, with distinct sections for Regular and exclusive VIP rooms.
*   **VIP System:** VIP members gain access to premium rooms and receive a 10% discount on all bookings, which is automatically calculated.
*   **Seamless Booking:** An easy-to-use booking process, from date selection and cost preview to final confirmation.
*   **Profile Management:** Users can view and update their personal information (name, email, contact number) and have the option to delete their account.
*   **My Bookings:** A dedicated section for customers to view their past and present bookings and cancel active reservations.

## 🛠️ Technologies Used
*   **Backend:** PHP
*   **Database:** MySQL
*   **Frontend:** HTML, CSS, JavaScript
*   **Charting:** Chart.js for admin dashboard visualizations.

## 🚀 Getting Started

To get a local copy up and running, follow these simple steps.

### Prerequisites
You need a local web server environment that supports PHP and MySQL.
*   [XAMPP](https://www.apachefriends.org/index.html) (cross-platform)
*   [WAMP](https://www.wampserver.com/en/) (for Windows)
*   [MAMP](https://www.mamp.info/en/windows/) (for macOS & Windows)

### Installation Steps
1.  **Clone the Repository**
    ```sh
    git clone https://github.com/risuamv-commits/database-solace.git
    ```

2.  **Move to Web Server Directory**
    Move the cloned project folder into your web server's document root. For XAMPP, this is typically the `htdocs` folder (`C:\xampp\htdocs`).

3.  **Database Setup**
    a. Open your database management tool (e.g., phpMyAdmin, accessible at `http://localhost/phpmyadmin`).

    b. Create a new database named `resort_booking`.

    c. Select the `resort_booking` database and run the following SQL queries to create the necessary tables and a default admin user.

    ```sql
    --
    -- Table structure for table `admin`
    --
    CREATE TABLE `admin` (
      `admin_id` int(11) NOT NULL AUTO_INCREMENT,
      `username` varchar(255) NOT NULL,
      `password` varchar(255) NOT NULL,
      PRIMARY KEY (`admin_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    --
    -- Dumping data for table `admin`
    --
    INSERT INTO `admin` (`admin_id`, `username`, `password`) VALUES
    (1, 'admin', 'admin123');

    --
    -- Table structure for table `customer`
    --
    CREATE TABLE `customer` (
      `Customer_ID` int(11) NOT NULL AUTO_INCREMENT,
      `Name` varchar(255) NOT NULL,
      `Email` varchar(255) NOT NULL,
      `Contact_Number` varchar(20) DEFAULT NULL,
      `Password` varchar(255) NOT NULL,
      `VIP_Status` varchar(50) DEFAULT 'Regular',
      PRIMARY KEY (`Customer_ID`),
      UNIQUE KEY `Email` (`Email`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    --
    -- Table structure for table `room`
    --
    CREATE TABLE `room` (
      `Room_ID` int(11) NOT NULL AUTO_INCREMENT,
      `Room_Type` varchar(255) NOT NULL,
      `Price_Per_Night` decimal(10,2) NOT NULL,
      `Availability` varchar(50) NOT NULL DEFAULT 'Available',
      `Description` text DEFAULT NULL,
      `Category` varchar(50) DEFAULT 'Regular',
      `Image` varchar(255) DEFAULT NULL,
      PRIMARY KEY (`Room_ID`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    --
    -- Table structure for table `booking`
    --
    CREATE TABLE `booking` (
      `Booking_ID` int(11) NOT NULL AUTO_INCREMENT,
      `Customer_ID` int(11) NOT NULL,
      `Room_ID` int(11) NOT NULL,
      `Book_In` date NOT NULL,
      `Book_Out` date NOT NULL,
      `Nights_Stayed` int(11) NOT NULL,
      `Total_Price` decimal(10,2) NOT NULL,
      `Status` varchar(50) NOT NULL DEFAULT 'Active',
      PRIMARY KEY (`Booking_ID`),
      KEY `Customer_ID` (`Customer_ID`),
      KEY `Room_ID` (`Room_ID`),
      CONSTRAINT `booking_ibfk_1` FOREIGN KEY (`Customer_ID`) REFERENCES `customer` (`Customer_ID`) ON DELETE CASCADE,
      CONSTRAINT `booking_ibfk_2` FOREIGN KEY (`Room_ID`) REFERENCES `room` (`Room_ID`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ```
    
4.  **Configure Database Connection**
    a. Open the `config.php` file in the project root.

    b. Update the database connection details to match your local environment.
    ```php
    $host = 'localhost:3307'; // Change this to your MySQL host, e.g., 'localhost' or '127.0.0.1'
    $db   = 'resort_booking'; // Database name
    $user = 'root';           // Your MySQL username
    $pass = '';               // Your MySQL password
    ```
    > **Note:** The default host is set to `localhost:3307`. If your MySQL server runs on the default port (3306), you should change `$host` to `'localhost'`.

5.  **Run the Application**
    a. Start your Apache and MySQL services from the XAMPP/WAMP/MAMP control panel.

    b. Open your web browser and navigate to `http://localhost/database-solace/` (or the name you gave the project folder).

    c. You can log in to the admin panel at `http://localhost/database-solace/admin_login.php` with:
    *   **Username:** `admin`
    *   **Password:** `admin123`

## 📸 Project Showcase

### Landing Page
The public-facing homepage provides a modern and welcoming introduction to the resort. It features:
- A clean navigation bar with links to log in, register, or access the admin panel.
- An impressive hero section with the resort's name and tagline.
- A showcase of featured available rooms with high-quality images, pricing, and a "Book Now" call-to-action.
- An "About Us" section highlighting the resort's key amenities.

### Customer Dashboard
After logging in, customers are directed to their personal dashboard, which includes:
- A personalized welcome message.
- A display of their current membership status (Regular or VIP).
- A grid of currently available rooms, tailored to their membership level (VIPs see all rooms).
- Easy navigation to book a room, view their bookings, or manage their profile.

### Admin Dashboard
The admin dashboard is a powerful tool for managing the entire resort operation. It is designed for clarity and efficiency:
- A grid of statistical cards showing live counts of customers, rooms, bookings, and more.
- Interactive charts visualizing customer growth trends and the distribution of booking statuses.
- A sidebar providing quick access to all management sections: Customers, Rooms, and Bookings.
- The dashboard data refreshes automatically, providing administrators with the most current information at a glance.