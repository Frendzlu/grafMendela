<?php
class Database {
    private $servername = "localhost";
    private $username = "root";
    private $password = "";
    private $dbname = "projectDB";
    private $tableName = "bodyTemp";
    public $nullCast = "null";

    public function __construct() {
        $this->conn = new mysqli($this->servername, $this->username, $this->password);
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
        //echo "Connected successfully <br>";
        $this->createDatabase();
        $this->createTable();
        $this->getAll();
    }

    public function createDatabase() {
        $sql = "CREATE DATABASE IF NOT EXISTS ".$this->dbname;
        if ($this->conn->query($sql) === true) {
            //echo "Database created successfully <br>";
        } else {
            //echo "Error creating database: " . $this->conn->error;
        }
    }

    public function createTable() {
        $sql = "CREATE TABLE IF NOT EXISTS ".$this->dbname.".".$this->tableName."(
            ID int(64) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            Day int NOT NULL,
            Temp DOUBLE(64, 2)
        );";
        if ($this->conn->query($sql) === true) {
            //echo "Table created successfully<br>";
        } else {
            //echo "Error creating table: " . $this->conn->error;
        }
    }

    public function getAll()  {
        $sql = "SELECT Day, COALESCE(Temp, '$this->nullCast') AS Temp FROM $this->dbname.$this->tableName";
        $result = $this->conn->query($sql);
        $returnVal = [];
        if ($result && $result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                //echo "DAY: " . $row["Day"]. ", TEMPERATURE: " . $row["Temp"]. "<br>";
                array_push($returnVal, $row);
            }
        } else {
            //echo "0 results";
        }
        $this->values = $returnVal;
    }

    public function changeData($day, $value){
        $day = intval($day);
        $value = ($value != "null") ? doubleval($value) : 'null';
        $sql = "UPDATE $this->dbname.$this->tableName SET Temp = $value WHERE Day = $day";
        $this->conn->query($sql);
        return $sql;
    }
}
class ImageDraw {
    public function __construct($params) {
        $this->width = $params["width"];
        $this->height = $params["height"];
        $this->redLine = $params["redLine"];
        $this->margins = $params["margins"];
        $this->image = imagecreatetruecolor($this->width, $this->height);
        $w = imagecolorallocate($this->image, 255, 255, 255);
        $b = imagecolorallocate($this->image, 0, 0, 0);
        $this->styles = [
            "white" => $w,
            "black" => $b,
            "red" => imagecolorallocate($this->image, 255, 0, 0),
            "grey" => imagecolorallocate($this->image, 127, 127, 127),
            "blue" => imagecolorallocate($this->image, 0, 0, 255),
            "dash" => IMG_COLOR_STYLED,
            "dashArr" => [$w, $w, $w, $w, $w, $w, $w, $w, $b, $b, $b, $b]
        ];
        $this->lim = [
            $this->margins[0]/100*$this->height,
            $this->width - $this->margins[1]/100*$this->width,
            $this->height - $this->margins[2]/100*$this->height,
            $this->margins[3]/100*$this->width
        ];
        $this->dims = [$this->lim[1] - $this->lim[3], $this->lim[2] - $this->lim[0]];
        $this->temp = [36.0, 37.4];
    }

    public function drawBackground() {
        imagefilledrectangle($this->image,0,0,$this->width, $this->height, $this->styles["white"]);
    }

    public function drawAxes() {
        imageline($this->image, $this->lim[3], $this->lim[2], $this->lim[3], $this->lim[0], $this->styles["black"]);
        imageline($this->image, $this->lim[3], $this->lim[2], $this->lim[1], $this->lim[2], $this->styles["black"]);
        //arrows
        $cath1 = 5;
        $cath2 = 10;
        imageline($this->image, $this->lim[3], $this->lim[0], $this->lim[3]-$cath1, $this->lim[0]+$cath2, $this->styles["black"]);
        imageline($this->image, $this->lim[3], $this->lim[0], $this->lim[3]+$cath1, $this->lim[0]+$cath2, $this->styles["black"]);
        imageline($this->image, $this->lim[1], $this->lim[2], $this->lim[1]-$cath2, $this->lim[2]-$cath1, $this->styles["black"]);
        imageline($this->image, $this->lim[1], $this->lim[2], $this->lim[1]-$cath2, $this->lim[2]+$cath1, $this->styles["black"]);
        //labels
        $fontSize = 3;
        $length = 10;
        $labelText = "Days";
        $prop = 19/10;
        $labelOffset = $fontSize * strlen($labelText);
        imagestring($this->image, $fontSize, $this->width/2 - $labelOffset, $this->lim[2] + $length * $prop, $labelText, $this->styles["black"]);
        $labelText = "Temperature";
        $labelOffset = $fontSize * strlen($labelText) + $fontSize;
        $prop = 6;
        imagestringup($this->image, $fontSize, $this->lim[3] - $length * $prop, $this->height/2 + $labelOffset, $labelText, $this->styles["black"]);
    }

    public function drawGrid($daysRange, $tempsRange = 6) {
        $distX = $this->dims[0] / ($daysRange + 1);
        $distY = $this->dims[1] / ($tempsRange + 1);
        $daysX = $this->lim[3] + $distX;
        $tempsY = $this->lim[2] - $distY;
        $length = 10;
        $fontSize = 2;
        $this->dayPos = [];
        $changeInTemp = 0.2;
        for ($i = 0; $i < $daysRange; $i++) {
            array_push($this->dayPos, $daysX);
            $labelOffset = (5/$fontSize) * strlen((string)$i+1);
            imagesetstyle ($this->image, $this->styles["dashArr"]);
            imageline($this->image, $daysX, $this->lim[2] + $length / 2, $daysX, $this->lim[2] - $length / 2, $this->styles["black"]);
            imageline($this->image, $daysX, $this->lim[2] - $length / 2, $daysX, $this->lim[0], $this->styles["dash"]);
            imagestring($this->image, $fontSize, $daysX - $labelOffset/2, $this->lim[2] + $length * 2/3, $i+1, $this->styles["black"]);
            $daysX += $distX;
        }
        $currentTemp = $this->temp[0]+0.2;
        for ($i = 0; $i < $tempsRange; $i++) {
            imagesetstyle ($this->image, $this->styles["dashArr"]);
            imageline($this->image, $this->lim[3] + $length / 2, $tempsY, $this->lim[3] - $length / 2, $tempsY, $this->styles["black"]);
            imageline($this->image, $this->lim[3] + $length / 2, $tempsY, $this->lim[1], $tempsY, $this->styles["dash"]);
            $changedNumber = number_format($currentTemp, 1);
            if (number_format($currentTemp, 1) == (int)$currentTemp) {
                imageline($this->image, $this->lim[3] + $length / 2, $tempsY, $this->lim[1], $tempsY, $this->styles["red"]);
            } else {
                $changedNumber -= (int)$currentTemp;
                $changedNumber = number_format($changedNumber, 1);
                $changedNumber = ltrim($changedNumber, '0');
            }
            $labelOffset = ($fontSize+5) * strlen($changedNumber)*17/19 + $length;
            imagestring($this->image, $fontSize, $this->lim[3] - $labelOffset, $tempsY-3/($fontSize+5/$fontSize)*$length/2, $changedNumber, $this->styles["black"]);
            $tempsY -= $distY;
            $currentTemp += $changeInTemp;
        }
    }

    public function drawData($data, $nullValue) {
        $dotSize = 4;
        $calcDotSize = $dotSize / 100 * ($this->height < $this->width ? $this->height : $this->width);
        $circle = [$calcDotSize, $calcDotSize];
        $previousCords = 0;
        $returnArray = [];
        for ($i = 0; $i < count($data); $i++) {
            //echo $data[$i]["Temp"]."<br>";
            //echo gettype($data[$i]["Temp"])."<br>";
            //echo $data[$i]["Temp"]*2 ."<br>";
            if ($data[$i]["Temp"] != $nullValue) {
                if ($data[$i]["Temp"] == 0) {
                    $previousCords = 0;
                    array_push($returnArray, [$this->dayPos[$i], $this->lim[2], $data[$i]["Day"]]);
                    imagefilledellipse($this->image, $this->dayPos[$i], $this->lim[2], $circle[0], $circle[1], $this->styles["red"]);
                } else {
                    //$realHeight = $this->dims[1] - $this->dims[1]/(($this->temp[1]-$this->temp[0])/0.2+2);
                    //$offset = $this->dims[1] / (($this->temp[1]-$this->temp[0])/0.2+2);
                    $degreesPerPixel = ($this->temp[1]-$this->temp[0])/$this->dims[1];
                    $y = $this->lim[2] - ($data[$i]["Temp"] - $this->temp[0]) / $degreesPerPixel;
                    imagefilledellipse($this->image, $this->dayPos[$i], $y, $circle[0], $circle[1], $this->styles["blue"]);
                    if ($previousCords != 0) {
                        imageline($this->image, $previousCords[0], $previousCords[1], $this->dayPos[$i], $y, $this->styles["blue"]);
                    }
                    $previousCords = [$this->dayPos[$i], $y];
                    array_push($returnArray, [$this->dayPos[$i], $y, $data[$i]["Day"]]);
                }
            } else {
                $previousCords = 0;
                imagefilledellipse($this->image, $this->dayPos[$i], $this->lim[2], $circle[0], $circle[1], $this->styles["grey"]);
                array_push($returnArray, [$this->dayPos[$i], $this->lim[2], $data[$i]["Day"]]);
            }
        }
        $this->mapData = [$returnArray, $calcDotSize];
    }

    public function returnImg() {
        imagepng($this->image, "imagens.png");
        imagepng($this->image);
    }
}

class Main {
    public function __construct() {
        $this->queryArgs = [
            "width" => 800,
            "height" => 300,
            "redLine" => [true, 37],
            "margins" => [12.5, 5, 12.5, 10],
        ];
        $this->parsedParams = [];
        $this->parseQuery();
        $this->data = new Database();
        $this->image = new ImageDraw($this->parsedParams);
        $this->image->drawBackground();
        $this->image->drawAxes();
        $this->image->drawGrid(count($this->data->values));
        $this->image->drawData($this->data->values, $this->data->nullCast);
    }

    public function parseQuery() {
        foreach (array_keys($this->queryArgs) as $q) {
            if (isset($_GET[$q])) {
                $this->parsedParams[$q] = $_GET[$q];
            } else {
                $this->parsedParams[$q] = $this->queryArgs[$q];
            }
        }
    }
}
$mainInstance = new Main();