<?php 
    require_once 'BaseModel.php';

class Asset extends BaseModel {
    protected $tblassets = 'tblassets_inventory';
    protected $tblgroup = 'tblassets_group';
    protected $tblbranch = 'tblbranch';
    protected $tblcategory = 'tblassets_category';
    protected $tblassign = 'tblassets_assignment';
    protected $tblemployee = 'tblemployee';
    protected $tblaccounts = 'tblaccounts';

    
    public function fetchAllAssets(): array {
        $sql = "SELECT g.group_id, g.groupName, g.description, c.categoryName, COUNT(i.group_id) AS totalItems, SUM(CASE WHEN i.status = 'ASSIGNED' THEN 1 ELSE 0 END) AS assigned, SUM(CASE WHEN i.status = 'UNASSIGNED' THEN 1 ELSE 0 END) AS unassigned 
        FROM {$this->tblgroup} g 
        JOIN {$this->tblcategory} c ON g.category_id = c.category_id 
        LEFT JOIN {$this->tblassets} i ON g.group_id = i.group_id 
        AND i.status NOT IN ('DISPOSE','LOST') 
        GROUP BY g.group_id, g.groupName, g.description, c.categoryName
        ORDER BY g.group_id ASC; ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addBranch(string $branchName, string $branchCode, string $branchAddress, string $createdBy): ?int
    {
        $sql = "
            INSERT INTO {$this->tblbranch} 
            (branchCode, branchName, branchAddress, datecreated, createdby)
            VALUES (:branchCode, :branchName, :branchAddress, :datecreated, :createdby)
        ";

        $stmt = $this->pdo->prepare($sql);
        $dateCreated = date("Y-m-d H:i:s");
        $success = $stmt->execute([
            ':branchCode'   => $branchCode,
            ':branchName'   => $branchName,
            ':branchAddress'=> $branchAddress,
            ':datecreated'  => $dateCreated,
            ':createdby'    => $createdBy,
        ]);

        if ($success) {
            return (int) $this->pdo->lastInsertId();
        }
        return null;
    }

    public function addCategory(string $categoryName, string $ic_code, string $createdBy): ?int
    {
        $sql = "
            INSERT INTO {$this->tblcategory} 
            (categoryName, ic_code, datecreated, createdby)
            VALUES (:categoryName, :ic_code, :datecreated, :createdby)
        ";

        $stmt = $this->pdo->prepare($sql);
        $dateCreated = date("Y-m-d H:i:s");
        $success = $stmt->execute([
            ':ic_code'      => $ic_code,
            ':categoryName' => $categoryName,
            ':datecreated'  => $dateCreated,
            ':createdby'    => $createdBy,
        ]);

        if ($success) {
            return (int) $this->pdo->lastInsertId();
        }
        return null;
    }

    public function addGroup(string $groupName, string $description, int $categoryId, string $ic_code, string $createdBy): ?int
    {
        $sql = "
            INSERT INTO {$this->tblgroup} 
            (category_id, ic_code, groupName, description, datecreated, createdby)
            VALUES (:category_id, :ic_code, :groupName, :description, :datecreated, :createdby)
        ";

        $stmt = $this->pdo->prepare($sql);
        $dateCreated = date("Y-m-d H:i:s");

        $success = $stmt->execute([
            ':category_id' => $categoryId,
            ':ic_code'     => $ic_code,
            ':groupName'   => $groupName,
            ':description' => $description,
            ':datecreated' => $dateCreated,
            ':createdby'   => $createdBy,
        ]);

        if ($success) {
            return (int) $this->pdo->lastInsertId();
        }
        return null;
    }


    public function fetchCategories(): array {
        $stmt = $this->pdo->prepare("SELECT category_id, ic_code, categoryName FROM {$this->tblcategory} ORDER BY categoryName ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function fetchGroupById(int $groupId): ?array
    {
        $sql = "SELECT g.*, c.categoryName, c.ic_code
                FROM {$this->tblgroup} g
                JOIN {$this->tblcategory} c ON g.category_id = c.category_id
                WHERE g.group_id = :group_id
                LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':group_id' => $groupId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function updateGroup(int $groupId, string $groupName, string $description): bool
    {
        $sql = "UPDATE {$this->tblgroup}
                SET groupName = :groupName,
                    description = :description
                WHERE group_id = :group_id";
        $stmt = $this->pdo->prepare($sql);
        $params = [
            ':groupName'   => $groupName,
            ':description' => $description,
            ':group_id'    => $groupId,
        ];
        $ok = $stmt->execute($params);
        return $ok; 
    }


    //Fetch items by group ID
    public function fetchItemsByGroupId(int $groupId): array {
        $stmt = $this->pdo->prepare("SELECT 
            i.inventory_id,
            i.assetNumber,
            i.serialNumber,
            i.itemInfo,
            i.status,
            b.branch_id,
            b.branchName,
            e.employee_id,
            CONCAT(e.firstname, ' ', e.lastname) AS employeeName,
            g.group_id,
            g.groupName,
            a.assignment_id,
            a.transferDetails
        FROM {$this->tblassets} i 
        LEFT JOIN {$this->tblassign} a 
            ON i.assignment_id = a.assignment_id
        LEFT JOIN {$this->tblbranch} b 
            ON i.branch_id = b.branch_id
        LEFT JOIN {$this->tblemployee} e
            ON i.employee_id = e.employee_id
        JOIN {$this->tblgroup} g 
            ON i.group_id = g.group_id
        WHERE i.group_id = ?
        ORDER BY i.inventory_id ASC; ");
        $stmt->execute([$groupId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }


    // Adding asset item
    public function addItem(int $groupId, string $serialNumber, string $itemInfo, string $year_purchased, string $createdBy): ?int
    {
        try {
            $this->pdo->beginTransaction();

            // 1) get ic_code from group -> category
            $sql = "SELECT c.ic_code
                    FROM {$this->tblgroup} g
                    JOIN {$this->tblcategory} c ON g.category_id = c.category_id
                    WHERE g.group_id = :group_id
                    LIMIT 1";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':group_id' => $groupId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $ic_code = $row['ic_code'] ?? null;

            if (empty($ic_code)) {
                $this->pdo->rollBack();
                return null;
            }

            // 2) compute assetCode (sequential) and assetNumber
            $stmt = $this->pdo->query("SELECT COUNT(*) AS cnt FROM {$this->tblassets}");
            $countRow = $stmt->fetch(PDO::FETCH_ASSOC);
            $total = (int)($countRow['cnt'] ?? 0);
            $assetCode = $total + 1;
            $assetCodePadded = str_pad($assetCode, 3, "0", STR_PAD_LEFT);

            // use last 2 chars of provided year string (e.g. "2022" -> "22")
            $yearshort = substr($year_purchased, -2);
            $assetNumber = "{$ic_code}-{$yearshort}{$assetCodePadded}";

            // 3) insert into tblassets_inventory
            $sql = "INSERT INTO {$this->tblassets}
                    (group_id, serialNumber, itemInfo, status, assetCode, assetNumber, year_purchased, datecreated, createdby)
                    VALUES (:group_id, :serialNumber, :itemInfo, 'UNASSIGNED', :assetCode, :assetNumber, :year_purchased, :datecreated, :createdby)";
            $stmt = $this->pdo->prepare($sql);
            $params = [
                ':group_id'      => $groupId,
                ':serialNumber'  => $serialNumber,
                ':itemInfo'      => $itemInfo,
                ':assetCode'     => $assetCode,
                ':assetNumber'   => $assetNumber,
                ':year_purchased'=> $year_purchased,
                ':datecreated'   => date("Y-m-d H:i:s"),
                ':createdby'     => $createdBy,
            ];
            $ok = $stmt->execute($params);

            if (!$ok) {
                $this->pdo->rollBack();
                return null;
            }

            $newId = (int) $this->pdo->lastInsertId();
            $this->pdo->commit();
            return $newId;
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            // optionally rethrow or log
            return null;
        }
    }

    // inside class Asset extends BaseModel
    public function updateItem(int $inventoryID, string $itemInfo, string $serialNumber, string $yearPurchased, string $status, ?string $reason, ?int $performedByAccountId = null): bool
    {
        try {
            $this->pdo->beginTransaction();

            // Normalize values
            $status = strtoupper($status);
            $now = date('Y-m-d H:i:s');

            if (in_array($status, ['RETURNED', 'DISPOSED', 'LOST'])) {
                // 1) update inventory: clear assignment & employee
                $sqlUp = "UPDATE {$this->tblassets}
                        SET assignment_id = NULL,
                            itemInfo = :itemInfo,
                            serialNumber = :serialNumber,
                            year_purchased = :yearPurchased,
                            status = :status,
                            employee_id = NULL
                        WHERE inventory_id = :inventory_id";
                $stmt = $this->pdo->prepare($sqlUp);
                $ok = $stmt->execute([
                    ':itemInfo'     => $itemInfo,
                    ':serialNumber' => $serialNumber,
                    ':yearPurchased'=> $yearPurchased,
                    ':status'       => $status,
                    ':inventory_id' => $inventoryID,
                ]);
                if (!$ok) { $this->pdo->rollBack(); return false; }

                // 2) insert assignment record describing the return/dispose/lost
                $transferDetails = $reason ? trim($reason) : sprintf('%s without reason', $status);
                $dateIssued = date('Y-m-d');     // legacy used both
                $dateReturned = date('Y-m-d');

                $sqlIns = "INSERT INTO {$this->tblassign}
                        (employee_id, inventory_id, assignedTo, dateIssued, transferDetails, dateReturned, datecreated, createdby)
                        VALUES (:employee_id, :inventory_id, :assignedTo, :dateIssued, :transferDetails, :dateReturned, :datecreated, :createdby)";
                $stmt2 = $this->pdo->prepare($sqlIns);

                // employee_id is NULL (unassigned) for returned/lost/disposed in legacy
                $ok2 = $stmt2->execute([
                    ':employee_id'    => null,
                    ':inventory_id'   => $inventoryID,
                    ':assignedTo'     => "Unassigned / {$status}",
                    ':dateIssued'     => $dateIssued,
                    ':transferDetails'=> $transferDetails,
                    ':dateReturned'   => $dateReturned,
                    ':datecreated'    => $now,
                    ':createdby'      => $performedByAccountId ?? 'SYSTEM',
                ]);
                if (!$ok2) { $this->pdo->rollBack(); return false; }

                // 3) link assignment_id back to inventory
                $newAssignmentId = (int) $this->pdo->lastInsertId();
                $sqlUpd = "UPDATE {$this->tblassets} SET assignment_id = :assignment_id WHERE inventory_id = :inventory_id";
                $stmt3 = $this->pdo->prepare($sqlUpd);
                $ok3 = $stmt3->execute([':assignment_id' => $newAssignmentId, ':inventory_id' => $inventoryID]);
                if (!$ok3) { $this->pdo->rollBack(); return false; }

                $this->pdo->commit();
                return true;
            } else {
                // Normal update (no assignment change)
                $sql = "UPDATE {$this->tblassets}
                        SET itemInfo = :itemInfo,
                            serialNumber = :serialNumber,
                            year_purchased = :yearPurchased,
                            status = :status
                        WHERE inventory_id = :inventory_id";
                $stmt = $this->pdo->prepare($sql);
                $ok = $stmt->execute([
                    ':itemInfo'     => $itemInfo,
                    ':serialNumber' => $serialNumber,
                    ':yearPurchased'=> $yearPurchased,
                    ':status'       => $status,
                    ':inventory_id' => $inventoryID,
                ]);
                if (!$ok) { $this->pdo->rollBack(); return false; }
                $this->pdo->commit();
                return true;
            }
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            // optionally log $e->getMessage()
            return false;
        }
    }
    public function fetchInventoryById(int $inventoryId): ?array
    {
        $sql = "SELECT i.*, g.group_id, g.groupName
                FROM {$this->tblassets} i
                LEFT JOIN {$this->tblgroup} g ON i.group_id = g.group_id
                WHERE i.inventory_id = :inventory_id
                LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':inventory_id' => $inventoryId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }
// find employee by approximate name or ID (for AJAX search)
    public function findEmployeeByQuery(string $q): ?array
    {
        // match numeric id or name parts
        if (ctype_digit($q)) {
            $sql = "SELECT e.employee_id, CONCAT(e.lastname, ', ', e.firstname, ' ', IFNULL(e.middlename, '')) AS fullname, b.branchName, b.branchCode
                    FROM {$this->tblemployee} e
                    LEFT JOIN {$this->tblbranch} b ON e.branch_id = b.branch_id
                    WHERE e.employee_id = :id
                    LIMIT 1";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => (int)$q]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ?: null;
        } else {
            $like = '%' . $q . '%';
            $sql = "SELECT e.employee_id, CONCAT(e.lastname, ', ', e.firstname, ' ', IFNULL(e.middlename, '')) AS fullname, b.branchName, b.branchCode
                    FROM {$this->tblemployee} e
                    LEFT JOIN {$this->tblbranch} b ON e.branch_id = b.branch_id
                    WHERE e.firstname LIKE :like OR e.lastname LIKE :like OR CONCAT(e.firstname, ' ', e.lastname) LIKE :like
                    ORDER BY e.lastname ASC
                    LIMIT 1";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':like' => $like]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ?: null;
        }
    }

    public function transferAssetToEmployee(int $inventoryId, int $employeeId, string $transferDetails, $performedBy): array
    {
        try {
            $this->pdo->beginTransaction();

            // 1) load current asset
            $inventory = $this->fetchInventoryById($inventoryId);
            if (!$inventory) {
                $this->pdo->rollBack();
                return ['ok'=>false, 'message'=>'Inventory not found'];
            }
            $oldAssetNumber = $inventory['assetNumber'] ?? '';

            // 2) compute baseAssetNumber (first two dash parts like OE-24)
            $parts = explode('-', $oldAssetNumber);
            $baseAssetNumber = (count($parts) >= 2) ? ($parts[0] . '-' . $parts[1]) : $oldAssetNumber;

            // 3) get branch info for new employee — QUALIFY branch_id to avoid ambiguity
            $stmt = $this->pdo->prepare(
                "SELECT b.branch_id AS branch_id, b.branchCode
                FROM {$this->tblbranch} b
                JOIN {$this->tblemployee} e ON e.branch_id = b.branch_id
                WHERE e.employee_id = :eid
                LIMIT 1"
            );
            $stmt->execute([':eid' => $employeeId]);
            $branch = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$branch) {
                $this->pdo->rollBack();
                return ['ok'=>false, 'message'=>'Employee branch not found'];
            }
            $branch_id = (int)$branch['branch_id'];
            $branchCode = $branch['branchCode'];

            // 4) compute next transferCount safely (MAX cast to unsigned)
            $stmt = $this->pdo->query("SELECT IFNULL(MAX(CAST(transferCount AS UNSIGNED)), 0) AS m FROM {$this->tblassign}");
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $maxCount = (int)($row['m'] ?? 0);
            $newTransferCount = $maxCount + 1;
            $formattedTransferCount = str_pad($newTransferCount, 3, "0", STR_PAD_LEFT);

            // 5) new asset number
            $newAssetNumber = $baseAssetNumber . '-' . $branchCode . $formattedTransferCount;

            // 6) assignedTo (employee fullname)
            $stmt = $this->pdo->prepare("SELECT CONCAT(lastname, ', ', firstname, ' ', IFNULL(middlename,'')) AS fullname FROM {$this->tblemployee} WHERE employee_id = :eid LIMIT 1");
            $stmt->execute([':eid' => $employeeId]);
            $emp = $stmt->fetch(PDO::FETCH_ASSOC);
            $assignedTo = $emp ? $emp['fullname'] : 'Employee ' . $employeeId;

            // 7) insert into assignment table
            $now = date('Y-m-d H:i:s');
            $dateIssued = date('Y-m-d');
            $sqlInsert = "INSERT INTO {$this->tblassign} (employee_id, inventory_id, transferCount, assignedTo, dateIssued, transferDetails, datecreated, createdby)
                        VALUES (:employee_id, :inventory_id, :transferCount, :assignedTo, :dateIssued, :transferDetails, :datecreated, :createdby)";
            $stmt = $this->pdo->prepare($sqlInsert);
            $ok = $stmt->execute([
                ':employee_id' => $employeeId,
                ':inventory_id' => $inventoryId,
                ':transferCount' => $formattedTransferCount,
                ':assignedTo' => $assignedTo,
                ':dateIssued' => $dateIssued,
                ':transferDetails' => $transferDetails,
                ':datecreated' => $now,
                ':createdby' => $performedBy
            ]);
            if (!$ok) { $this->pdo->rollBack(); return ['ok'=>false, 'message'=>'Failed inserting assignment']; }

            $assignmentId = (int)$this->pdo->lastInsertId();

            // 8) update inventory with new assignment/employee/branch/assetNumber/status
            $newStatus = 'ASSIGNED';
            $sqlUpd = "UPDATE {$this->tblassets} SET assignment_id = :aid, employee_id = :eid, branch_id = :bid, assetNumber = :assetNumber, status = :status WHERE inventory_id = :inventory_id";
            $stmt = $this->pdo->prepare($sqlUpd);
            $ok2 = $stmt->execute([
                ':aid' => $assignmentId,
                ':eid' => $employeeId,
                ':bid' => $branch_id,
                ':assetNumber' => $newAssetNumber,
                ':status' => $newStatus,
                ':inventory_id' => $inventoryId
            ]);
            if (!$ok2) { $this->pdo->rollBack(); return ['ok'=>false, 'message'=>'Failed updating inventory']; }

            // 9) log to tbllogs
            $sqlLog = "INSERT INTO tbllogs (datelog, timelog, action, module, ID, performedby) VALUES (:datelog, :timelog, :action, :module, :ID, :performedby)";
            $stmt = $this->pdo->prepare($sqlLog);
            $date = date('Y-m-d');
            $time = date('H:i:s');
            $action = "Transferred asset {$oldAssetNumber} to {$assignedTo} ({$branchCode})";
            $stmt->execute([
                ':datelog' => $date,
                ':timelog' => $time,
                ':action' => $action,
                ':module' => 'Asset Inventory',
                ':ID' => $inventoryId,
                ':performedby' => $performedBy
            ]);

            $this->pdo->commit();
            return ['ok'=>true, 'newAssetNumber' => $newAssetNumber];
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            return ['ok'=>false, 'message' => $e->getMessage()];
        }
    }

    public function fetchAssignmentsByInventoryId(int $inventoryId): array {
        $sql = "SELECT employee_id, assignedTo, transferDetails, dateIssued, dateReturned, createdby
        FROM {$this->tblassign}
        WHERE inventory_id = ?
        ORDER BY assignment_id DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$inventoryId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getAssetNotificationTargets(int $inventoryId): array
    {
        $sql = "
            SELECT 
                emp.account_id AS employee_account_id,
                head.account_id AS head_account_id,
                inv.assetNumber,
                emp.department
            FROM tblassets_inventory inv
            JOIN tblemployee emp ON emp.employee_id = inv.employee_id
            LEFT JOIN tblemployee head_emp 
                ON head_emp.department = emp.department
            AND head_emp.position = 'HEAD'
            LEFT JOIN tblaccounts head 
                ON head.account_id = head_emp.account_id
            WHERE inv.inventory_id = :inventory_id
            LIMIT 1
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['inventory_id' => $inventoryId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    
}