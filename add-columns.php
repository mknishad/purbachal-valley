<?php
require_once 'config.php';

try {
    $pdo->exec("ALTER TABLE members ADD COLUMN photo VARCHAR(255) AFTER nominee_phone");
    echo "Column photo added<br>";
} catch (Exception $e) {
    echo "photo: " . $e->getMessage() . "<br>";
}

try {
    $pdo->exec("ALTER TABLE members ADD COLUMN signature VARCHAR(255) AFTER photo");
    echo "Column signature added<br>";
} catch (Exception $e) {
    echo "signature: " . $e->getMessage() . "<br>";
}

try {
    $pdo->exec("ALTER TABLE members ADD COLUMN nominee_nid_image VARCHAR(255) AFTER signature");
    echo "Column nominee_nid_image added<br>";
} catch (Exception $e) {
    echo "nominee_nid_image: " . $e->getMessage() . "<br>";
}

echo "Done!";
?>