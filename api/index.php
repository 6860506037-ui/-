<?php
// api/index.php
header('Content-Type: application/json');
$host = '202.29.70.18';    
$user = 'trees_db';        
$db   = 'trees_db';        
$port = '3306';            
$pass = '1111'; // อย่าลืมใส่รหัสผ่านจริงจากหน้า Dokploy

$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed"]));
}

// รับค่าเพื่อบันทึกลงฐานข้อมูล
if (isset($_GET['add_node'])) {
    $val = intval($_GET['add_node']);
    $stmt = $conn->prepare("INSERT INTO bst_nodes (value) VALUES (?)");
    $stmt->bind_param("i", $val);
    $stmt->execute();
    echo json_encode(["status" => "success"]);
    exit();
}
// ... ต่อจากบรรทัดที่ 22 (exit();) ...

// 2. ส่วนของการดึงข้อมูลทั้งหมดมาวาดต้นไม้
$result = $conn->query("SELECT value FROM bst_nodes ORDER BY id ASC");
$nodes = [];

while($row = $result->fetch_assoc()) {
    $nodes[] = (int)$row['value'];
}

echo json_encode($nodes); // ส่งรายการตัวเลขกลับไปให้ index.html วาดรูป
$conn->close();
// ดึงข้อมูลทั้งหมดส่งกลับไปวาดต้นไม้
$result = $conn->query("SELECT value FROM bst_nodes ORDER BY id ASC");
$nodes = [];
while($row = $result->fetch_assoc()) {
    $nodes[] = (int)$row['value'];
}
echo json_encode($nodes);
?>
