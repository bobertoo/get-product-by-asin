<?php
  include_once("config.php");

  function getConnector(){
    global $db;
    return mysqli_connect($db[host], $db[user], $db[pass], $db[name]);
  }

  if(isset($_GET['action'])){
    if(function_exists($_GET['action'])){
      call_user_func($_GET['action']);
    }
  }

  function getAllProducts(){
    $con = getConnector();

    if ($con->connect_error) {
      die("Connection failed: " . $con->connect_error);
    }

    $stmt = $con->prepare("select * from products");
    $stmt->execute();
    $stmt->bind_result($id, $asin, $title, $mpn, $price);

    while($stmt->fetch()){
      $output[]=array(
      'id' => $id,
      'asin' => $asin,
      'title' => $title,
      'mpn' => $mpn,
      'price' => $price
      );
    }

    echo json_encode($output);

    $stmt->close();
    $con->close();
  }


  if ( $_SERVER['REQUEST_METHOD'] == 'POST' ) {
    $con = getConnector();

    if ($con->connect_error) {
      die("Connection failed: " . $con->connect_error);
    }

    $stmt = $con->prepare("INSERT INTO products (asin, title, mpn, price) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $asin, $title, $mpn, $price);

    // set parameters and execute
    $asin = $_POST['asin'];
    $title = $_POST['title'];
    $mpn = $_POST['mpn'];
    $price = $_POST['price'];
    $stmt->execute();

    echo "New records created successfully";

    $stmt->close();
    $con->close();
  }

  function getAmazonUrl(){
    global $aws;

    $endpoint = "webservices.amazon.com";
    $uri = "/onca/xml";

    if(isset($_GET['asin'])){
      $asin = $_GET['asin'];
    }

    $params = array(
      "Service" => "AWSECommerceService",
      "Operation" => "ItemLookup",
      "AWSAccessKeyId" => $aws[access_key_id],
      "AssociateTag" => $aws[access_key_id],
      "ItemId" => $asin,
      "IdType" => "ASIN",
      "ResponseGroup" => "ItemAttributes"
    );

    // Set current timestamp if not set
    if (!isset($params["Timestamp"])) {
      $params["Timestamp"] = gmdate('Y-m-d\TH:i:s\Z');
    }

    // Sort the parameters by key
    ksort($params);

    $pairs = array();

    foreach ($params as $key => $value) {
      array_push($pairs, rawurlencode($key)."=".rawurlencode($value));
    }

    // Generate the canonical query
    $canonical_query_string = join("&", $pairs);

    // Generate the string to be signed
    $string_to_sign = "GET\n".$endpoint."\n".$uri."\n".$canonical_query_string;

    // Generate the signature required by the Product Advertising API
    $signature = base64_encode(hash_hmac("sha256", $string_to_sign, $aws[secret_key], true));

    // Generate the signed URL
    $request_url = 'http://'.$endpoint.$uri.'?'.$canonical_query_string.'&Signature='.rawurlencode($signature);

    $xml_string = file_get_contents($request_url);
    $xml = simplexml_load_string($xml_string);
    $json = json_encode($xml);

    echo $json;
  }

?>
