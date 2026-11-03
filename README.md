# HouseHunt — PHP and MySQL rental marketplace

## Requirements
- PHP 8.1+ with PDO MySQL and Fileinfo extensions
- MySQL 8+
- Apache (XAMPP recommended on Windows)

## Install with XAMPP
1. Extract this folder into `C:\xampp\htdocs\househunt`.
2. Start Apache and MySQL in XAMPP.
3. Open `http://localhost/phpmyadmin`.
4. Import `database.sql`. It creates the `househunt` database, tables and amenity options.
5. Open `config.php` and check database host/name/user/password. Defaults: localhost, househunt, root, blank password.
6. Visit `http://localhost/househunt/`.

## Test workflow
1. Register as a Landlord.
2. Open Dashboard → List property. Fill in details and upload at least one JPG, PNG or WEBP photo (max 5 MB each).
3. Log out and register as a Tenant.
4. Browse and test location, price, house type, bedrooms and amenities filters.
5. Open a listing, save it to favourites and send an enquiry.
6. Log back in as landlord and open tenant enquiries.

## Included
- Role-aware tenant and landlord accounts
- Password hashing, PDO prepared statements and CSRF tokens
- Landlord listing creation, editing, deletion and availability status
- Multiple property photo uploads with MIME/image validation and random filenames
- Combined property search filters and price sorting
- Property detail pages, favourites and enquiries
- Responsive HTML/CSS layout and mobile navigation

## Important limitations before public deployment
Map-based search, email/SMS alerts, verified landlord identity, admin moderation, reviews and payment processing are not implemented. Search is text-based, not distance-based. Configure HTTPS, secure session cookie settings, backups, rate limiting, email verification, moderation/reporting and production database credentials before public deployment. Never pay a deposit before physically viewing the property and verifying ownership. The platform does not collect rent.
