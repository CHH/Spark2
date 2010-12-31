# Willkommen zu Spark2

Spark ist ein MVC Framework für PHP 5.3+. Es ist bewusst einfach gehalten, ganz nach dem Prinzip "Code for today".
Es ist Open-Source und steht unter der MIT Lizenz.

## Das "Hallo Welt" Beispiel in Spark:

1) Erzeuge ein Verzeichnis "hello_world" im Document-Root deines Webservers.

2) Erzeuge einen Virtual Host für deine App (z.B. hello_world.local).

3) Gib das in "/hello_world/.htaccess"
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} -s [OR]
RewriteCond %{REQUEST_FILENAME} -l [OR]
RewriteCond %{REQUEST_FILENAME} -d 
RewriteRule ^.*$ - [NC,L]
RewriteRule ^.*$ index.php/$1 [NC,L]

4) Kopiere den Ordner "lib" aus dem heruntergeladenen Archiv nach "hello_world"

5) Schreib das in "/hello_world/index.php"
<?php

use Spark\App, Spark\HttpRequest, Spark\HttpResponse;

require_once "lib/Spark.php";

$app = new App;
$app
->routes
->get("/", function($request, $response) {
    echo "Hello World";
});

$app(new HttpRequest, new HttpResponse);
?>

Schau ins Wiki wenn du mehr erfahren willst.

Viel Spaß!