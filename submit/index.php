<?php
    require_once '../init.php';
    
    $in = trim($_POST["quote"]);
    if (!empty($in) && $_POST["pass"] == $_ENV['SUBMIT_PASSWORD'])
    {
        $fp = fopen('./quotes.txt', 'a');//opens file in append mode
        fwrite($fp, $in . "\n");
        fclose($fp);
        header('Location: /submit');
        exit;
    }
?>  

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
<form method="post">
    <label for="quote">Citat:</label><br>
    <textarea rows="10" cols="100" name="quote" id="quote" required></textarea><br>
    <label for="quote">Pass:</label><br>
    <input type="password" name="pass" id="pass" required><br>
    <input type="submit" value="Submit">
</form>
<br>
Citat skall läggas in i formatet: <br>
"Citat" - Person, sammanhang<br>
<br>
<br>
<h3>Citat i buffer:</h3>
<pre>
<?php
echo htmlspecialchars(file_get_contents("quotes.txt"));
?>
</pre>

</body>
</html>