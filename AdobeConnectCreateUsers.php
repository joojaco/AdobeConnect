This file allows you to do server-side API calls to Adobe connect, taking data from a csv import file and creating users. The account in this script must have admin privileges in Adobe Connect to work.

<html>

<body>
    <?php

    // Set directory where the Adobe Connect import file is stored
    $dir = glob('C:PathToImportFolder/*.csv');

    // This lambda function takes the files from the $dir array and sorts them based on most recent timestamp, so you can select the most recently modified file.
    usort($dir, function($a, $b) {
        return filemtime($a) < filemtime($b);
    });

    
    // Gets correct file (most recent) for import
    $filename = $dir[0];

    // Url that all API calls will start with - change "Domain" in the URL below to fit your hosted Adobe Connect URL
    $apiUrl = "https://Domain.adobeconnect.com/api/xml?action=";

    // Logging in to the API - yes the password goes in the URL, this script uses SSL and I recommend adding a VPN for an additional layer of security. Update the password and Domain placeholders below.
    //The initial API call will return with a header cookie that will be used in all subsequent API calls in this script.
    $temp = get_headers($apiUrl . "login&login=api&password=PasswordGoesHere&domain=Domain.adobeconnect.com");

    // Regex to locate and extract the header cookie from the initial API call
    preg_match("/BREEZESESSION=([0-9a-zA-Z]+);/", $temp[1], $match);
    $str = $temp[1];
    $sub = preg_match("/BREEZESESSION=([0-9a-zA-Z]+);/", $str, $session);


    // Creating a variable to store the csv file as a nested array, which will hold all the sub-arrays (rows/cells from the csv file).
    $ac_file = [];

    // Open the Adobe Connect import csv file for reading
    if (($h = fopen("{$filename}", "r")) !== FALSE) {
        // Each line in the file is converted into an individual array that we call $data
        // The items of the array are comma separated
        while (($data = fgetcsv($h, 1000, ",")) !== FALSE) {
            // Each individual array is being pushed into the nested array
            $ac_file[] = $data;
        }

        // Close the file
        fclose($h);
    }


    // iterating through the rows in the ac import file, omitting the header line from the file
    $num = 0;
    $count = 1;
   
    //foreach row in ac-file
    foreach ($ac_file as $row) {

        if ($num > 0) {

        $rowData = $ac_file[$num];
        //echo "<div>".var_dump($rowData)."</div>";

        $i = 0;
        
        // Iterating through each sub-array (row) to create users
        foreach ($row as $cell) {
     
            $i++;
        }

        // Making the API call that will create each user. I did a single call per user, so multiple API calls, but it is possible to string all users into one URL API call - I was just too lazy to figure that out and this works just fine.
        $makeUser = join("", [trim($apiUrl), "principal-update&first-name=", trim($rowData[0]), "&last-name=", trim($rowData[1]), "&has-children=0&login=", trim($rowData[2]), "&email=", trim($rowData[3]), "&password", trim($rowData[4]), "&type=user&session=", trim($session[1])]);

        //echo "<div>" . var_dump(($makeUser)) . "</div>";
        print $makeUser;


        $makeUserResponse = file_get_contents($makeUser);
        //echo "<div>" . htmlentities($makeUserResponse) . "</div>";
        print htmlentities($makeUserResponse);
        $count++;
    }

    echo "<br>";
        $num++;
        
    }

    // Log out after users are created.
    $logout = file_get_contents("https://Domain.adobeconnect.com/api/xml?action=logout");

    echo "<div>".htmlentities($logout)."</div>";
   


    ?>
</body>

</html>