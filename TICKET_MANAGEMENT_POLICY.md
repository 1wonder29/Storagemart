# Storage-Mart Ticket Management System (TMS) Policy

**Document Version:** 3.0  
**Effective Date:** June 17, 2026  
**Applies To:** All Storage-Mart employees, contractors, and authorized users  
**System:** Storage-Mart Ticket Management System (TMS)  
**Policy Owner:** Information Technology (IT) Department  
**Policy Approvers:** Management, Operations, and IT Leadership

---

## 1. Policy Statement

Storage-Mart adopts the Ticket Management System (TMS) as the official internal platform for logging, processing, monitoring, and recording operational and technical concerns across all authorized branches and departments. All covered users shall use TMS in accordance with this policy to ensure service consistency, accountability, traceability, and information security.

No ticket-related action shall be considered official unless recorded in TMS or in formally authorized records integrated with TMS processes.

---

## 2. Purpose and Objectives

This policy establishes a professional governance framework for ticket operations and related record handling. It aims to:

- Standardize ticket submission and processing procedures
- Define role-based responsibilities and decision authority
- Preserve complete ticket history and auditability
- Protect system access and data confidentiality
- Support operational reporting and service improvement
- Align organizational practice with current system-enforced behavior

---

## 3. Scope

### 3.1 Organizational Scope

This policy applies to all authorized users of TMS, including personnel under roles currently supported by the platform:

- `ADMIN`
- `EMPLOYEE`
- `HEAD`
- `HR`
- `IT`
- `AOM`
- `OM`
- `HOM`

### 3.2 Process Scope

This policy governs:

- Ticket creation, review, assignment, reassignment, resolution, cancellation, and closure
- Ticket status transitions and related remarks/history
- Role-based ticket access and action permissions
- Authentication and account protection controls relevant to TMS use
- Ticket audit trail and system logging requirements
- Asset/accountability records connected to authorized workflows

### 3.3 System Boundary

TMS is an internal company system. It is not a public-facing customer support platform and does not replace external procurement, payroll, or unrelated financial systems.

---

## 4. Limitations and Alignment Note

This policy is aligned to current platform implementation. Where procedural requirements exceed present automation, authorized departments shall perform those requirements manually with proper documentation until corresponding system features are implemented.

Policy interpretation shall prioritize:

1. Data integrity and auditability  
2. Role-based authorization  
3. Business continuity and accountable resolution

---

## 5. Definition of Terms

| Term | Definition |
|------|------------|
| Ticket | A system record of a concern, request, incident, or issue requiring review or action |
| Ticket Number | Unique system-generated identifier for tracking and reporting |
| Requester | Employee or authorized user for whom the concern is filed |
| Created By | Account that submitted the ticket (may differ from requester where permitted) |
| Assigned To | IT personnel currently responsible for ticket handling |
| Priority | Ticket urgency level currently supported by system: `Low`, `Medium`, `High` |
| Category | Classification of concern (for example, hardware, software, network, facility, or other configured type) |
| Status | Current workflow state of a ticket |
| Ticket History | Per-ticket record of action events and status transitions |
| Audit Trail | System-level logging that supports monitoring, review, and accountability |
| Branch Scope | Access limitation that restricts users to authorized branches/departments |
| Cancellable Status | Ticket state where cancellation is currently allowed by system rules |

---

## 6. General Policy Requirements

All covered users shall comply with the following:

- Concerns requiring action shall be filed through TMS.
- Ticket entries shall be factual, complete, and business-relevant.
- Users shall act only within granted permissions.
- Every material ticket action shall be properly recorded.
- Unauthorized access, credential sharing, tampering, and fraudulent ticket activity are prohibited.
- Ticket records shall be retained for operational and compliance purposes.

---

## 7. Governance and Accountability

### 7.1 Policy Ownership

The IT Department owns this policy and is responsible for technical control alignment, implementation oversight, and policy update proposals.

### 7.2 Management Oversight

Management, Operations Leadership, and designated department authorities are responsible for enforcement, escalation governance, and organizational compliance.

### 7.3 User Accountability

All users are personally accountable for actions performed under their accounts and roles.

---

## 8. Roles and Responsibilities

### 8.1 Employees and End Users (`EMPLOYEE`)

Employees and authorized end users shall:

- Submit accurate and complete tickets
- Monitor their own tickets and respond to requests for clarification
- Use TMS strictly for legitimate business concerns
- Protect login credentials and report suspected compromise
- Follow documented ticket and escalation procedures

### 8.2 Department Head (`HEAD`)

Department Heads shall:

- Oversee tickets within approved scope
- File tickets in support of supervised users when appropriate
- Coordinate with IT/Admin for urgent or unresolved concerns
- Enforce proper use of ticket workflow under their area

### 8.3 Human Resources (`HR`)

HR shall:

- Process HR-related tickets and authorized accountability records
- Coordinate with IT, Operations, and Management for employee-related matters
- Ensure proper documentation in workflows affecting personnel accountability

### 8.4 IT Personnel (`IT`)

IT personnel shall:

- Review and process assigned tickets in a timely manner
- Update statuses and remarks based on actual work progress
- Record findings, actions, and outcomes clearly
- Monitor queue health and unresolved concerns
- Maintain quality, continuity, and traceable records of service activity

### 8.5 Area Operations Manager (`AOM`)

AOM shall:

- Operate within assigned branch scope
- Monitor and coordinate branch ticket activity
- Create and track tickets for authorized operational concerns
- Maintain branch-level visibility and coordination with IT/Admin

### 8.6 Operations Management (`OM` and `HOM`)

Operations Management shall:

- Oversee operational assignment structures and branch coordination
- Initiate and monitor tickets under authorized operations scope
- Support continuity during branch/personnel reassignment events

### 8.7 Administrator (`ADMIN`)

Administrators shall:

- Maintain full oversight of ticket operations
- Approve, assign, reassign, decline, and monitor tickets
- Enforce access governance and system-level controls
- Ensure policy implementation consistency and audit visibility

---

## 9. Ticket Submission Standards

### 9.1 Required Minimum Information

Tickets should contain sufficient information for assessment and action, including:

- Affected employee/requester
- Branch or location context
- Category
- Priority (`Low`, `Medium`, or `High`)
- Clear concern details, including impact and relevant context

### 9.2 Submission Quality Rules

Users shall avoid:

- Duplicate tickets for the same active concern
- Misleading or materially incomplete entries
- Non-business or abusive content
- Attempts to bypass approval or control flows

### 9.3 Ticket Validation

Authorized users may decline or cancel tickets that are invalid, unauthorized, duplicative, or not actionable, provided the reason is properly recorded.

---

## 10. Priority Classification and Handling

The current TMS implementation supports the following priority values:

| Priority | Operational Meaning |
|----------|---------------------|
| High | Urgent concern requiring immediate attention and close monitoring |
| Medium | Significant concern requiring timely handling in normal support flow |
| Low | Non-urgent concern processed in standard queue order |

IT and Administration may adjust submitted priority where business impact or urgency is misclassified.

---

## 11. Ticket Status Framework

### 11.1 Primary statuses used in active workflows

- `Pending`
- `In Progress`
- `Resolved`
- `Closed`
- `Cancelled`

### 11.2 Additional statuses recognized in existing rule logic

- `On Hold`
- `Reopened`

These additional statuses may appear in specific module transitions or legacy-compatible workflows.

### 11.3 Status Integrity

Status transitions shall reflect actual operational state and must not be manipulated to misrepresent performance, ownership, or completion.

---

## 12. Ticket Lifecycle Control

### 12.1 Standard flow

- New tickets enter `Pending`
- Authorized review determines disposition (approve/assign/decline)
- Assigned tickets move to `In Progress` during active handling
- Completed corrective actions move tickets to `Resolved`
- Tickets may be `Closed` through authorized closure behavior

### 12.2 History preservation

Ticket number, action history, and audit relevance shall be preserved during assignment or status transitions.

### 12.3 Continuity requirement

Operational changes (including branch/personnel transitions) must preserve continuity of unresolved concerns and documentation trail.

---

## 13. Cancellation Policy

### 13.1 Cancellable statuses

Tickets are cancellable only when current status is:

- `Pending`
- `In Progress`
- `On Hold`
- `Reopened`

### 13.2 Mandatory controls

- Cancellation reason is required.
- Cancellation is recorded in ticket history and logs.
- Cancelled tickets remain retained for audit and reporting.
- Recurring concerns require a new ticket; cancelled tickets are not reactivated by standard flow.

### 13.3 Role-based cancellation authority

Current system-enforced cancellation scope:

- `ADMIN`: any cancellable ticket
- `IT`: any cancellable ticket
- `EMPLOYEE`: own tickets only
- `HEAD` and `HR`: matching department scope
- `AOM`: authorized AOM scope
- `OM` and `HOM`: tickets created by the same account

---

## 14. Assignment, Reassignment, and Operational Continuity

- Approved tickets shall be assigned to appropriate IT personnel.
- Reassignment shall be performed only by authorized personnel.
- Ownership changes shall not invalidate history and audit traceability.
- Branch reassignment scenarios must transfer active operational monitoring responsibilities without altering historical ticket records.
- Reassignment decisions should consider scope, workload, urgency, and continuity.

---

## 15. Access Control, Authentication, and Session Security

### 15.1 Role-Based Access Control (RBAC)

TMS access is role-based and scope-restricted (branch/department where applicable). Users may access only records required for their authorized function.

### 15.2 Authentication and password controls

- Password reset and verification use secure hashing support (`password_hash` / `password_verify`).
- Failed login attempt tracking is enforced.
- Current account protection logic deactivates access after repeated failed attempts (currently three consecutive failed attempts).

### 15.3 Session controls

- Session regeneration occurs on successful login.
- Credential sharing and account misuse are prohibited.

---

## 16. Audit Trail, Logging, and Documentation Requirements

TMS shall maintain auditable records for key actions, including:

- Ticket creation and updates
- Assignment and reassignment
- Status transitions
- Cancellation and closure events
- Authentication-related events where logged by system components

Audit and ticket history records shall be preserved for monitoring, verification, and compliance purposes.

Any unauthorized alteration, suppression, or deletion of logs or history is strictly prohibited.

---

## 17. Data Privacy and Confidentiality

All users shall:

- Access only authorized information
- Handle ticket content confidentially
- Avoid unauthorized sharing, extraction, or disclosure of records
- Observe company privacy and information security obligations

Sensitive information shall be handled under least-privilege and need-to-know principles.

---

## 18. Asset and Accountability Record Handling

TMS supports authorized accountable record workflows across HR/IT/admin/operations modules.

- Only authorized roles may create or modify such records.
- Employees may view records assigned to them where permitted.
- Unauthorized transfer, editing, or deletion of accountable records is prohibited.

---

## 19. Escalation and Exception Handling

Escalation shall be initiated when:

- Ticket progress is blocked beyond reasonable handling window
- Business impact increases materially
- Ownership or scope conflicts prevent action
- Priority requires management-level intervention

Escalation actions should be documented in ticket remarks/history and routed to authorized IT/Admin/Management channels.

Exceptions to normal flow must be documented with rationale and approving authority.

---

## 20. Prohibited Activities

The following are prohibited:

- Unauthorized system or data access
- Use of another user account or credentials
- Fraudulent, malicious, or misleading ticket activity
- Deliberate data manipulation or record destruction
- Bypassing role controls, workflow controls, or security safeguards
- Abuse of elevated privileges

---

## 21. Violations and Disciplinary Action

Violations of this policy may result in one or more of the following:

- Ticket action reversal or administrative correction
- Temporary or permanent account access restriction
- Formal corrective action under company policy
- Escalation to HR/Management for disciplinary proceedings
- Additional legal or compliance action where applicable

Disciplinary decisions shall consider severity, intent, recurrence, and operational impact.

---

## 22. Monitoring, Reporting, and Continuous Improvement

Management and authorized departments may use TMS records for:

- Workload and queue monitoring
- Service trend analysis
- Root-cause and recurring issue review
- Policy compliance assessment
- Process improvement planning

Data from TMS shall be used responsibly and in accordance with confidentiality and governance requirements.

---

## 23. Policy Review and Revision

This policy shall be reviewed periodically by IT and Management, and updated as needed to reflect:

- System changes and newly implemented controls
- Operational requirements
- Security and compliance standards
- Organizational governance directives

| Version | Date | Change Summary |
|---------|------|----------------|
| 3.0 | June 17, 2026 | Professional detailed policy rewrite aligned with current TMS implementation and governance structure |
| 2.0 | June 17, 2026 | Alignment to enforced roles, status logic, cancellation rules, and authentication behavior |
| 1.0 | June 15, 2026 | Initial policy version |

---

## 24. Effectivity and Acknowledgment

This policy takes effect on the date stated above and remains in force until superseded by an approved revision.

Use of TMS constitutes acknowledgment of and agreement to comply with this policy and related company procedures.

For clarifications, users may coordinate with their Department Head, HR, Operations Management, or IT Administration.
