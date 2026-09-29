# SokoTani

SokoTani is a Laravel marketplace for local agricultural products. Farmers can publish crop listings, buyers can negotiate wholesale prices, and both sides can track updates through in-app notifications. The app is designed for direct farm-to-market trading in a village context with transparent negotiation and simple trust signals.

## Features

- Farmer storefronts with public farm profile pages
- Product catalog with search and category filtering
- Wholesale negotiation flow with buyer and farmer responses
- Offer tracking and status updates
- In-app notification center for new offers, counters, accept/reject actions, and completion events
- Admin verification for farmer trust badges
- Demo seed data for farmer, buyer, and admin accounts

## Local setup

1. Clone the repository.
2. Copy the environment example:

```bash
cp .env.example .env
```

3. Generate the application key:

```bash
php artisan key:generate
```

4. Configure your database connection in `.env`.
5. Run the database setup and seed demo data:

```bash
php artisan migrate
php artisan db:seed --class="Database\\Seeders\\DemoMarketplaceSeeder"
```

6. Start the app:

```bash
php artisan serve
```

7. Open http://localhost:8000

## Demo accounts

The demo seeder creates these local accounts. The shared password is:

```text
SokoTaniDemo!2026
```

Accounts:

- Farmer: `farmer.budi@sokotani.test`
- Farmer: `farmer.sari@sokotani.test`
- Farmer: `farmer.joko@sokotani.test`
- Buyer: `buyer.maya@sokotani.test`
- Buyer: `buyer.andi@sokotani.test`
- Buyer: `buyer.rina@sokotani.test`
- Admin: `admin@sokotani.test`

## Admin verification workflow

Admins review public farmer profiles and explicitly verify or revoke the verification badge.

Promote an existing user to admin:

```bash
php artisan sokotani:make-admin admin@sokotani.test
```

This command prompts for the account password before updating the role.

## Notifications

When an offer is created, countered, accepted, rejected, cancelled, or completed, the related user receives an in-app notification. The notification center is available from the account menu.

## Testing

Run the automated checks with:

```bash
php artisan test
```

## License

This project is licensed under the MIT License.
