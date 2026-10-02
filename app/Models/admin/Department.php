<?php
require_once 'BaseModel.php';

class Department extends BaseModel {

    protected $table = 'tbldepartments';
    protected $tblemployee = 'tblemployee';

    public function fetchAll(): array {
        $stmt = $this->pdo->prepare("SELECT department_id, code, label FROM {$this->table} ORDER BY label ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function fetchById(int $departmentId): ?array {
        $stmt = $this->pdo->prepare("SELECT department_id, code, label FROM {$this->table} WHERE department_id = ? LIMIT 1");
        $stmt->execute([$departmentId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function codeExists(string $code, int $excludeId = 0): bool {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE code = ? AND department_id != ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$code, $excludeId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function addDepartment(string $code, string $label): ?int {
        $stmt = $this->pdo->prepare("INSERT INTO {$this->table} (code, label) VALUES (?, ?)");
        $ok = $stmt->execute([$code, $label]);
        if (!$ok) {
            error_log('Department::addDepartment execute failed: ' . json_encode($stmt->errorInfo()));
            return null;
        }
        return (int) $this->pdo->lastInsertId();
    }

    public function updateDepartment(int $departmentId, string $code, string $label): bool {
        $stmt = $this->pdo->prepare("UPDATE {$this->table} SET code = ?, label = ? WHERE department_id = ? LIMIT 1");
        return $stmt->execute([$code, $label, $departmentId]);
    }

    public function countEmployeesUsingCode(string $code): int {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM {$this->tblemployee} WHERE department = ?");
        $stmt->execute([$code]);
        return (int) $stmt->fetchColumn();
    }

    public function deleteDepartment(int $departmentId): bool {
        $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE department_id = ? LIMIT 1");
        return $stmt->execute([$departmentId]);
    }
}
