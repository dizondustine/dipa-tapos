<?php

session_start();
include "../../config/database.php";

// Only admin can access this page
if (!isset($_SESSION["role"]) || $_SESSION["role"] != "admin") {
    header("Location: ../../index.php");
    exit;
}

// Get subject ID from URL
$id = isset($_GET["id"]) ? intval($_GET["id"]) : 0;

// Check if ID is valid
if ($id <= 0) {
    die("Invalid Subject ID!");
}

// Get subject data
$sql = "SELECT * FROM subjects WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$subject = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

// Check if subject exists
if (!$subject) {
    die("Subject Not Found!");
}

$message = "";

// Update subject
if (isset($_POST["update"])) {

    // Get form values
    $subject_code = trim($_POST["subject_code"]);
    $subject_name = trim($_POST["subject_name"]);
    $units = intval($_POST["units"]);

    // Validate fields
    if ($subject_code == "" || $subject_name == "") {

        $message = "Please fill in all required fields.";

    } elseif ($units < 0) {

        $message = "Units cannot be negative.";

    } else {

        // Update subject
        $sql = "UPDATE subjects 
                SET subject_code = ?, 
                    subject_name = ?, 
                    units = ?
                WHERE id = ?";

        $stmt = mysqli_prepare($conn, $sql);

        if ($stmt) {

            mysqli_stmt_bind_param(
                $stmt,
                "ssii",
                $subject_code,
                $subject_name,
                $units,
                $id
            );

            if (mysqli_stmt_execute($stmt)) {

                mysqli_stmt_close($stmt);

                // Redirect after successful update
                header("Location: index.php");
                exit;

            } else {

                $message = "Could not update: " . mysqli_stmt_error($stmt);

                mysqli_stmt_close($stmt);
            }

        } else {

            $message = "Database Error: " . mysqli_error($conn);
        }
    }
}

?>

<!doctype html>
<html lang="en">

<head>

    <meta charset="utf-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Edit Subject</title>

    <link
        href="../../assets/vendor/bootstrap/css/bootstrap.min.css"
        rel="stylesheet"
    >

</head>

<body class="bg-light">

<div class="container py-5" style="max-width:700px">

    <div class="card border-0 shadow-sm">

        <div class="card-body p-4">

            <h2 class="mb-4">Edit Subject</h2>


            <?php if ($message != "") { ?>

                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($message); ?>
                </div>

            <?php } ?>


            <form method="POST">


                <!-- Subject Code -->

                <div class="mb-3">

                    <label class="form-label">
                        Subject Code
                    </label>

                    <input
                        type="text"
                        name="subject_code"
                        class="form-control"
                        value="<?php echo htmlspecialchars($subject["subject_code"]); ?>"
                        required
                    >

                </div>


                <!-- Subject Name -->

                <div class="mb-3">

                    <label class="form-label">
                        Subject Name
                    </label>

                    <input
                        type="text"
                        name="subject_name"
                        class="form-control"
                        value="<?php echo htmlspecialchars($subject["subject_name"]); ?>"
                        required
                    >

                </div>


                <!-- Units -->

                <div class="mb-3">

                    <label class="form-label">
                        Units
                    </label>

                    <input
                        type="number"
                        name="units"
                        class="form-control"
                        value="<?php echo htmlspecialchars($subject["units"]); ?>"
                        min="0"
                        required
                    >

                </div>


                <!-- Buttons -->

                <button
                    type="submit"
                    name="update"
                    class="btn btn-primary"
                >
                    Update Subject
                </button>


                <a
                    href="index.php"
                    class="btn btn-secondary"
                >
                    Cancel
                </a>

            </form>

        </div>

    </div>

</div>

</body>

</html>
