<?php
require_once(__DIR__.'/functions.php');
$mysqli = DbConnect();

// Get the format from the GET parameters
$format = isset($_GET['format']) ? $_GET['format'] : 'array';
$startDate = isset($_GET['from']) ?$_GET['from'] : NULL;
$endDate = isset($_GET['till']) ? $_GET['till'] : NULL;
// Get the theses
$theses = showUnderExamTheses($mysqli, $startDate, $endDate);

// Output the results in the desired format
if ($format === 'json') {
    header('Content-Type: application/json');
    echo json_encode($theses);
} elseif ($format === 'xml') {
    header('Content-Type: text/xml');
    $xml = new SimpleXMLElement('<theses/>');
    foreach ($theses as $thesis) {
        $thesisElement = $xml->addChild('thesis');
        foreach ($thesis as $key => $value) {
            $thesisElement->addChild($key, htmlspecialchars($value));
        }
    }
    echo $xml->asXML();
} else {
    var_dump($theses);
}
?>
