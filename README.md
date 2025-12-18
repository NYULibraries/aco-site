# Arabic Collections Online site

## Local development

To get a Laravel 12 project running on a Mac using Sail (which is a Docker-based environment), you need to ensure three layers are ready: Homebrew (the Mac package manager), Docker (the engine), and the Laravel files themselves.

### Step 1: Install the Prerequisites

1) Install Homebrew (if not already installed):

```bash
/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
```

2) Install Docker Desktop: Laravel Sail requires Docker to run.

```bash
brew install --cask docker
```

Important: After this runs, press Cmd + Space, search for Docker, and open it. You must accept the terms and let it start before moving to Step 2.

3) Install PHP and Composer (Required only for the initial setup of the project):

```bash
brew install php composer
```

### Step 2: Initialize the Laravel Project

```bash
git clone https://github.com/NYULibraries/aco-site aco
cd aco
git checkout lara
cp .env.example .env
composer run setup
```

### Step 3: Configure Laravel Sail

Install Sail into the project:

```bash
php artisan sail:install
```

Start the environment:

```bash
./vendor/bin/sail up -d
```
