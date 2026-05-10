<?php
/**
 * Logout - Destroy session and redirect
 * 
 * session_destroy() removes all session data server-side.
 * The PHPSESSID cookie becomes invalid after this call.
 */
session_start();
session_destroy();
header('Location: index.php');
exit;
