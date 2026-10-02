<?php 
    require_once __DIR__. '/../admin/BaseModel.php';

class Asset extends BaseModel{
    protected $tblinventory = 'tblassets_inventory';
    protected $tblgroup = 'tblassets_group';
    /**
     * Fetch all assets assigned to a specific employee.
     */
    public function fetchAssetsByEmployee(int $employeeId): array
    {
        $sql = "SELECT 
                    ai.inventory_id,
                    ai.assetNumber,
                    ai.itemInfo,
                    ai.serialNumber,
                    g.groupName,
                    g.description
                FROM {$this->tblinventory} ai
                LEFT JOIN {$this->tblgroup} g ON ai.group_id = g.group_id
                WHERE ai.employee_id = ?
                ORDER BY ai.inventory_id DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$employeeId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}