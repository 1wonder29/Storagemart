<?php

class BaseModel {
    protected $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function getPDO(): ?PDO {
        return $this->pdo ?? null;
    }
}
