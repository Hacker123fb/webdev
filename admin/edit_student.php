<?php
/**
 * Administrator - Edit Student
 * Semester V Web Development Mini Project
 */

// Include authentication and enforce Administrator role
require_once __DIR__ . '/../includes/auth.php';
require_admin('../login.php');

// Include database connection
require_once __DIR__ . '/../config/database.php';

$student_db_id = (int)($_GET['id'] ?? 0);
if ($student_db_id <= 0) {
    header("Location: students.php");
    exit;
}

$error_message = '';

// Fetch student details
try {
    $stmt = $pdo->prepare("SELECT * FROM students WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $student_db_id]);
    $student = $stmt->fetch();

    if (!$student) {
        header("Location: students.php?msg=not_found");
        exit;
    }
} catch (PDOException $e) {
    die("Database error loading student.");
}

$student_id = $student['student_id'];
$name       = $student['name'];
$email      = $student['email'];
$phone      = $student['phone'];
$department = $student['department'];
$semester   = (int)$student['semester'];

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = trim($_POST['student_id'] ?? '');
    $name       = trim($_POST['name'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $phone      = trim($_POST['phone'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $semester   = (int)($_POST['semester'] ?? 1);

    // Basic validation
    if ($student_id === '' || $name === '' || $email === '' || $phone === '' || $department === '') {
        $error_message = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Please enter a valid email address.";
    } elseif ($semester < 1 || $semester > 12) {
        $error_message = "Semester must be between 1 and 12.";
    } else {
        try {
            // Check for duplicate Student ID in other records
            $dupStmt = $pdo->prepare("SELECT id FROM students WHERE student_id = :sid AND id != :id LIMIT 1");
            $dupStmt->execute([':sid' => $student_id, ':id' => $student_db_id]);
            if ($dupStmt->fetch()) {
                $error_message = "The Student ID '{$student_id}' is already registered for another student.";
            } else {
                $updateStmt = $pdo->prepare("
                    UPDATE students 
                    SET student_id = :sid,
                        name = :name,
                        email = :email,
                        phone = :phone,
                        department = :department,
                        semester = :semester
                    WHERE id = :id LIMIT 1
                ");
                $updateStmt->execute([
                    ':sid'        => $student_id,
                    ':name'       => $name,
                    ':email'      => $email,
                    ':phone'      => $phone,
                    ':department' => $department,
                    ':semester'   => $semester,
                    ':id'         => $student_db_id
                ]);

                header("Location: students.php?msg=updated");
                exit;
            }
        } catch (PDOException $e) {
            $error_message = "Database error while updating student.";
        }
    }
}

$page_title = "Edit Student - Library Management System";
$base_path  = "../";
require_once __DIR__ . '/../includes/header.php';
?>

<main class="main-content">
    <div class="container" style="max-width: 650px;">

        <div class="auth-card" style="margin-top: 1rem;">
            <div class="toolbar" style="margin-bottom: 1rem;">
                <h1 class="auth-title" style="margin: 0;">Edit Student Profile</h1>
                <a href="students.php" class="btn btn-secondary btn-sm">&larr; Back to Students</a>
            </div>

            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>

            <form action="edit_student.php?id=<?php echo $student_db_id; ?>" method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="student_id" class="form-label">Student ID / Roll No *</label>
                        <input type="text" id="student_id" name="student_id" class="form-control" value="<?php echo htmlspecialchars($student_id); ?>" required autofocus>
                    </div>
                    <div class="form-group">
                        <label for="name" class="form-label">Full Name *</label>
                        <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($name); ?>" required>
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="email" class="form-label">Email Address *</label>
                        <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($email); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="phone" class="form-label">Phone Number *</label>
                        <input type="tel" id="phone" name="phone" class="form-control" value="<?php echo htmlspecialchars($phone); ?>" required>
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="department" class="form-label">Department *</label>
                        <input type="text" id="department" name="department" class="form-control" value="<?php echo htmlspecialchars($department); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="semester" class="form-label">Semester (1-12) *</label>
                        <input type="number" id="semester" name="semester" class="form-control" min="1" max="12" value="<?php echo htmlspecialchars((string)$semester); ?>" required>
                    </div>
                </div>

                <div class="form-group" style="margin-top: 1.5rem; display: flex; gap: 1rem;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">Update Student</button>
                    <a href="students.php" class="btn btn-outline">Cancel</a>
                </div>
            </form>
        </div>

    </div>
</main>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
