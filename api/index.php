<?php
$host = '202.29.70.18';    
$user = 'trees_db';        
$db   = 'trees_db';        
$port = '3306';            
$pass = 'รหัสผ่านจริงของคุณ'; // อย่าลืมแก้ตรงนี้เป็นรหัสผ่านจริง!

$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    die("เชื่อมต่อไม่สำเร็จ: " . $conn->connect_error);
}

// 1. ส่วนของการรับค่าเพื่อบันทึกลงฐานข้อมูล (เมื่อกดเพิ่มจากหน้าเว็บ)
if (isset($_GET['add_node'])) {
    $val = intval($_GET['add_node']);
    $stmt = $conn->prepare("INSERT INTO bst_nodes (value) VALUES (?)");
    $stmt->bind_param("i", $val);
    $stmt->execute();
    $stmt->close();
    header("Location: index.php"); // รีเฟรชหน้าเพื่อล้างค่า GET
    exit();
}

// 2. ส่วนของการล้างข้อมูล (เมื่อกด Reset)
if (isset($_GET['reset'])) {
    $conn->query("TRUNCATE TABLE bst_nodes");
    header("Location: index.php");
    exit();
}

// 3. ดึงข้อมูลจากฐานข้อมูลมาเพื่อนำไปวาดต้นไม้
$result = $conn->query("SELECT value FROM bst_nodes ORDER BY id ASC");
$db_nodes = [];
while($row = $result->fetch_assoc()) {
    $db_nodes[] = (int)$row['value'];
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>Binary Search Tree Interactive Game</title>
    <style>
        body { font-family: 'Sarabun', sans-serif; text-align: center; background-color: #f4f7f6; margin: 0; padding: 20px; }
        .controls { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); display: inline-block; margin-bottom: 20px; }
        input { padding: 10px; width: 80px; border: 1px solid #ddd; border-radius: 5px; font-size: 16px; }
        button { padding: 10px 15px; cursor: pointer; border: none; border-radius: 5px; background-color: #28a745; color: white; font-weight: bold; }
        button.reset { background-color: #dc3545; }
        canvas { background: white; border: 1px solid #ccc; border-radius: 10px; display: block; margin: 0 auto 20px; }
        .results { display: flex; justify-content: center; gap: 10px; flex-wrap: wrap; }
        .method-item { padding: 10px; border-left: 5px solid #007bff; background: white; min-width: 200px; text-align: left; border-radius: 5px; }
        b { color: #d63384; }
        .db-status { color: green; margin-bottom: 10px; font-weight: bold; }
    </style>
</head>
<body>

    <h1>BST Traversal Explorer</h1>
    <div class="db-status">✓ เชื่อมต่อฐานข้อมูล MariaDB แล้ว</div>
    
    <div class="controls">
        <input type="number" id="nodeValue" placeholder="ระบุเลข">
        <button onclick="sendToDB()">เพิ่ม Node</button>
        <button class="reset" onclick="resetDB()">ล้างต้นไม้</button>
    </div>

    <canvas id="treeCanvas" width="800" height="400"></canvas>

    <div class="results">
        <div class="method-item"><strong>Preorder:</strong><br><span id="preorderRes">-</span></div>
        <div class="method-item"><strong>Inorder:</strong><br><span id="inorderRes">-</span></div>
        <div class="method-item"><strong>Postorder:</strong><br><span id="postorderRes">-</span></div>
    </div>

<script>
    class Node {
        constructor(val) {
            this.val = val;
            this.left = null;
            this.right = null;
        }
    }

    let root = null;

    // รับค่าจาก PHP (ข้อมูลจากฐานข้อมูล)
    const savedNodes = <?php echo json_encode($db_nodes); ?>;

    // ฟังก์ชันส่งค่าไป PHP เพื่อบันทึก
    function sendToDB() {
        const val = document.getElementById('nodeValue').value;
        if (val === '') return;
        window.location.href = `index.php?add_node=${val}`;
    }

    function resetDB() {
        if(confirm("ต้องการลบข้อมูลทั้งหมดในฐานข้อมูลใช่หรือไม่?")) {
            window.location.href = `index.php?reset=1`;
        }
    }

    // วาดต้นไม้จากข้อมูลที่ดึงมา
    function initTree() {
        savedNodes.forEach(val => {
            root = insert(root, val);
        });
        updateUI();
    }

    function insert(node, val) {
        if (!node) return new Node(val);
        if (val < node.val) node.left = insert(node.left, val);
        else if (val > node.val) node.right = insert(node.right, val);
        return node;
    }

    function updateUI() {
        const canvas = document.getElementById('treeCanvas');
        const ctx = canvas.getContext('2d');
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        if (root) drawNode(ctx, root, canvas.width / 2, 40, canvas.width / 4);
        
        document.getElementById('preorderRes').innerHTML = `<b>${getPreorder(root).join(' → ')}</b>`;
        document.getElementById('inorderRes').innerHTML = `<b>${getInorder(root).join(' → ')}</b>`;
        document.getElementById('postorderRes').innerHTML = `<b>${getPostorder(root).join(' → ')}</b>`;
    }

    // Traversal Logic
    function getPreorder(n, r=[]) { if(n){ r.push(n.val); getPreorder(n.left,r); getPreorder(n.right,r); } return r; }
    function getInorder(n, r=[]) { if(n){ getInorder(n.left,r); r.push(n.val); getInorder(n.right,r); } return r; }
    function getPostorder(n, r=[]) { if(n){ getPostorder(n.left,r); getPostorder(n.right,r); r.push(n.val); } return r; }

    function drawNode(ctx, node, x, y, offset) {
        if (node.left) {
            ctx.beginPath(); ctx.moveTo(x, y); ctx.lineTo(x - offset, y + 60); ctx.stroke();
            drawNode(ctx, node.left, x - offset, y + 60, offset / 1.8);
        }
        if (node.right) {
            ctx.beginPath(); ctx.moveTo(x, y); ctx.lineTo(x + offset, y + 60); ctx.stroke();
            drawNode(ctx, node.right, x + offset, y + 60, offset / 1.8);
        }
        ctx.beginPath(); ctx.arc(x, y, 20, 0, Math.PI * 2);
        ctx.fillStyle = "#007bff"; ctx.fill();
        ctx.fillStyle = "white"; ctx.fillText(node.val, x, y + 5);
    }

    window.onload = initTree;
</script>
</body>
</html>
