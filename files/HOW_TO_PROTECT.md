# How to protect all pages with login

## Step 1 — Add auth.php to every PHP page

At the very top of EVERY page (before anything else), add:

```php
<?php require_once("auth.php"); ?>
```

Example — main.php should start like:
```php
<?php require_once("auth.php"); ?>
<!DOCTYPE html>
...
```

Pages to update:
- main.php
- facture.php
- produit.php
- matiere.php
- client.php
- fournisseur.php
- employe.php
- ajoutproduit.php
- ajoutmatiere.php
- ajoutclient.php
- ajoutfournisseur.php
- ajoutemploye.php
- ajoutfamille.php
- ajoutfacture.php
- details_produit.php
- details_client.php
- details_fournisseur.php
- details_employe.php
- details_matiere.php
- details_facture.php
- print_facture.php
- categories.php
- export_excel.php
- export_employes.php
- export_fournisseurs.php

## Step 2 — Add logout button to nav in style.css

The nav already has the sidebar. Add this at the bottom of the nav in every page,
OR add it once inside nav in style.css as a fixed bottom element:

```html
<div class="nav-logout">
    <a href="logout.php">⬡ Déconnexion</a>
</div>
```

## Step 3 — Change the default password

Open config.php and change the password hash.
To generate a new hash, run this in a PHP file:

```php
echo password_hash('your_new_password', PASSWORD_DEFAULT);
```

Then paste the result into config.php replacing ADMIN_PASSWORD_HASH.

## Default credentials
- Username: admin
- Password: ramoclean2024
