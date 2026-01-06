# Migration plan

The scope of the project is to migrate the site from the current build strategy and to
replace the old JavaScript framework (YUI3). The site design will remain as-is unless
a circumstance necessitates a change.

##### Site details

- The current site URL is https://dlib.nyu.edu/aco/, but we will redirect that URL to the new URL https://aco.dlib.nyu.edu/
- The site's GitHub repository: https://github.com/NYULibraries/aco-site/tree/lara

##### Prep work

1) **DONE**: Architecture Review: Explore the current site architecture and functionality to better
understand the task and requirements. This will allow us to compile a list of questions
and function requirements.

2) **DONE**: Framework Decision: Make a decision on the framework/architecture we will use. I shared
a version of the site that used only PHP as an example of what can be done with a PHP-only
approach, but I am open to creating a single-page app with React or other alternatives.

Decision:

- Apache
- homebrew
  - PHP 8.2.28
  - Laravel 12 (Blade)
- nvm
  - NodeJS v20.19.6
  - pnpm

##### Local development

To get a Laravel 12 project running on a Mac using Sail (which is a Docker-based environment), you need to ensure three layers are ready: Homebrew (the Mac package manager), Docker (the engine), and the Laravel files themselves.

##### Step 1: Install the Prerequisites

1) - Install Homebrew (if not already installed):

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

##### Step 2: Initialize the Laravel Project

```bash
git clone https://github.com/NYULibraries/aco-site aco
git checkout lara
cd aco
composer run setup
```

##### Step 3: Configure Laravel Sail

Install Sail into the project:

```bash
php artisan sail:install
```

Start the environment:

```bash
./vendor/bin/sail up -d
```

##### Setup & Discovery

- Write set-up basic documentation (README.md)
- Set up Laravel Sail

##### Technical Debt & Risks

1) YUI3 Removal: We must ensure no legacy YUI3 scripts are being enqueued. If the "design remains as-is," we need to extract the raw CSS from the old site and move it into resources/css/app.css.

2) The "Subdomain" Trap: Moving from /aco/ (folder) to aco. (subdomain) means any hardcoded absolute links in the old static files (e.g., <a href="/aco/about">) will break. Action: Run a global "Find and Replace" for /aco/ in the content strings.

##### Migrate routes

- Home (https://dlib.nyu.edu/aco/ | http://3.219.126.115/aco/)
  - Total count of volumes across (needs dynamic counts or cache with cron job.) | Damon  
  - Subjects count (needs dynamic counts or cache with cron job.) | Damon  
  - Style shift | Damon  
  - Featured Titles | Taka

- About (https://dlib.nyu.edu/aco/about/ | http://3.219.126.115/aco/about/)
  - Done

- Other Resources (https://dlib.nyu.edu/aco/resources/ | http://3.219.126.115/aco/resources/ )
  - Trailing slash on the Arabic URL | Damon

- Browse titles (https://dlib.nyu.edu/aco/browse/ | http://3.219.126.115/aco/browse/)
  - Page title | Damon
  - Browse widget | Taka

- Browse by Category (https://dlib.nyu.edu/aco/browse-by-category/ | http://3.219.126.115/aco/browse-by-category/)
  - Style shift  | Damon
  - "All" link is missing from the English and Arabic section  | Damon
  - Arabic section: The tags count is missing "books" | Damon

- Search Collections (https://dlib.nyu.edu/aco/searchcollections/ | http://3.219.126.115/aco/searchcollections/)
  - Done

- Search Results (https://dlib.nyu.edu/aco/search/ | http://3.219.126.115/aco/search/)
  - Search widget | Taka
  - "About this search" dropdown not working | Damon
  - Pagination style | Damon

Others:

- https://nyu.atlassian.net/browse/DLPAS-372 | Damon
  - Icon: /aco/images/logos/uae2.png
