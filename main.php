<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <script src="index.js"></script>
</head>
<body>
    <?php
        $string = "image.php";
        if (isset($_GET["width"])){
            $string .= "?width=".$_GET["width"];
        }
        if (isset($_GET["height"])){
            if ($string == "image.php"){
                $string .= "?height=".$_GET["height"];
            } else {
                $string .= "&height=".$_GET["height"];
            }
        }
        echo "<img id=\"uwu\" src='$string' alt='smth is not working' usemap='#mapName'>"
    ?>
    <map id="mapName" name="mapName">
        <?php 
            require_once('class.php');
            $data = $mainInstance->image->mapData;
            for ($i = 0; $i < count($data[0]); $i++) {
                echo "<area shape='circle' id='".$data[0][$i][2]."' coords='".$data[0][$i][0].",".$data[0][$i][1].",".$data[1]."' href='#' onclick='showModal(event)'/>";
            }
        ?>
    </map>
    <div id="formDiv" style="display: none; position: absolute; background: rgba(0, 0, 0, 0.5); top: 0; left: 0; width: 100vw; height: 100vh">
        <div id="form" style="position: relative; top: 45vh; left: 45vw;">
            <input id="tempInput" oninput="validate(event)">
            <button onclick="setTemp(event)">ZAPISZ</button>
            <button onclick="setNull(event)">BRAK POMIARU</button>
            <button onclick="setIll(event)">CHOROBA</button>
            <button onclick='hideModal(event)'>ANULUJ</button>
        </div>
    </div>
    <a href="pdf.php">Pobierz pdf</a>
</body>
</html>
<?php
