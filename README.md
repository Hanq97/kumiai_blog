# Kumiai WordPress Blog

A custom WordPress blog project specifically built for the **Kumiai System (監理ワン)**. It features a modern, Japanese B2B-style card-based design and a fully integrated Docker environment for easy local development and Git-based server deployments.

## Key Features
- **Minimalist UI:** A clean, 3-column card grid design optimized for modern readability and Japanese corporate standards.
- **Auto Dummy Content:** Upon the first launch, the system automatically generates 14 SEO-optimized test posts (complete with H2/H3 headings) and configures the homepage and permalinks automatically without any manual setup.
- **Dynamic Table of Content (TOC):** A custom Vanilla JS script automatically extracts H2/H3 headings from posts and generates a sticky Table of Contents in the sidebar.
- **WP Customizer Integration:** Easily update the main Logo link and the sidebar/footer CTA (Call-to-Action) buttons directly from the WordPress Admin Dashboard without looking at code.

## Prerequisites
- [Docker Desktop](https://www.docker.com/products/docker-desktop) and Docker Compose installed on your local machine or VPS.

## Local Setup Instructions

1. Clone the repository:
   ```bash
   git clone [your-repo-link]
   cd kumiai-wp
   ```
2. Start the Docker containers:
   ```bash
   docker compose up -d
   ```
   *(If no `.env` file is present, the system defaults to using `root_password` and `wp_password` for initial setup)*

3. Open your browser:
   - **Frontend:** `http://localhost:8000/`
   - **Admin Dashboard:** `http://localhost:8000/wp-admin/`
   *(Admin credentials are the ones you create during the initial 1-minute WordPress setup screen)*

## Production Server Deployment (VPS)

Platforms like Vercel or Netlify are not suitable since WordPress requires MySQL and persisted volumes. Deploying to a Docker-capable VPS (like DigitalOcean, AWS EC2, or Vultr) is the recommended approach.

1. SSH into your VPS and ensure Git and Docker are installed.
2. Clone the repository:
   ```bash
   git clone [your-repo-link]
   cd kumiai-wp
   ```
3. Secure your Database Credentials:
   - Duplicate the example environment file:
     ```bash
     cp .env.example .env
     ```
   - Open `.env` and replace the placeholder passwords with strong, secure credentials. (Your `.env` file is ignored by Git, ensuring security).
4. Run the application:
   ```bash
   docker compose up -d
   ```
5. The built-in theme initialization script will automatically populate the database with the required posts and settings.

## Managing Menus & Navigation
- While posts are auto-generated, **Header and Footer menus** are intentionally left blank for you to customize.
- Navigate to **Admin Dashboard ➔ Appearance ➔ Menus** to create and assign links to your preferred navigational areas.

---
