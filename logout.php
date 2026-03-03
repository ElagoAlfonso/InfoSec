<?php
// Need this so PHP knows which session we're even talking about
session_start();    
// Clear out all the variables (like username, etc.)
session_unset();    
// Completely nuke the session from the server
session_destroy();  
// Send them back to the login page now that they're kicked out
header("Location: login.html");
exit();
?>