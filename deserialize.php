<?php
include_once("./lib.php");

$arUploadFileInfo = $_FILES["file"];
$uploadFileName = $arUploadFileInfo["name"];

move_uploaded_file($arUploadFileInfo["tmp_name"], "uploads/" . $uploadFileName);

$data = file_get_contents("uploads/" . $uploadFileName);
$arDeserializedData = deserialize($data);
// $test = serialize($arDeserializedData);
?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Форма</title>
</head>

<body>
    <a class="content__button content__button_type_link" href="./download.php?file=<?= $uploadFileName; ?>">Скачать</a>
    <div>
        <?php
        if ($_POST["type"] === "var_dump") {
            varDump($arDeserializedData);
            file_put_contents("deserialized/d-" . $uploadFileName, var_export($arDeserializedData, true));
        } else {
            printR($arDeserializedData);
            file_put_contents("deserialized/d-" . $uploadFileName, print_r($arDeserializedData, true));
        }
        // echo $test;
        ?>
    </div>
</body>

</html>