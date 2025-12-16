<?php
/**
 * DEPRECATED - Redirect to MVC route
 * Use POST /cart/remove instead (handled by CartController@remove)
 */
if (isset($_GET['index'])) {
    // Redirect with query parameter
    header('Location: /cart/remove?index=' . urlencode($_GET['index']));
} else {
    header('Location: /cart');
}
exit;