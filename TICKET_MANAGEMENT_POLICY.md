# Storage-Mart Ticket Management System (TMS) Policy

**Document Version:** 2.0  
**Effective Date:** June 17, 2026  
**Applies To:** All Storage-Mart employees, contractors, and authorized users  
**System:** Storage-Mart Ticket Management System (TMS)

---

## 1. Objective

This policy defines the official standards for submission, processing, monitoring, and record management of tickets and related accountable records in the Storage-Mart Ticket Management System (TMS).

The objective is to ensure:

- Consistent ticket handling across all branches and departments
- Clear ownership and accountability per role
- Accurate, auditable records of actions and status changes
- Secure and authorized use of system data and user accounts

---

## 2. Scope and Limitations

### 2.1 Scope

The TMS is the official internal platform for:

- Ticket filing, approval, assignment, resolution, cancellation, and closure
- Ticket history and audit logging
- Role-based ticket visibility and action control
- Branch and operations monitoring for authorized users
- Internal notifications related to ticket events

The system currently supports these roles: `ADMIN`, `EMPLOYEE`, `HEAD`, `HR`, `IT`, `AOM`, `OM`, and `HOM`.

### 2.2 Limitations

TMS is for internal organizational use only. It is not an external customer service platform and is not used for payroll or direct procurement transactions.

Policy statements that depend on unimplemented automation must be treated as procedural requirements handled by authorized personnel until system features are added.

---

## 3. Definitions

| Term | Definition |
|------|------------|
| Ticket | A system record of a concern, request, incident, or issue |
| Ticket Number | Unique system-generated tracking number |
| Priority | Ticket urgency level: `Low`, `Medium`, or `High` |
| Category | Ticket classification (for example: hardware, software, network, facility, or other configured type) |
| Status | Current workflow state of a ticket |
| Ticket History | Per-ticket log of actions and status transitions |
| Audit Trail | System activity logs for accountability and review |
| Assigned To | IT personnel currently responsible for working the ticket |
| Branch Scope | Role-based branch access limitation (notably for AOM and operations roles) |

---

## 4. General Policy

- All technical and operational concerns requiring action must be submitted through TMS.
- Users must provide complete and accurate information when creating or updating tickets.
- Access rights and actions are governed by role-based access control.
- Unauthorized access, credential sharing, data tampering, and fraudulent submissions are prohibited.
- Significant ticket events must remain traceable through ticket history and audit logs.

---

## 5. Roles and Responsibilities

### 5.1 Employees and End Users

- Submit valid, complete, and truthful ticket requests.
- Monitor their own ticket updates and respond when additional details are requested.
- Protect account credentials and report suspected account compromise.
- Use TMS only for legitimate business concerns.

### 5.2 Department Head (`HEAD`)

- Oversee ticket activity within authorized department scope.
- Create and monitor tickets in support of supervised personnel.
- Coordinate escalations with IT and Administration when needed.

### 5.3 Human Resources (`HR`)

- Process HR-related tickets within authorized scope.
- Coordinate employee/accountability concerns with IT and operations management as needed.

### 5.4 IT Personnel (`IT`)

- Work assigned tickets and update status/remarks in a timely manner.
- Record actions taken and outcomes in ticket history.
- Monitor pending, in-progress, and unresolved work queues.
- Maintain service quality and communicate updates to stakeholders.

### 5.5 Area Operations Manager (`AOM`)

- Monitor tickets and employees only within assigned branch scope.
- Create and track tickets for authorized branches and supervised operations concerns.

### 5.6 Operations Management (`OM` / `HOM`)

- Oversee branch assignments, employee-to-AOM assignments, and operations monitoring.
- Create and monitor tickets under operations authority.

### 5.7 Administrator (`ADMIN`)

- Full ticket oversight and approval authority.
- Assign/reassign tickets and decline invalid requests.
- Manage account access, role governance, and audit visibility.

---

## 6. Ticket Submission and Validation

Each ticket should include at minimum:

- Affected employee/requester
- Branch or location context
- Category
- Priority (`Low`, `Medium`, `High`)
- Concern details sufficient for assessment

Invalid, duplicate, unauthorized, or materially incomplete tickets may be declined or cancelled by authorized personnel with a recorded reason.

---

## 7. Priority Classification

The system currently enforces three priority values:

| Priority | Meaning |
|----------|---------|
| High | Urgent concern requiring immediate attention |
| Medium | Operational concern requiring timely handling |
| Low | Non-urgent concern handled in normal queue |

IT or Administration may adjust priority when business impact differs from the submitted value.

---

## 8. Ticket Lifecycle and Status Policy

### 8.1 Primary statuses currently used in system workflows

- `Pending`
- `In Progress`
- `Resolved`
- `Closed`
- `Cancelled`

### 8.2 Additional statuses recognized in rule logic

The cancellation and transition logic also recognizes:

- `On Hold`
- `Reopened`

These states may appear through specific actions or legacy transitions depending on module flow.

### 8.3 Resolution and Closure

- Tickets are marked `Resolved` when IT has completed corrective action.
- `Resolved` tickets may transition to `Closed` through configured closure behavior and/or authorized administrative action.
- Closed or cancelled tickets remain retained for audit and reporting.

---

## 9. Ticket Cancellation Policy

### 9.1 Cancellable statuses

Tickets are cancellable only while status is one of:

- `Pending`
- `In Progress`
- `On Hold`
- `Reopened`

### 9.2 Required controls

- Cancellation reason is mandatory.
- Cancellation action must be recorded in ticket history and logs.
- Cancelled tickets are retained and are not reactivated; recurring concerns require a new ticket.

### 9.3 Role-based cancellation authority

The system enforces the following cancellation scope:

- `ADMIN`: may cancel any cancellable ticket
- `IT`: may cancel any cancellable ticket
- `EMPLOYEE`: own tickets only
- `HEAD` and `HR`: tickets within matching department scope
- `AOM`: tickets within authorized AOM scope
- `OM` and `HOM`: tickets created by that account

---

## 10. Assignment, Reassignment, and Continuity

- New tickets enter `Pending` and are reviewed by authorized personnel.
- Approved tickets are assigned to appropriate IT personnel.
- Reassignment may be performed by authorized administrators/IT supervisors.
- Ticket ownership changes must preserve ticket number, history, and audit traceability.

For branch reassignment scenarios (AOM/operations changes), ongoing operational ownership must be transferred through authorized processes without altering historical records.

---

## 11. User Access, Authentication, and Session Security

### 11.1 Access control

TMS enforces role-based access control, including branch/department scoping where applicable.

### 11.2 Password and login controls

- Password reset uses secure hashing mechanisms (`password_hash` / `password_verify`).
- Failed login monitoring is enforced; account protection controls apply after repeated failed attempts (currently three consecutive failed attempts trigger deactivation logic).

### 11.3 Session controls

- User session regeneration occurs on successful login.
- Users must not share credentials or use another user account.

---

## 12. Audit Trail, Logging, and Data Privacy

- Ticket actions (create, update, assign, resolve, cancel, close) must remain auditable.
- System logs and ticket history are required for accountability, monitoring, and investigations.
- Users must handle ticket data confidentially and access only authorized records.
- Unauthorized alteration or deletion of records/logs is prohibited.

---

## 13. Asset and Inventory-Related Records

TMS supports accountable record workflows across modules (including HR/IT/admin operations). Only authorized users may create or modify these records.

Employees may view assigned records where allowed but cannot perform unauthorized record changes.

---

## 14. Violations and Disciplinary Action

Violations of this policy include, but are not limited to:

- Unauthorized access or credential misuse
- Fraudulent or misleading ticket submissions
- Unauthorized data modification or deletion
- Abuse of privileges or attempts to bypass controls

Violations may result in account restriction, access revocation, administrative sanctions, and disciplinary action per company policy.

---

## 15. Policy Review and Revision

This policy shall be reviewed periodically by IT and Management to ensure continued alignment with implemented system behavior, operational requirements, and security standards.

| Version | Date | Change Summary |
|---------|------|----------------|
| 2.0 | June 17, 2026 | Aligned with current system enforcement, roles, status logic, cancellation controls, and authentication behavior |
| 1.0 | June 15, 2026 | Initial policy version |

---

## 16. Acknowledgment

By using TMS, all authorized users acknowledge and agree to comply with this policy and related company procedures.

For clarifications, coordinate with your Department Head, HR, Operations Management, or IT Administration.
