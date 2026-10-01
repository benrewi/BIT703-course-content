<?php
require_once "sessions.php";
end_session();
header("Location: login.php");
exit;