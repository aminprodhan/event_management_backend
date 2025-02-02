# Event Management System

## Project Overview

This is a simple event management system that allows users to create, manage, and view events, register attendees, and generate event reports. The system is built using **PHP** for the frontend, ensuring a smooth user experience and robust functionality.

## Features

- **User Authentication**: Secure login and registration system.
- **Role-Based Access Control**: Admin, User.
- **Event Creation & Management**: Create, edit,viwe and delete events.  
- **Event Dashboard**: View an overview of all events.  
- **Event Search**: Users can search for a specific event.  
- **Attendee Management**: Register and manage attendees.  
- **Reports**: View event details and download attendee lists as a CSV file..

## Installation Instructions

### Prerequisites

Ensure you have the following installed on your system:

- PHP (>= 8.1)
- Composer
- Apache/Nginx
- MySQL Database

### Backend Setup (Laravel)

```sh
# Clone the repository
git clone https://github.com/aminprodhan/event_management_backend.git
cd event_management_backend

#import database
import event_management.sql file as new db

# Install dependencies
composer install

# Configure database in .env file
DB_CONNECTION=mysql  
DB_HOST=127.0.0.1  
DB_PORT=3306  
DB_DATABASE=event_management  
DB_USERNAME=root  
DB_PASSWORD=


## Login Credentials

### Admin Account

- **Email**: [admin@gmail.com]
- **Password**: 123

