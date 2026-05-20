-- Add usertype column to tblaccounts if it doesn't exist
ALTER TABLE tblaccounts 
ADD COLUMN usertype VARCHAR(50) DEFAULT 'EMPLOYEE' AFTER password;

-- Add index for faster lookups
CREATE INDEX idx_usertype ON tblaccounts(usertype);

-- Update existing records to have correct usertype based on their role
-- (You may need to adjust this based on your actual data)

-- Verify the column was added
DESCRIBE tblaccounts;
