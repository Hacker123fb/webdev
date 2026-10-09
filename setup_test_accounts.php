<?php
/**
 * One-Time Test Account Provisioning Script
 * 
 * IMPORTANT: DELETE OR REMOVE THIS SCRIPT IMMEDIATELY AFTER RUNNING!
 * Do NOT leave this script accessible in production.
 */

require_once __DIR__ . '/config/database.php';

header('Content-Type: text/plain; charset=utf-8');

echo "========================================================\n";
echo "Library Management System - Test Account Provisioner\n";
echo "========================================================\n\n";

try {
    // 1. Ensure students table has user_id column if upgrading from earlier schema
    $colCheck = $pdo->query("SHOW COLUMNS FROM `students` LIKE 'user_id'")->fetch();
    if (!$colCheck) {
        $pdo->exec("ALTER TABLE `students` ADD COLUMN `user_id` INT NULL UNIQUE AFTER `id`");
        echo "[+] Added `user_id` column to `students` table.\n";
    }

    // 2. Provision Admin Account
    $adminUsername = 'admin';
    $adminPassword = 'admin123';
    $adminHash = password_hash($adminPassword, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username LIMIT 1");
    $stmt->execute([':username' => $adminUsername]);
    $admin = $stmt->fetch();

    if ($admin) {
        $update = $pdo->prepare("UPDATE users SET password = :pass, role = 'admin' WHERE id = :id");
        $update->execute([':pass' => $adminHash, ':id' => $admin['id']]);
        echo "[✓] Admin account updated: '{$adminUsername}' (Password: '{$adminPassword}')\n";
    } else {
        $insert = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (:user, :pass, 'admin')");
        $insert->execute([':user' => $adminUsername, ':pass' => $adminHash]);
        echo "[✓] Admin account created: '{$adminUsername}' (Password: '{$adminPassword}')\n";
    }

    // 3. Provision Student Account
    $studentUsername = 'student';
    $studentPassword = 'student123';
    $studentHash = password_hash($studentPassword, PASSWORD_DEFAULT);

    $stmt->execute([':username' => $studentUsername]);
    $studentUser = $stmt->fetch();

    if ($studentUser) {
        $update = $pdo->prepare("UPDATE users SET password = :pass, role = 'student' WHERE id = :id");
        $update->execute([':pass' => $studentHash, ':id' => $studentUser['id']]);
        $studentUserId = $studentUser['id'];
        echo "[✓] Student account updated: '{$studentUsername}' (Password: '{$studentPassword}')\n";
    } else {
        $insert = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (:user, :pass, 'student')");
        $insert->execute([':user' => $studentUsername, ':pass' => $studentHash]);
        $studentUserId = $pdo->lastInsertId();
        echo "[✓] Student account created: '{$studentUsername}' (Password: '{$studentPassword}')\n";
    }

    // 4. Link Student User to Student Profile (Rahul Sharma / STU-2026-001)
    $linkStmt = $pdo->prepare("UPDATE students SET user_id = :user_id WHERE student_id = 'STU-2026-001'");
    $linkStmt->execute([':user_id' => $studentUserId]);
    echo "[✓] Associated user ID {$studentUserId} ('{$studentUsername}') with student record 'STU-2026-001' (Rahul Sharma)\n\n";

    echo "========================================================\n";
    echo "SUCCESS: Test accounts are active and ready!\n";
    echo "SECURITY REMINDER: Please delete this file (setup_test_accounts.php)\n";
    echo "========================================================\n";

} catch (PDOException $e) {
    echo "Error provisioning test accounts: " . $e->getMessage() . "\n";
}
