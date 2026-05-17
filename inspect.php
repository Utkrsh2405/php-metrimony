<?php require "includes/dbconn.php"; $r = mysqli_query($conn, "SHOW COLUMNS FROM plans"); if($r) { while($row = mysqli_fetch_row($r)) echo $row[0]."\n"; } else echo mysqli_error($conn); ?>
