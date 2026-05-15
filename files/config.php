<?php
/* =============================================
   ADMIN CREDENTIALS
   Change these before going to production.
   Password is stored as a bcrypt hash.
   To generate a new hash, run:
   echo password_hash('yourpassword', PASSWORD_DEFAULT);
   ============================================= */

define('ADMIN_USERNAME', 'admin');

/* Default password: ramoclean2024
   Replace this hash with your own using password_hash() */
define('ADMIN_PASSWORD_HASH', password_hash('ramoclean2024', PASSWORD_DEFAULT));

/* Session name — keeps it separate from other apps on same server */
define('SESSION_NAME', 'ramoclean_session');
?>
