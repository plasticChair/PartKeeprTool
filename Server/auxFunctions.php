<?php

#Deadbug Electronics

##************************************************************##
##************************************************************##
function initDB()
{
    
    $errorOnly = TRUE;
    $conn = new mysqli("localhost",USERNAME,PASSWORD);

     // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    } 
    
    if (!$errorOnly)
    {
        echo "Connected successfully";
        echo "\r";
    }

     
    $sql = "USE PartKeepr";
    if ($conn->query($sql) === TRUE) {
        if (!$errorOnly)
        {
             echo "Selected database";
        }
    } else {
        echo "Database not selected or error: " . $conn->error;
    }
    
    return $conn;
}

##************************************************************##
##************************************************************##
function data2json($data)
{

    $jsonObj->result = $data;
    
    return json_encode($jsonObj);
}

##************************************************************##
##************************************************************##
function sendQueryDirect($conn, $queryCmd, $successText = NULL, $failText = NULL)
{
  $data = array();

  if ($result = $conn->query($queryCmd)) 
  {
     while($row = mysqli_fetch_array($result,MYSQLI_ASSOC)){
           // This will loop through each row, now use your loop here
           $data[] = $row;
        }
      #echo $successText;
      if (empty($data))
      {
          return NULL;
      }
      else{
        return $data;    
      }
      
  }
  else
  {
      echo "<br>";
      echo $queryCmd;
      echo "<br><br>";
      echo $conn->error;
      echo $failText;
      return NULL;
  }
}

##************************************************************##
##************************************************************##
function sendQuery($conn, $queryCmd, $successText = NULL, $failText = NULL)
{
  $data = array();

  if ($result = $conn->query($queryCmd)) 
  {
     while($row = mysqli_fetch_array($result)){
           // This will loop through each row, now use your loop here
           $data[] = $row[0];
        }
      #echo $successText;
      if (empty($data))
      {
          return NULL;
      }
      else{
        return $data;    
      }
      
  }
  else
  {
      echo "<br>";
      echo $queryCmd;
      echo "<br><br>";
      echo $conn->error;
      echo $failText;
      return NULL;
  }
}


##************************************************************##
##************************************************************##
function testPart($conn, $partNameIN,$stockOption=0)
{
    if ($stockOption)
    {
     $cmd =  "SELECT internalPartNumber 
                FROM Part 
                WHERE name = '" . $partNameIN . "'
                  AND stockLevel > 0";
    }
    else
    {
         $cmd =  "SELECT internalPartNumber 
                FROM Part 
                WHERE name = '" . $partNameIN . "'";
        
    }

    $result = sendQuery($conn, $cmd);
    return $result[0];
    
}


##************************************************************##
##************************************************************##
function removeInventory($conn, $partNameIN)
{
    
    $cmd = "UPDATE Part
               SET stockLevel = stockLevel - 1
             WHERE name = '" . $partNameIN . "'
                AND stockLevel > 0" ;
    

    sendQuery($conn, $cmd);

}


##************************************************************##
##************************************************************##
function addInventory($conn, $partNameIN)
{
    
    
     $cmd = "UPDATE Part
               SET stockLevel = stockLevel + 1
             WHERE name = '" . $partNameIN . "'" ;
    

    sendQuery($conn, $cmd);
}


##************************************************************##
##************************************************************##
function getInventory($conn, $partNameIN)
{
    
    if ($result = $conn->query("SELECT * 
                              FROM Part 
                              WHERE name = '" . $partNameIN . "'" )) 
    {
        $data = mysqli_fetch_assoc($result);

        /* free result set */
        $result->close();

        return $data["stockLevel"];
    }
    else
    {
        echo $conn->error;
        echo "<br>";
         return null;
    }
    
    
}


##************************************************************##
##************************************************************##
function getLastid($conn, $table)
{
# give table string, return int
    if ($result = $conn->query("SELECT MAX(id) FROM " . $table )) 
    {
        $data = mysqli_fetch_assoc($result);
        /* free result set */
       # $result->close();

        return (int)$data["MAX(id)"];
    }
    else
    {
        echo $conn->error;
        echo "<br>";
        return null;
    }
    
}


##************************************************************##
##************************************************************##
function findIdFromName($conn, $table, $name, $auxName = NULL)
{    
    if($auxName == NULL)
    {
        $auxName = "name";
    }
    
    
    
    if ($result = $conn->query("SELECT id
                                FROM " . $table . "
                                WHERE BINARY " . $auxName .  " = '" . $name . "'" )) 
    {
        $data = mysqli_fetch_assoc($result);
       
        /* free result set */
      #  $result->close();
        
        if ($data == NULL){
            return NULL;
        }
        else{
             return (int)$data['id'];
        }
    }
    else
    {
        echo $conn->error;
        echo "<br>";
        return NULL;
    }   
}


##************************************************************##
##************************************************************##
function insertPartParameter($conn, $partID, $unitid, $name, $value, $stringVal, $prefixid )
{
    
    $MaxId    = getLastid($conn, "PartParameter") +  1;
    
    $valueType = "numeric";
    if($value == null)
    {
        $valueType = "string";
    }

    if($value == null)
    {
        $value = 'NULL';
    }
    
    if($prefixid == null)
    {
        $prefixid = 'NULL';
    }
    
    if($unitid == null)
    {
        $unitid = 'NULL';
    }
 
    if (false)
    {
        echo "  PRINT OUT ------ <br>";

        var_dump($partID);
        echo " (PartID) <br>";
        var_dump($unitid);
        echo " (unitid) <br>";
        var_dump($name);
        echo " (name) <br>";
        var_dump($value);
        echo " (value) <br>";
        var_dump($stringVal);
        echo " (stringVal) <br>";
        var_dump($prefixid);
        echo " (prefixid) <br>";
    }
    

    
     // Update
     $cmdUpdate = "update `PartParameter` set `unit_id`=" . $unitid . " ,`value`=" . $value . ",`stringValue`='" . $stringVal . "',`valueType`='" .  $valueType . "',`siPrefix_id`=" . $prefixid . " WHERE part_id = " . $partID . " 
                    AND name = '" . $name . "'";

    //Command for insert
    $cmdInsert = "INSERT INTO `PartParameter` (`id`, `part_id`, `unit_id`, `name`, `description`, `value`, `normalizedValue`, `maximumValue`, `normalizedMaxValue`, `minimumValue`, `normalizedMinValue`, `stringValue`, `valueType`, `siPrefix_id`, `minSiPrefix_id`, `maxSiPrefix_id`) VALUES (". $MaxId . "," . $partID . "," . $unitid . ",'" . $name . "', '', " . $value . ", NULL , NULL, NULL , NULL, NULL ,'" . $stringVal . "','" .  $valueType . "'," . $prefixid . ",   NULL, NULL)";

    
    $sendInsert = 0;
    // Command for check if exists
    $cmdCheck = "SELECT * FROM PartParameter where  part_id = " . $partID ." AND name = '" . $name . "'";
    
        
    $returnedData = sendQuery($conn, $cmdCheck);

    
    if (empty($returnedData))
    {
        $sendInsert = 1;
    }
    
    if ($sendInsert == 1)
    {
        $returnedData = sendQuery($conn, $cmdInsert);
       # echo "added beans<br>";
    }
    else
    {
       $returnedData = sendQuery($conn, $cmdUpdate);
        #echo "Updated beans <br>";
    }

}


##************************************************************##
##************************************************************##
function insertFootprint($conn, $name)
{
    
    $id = findIdFromName($conn, "Footprint", $name);

    if ($id == NULL)
    {
        $MaxId = getLastid($conn, "Footprint") +  1;
        
        $cmd = "INSERT INTO `Footprint` (`id`, `category_id`, `name`, `description`) VALUES (" . strval($MaxId) . ", 1, '" . $name . "','' )";
        
        sendQuery($conn, $cmd);
        
        return "OK";
    }
    else{
        return 999;
    }
}


##************************************************************##
##************************************************************##
function updateGenPart($conn, $id, $itemData)
{
    
    $fpid = findIdFromName($conn, "Footprint", $itemData['PartParameters']['Package / Case:']);
    
    if ($fpid == NULL)
    {
        $fpid = 'NULL';
    }

    
    $desc  = $itemData["Description"];
   
    $URL  = $itemData["DataSheetUrl"];

    $cmd = "UPDATE Part
           Set        footprint_id = " . $fpid  . ",
                      description  = '" . $desc  . "',
                      comment      = '" . $URL . "'
                      WHERE id     = " . $id ;

    sendQuery($conn, $cmd);
}


##************************************************************##
##************************************************************##
function createPart($conn, $itemName)
{
     $id = findIdFromName($conn, "Part", $itemName);
    
    if($id == NULL)
    {
        $id = getLastid($conn, "Part") + 1;

        $cmd = "INSERT INTO `Part`(`id`, `category_id`, `footprint_id`, `name`, `description`, `comment`, `stockLevel`, `minStockLevel`, `averagePrice`, `status`, `needsReview`, `partCondition`, `productionRemarks`, `createDate`, `internalPartNumber`, `removals`, `lowStock`, `metaPart`, `partUnit_id`, `storageLocation_id`) VALUES (" . $id . ",1,NULL,'" . $itemName . "','','','','','','',0,'','','" . date("Y-m-d H:i:s") . "','','','','',1,1)";
        
        sendQuery($conn, $cmd);
        
        return "OK";
        
    }
    return NULL;
}




##************************************************************##
##************************************************************##
function UpdatePart($conn, $itemName, $itemData)
{
    
    $id = findIdFromName($conn, "Part", $itemName);

    $fpid = findIdFromName($conn, "Footprint", $itemData['PartParameters']['Package / Case:']);
    
    if ($fpid == NULL)
    {
        $fpid = 'NULL';
    }
    
    $catId = findIdFromName($conn, "PartCategory",  $itemData["DB Category"]);
    if ($catId == NULL)
    {
        $catId = 1;
    }
    
    $desc  = $itemData["Description"];
    $stock = $itemData["Stock"];
    if ($stock == NULL)
    {
        $stock = 0;
    }
    $DBPN  = $itemData["DB PN"];
    if ($DBPN == NULL)
    {
        $DBPN = null;
    }
    $URL  = $itemData["DataSheetUrl"];

    $cmd = "UPDATE Part
           Set        category_id = " . $catId . ",
                     footprint_id = " . $fpid  . ",
                      description = '" . $desc  . "',
                       stockLevel = " . $stock . ",
                       comment    = '" . $URL . "',
               internalPartNumber = '" . $DBPN  . "'
                         WHERE id = '" . $id . "'" ;

        sendQuery($conn, $cmd);
}



##************************************************************##
##************************************************************##
function string2NumUnit($conn, $id, $itemData)
{
    $unitVal = null;
    $unitVal2 = null;
    $tempCheck = null;
    $unit = null;
    
    
    foreach($itemData as $param)
    {
        foreach($param as $key => $value)
        {
            #echo $key ."-> ". $value . "<br>";
            
            preg_match('/^\d*\.?\d*\s/', $value, $unitVal, PREG_OFFSET_CAPTURE);
            preg_match('/\sto\s/', $value, $unitVal2);
            #preg_match('/^[+-]\s\d*\.? C$|^\d*\.? C$/', $value, $tempCheck);
            preg_match('/^[+-]\s\d*\.? C$/', $value, $tempCheck);

            $symbolFound = 0;
            $captured = "";
            $foundPre = 0;
            $notFound = 0;
            $unitID = NULL;
            $preID = NULL;
            
            
            // IF temp, Skip all
            if (empty($tempCheck))
            {
                // Else 
                if (($unitVal != null) & (strlen($unitVal[0]) < 7) )
                {
                    //Get full unit
                    $unit = preg_replace('/^\d*\.?\d*\s/', '', $value);
 
                    // Strip extras
                    $unit = preg_replace('/\s\(([^\)]+)\/', '', $unit);
                    
                    $prefixes = getAll($conn, "SiPrefix", "symbol");

                    //Find Prefix
                    foreach($prefixes as $pre)
                    {
                        if ($pre == $unit[0])
                        {
                           # echo "found: " . $pre;
                            
                            $preID = findIdFromName($conn, "SiPrefix", $pre, "symbol");
                           # echo "(" . $preID .")";
                            $foundPre = 1;
                            $unit = substr($unit, 1);
                            break;

                        }    
                    }

                    
                    // OK so no prefix, find symbol with full name (Ohm)
                    if ($symbolFound == 0)
                    {
                       # echo "Searching for:" . $unit . "---";
                      #  echo "and " . substr($unit, 0,-1) . "<br>";
                        $symbols = getAll($conn, "Unit", "name");
                        foreach($symbols as $symbol)
                        {
                        #    echo $symbol . "<br>";
                            if($unit == $symbol)
                            {
                                $symbolFound = 1;
                                $captured = $symbol;
                                $unitID = findIdFromName($conn, "Unit", $unit);
                                break;
                            }
                            elseif((substr($unit, 0,-1) == $symbol) & (strlen($unit) > 2) & ($symbolFound == 0))
                            {
                                $symbolFound = 1;
                                $captured = $symbol;
                                $unitID = findIdFromName($conn, "Unit", substr($unit, 0,-1));
                                break;
                            }
                        }
                    }
                    
                    // Still nothing?  Search for symbol abbreviation
                     if ($symbolFound == 0)
                     {
                          $symbols = getAll($conn, "Unit", "symbol");
                        # echo "Searching2 for:" . $unit . "---";
                        foreach($symbols as $symbol)
                        {
                         #      echo $symbol . "<br>";
                              if($unit == $symbol)
                            {
                                $symbolFound = 1;
                                $captured = $symbol;
                                $unitID = findIdFromName($conn, "Unit", $unit, "symbol");
                               #   echo "FOUND: " . $unitID ;
                                  break;
                            }
                            elseif((substr($unit, 0,-1) == $symbol) & ($symbolFound == 0))
                            {
                                $symbolFound = 1;
                                $captured = $symbol;
                                $unitID = findIdFromName($conn, "Unit", substr($unit, 0,-1), "symbol");
                                break;
                            }
                        }
                     }
                }
                else
                {
                    #echo "Not found -> " . $value . " --- " . $symbolFound . "<br>";
                    $notFound = 1;
                }
                
                
                // INSERT NUMBER
                if ($symbolFound == 1)
                {
                    insertPartParameter($conn, $id, $unitID, $key,  $unitVal[0][0], NULL, $preID );

                }
                else  //INSERT STRING
                {
                    insertPartParameter($conn, $id, $unitID, $key,  NULL, $value, NULL );
                }
                
            }
            else
            {
                $temp = substr($value, 0,-2);
                $temp = preg_replace('/\s/', '', $temp);

            // INSERT TEMP
                insertPartParameter($conn, $id, 21, $key, $temp, NULL, NULL );
                
            }
            
        }        
    }
}


##************************************************************##
##************************************************************##
function getAll($conn, $table, $title)
{
    $data = array();
    
    
    $cmd = "SELECT " . $title . " FROM " . $table ;
   return sendQuery($conn, $cmd);
}



##************************************************************##
##************************************************************##
function setAttachments($conn, $id, $data)
{
    
    $impath = $data['ImagePath'];
    $impathfix = null;
    preg_match('/.+(\/.+)$/', $impath, $impathfix);
    $impathfix = substr($impathfix[1],1,-4);

    $idPAtt = getLastid($conn, "PartAttachment") + 1;
          
    $cmd = "SELECT * FROM PartAttachment WHERE part_id = " . $id ." AND originalname = '" . $impathfix . ".jpg'";
    
     $returnedData = sendQuery($conn, $cmd);

    // Image
    if (empty($returnedData))
    {
        $cmd = "INSERT INTO `PartAttachment`(`id`, `part_id`, `type`, `filename`, `originalname`, `mimetype`, `size`, `extension`, `description`, `created`, `isImage`) VALUES (" . $idPAtt . ", " . $id .  ", 'PartAttachment' , '" . $impathfix .  "', '" . $impathfix . ".jpg', 'image/jpeg' , 0 , '' , null , '" . date("Y-m-d H:i:s") . "', null)";

         sendQuery($conn, $cmd);
    }

    // QR Code
    $idPAtt = getLastid($conn, "PartAttachment") + 1;
    $DBPN = getDBPN($conn, $id);
    
    $cmd = "SELECT * FROM PartAttachment WHERE part_id = " . $id ." AND originalname = 'QR_" . $DBPN . ".jpeg'";
    $returnedData = sendQuery($conn, $cmd);

    if (empty($returnedData))
    {
        $cmd = "INSERT INTO `PartAttachment`(`id`, `part_id`, `type`, `filename`, `originalname`, `mimetype`, `size`, `extension`, `description`, `created`, `isImage`) VALUES (" . $idPAtt . ", " . $id .  ", 'PartAttachment' , 'QR_" . $DBPN .  "', 'QR_" . $DBPN . ".jpeg', 'image/jpeg' , 0 , '' , null , '" . date("Y-m-d H:i:s") . "', null)";

         sendQuery($conn, $cmd);
    }   
}


##************************************************************##
##************************************************************##
function setDBPN($conn)
{
    $cmd = "SELECT name FROM Part WHERE ((internalPartNumber = '') OR (internalPartNumber IS NULL)) AND category_id > 1";
    $blankPN = sendQuery($conn, $cmd);
    var_dump( $blankPN);
    
    echo "<br>";

    foreach($blankPN as $part) 
    {
        
        $cmd =  "SELECT MAX(internalPartNumber) from Part WHERE category_id = (SELECT category_id FROM Part where name = '" . $part . "')";
        $highestPN = sendQuery($conn, $cmd);
        echo $highestPN[0] . "<br>";
      #  
        //autogenerate logic, don't use
        if (empty($highestPN[0]))
        {
             echo "------ MANUALLY GENERATE NUMBER FOR: " . $part . "<br>" ;
            if (false)
            {
               
                $cmd =  "SELECT category_id FROM Part WHERE name = '" . $part . "'";
                $currCatID = sendQuery($conn, $cmd);

                $currCatID = $currCatID[0];
                echo $currCatID . " ---";

                $counter = 0;
                echo "ENTER LOOP -- <br>";
                while (true)
                {
                    $cmd =  "SELECT parent_id FROM PartCategory WHERE id = " . $currCatID;

                    $currCatID = sendQuery($conn, $cmd);

                    $cmd =  "SELECT MAX(internalPartNumber) FROM Part WHERE category_id = " . $currCatID[0];
                    $highestPN = sendQuery($conn, $cmd);
                    $currCatID = $highestPN[0];
                    if (empty($highestPN[0]))
                    {
                        break;
                    }   

                    $counter = $counter +1;
                    if ($counter > 5)
                    {
                        echo "<br>MAX LOOP";
                        break;
                    }
                }
            }
        }
        else
        {
            $highestPN = $highestPN[0] + 1;

            $cmd = "SELECT name FROM Part WHERE internalPartNumber = " . $highestPN;
            $dupPN = sendQuery($conn, $cmd);
            
            echo $dupPN[0] . "<br>";
            if (empty($dupPN[0]))
            {
                $cmd = "UPDATE Part
                       SET internalPartNumber = " . $highestPN . "
                       WHERE name = '" . $part . "'";
               sendQuery($conn, $cmd);
            }
            else
            {
                echo "------ Duplicate NUMBER FOR: " . $part . ", " . $highestPN . "<br>" ;
            }
        }
    }    
}


##************************************************************##
##************************************************************##
function setDBPNPart($conn, $id)
{
    # Input: Internal ID
    #Output: PN if generated
    #        Null if exists
    $cmd =  "SELECT internalPartNumber from Part WHERE id = " . $id . " AND category_id > 1 ";
    $checkPN = sendQuery($conn, $cmd);
    
    if (empty($checkPN[0]))
    {
        $cmd =  "SELECT MAX(internalPartNumber) from Part WHERE category_id = (SELECT category_id FROM Part where id = " . $id . ")";
        $highestPN = sendQuery($conn, $cmd);

        $highestPN = $highestPN[0] + 1;

        $cmd = "SELECT name FROM Part WHERE internalPartNumber = " . $highestPN ;
        $dupPN = sendQuery($conn, $cmd);

        if (empty($dupPN[0]))
        {
            $cmd = "UPDATE Part
                   SET internalPartNumber = " . $highestPN . "
                   WHERE id = " . $id ;
           sendQuery($conn, $cmd);
        }
        else
        {
            echo "------ Duplicate NUMBER FOR: " . $part . ", " . $highestPN . "<br>" ;
        }
        return $highestPN;
    }
    return $checkPN[0];
}


##************************************************************##
##************************************************************##
function getDBPN($conn, $id)
{
    
    $cmd =  "SELECT internalPartNumber from Part WHERE id = " . $id;
    $PN = sendQuery($conn, $cmd);
    
    return $PN[0];
}

##************************************************************##
##************************************************************##
function grab_image($url,$saveto, $filename){
    
$file   = file($url);
$result = file_put_contents($img, $filename);
}

?>