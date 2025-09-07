# Documentatie Backend/API

## Overzicht

Deze backend is gebouwd met Laravel (PHP) en biedt een RESTful API voor de frontend. De codebase is gestructureerd voor onderhoudbaarheid, veiligheid en uitbreidbaarheid.

## Structuur

-   `app/Http/Controllers/`: API-controllers.
-   `app/Models/`: Eloquent-modellen voor database interactie.
-   `routes/api.php`: Definieert de API-endpoints.
-   `database/migrations/`: Database migraties.
-   `tests/`: Unit- en integratietests.

## Belangrijke Features

-   RESTful API met duidelijke endpoint-structuur.
-   Authenticatie en autorisatie via Laravel Sanctum.
-   Foutafhandeling en validatie van requests.
-   Caching en optimalisatie van responses.
-   Omgang met API-keys en secrets via `.env`.

## Ontwikkelen & Bouwen

-   Starten: `php artisan serve`
-   Migraties: `php artisan migrate`
-   Testen: `php artisan test`

## Best practices

-   Scheiding van logica (controllers, services, models).
-   Duidelijke validatie en foutafhandeling.
-   Uitbreidbaar en onderhoudbaar opgezet.

## Zie ook

-   [README-KWALITEIT.md](../frontend/README-KWALITEIT.md) voor kwaliteitsaspecten.
