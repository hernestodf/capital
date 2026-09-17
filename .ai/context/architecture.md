# Arquitetura
## Camadas
- **Core**: Router, Application, Request/Response, CSRF, Session, ErrorHandler
- **Controllers**: MVC controllers (App\Http\Controller)
- **Models**: Repository pattern em src/Repository/
- **Views**: templates PHP em views/, Tailwind CSS
- **Database**: mysqli procedural via src/Database/

## Fluxo
1. Request → Router (src/routes.php) → Controller → Repository → Database
2. Response → View (twig-like) ou JSON

## Características
- PHP procedural com alguns objetos
- Rotas definidas em arrays com middleware
- Autenticação via sessão PHP
- Upload via $_FILES
