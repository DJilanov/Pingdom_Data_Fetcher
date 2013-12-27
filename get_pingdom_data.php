<?php
  function get_pingdom_data(){
    // Here we load the file from the folder where is the script!
    $encodedJson = file_get_contents(realpath(dirname(__FILE__).'/settings.json'));
    // We decode the code 
    $json=json_decode($encodedJson,true);
    // Asign the servers that are loaded for checking to a value
    $ServersToBeChecked = $json["Pingdom"]["Servers"];
    $Ids=[];
    foreach ($ServersToBeChecked as  $value) {
      $Ids[]=$value;
    }
    // Here we will save the response
    $AllServersInfo;
    $curl = curl_init();
    for($i = 0; $i < count(Ids); $i++){
        // Set target URL
      curl_setopt($curl, CURLOPT_URL, "https://api.pingdom.com/api/2.0/summary.performance/".$Ids[ $i ]."?includeuptime=true&order=asc&resolution=day");
        // Set the desired HTTP method (GET is default, see the documentation for each request)
      curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
        // Set user (email) and password
      curl_setopt($curl, CURLOPT_USERPWD, $json["Pingdom"]["user"]);
        // Add a http header containing the application key (see the Authentication section of this document)
      curl_setopt($curl, CURLOPT_HTTPHEADER, array($json["Pingdom"]["app-key"]));
        // Ask cURL to return the result as a string
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
   
        // Execute the request and decode the json result into an associative array
      $curl_res = curl_exec($curl);
      $response = json_decode($curl_res, true);
      #var_dump($response);
        // Check for errors returned by the API
      if (isset($response['error'])) {
          print "Error: " . $response['error']['errormessage'] . "\n";
          // if we recieve error mostly the file that is written get incompleate....I personaly think its much better just to
          // exit the code whitotu saving anything so we dont break any markups
          exit;
      }
      $AllServersInfo[ $i ] = $response;
    }
    return $AllServersInfo;
  }

    function Main(){
      //load the file where we will save everything.
      $filepath = realpath(dirname(__FILE__).'/storagePingdom.json');
      $statusData = array();
      $fileData = array();
      // open it as read + write
      fopen($filepath, 'r+');
      $fileData = json_decode(file_get_contents($filepath));
      //main job. We load the calls and write it with timestamp so we knew how old is the info
      $statusData['objects'] = get_pingdom_data(); // make the call
      $statusData['timestamp'] = time();
      // if we gonna check did it get itself to the finale
      echo "execute ";
      file_put_contents($filepath, json_encode($statusData)); // write to file
      
  }

  Main();
?>