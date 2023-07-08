<?php

$author_id = 1;
$modifier_post = 100;
$modifier_category = 10;

$db_old = new PDO('mysql:host=localhost;dbname=XXXX', 'root', '');
$db_new = new PDO('mysql:host=localhost;dbname=XXXX', 'root', '');

require_once('categories.php');
require_once('posts.php');
require_once('comments.php');

