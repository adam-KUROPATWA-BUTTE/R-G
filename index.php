<?php
/**
 * DEPRECATED - This file is kept for backward compatibility only
 * All requests should go through public/index.php via .htaccess rewriting
 * If you're seeing this, your .htaccess is not working correctly
 */

// Redirect to MVC route
header('Location: /');
exit;