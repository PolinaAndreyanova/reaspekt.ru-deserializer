<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="./index.css" />
    <title>Форма</title>
</head>

<body class="content">
    <form class="content__form" method="post" enctype="multipart/form-data" action="deserialize.php" target="_blank">
        <h1 class="content__header">Загрузите файл с данными</h1>

        <input class="content__input" type="file" name="file" value="" accept=".data" placeholder="Файл" required />
        
        <p><input name="type" type="radio" value="print_r"> print_r</p>
        <p><input name="type" type="radio" value="var_dump" checked> var_dump</p>
        
        <button class="content__button" type="submit" name="download">Загрузить</button>
    </form>
</body>

</html>