<?php
$fileName = $_GET["file"];
$filePath = "deserialized/d-" . $fileName;

header("Content-Type: text/plain");
header("Content-Length: " . filesize($filePath));
header("Content-Disposition: attachment; filename=d-$fileName");

readfile($filePath);