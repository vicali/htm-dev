<?php
if ($_GET['randomId'] != "yd4iqunif6w0rDmnwJExAZbn9QUsOGU1anbg1plGIKCStlb9sMqFieDuYBxB70ub") {
    echo "Access Denied";
    exit();
}

// display the HTML code:
echo stripslashes($_POST['wproPreviewHTML']);

?>  
