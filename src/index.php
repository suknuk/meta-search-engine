<?php
error_log("Content-Length: >".$_SERVER['CONTENT_LENGTH'].'<');

header('Content-Type: application/vnd.api+json');

$response = array(
  'jsonapi'=> array(
    'version' => '1.0',
    'meta' => array()
  )
);

echo json_encode($response);

?>