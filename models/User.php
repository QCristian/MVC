<?php
class User {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function findByUsername($username) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function usernameExists($username, $excludeId = null) {
        $stmt = $this->pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
        $stmt->execute([$username, $excludeId ?? -1]);
        return (bool) $stmt->fetchColumn();
    }

    public function emailExists($email, $excludeId = null) {
        if ($email === '') {
            return false;
        }
        $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $excludeId ?? -1]);
        return (bool) $stmt->fetchColumn();
    }

    public function createUser($username, $email, $role, $password) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare("INSERT INTO users (username, email, password, role, force_password_change) VALUES (?, ?, ?, ?, 0)");
        return $stmt->execute([$username, $email ?: null, $hash, $role]);
    }

    public function updateUser($id, $username, $email, $role, $password = null) {
        $fields = [
            'username' => $username,
            'email' => $email ?: null,
            'role' => $role,
            'updated_at' => 'NOW()'
        ];

        if ($password !== null && $password !== '') {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $fields['password'] = $hash;
            $fields['force_password_change'] = 0;
        }

        $sql = 'UPDATE users SET username = ?, email = ?, role = ?, updated_at = NOW()';
        $params = [$username, $email ?: null, $role];

        if ($password !== null && $password !== '') {
            $sql .= ', password = ?, force_password_change = 0';
            $params[] = $hash;
        }

        $sql .= ' WHERE id = ?';
        $params[] = $id;

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    public function updatePassword($id, $hash, $force_password_change = 0) {
        $stmt = $this->pdo->prepare("UPDATE users SET password = ?, force_password_change = ?, updated_at = NOW() WHERE id = ?");
        return $stmt->execute([$hash, $force_password_change, $id]);
    }

    public function updateRole($id, $role) {
        $old = $this->findById($id);
        $oldRole = $old['role'] ?? null;
        $stmt = $this->pdo->prepare("UPDATE users SET role = ?, updated_at = NOW() WHERE id = ?");
        $ok = $stmt->execute([$role, $id]);
        if ($ok) {
            $audit = $this->pdo->prepare("INSERT INTO role_audit (changed_by, user_id, old_role, new_role) VALUES (?, ?, ?, ?)");
            $audit->execute([isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null, $id, $oldRole, $role]);
        }
        return $ok;
    }

    public function deleteUser($id) {
        $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getAll() {
        $stmt = $this->pdo->query("SELECT id,username,email,role,created_at,force_password_change FROM users ORDER BY id DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getNewUsers($days = 3) {
        $stmt = $this->pdo->prepare(
            "SELECT id, username, email, role, created_at FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY) ORDER BY created_at DESC"
        );
        $stmt->execute([$days]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}