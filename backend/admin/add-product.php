<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add Product | Admin - VisionWave</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../../assets/css/style.css">
  <style>
    body {
      background: #121212;
      color: #fff;
      font-family: sans-serif;
      padding: 40px;
    }
    .form-box {
      max-width: 500px;
      margin: auto;
      background: #1e1e2f;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 0 10px #000;
    }
    h2 {
      text-align: center;
      color: #03a9f4;
    }
    label {
      display: block;
      margin-top: 15px;
      font-weight: bold;
    }
    input, select, textarea {
      width: 100%;
      padding: 10px;
      border: none;
      margin-top: 5px;
      border-radius: 8px;
      background: #2a2a3d;
      color: #fff;
    }
    button {
      margin-top: 20px;
      padding: 12px;
      background: #2979ff;
      color: #fff;
      border: none;
      border-radius: 10px;
      width: 100%;
      font-weight: bold;
      cursor: pointer;
    }
    button:hover {
      background: #1e88e5;
    }
  </style>
</head>
<body>

  <div class="form-box">
    <h2>Add New Product</h2>
    <form action="save-product.php" method="POST" enctype="multipart/form-data">
      <label>Product Name:</label>
      <input type="text" name="name" required>

      <label>Price (₦):</label>
      <input type="number" name="price" step="0.01" required>

      <label>Category:</label>
      <input type="text" name="category" required>

      <label>Description:</label>
      <textarea name="description" rows="4"></textarea>

      <label>Image File:</label>
      <input type="file" name="image" accept="image/*" required>

      <button type="submit">➕ Upload Product</button>
    </form>
  </div>

</body>
</html>
