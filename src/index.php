<?php
#error_log("Content-Length: >".$_SERVER['CONTENT_LENGTH'].'<');

header('Content-Type: application/vnd.api+json');

$JSONAPI = array(
  'version' => '1.0',
  'meta' => array(
    'name' => 'schul-cloud-meta-search-engine',
    'source' => 'https://github.com/schul-cloud/meta-search-engine',
    'description' => 'This is a meta search engine which unites other search engines.'
  )
);

if (isset($_SERVER['HTTP_ACCEPT'])) {
  error_log("Accept header set to ".$_SERVER['HTTP_ACCEPT']);
  $accepted_content_types = explode(',', $_SERVER['HTTP_ACCEPT']);
  $served_content_types = array('*/*', 'application/*', 'application/vnd.api+json');
  $content_type_is_acceptable = false;
  foreach ($accepted_content_types as $accepted_content_type) {
    if (in_array($accepted_content_type, $served_content_types)) {
      $content_type_is_acceptable = true;
      break;
    }
  }
} else {
  $content_type_is_acceptable = false;
  error_log("Accept header not set");
}

$parameters_are_valid = isset($_GET['Q']) && count($_GET) == 1;

if (!$content_type_is_acceptable) {
  # we can not serve this content type
  $response = array(
    'jsonapi' => $JSONAPI,
    'errors' => array(
      array(
        'status' => '406',
        'title' => 'Not Acceptable',
        'detail' => '"application/vnd.api+json" is the content type to accept.'
      )
    )
  );
  http_response_code(406);
  echo json_encode($response);
} else if (!$parameters_are_valid) {
  # we can not serve this request with invalid parameters
  $response = array(
    'jsonapi' => $JSONAPI,
    'errors' => array(
      array(
        'status' => '400',
        'title' => 'Bad Request',
        'detail' => 'Currently, only the "Q" parameter is supported.'
      )
    )
  );
  http_response_code(400);
  echo json_encode($response);
} else {
  # This is a proper response
  $Q = $_GET['Q'];
  
  if (isset($_SERVER['HTTP_HOST'])) {
    $host = $_SERVER['HTTP_HOST'];
  } else {
    $host = 'localhost';
  }
  
  $response = array(
    'jsonapi' => $JSONAPI,
    'links' => array(
      'self' => array(
        'href' => 'http://'.$host.'/v1/search/?Q='.$Q,
        'meta' => array(
          'count' => 0,
          'limit' => 10,
          'offset' => 0,
        )
      ),
      'first' => null,
      'last' => null,
      'prev' => null,
      'next' => null
    ),
    'data' => array(),
  );
  echo json_encode($response);
}

# finish the response
echo "\r\n";
flush();
?>
