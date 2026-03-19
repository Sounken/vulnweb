<?php
// DEMO : code volontairement mal formaté
// PHPCS (linter) bloque la pipeline
$search = $_GET['search'] ?? '';
if($search)
{
$sql="SELECT * FROM users WHERE username = '$search'";
$result = $pdo->query($sql);
foreach($result as $row) {
echo "<li>" . $row['username'] . "</li>";
}
}
