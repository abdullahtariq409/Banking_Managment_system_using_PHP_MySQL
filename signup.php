<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $name = $_POST['name'];
  $email = $_POST['email'];
  $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

  $sql = "INSERT INTO users (name, email, password) VALUES (?, ?, ?)";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("sss", $name, $email, $password);

  if ($stmt->execute()) {
    $user_id = $stmt->insert_id;

    // Create a default account record
    $conn->query("INSERT INTO information (user_id, credit, debit, remaining) VALUES ($user_id, 0, 0, 10000)");

    $_SESSION['user_id'] = $user_id;
    $_SESSION['user_name'] = $name;
    header("Location: account.php");
    exit();
  } else {
    echo "Signup failed: " . $conn->error;
  }
}
?>
<?php
include 'db.php';

if (isset($_POST['signup'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (name, email, password) VALUES ('$name', '$email', '$password')";
    if ($conn->query($sql) === TRUE) {
        echo "Signup successful! <a href='login.php'>Login now</a>";
    } else {
        echo "Error: " . $conn->error;
    }
}
?>
<form method="POST" action="">
    <h2>Signup</h2>
    <input type="text" name="name" placeholder="Name" required><br><br>
    <input type="email" name="email" placeholder="Email" required><br><br>
    <input type="password" name="password" placeholder="Password" required><br><br>
    <button type="submit" name="signup">Signup</button>
</form>