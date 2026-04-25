# Sheenvalue Core Application

# Getting Started

To initialise the project use the following commands:

- install dependencies

```shell
  composer install

  npm install
```

- build assets and resources

```shell
  npm run build
```

- running migrations

```shell
  php bin/console doctrine:migrations:migrate --no-interaction
```

- seeding database

```shell
  php bin/console doctrine:fixtures:load
```

If you are not using `Apache`, you can use `symfony` dev server via running this
command:

```shell
symfony server:start
```

# Configuration

Make sure to create `.env.local` file with correct database credentials.

Database name **sheenvalue_core_app**.

Make sure to enable `extension=intl` in `php.ini` file.

- keep .env as is and create .env.local to override your local values
- The key files in config/jwt/\* are not uploaded to the repo, and you have to
  use your own local key files.
- create an empty MySql DB and use its credentials in .env.local before running
  the following commands.

- running jop queue

```shell
  php bin/console messenger:consume async --limit=5 -vv
```

```generate entities
php bin/console  doctrine:mapping:import "App\Entity\Temp" annotation --path=src/Entity/Temp
php bin/console make:entity --regenerate App\Entity\Temp\
```

```generate new  migration file
php bin/console doctrine:migrations:generate
```

```Update admin roles
php bin/console app:update-admin-role
```
