<?php
function makeSelect($selectName, $id, $name, $description, $table, $where, $sort, $onChange){
    global $dbhost;
    global $dbuser;
    global $dbpassword;
    global $database;
    // connect to the MySQL database server
    $mysqli = new mysqli($dbhost, $dbuser, $dbpassword, $database);
    if($mysqli->connect_errno){
        $select  = "Connection Error: " . $mysqli->connect_errno;
        return $select ;
    }

    // the actual query for the select data
    $SQL = "SELECT $id, $name, $description FROM $table $where ORDER BY $sort LIMIT 1000";
    $result = $mysqli->query( $SQL );
    if (!$result) {
        $select = "Couldn't execute query.".$mysqli->error;
        return $select;
    }
    $mysqli->close();
    
    $row = $result->fetch_array(MYSQL_ASSOC);
    if($row){
        $select  = '<select  id="'.'id'.$selectName.'" name="'.$selectName.'" value="'.$row[$name].'" onChange="'.$onChange.'">';
        $select .= '<option value="'.$row[$id].'">'.$row[$name].' - '.$row[$description].'</option>';
        while($row = $result->fetch_array(MYSQL_ASSOC)) {
            $select .= '<option value="'.$row[$id].'">'.$row[$name].' - '.$row[$description].'</option>';
        }
        $select  .= "</select>";
    }else{
        $select = "No select options found...";
    }
    return $select;
}

?>
