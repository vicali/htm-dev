<?php
 function netrc($hostname) {
  $homedir = getenv("HOME") ? getenv("HOME") : "/home/roiexp";
  $netrc = file($homedir . DIRECTORY_SEPARATOR . ".netrc");
  foreach ($netrc as $line) {
   $credentials = split(" ", trim($line));
   if ($credentials[1] == $GLOBALS["database"])
    return array($credentials[1], $credentials[3], $credentials[5]);
  }
 }
 function query($query) {
  list($database, $username, $password) = netrc($GLOBALS["database"]);
  mysql_connect("localhost", $username, $password);
  mysql_select_db($database);
  $fields = NULL;
  $result = mysql_query($query);
  error_log("query: ${query}, result: ${result}");
  return $result;
 }
 function extract_post($key) {
  if (array_key_exists($key, $_POST)) {
   $value = $_POST[$key];
   unset($_POST[$key]);
   return $value;
  } else {
   return "";
  }
 }
 function removeslashes($string) {
  return get_magic_quotes_gpc() ? stripslashes($string) : $string;
 }
 function remove_crlf($string) {
  return str_replace(array("\r", "\n"), " ", $string);
 }
 function dispatch_query() {
  $table = extract_post("table");
  $query = extract_post("query");
  $where = extract_post("where");
  error_log("query=$query, table=$table, where=$where");
  if ($query == "insert") {
   if ($table == "complaints") $_POST["ip"] = $_SERVER["REMOTE_ADDR"];
   $sql = "INSERT INTO $table (" . implode(", ", array_keys($_POST)) .
    ") VALUES ('" . implode("', '", array_values($_POST)) . "')";
  } elseif ($query == "select") {
   $sql = "SELECT " . implode(", ", array_keys($_POST)) .
    " FROM $table";
   if ($where) $sql .= " WHERE " . removeslashes(urldecode($where));
  } else {
   error_log("only 'select' and 'insert' supported");
   return false;
  }
  $result = query($sql);
  if ($query == "insert" && $result) {
   print "Insert successful\r\n";
  } elseif ($query == "select" && $result) {
   while ($row = mysql_fetch_assoc($result)) {
    if (!$fields) {
     $fields = implode("\t", array_keys($row));
     print $fields . "\r\n";
    }
    $values = implode("\t", array_map(remove_crlf, (array_values($row))));
    print $values . "\r\n";
   }
  }
  //print "query was: ${query}";
  return ($result);
 }
 header("Content-type: text/plain");
 $GLOBALS["database"] = "roiexp_med";
 if (!dispatch_query()) error_log("no valid query");
?>
