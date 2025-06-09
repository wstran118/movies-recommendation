# movies-recommendation
A RESTful API built with PHP 8 and PostgreSQL, providing user authentication, movie management, and personalized movie recommendations. The project includes a simple front-end interface for testing, with separate login and register pages, activity logging, and performance tracking for API calls.

## Features

- User Authentication: Register and login with JWT-based authentication.
- Movie Management: List movies, rate movies (1-5), view trending movies, and get personalized recommendations.
- Activity Logging: Tracks user actions (register, login, rate movie, etc.) with execution times in the database.
- Permance Tracking: Measures and returns execution time for each API call, displayed in the front-end.
- Modular Design: Organized into controllers and services with PHP 8 strict typing.
- Front-End: Separate login.html, register.html, and test.html pages using Tailwind CSS.
- Database: PostgreSQL with explicit column selection for optimized queries and activity logging.
