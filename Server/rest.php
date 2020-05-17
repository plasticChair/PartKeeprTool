<?php

#Deadbug Electronics


define('CWD', getcwd()); 
require CWD .'/../auxFunctions.php';



$CommandIN =  $_GET['Control'];  // value


##************************************************************##
##************************************************************##
if ($CommandIN == "FindID")
{
    $conn = initDB();
    $id = findIdFromName($conn, "Part" , $_GET['Var']);
    echo data2json($id);
}


##************************************************************##
##************************************************************##
if ($CommandIN == "GenPN")
{
    $conn = initDB();
    $id = $_GET['Var'];
    
    if ($id == null)
    {
        echo data2json(NULL);
    }
    else
    {
        $PN = setDBPNPart($conn, $id);
        echo data2json($PN);
    }
}


##************************************************************##
##************************************************************##
if ($CommandIN == "MakePart")
{
    $conn = initDB();
    $PN = $_GET['Var'];
    
    if ($PN == null)
    {
        echo data2json(NULL);
    }
    else
    {
        $result = createPart($conn, $PN);
        echo data2json($result);
    }
}



##************************************************************##
##************************************************************##
if ($CommandIN == "updatePart")
{
    $conn = initDB();
    $id = $_GET['Var'];
    $data = json_decode(str_replace("'", "\"", $_GET['Data']), true);
    
    if ($id == null)
    {
        echo data2json(NULL);
    }

    foreach ( $data as $key => $value ) 
    {
        // Assign part parameters
         string2NumUnit($conn,$id , $value);

        //Make footprint if doesn't exist
        insertFootprint($conn, $data[$key]['PartParameters']['Package / Case:']); 

        //Update General part info
        updateGenPart($conn, $id, $value);

        //set Attachments
        setAttachments($conn, $id, $value);
    
    
        echo data2json("OK");
    }
    
}


##************************************************************##
##************************************************************##
if ($CommandIN == "FindEmpty")
{
     $conn = initDB();
    
     $cmd = "SELECT name FROM Part WHERE ((internalPartNumber = '') OR (internalPartNumber IS NULL))";
         
    $returnedData = sendQuery($conn, $cmd);
    echo data2json($returnedData);
}



##************************************************************##
##************************************************************##
if ($CommandIN == "Stock")
{
    $conn = initDB();
    
    $dir = $_GET['Var'];
    $part = $_GET['Data'];
        
    if ($dir == 1)
    {   
        if (testPart($conn, $part) ==NULL)
        {
            echo data2json("ERROR");
        }
        else
        {
            addInventory($conn, $part);
            echo data2json("OK");
        }
    }
    else
    {
        if (testPart($conn, $part,1) ==NULL)
        {
            echo data2json("ERROR");
        }
        else
        {
            removeInventory($conn, $part);  
            echo data2json("OK");
        }
    }
}



?>