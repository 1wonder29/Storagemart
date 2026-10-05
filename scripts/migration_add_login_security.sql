-- Login security (Oct 2026): 15-minute login lock and Forgot Password requests.
-- The app also creates these tables on first use; running this just makes it explicit.
-- Safe to run more than once.

CREATE TABLE IF NOT EXISTS tbllogin_attempts (
    username VARCHAR(100) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    attempts INT NOT NULL DEFAULT 0,
    last_attempt DATETIME NOT NULL,
    PRIMARY KEY (username, ip_address)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS tblpassword_reset_requests (
    request_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    account_id INT NOT NULL,
    note VARCHAR(255) NOT NULL DEFAULT '',
    ip_address VARCHAR(45) NOT NULL DEFAULT '',
    status VARCHAR(20) NOT NULL DEFAULT 'PENDING',
    requested_at DATETIME NOT NULL,
    resolved_by VARCHAR(100) NULL,
    resolved_at DATETIME NULL,
    KEY idx_reset_account (account_id),
    KEY idx_reset_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Read-only: accounts the old rule deactivated after 3 wrong passwords.
-- Failed logins no longer deactivate anyone; reactivate these from Users & Employees
-- if they are still with the company.
SELECT a.account_id, a.username, a.usertype, a.failed_attempts, a.last_attempt_time
FROM tblaccounts a
WHERE UPPER(a.status) = 'INACTIVE' AND a.failed_attempts >= 3;

-- EMERGENCY ONLY (if no administrator can log in): unlock and reactivate an admin.
-- UPDATE tblaccounts SET status = 'ACTIVE', failed_attempts = 0, last_attempt_time = NULL WHERE username = 'admin';
-- DELETE FROM tbllogin_attempts WHERE username = 'admin';
