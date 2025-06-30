require_once __DIR__.'/../config/db.php';
$pid=$_POST['product_id'];$user=$_POST['user'];$rate=$_POST['rating'];$txt=$_POST['comment'];
$stmt=$conn->prepare("INSERT INTO reviews (product_id,user,rating,comment) VALUES (?,?,?,?)");
$stmt->bind_param("isis",$pid,$user,$rate,$txt);$stmt->execute();echo 'ok';
