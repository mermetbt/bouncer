# bouncer
The Xinchejian payment/door/membership system! Allows members to register their (cash) payment and receive a password.
This password can be used to open the door. Once you open the door, your device may access members' Wi-Fi.

## Structure
There are three entry points:
* `index.php` opens the door (if the MAC is known) or redirects to `index.html` for PIN, finally `welcomeback.html`
* `submit.html` for submitting a new payment, redirects to `submit.php` and finally `welcome.html`
* `admin.php` for payment verification; calls `verify.php` using XHR

### The system's workflow
1. Pay money in envelope, write name on it
1. Scan the QR code at the box
1. Fill in name/email and payment
1. Get email with code for door

## SQL
The MySQL DB schema create script can be found in `db.sql`.

## To-Do
* Cronjob for membership reminders DONE

