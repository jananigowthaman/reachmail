# ReachMail

**A Laravel-based email outreach and campaign management application.**

<p align="center">
  <a href="https://laravel.com" target="_blank">
    <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="350" alt="Laravel Logo">
  </a>
</p>

<p align="center">
  <a href="https://laravel.com/docs"><img src="https://img.shields.io/badge/Laravel-Framework-FF2D20?logo=laravel&logoColor=white" alt="Laravel"></a>
  <a href="https://www.php.net/"><img src="https://img.shields.io/badge/PHP-Backend-777BB4?logo=php&logoColor=white" alt="PHP"></a>
  <a href="https://www.mysql.com/"><img src="https://img.shields.io/badge/MySQL-Database-4479A1?logo=mysql&logoColor=white" alt="MySQL"></a>
  <a href="https://github.com/jananigowthaman/reachmail"><img src="https://img.shields.io/badge/Project-ReachMail-0E8A65" alt="ReachMail"></a>
</p>

## About ReachMail

ReachMail is a web application built with Laravel and PHP to help organize professional email outreach, manage contacts, and maintain campaign records in one place.

The project is designed to support workflows such as recruiter outreach, job applications, professional networking, and business communication. It aims to reduce repetitive work by bringing contact organization, email preparation, campaign management, and outreach history together in a single workspace.

## Key Features

### Contact Management
- Organize professional contacts for targeted outreach.
- Import contact information from supported file formats, where implemented.
- Maintain contact details for campaign preparation.

### Email Campaign Management
- Create and organize email campaigns.
- Prepare messages for specific audiences.
- Review campaign details and maintain campaign history.
- Manage campaign status and sending workflows.

### Email Personalization
- Prepare reusable email content for different outreach purposes.
- Personalize messages using contact information, where supported.
- Review email content before sending.

### Outreach History
- Keep campaign records organized.
- Review previous outreach activity.
- Track sending status and campaign results according to the configured email integration.

### Development Focus
- Responsive web interface.
- Structured backend application using Laravel.
- Database-backed contact and campaign management.
- Integration with email infrastructure as configured.

> **Note:** Feature availability depends on the current implementation and configuration. Email delivery, open tracking, reply tracking, scheduling, and other provider-dependent features require the relevant integrations.

## Technology Stack

| Technology | Purpose |
|---|---|
| [Laravel](https://laravel.com/) | Backend web application framework |
| [PHP](https://www.php.net/) | Server-side development |
| [MySQL](https://www.mysql.com/) | Relational database |
| HTML5 | Page structure |
| CSS3 | Styling and responsive layouts |
| JavaScript | Frontend interactions |
| [Bootstrap](https://getbootstrap.com/) | Responsive UI components, if used |
| [Git](https://git-scm.com/) | Version control |

## Getting Started

Follow these instructions to set up ReachMail in a local development environment.

### Prerequisites

Install the following tools before proceeding:

- [PHP](https://www.php.net/downloads.php) compatible with the project
- [Composer](https://getcomposer.org/download/)
- [MySQL](https://dev.mysql.com/downloads/)
- [Git](https://git-scm.com/downloads)
- [Node.js and npm](https://nodejs.org/), if required by the frontend build

### Installation

**1. Clone the repository**

```bash
git clone https://github.com/jananigowthaman/reachmail.git
```

**2. Navigate to the project directory**

```bash
cd reachmail
```

**3. Install PHP dependencies**

```bash
composer install
```

**4. Configure environment variables**

Create a local environment file:

```bash
cp .env.example .env
```

On Windows Command Prompt, use:

```cmd
copy .env.example .env
```

**5. Generate the application key**

```bash
php artisan key:generate
```

**6. Configure the database**

Create a MySQL database and update the following values in your `.env` file:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=reachmail
DB_USERNAME=root
DB_PASSWORD=
```

Replace the database name, username, and password with your local configuration.

**7. Run database migrations**

```bash
php artisan migrate
```

If the application requires seed data, run the appropriate seeders after reviewing the project configuration.

**8. Install frontend dependencies, if required**

```bash
npm install
npm run build
```

**9. Start the development server**

```bash
php artisan serve
```

Open the local URL displayed in the terminal. By default, Laravel uses:

[http://127.0.0.1:8000](http://127.0.0.1:8000)

### Configuration

Configure the email provider, API credentials, queues, scheduled tasks, or other integrations if they are required by your implementation.

Keep credentials in environment variables and never commit private configuration files.

## Security and Responsible Use

- Never commit `.env` files, API keys, passwords, or email provider credentials.
- Use authorized email accounts and appropriate recipient consent.
- Test email workflows with test recipients before sending real campaigns.
- Follow applicable privacy, email marketing, and anti-spam requirements.
- Treat email open tracking as an estimate because privacy features and image blocking can affect tracking accuracy.

If you discover a security vulnerability in a public deployment, follow the repository owner's published security reporting instructions.

## Project Structure

ReachMail follows the Laravel application structure. Common directories include:

```text
reachmail/
├── app/             # Application logic
├── bootstrap/       # Application bootstrapping
├── config/          # Configuration files
├── database/        # Migrations, factories and seeders
├── public/          # Public entry point and assets
├── resources/       # Views and frontend resources
├── routes/          # Application routes
├── storage/         # Logs and generated files
├── tests/           # Automated tests
├── .env.example     # Example environment configuration
├── artisan          # Laravel command-line interface
├── composer.json    # PHP dependencies
└── package.json     # Frontend dependencies, if applicable
```

The exact structure may vary depending on the current implementation.

## Contributing

Contributions, suggestions, and bug reports are welcome.

1. Fork the repository.
2. Create a feature branch.
3. Implement and test your changes.
4. Submit a pull request describing the changes.

## Author

**Janani Gowthaman**

- GitHub: [@jananigowthaman](https://github.com/jananigowthaman)
- Project Repository: [ReachMail](https://github.com/jananigowthaman/reachmail)

## License

ReachMail's license has not yet been specified. The Laravel framework's MIT license does not automatically determine the license of this application.

Add a `LICENSE` file and update this section when you decide how the project source code may be used, modified, and distributed.

---

<p align="center">
  Built with Laravel and PHP.
</p>