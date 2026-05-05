-- SQL to add test HR account and sample data
-- This is optional for testing purposes

-- Add test HR account
INSERT INTO `tblaccounts` (`account_id`, `username`, `password`, `usertype`, `status`, `createdby`, `datecreated`)
VALUES (2200601, 'hr_test', '$2y$10$Mb2a98bbOoGAaPhbGdja8uXP8WFXB.uDXieCMXYt1z7f/MMeH4D7i', 'HR', 'ACTIVE', 'admin', NOW());

-- Optional: Add sample uniform inventory data
INSERT INTO `tbluniform_inventory` (`uniform_type`, `size`, `color`, `quantity_in_stock`, `cost_per_unit`, `supplier`, `reorder_level`, `status`, `createdby`, `datecreated`)
VALUES 
('Polo Shirt', 'S', 'Blue', 30, 12.50, 'Supplier A', 10, 'ACTIVE', 'admin', NOW()),
('Polo Shirt', 'M', 'Blue', 45, 12.50, 'Supplier A', 10, 'ACTIVE', 'admin', NOW()),
('Polo Shirt', 'L', 'Blue', 35, 12.50, 'Supplier A', 10, 'ACTIVE', 'admin', NOW()),
('Polo Shirt', 'XL', 'Blue', 20, 12.50, 'Supplier A', 10, 'ACTIVE', 'admin', NOW()),
('ID Badge', 'One Size', 'White/Blue', 100, 2.00, 'Supplier B', 20, 'ACTIVE', 'admin', NOW()),
('Cap', 'One Size', 'Blue', 25, 5.00, 'Supplier C', 10, 'ACTIVE', 'admin', NOW()),
('Safety Vest', 'One Size', 'Yellow', 15, 8.00, 'Supplier D', 5, 'ACTIVE', 'admin', NOW());
