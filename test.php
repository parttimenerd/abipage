<?php
define("DEBUG", isset($_REQUEST["debug"]));

require 'php/ReviewText.php';

$text = isset($_POST["text"]) ? $_POST["text"] : "";
?>
<h1>Schimpfworttest</h1>
<form method="POST">
    <textarea name="text" style="width: 100%"><?= $text ?></textarea><br/>
    <input type="submit"/>
</form><br/>
<?

if ($text != ""){
    $time = microtime(true);
    if (ReviewText::checkText($text))
        echo "Text ist sauber";
    else
        echo "Text ist dreckig";
   echo " - in " . ((microtime(true) - $time) * 1000) . 'ms';
}