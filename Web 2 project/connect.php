<?php
     define('DB_DSN', 'mysql:host=localhost;dbname=serverside;charset=utf8');
     define('DB_USER', 'serveruser');
     define('DB_PASS', 'gorgonzola7!');
     
    //  PDO is PHP Data Objects
    //  mysqli <-- BAD. 
    //  PDO <-- GOOD.
     try {
         // Try creating new PDO connection to MySQL.
         $db = new PDO(DB_DSN, DB_USER, DB_PASS);
         //,array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
     } catch (PDOException $e) {
         print "Error: " . $e->getMessage();
         die(); // Force execution to stop on errors.
         // When deploying to production you should handle this
         // situation more gracefully. ¯\_(ツ)_/¯
     }

     function isUsernameExists($conn, $username) {
        $sql = "SELECT * FROM user WHERE Username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
    
        return $result->num_rows > 0;
    }
    


 ?>