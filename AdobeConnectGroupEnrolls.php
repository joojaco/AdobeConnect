This script

<html>

<body>
    <?php

    // Sets to unlimited period of time so script can run without time limit
    ini_set('max_execution_time', 0);

    //set directory where ac-import file is stored
    $dir = glob('C:\PathToFolder\ac-group-enrolls\*.csv');

    // This lambda function takes the files from the $dir array and sorts them based on most recent timestamp
    usort($dir, function ($a, $b) {
        return filemtime($a) < filemtime($b);
    });


    // Gets correct csv file (most recent) for import
    $filename = $dir[0];


    echo "<div>" . var_dump($filename) . "</div>";

    //Url that all API calls will start with
    $apiUrl = "https://Domain.adobeconnect.com/api/xml?action=";

    // Logging in to the API - yes the password goes in the URL, this script uses SSL and I recommend adding a VPN for an additional layer of security. Update the password and Domain placeholders below.
    //The initial API call will return with a header cookie that will be used in all subsequent API calls in this script.
    $temp = get_headers($apiUrl . "login&login=api&password=PasswordGoesHere&domain=Domain.adobeconnect.com");

    // Regex to locate and extract the header cookie from the initial API call
    preg_match("/BREEZESESSION=([0-9a-zA-Z]+);/", $temp[1], $match);
    $str = $temp[1];
    $sub = preg_match("/BREEZESESSION=([0-9a-zA-Z]+);/", $str, $session);

    // Just an update to see the session info. This echo is not requied can be deleted. 
    echo "<div>" . var_dump($session) . "</div>";

    // Creating a variable to store the csv file as a nested array, which will hold all the sub-arrays (rows/cells from the csv file).
    $ac_file = [];

    // Open the Adobe Connect csv import csv file for reading
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

    echo "<div>" . var_dump($ac_file[1]) . "</div>";


    // iterating through the rows in the ac import file, omitting the header line from the file
    $num = 0;
    $count = 1;

    //foreach row in ac-file
    foreach ($ac_file as $row) {

        if ($num > 0) {

            $rowData = $ac_file[$num];
            //echo "<div>".var_dump($rowData)."</div>";

            $i = 0;

            foreach ($row as $cell) {

                $i++;
            }

            //////////////////////
            //This gets group of code gets the principl-id of the AC group
            $group = join("", [trim($apiUrl), "principal-list&filter-type=group&filter-name=", trim($rowData[0]), "&session=", trim($session[1])]);

            $groupInfo = file_get_contents($group);
            preg_match('/principal-id="([0-9]+)"/', $groupInfo, $match);
            $folderId = $match[1];

            //These are for status updates when running the script - you can delete this echo if you want.
            echo "<div>" . var_dump($folderId) . "</div>";
            echo "<div>" . var_dump($groupInfo) . "</div>";


            //This takes the user email addresses from the csv import file so the API can return each user's principal-id
            $user = join("", [trim($apiUrl), "principal-list&filter-type=user&filter-email=", trim($rowData[1]), "&session=", trim($session[1])]);

            //These are for status updates when running the script - you can delete this echo if you want.
            echo "<div>" . var_dump($rowData[1]) . "</div>";

            //This extracts each user's principal-id from the API's return
            $userInfo = file_get_contents($user);
            preg_match('/principal-id="([0-9]+)"/', $userInfo, $same);
            $userId = $same[1];

            //These are for status updates when running the script - you can delete this echo if you want.
            echo "<div>" . var_dump($userId) . "</div>";



            //Now that we've got the principal IDs of the group and users, it's time to enroll the student into the group!
            $groupEnrollment = join("", [trim($apiUrl), "group-membership-update&group-id=", trim($folderId), "&principal-id=", trim($userId), "&is-member=true&session=", trim($session[1])]);

            $groupUpdate = file_get_contents($groupEnrollment);

            echo "<div>" . var_dump($groupEnrollment) . "</div>";


            $count++;
        }

        echo "<br>";
        $num++;
    }


    // Log out of Adobe Connect after the script is done running.
    $logout = file_get_contents("https://Domain.adobeconnect.com/api/xml?action=logout");

    echo "<div>" . htmlentities($logout) . "</div>";

    ?>
</body>

</html>