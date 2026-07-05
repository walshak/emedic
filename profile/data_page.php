<?php
include("../Connections/Conn.php");

$stmt = $db->prepare("SELECT captured_by, COUNT(*) AS total_count FROM enrollee where captured_by!='' GROUP BY captured_by ORDER BY total_count ASC");
$stmt->execute();

echo "<style>
table {
  font-size: 20px;
  border-collapse: collapse;
  width: 100%;
}

th, td {
  border: 1px solid #ddd;
  padding: 8px;
  text-align: left;
}

th {
  background-color: #f0f0f0;
}
</style>";

echo "<table>";
echo "<tr><th>Captured By</th><th>Total Count</th></tr>";

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "<tr>";
    echo "<td>" . strtoupper($row['captured_by']) . "</td>";
    echo "<td>" . $row['total_count'] . "</td>";
    echo "</tr>";
}

echo "</table>";
?>