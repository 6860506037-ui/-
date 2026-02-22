<?php
// api/index.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // อนุญาตให้หน้าเว็บเรียกใช้ได้

$host = '202.29.70.18';    
$user = 'trees_db';        
$db   = 'trees_db';        
$port = 80;                // พอร์ต 80 ตามที่อาจารย์สั่ง
$pass = '1111';            // รหัสผ่านที่คุณตั้งไว้

// สร้างการเชื่อมต่อ
$conn = new mysqli($host, $user, $pass, $db, $port);

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die(json_encode(["error" => "เชื่อมต่อฐานข้อมูลไม่สำเร็จ: " . $conn->connect_error]));
}

// 1. ส่วนบันทึกข้อมูล (เมื่อกดปุ่มเพิ่ม)
if (isset($_GET['add_node'])) {
    $val = intval($_GET['add_node']);
    $stmt = $conn->prepare("INSERT INTO bst_nodes (value) VALUES (?)");
    $stmt->bind_param("i", $val);
    $stmt->execute();
    $stmt->close();
}

// 2. ส่วนดึงข้อมูลมาวาดต้นไม้ (ส่งกลับเป็น JSON)
$result = $conn->query("SELECT value FROM bst_nodes ORDER BY id ASC");
$nodes = [];
if ($result) {
    while($row = $result->fetch_assoc()) {
        $nodes[] = (int)$row['value'];
    }
}

echo json_encode($nodes);
$conn->close();
?>
