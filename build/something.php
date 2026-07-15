<?php
    $username = "pavel_2022";
    $first_n = "Pavel";
    $last_n = "Pavlowicz";


    // do-while loop to do test stuff
    $i = 1;
    echo "DO-WHILE LOOP<br>";
    do {
        echo "Username: ". $username . "<br>";
        echo "First Name: ". $first_n . "<br>";
        echo "Last Name: ". $last_n . "<br>";
        echo "<br>";
        $i +=1;
    }  while($i <= 5);
    
    // for loop to do test stuff
    echo "FOR LOOP<br>";
    for ($i; $i <= 5; $i++) {
        echo "Username: ". $username . "<br>";
        echo "First Name: ". $first_n . "<br>";
        echo "Last Name: ". $last_n . "<br>";
        echo "<br>";
    }
