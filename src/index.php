<?php
#
# This is the search merger.
# You can find the whole source code here:
#    https://github.com/schul-cloud/meta-search-engine
#
# The configuration for search engines can be found here in
# ./search-engines.txt

####################################################
# constants
$SERVER_NAME = 'schul-cloud-meta-search-engine';
$SEARCH_QUERY_PARAMETER_START = 'Search';
$TIMEOUT_IN_MILLISECONDS = 500;

####################################################
# definitions


####################################################
# request handling


header('Content-Type: application/vnd.api+json');

# compute the host of this server
if (isset($_SERVER['HTTP_HOST'])) {
  $host = $_SERVER['HTTP_HOST'];
} else {
  $host = 'localhost';
}

# set the url of the search
$HERE = 'http://'.$host.'/v1/search/';

# find out if the requested content type is supported
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

# find out if the request parameters are valid are usable
$parameters_are_valid = isset($_GET['Q']);
$invalid_parameter_message = 'You can use the "Search" parameter to set the search engines to request. ';
if (!$parameters_are_valid) {
  $invalid_parameter_message = $invalid_parameter_message.'Parameter "Q" must be set. ';
}

# parse the request parameters
$requested_search_engines = array();
$use_default_search_engines = true;
foreach ($_GET as $key => $value) {
  if ($key == 'Q') {
    # Q parameter is supported as is
    $Q = $value;
  } else if (substr($key, 0, strlen($SEARCH_QUERY_PARAMETER_START)) === $SEARCH_QUERY_PARAMETER_START) {
    array_push($requested_search_engines, $value);
  } else if ($key == 'Default') {
    if ($value == 'false') {
      $use_default_search_engines = false;
    } else {
      $invalid_parameter_message = $invalid_parameter_message.
                                   'The "Default" parameter can '.
                                   'only have the value "false" '.
                                   'but is set to "'.$value.'". ';
      $parameters_are_valid = false;
    }
  } else {
    $invalid_parameter_message = $invalid_parameter_message.
                                 'Parameter "'.$key.'" is not supported. ';
    $parameters_are_valid = false;
  }
}
if ($use_default_search_engines) {
  # get the default search engines to request
  $additional_search_engine_configuration = file_get_contents('./search-engines.txt');
  $default_requested_search_engines = preg_split ('/$\R?^/m', $additional_search_engine_configuration); # https://stackoverflow.com/a/7498886/1320237

  foreach($default_requested_search_engines as $search_engine) {
    array_push($requested_search_engines, trim($search_engine));
  }  
}

# set the jsonapi specification
# this is part of every response
$JSONAPI = array(
  'version' => '1.0',
  'meta' => array(
    'name' => $SERVER_NAME,
    'source' => $HERE.'source.php',
    'description' => 'This is a meta search engine which unites other search engines.',
    'requested-search-engines' => $requested_search_engines,
  )
);

# answer the request
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
  echo json_encode($response, JSON_PRETTY_PRINT);
} else if (!$parameters_are_valid) {
  # we can not serve this request with invalid parameters
  $response = array(
    'jsonapi' => $JSONAPI,
    'errors' => array(
      array(
        'status' => '400',
        'title' => 'Bad Request',
        'detail' => $invalid_parameter_message
      )
    )
  );
  http_response_code(400);
  echo json_encode($response, JSON_PRETTY_PRINT);
} else {
  # This is a proper response
  $Q = $_GET['Q'];
  $Q_encoded = urlencode($Q);

  $data = array();
  # create curl requests
  $curls = array();
  foreach($requested_search_engines as $search_engine_url) {
    $search_url = $search_engine_url."?Q=".$Q_encoded;
    # see these posts for how to use curl
    #    https://stackoverflow.com/a/959072/1320237
    #    http://codular.com/curl-with-php
    #
    # here is an example for parallel handling of curls
    #    https://nic3.de/php/curl-multi
    error_log('Configuring '.$search_url);
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => $search_url,
        CURLOPT_USERAGENT => $SERVER_NAME,
        CURLOPT_TIMEOUT_MS => $TIMEOUT_IN_MILLISECONDS,
      ));
    array_push($curls, $curl);
  }

  # request curl in parallel
  $multi_curl=curl_multi_init();
  foreach($curls as $curl)
  {
	  curl_multi_add_handle($multi_curl, $curl);
  }
  do
  {
	  usleep(10000);
	  curl_multi_exec($multi_curl, $running);
  }
  while($running > 0);

  # collect the results
  foreach($curls as $id => $curl)
  {
    error_log('Collecting search results from "'.$requested_search_engines[$id].'"');
    $json_string = curl_multi_getcontent($curl);
    if ($json_string) {
      $json = json_decode($json_string, true);
      if (isset($json['data'])) {
        $data = array_merge($data, $json['data']);
      } else {
        error_log('Could not find "data" field in response from "'.$search_url.'".');
      }
    } else {
      error_log('Could not request '.$search_url.
                ' Error: "'.curl_error($curl).'"'.
                ' - Code: ' . curl_errno($curl));
    }
	  curl_multi_remove_handle($multi_curl,$curl);
    curl_close($curl);
  }

  $response = array(
    'jsonapi' => $JSONAPI,
    'links' => array(
      'self' => array(
        'href' => $HERE.'?Q='.$Q_encoded,
        'meta' => array(
          'count' => count($data),
          'limit' => 10,
          'offset' => 0,
        )
      ),
      'first' => null,
      'last' => null,
      'prev' => null,
      'next' => null
    ),
    'data' => $data,
  );
  echo json_encode($response, JSON_PRETTY_PRINT);
}

# finish the response
echo "\r\n";
flush();
?>
