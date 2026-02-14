# Hypermarket Activities (DAW)

Aplicație web PHP + MySQL (PDO) bazată pe baza de date `hypermarket_activities`.

## Funcționalități

- Autentificare + roluri (admin/client/angajat)
- CRUD Admin: CategoriiProduse, Produs
- Analytics: VisitLog + raport admin
- Contact form: trimitere email SMTP (PHPMailer)
- PDF report: listă produse (FPDF)
- Parsare externă: IMDB / DAB-it (cu cache)

## Rulare local (XAMPP)

1. Copiază proiectul în `C:\xampp\htdocs\hypermarket-activities`
2. Pornește Apache + MySQL din XAMPP
3. Import DB:
   - phpMyAdmin → create DB `hypermarket_activities`
   - import `database/schema_hypermarket_activities.sql`
4. Config local:
   - copiază `app/config/config.example.php` → `app/config/config.local.php`
   - copiază `app/config/mail.example.php` → `app/config/mail.local.php`
5. Accesează:
   - [wip]

## Conturi demo

Conturile de demo se trimit evaluatorului pe email (conform cerințelor).
