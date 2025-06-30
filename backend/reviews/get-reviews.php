require_once __DIR__.'/../config/db.php';
$pid=(int)($_GET['product_id']??0);
$r=$conn->query("SELECT * FROM reviews WHERE product_id=$pid ORDER BY created_at DESC")
        ->fetch_all(MYSQLI_ASSOC);
header('Content-Type:application/json');echo json_encode($r);
