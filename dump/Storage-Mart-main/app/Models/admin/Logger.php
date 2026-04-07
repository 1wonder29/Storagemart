<?php
// app/Models/Logger.php

class Logger {
    protected $pdo = null;
    protected $link = null;
    protected $table = 'tbllogs'; // match your DB

    public function __construct($pdo = null, $link = null) {
        if ($pdo instanceof PDO) $this->pdo = $pdo;
        elseif (isset($GLOBALS['pdo']) && $GLOBALS['pdo'] instanceof PDO) $this->pdo = $GLOBALS['pdo'];

        if ($link) $this->link = $link;
        elseif (isset($GLOBALS['link'])) $this->link = $GLOBALS['link'];
    }

    public function log($action, $module, $id, $performedby) {
        $date = date('Y-m-d');
        $time = date('H:i:s');  
        if ($this->pdo) {
            $sql = "INSERT INTO {$this->table} (datelog, timelog, action, module, ID, performedby) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([$date, $time, $action, $module, $id, $performedby]);
        }
        if ($this->link) {
            $sql = "INSERT INTO {$this->table} (datelog, timelog, action, module, ID, performedby) VALUES (?, ?, ?, ?, ?, ?)";
            if ($stmt = mysqli_prepare($this->link, $sql)) {
                mysqli_stmt_bind_param($stmt, 'ssssss', $date, $time, $action, $module, $id, $performedby);
                $ok = mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
                return (bool)$ok;
            }
        }
        return false;
    }
}
