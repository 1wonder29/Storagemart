# Storage-Mart Ticket Management System (TMS) Policy

**Document Version:** 1.0  
**Effective Date:** June 15, 2026  
**Applies To:** All Storage-Mart employees, contractors, and authorized system users  
**System:** Storage-Mart Ticketing Management System (TMS)

---

## 1. Purpose

This policy establishes the standards, responsibilities, and procedures for using the Storage-Mart Ticket Management System (TMS). TMS is the official channel for reporting, tracking, and resolving workplace concerns related to IT support, facilities, assets, and operational issues across all branches.

The goals of this policy are to:

- Ensure consistent and timely handling of employee requests
- Define clear roles and accountability at every stage of a ticket
- Protect company data and maintain audit integrity
- Provide a transparent, traceable support process for all branches

---

## 2. Scope

This policy applies to:

- All tickets created, updated, assigned, resolved, cancelled, or closed within TMS
- All user roles with TMS access: **Employee**, **Head**, **HR**, **IT**, **AOM**, **OM**, **HOM**, and **Administrator**
- All Storage-Mart branches and departments

Issues that must be reported through TMS include, but are not limited to:

- Hardware, software, and network problems
- Facility-related concerns
- Asset-related support requests
- Operational issues requiring IT or departmental review

---

## 3. Definitions

| Term | Definition |
|------|------------|
| **Ticket** | A formal service request or incident record filed in TMS |
| **Ticket Number** | Unique identifier automatically assigned upon creation (format: `STM-YYYYMMDD-XXXX`) |
| **Requester / Filer** | The employee on whose behalf the ticket is filed |
| **Created By** | The account that submitted the ticket (may differ from the requester when filed by a manager) |
| **Assignee** | IT staff member responsible for working on the ticket |
| **Branch** | The Storage-Mart location associated with the ticket |
| **Department** | The organizational unit routing the ticket (e.g., IT, HR, Operations) |
| **Priority** | Urgency level assigned at creation: Low, Medium, or High |
| **Category** | Type of concern: Network, Hardware, Software, Facility, or Other |
| **Status** | Current stage of the ticket in the workflow |
| **Ticket History** | Immutable log of all status changes and actions on a ticket |
| **Audit Trail** | System-wide activity log of user actions for compliance and review |

---

## 4. Roles and Responsibilities

### 4.1 Employee

- File tickets only for legitimate work-related concerns
- Provide accurate branch, category, priority, and description details
- Respond promptly when IT or support staff request additional information
- Rate IT support after a ticket is marked **Resolved** (one rating per ticket)
- May cancel tickets they filed while status is **Pending**, **In Progress**, **On Hold**, or **Reopened**
- May view only their own tickets and related assets

### 4.2 Department Head

- Oversee tickets within their department
- Create tickets on behalf of department employees when appropriate
- Monitor ticket progress for their branch/department
- May cancel tickets within their department while in a cancellable status
- Escalate unresolved or overdue tickets to IT or Administration

### 4.3 Human Resources (HR)

- Create and manage HR-related tickets
- View tickets within the HR scope
- May cancel HR-department tickets while in a cancellable status
- Coordinate with Administration on employee-related service requests

### 4.4 IT Support

- Review, accept, and work on assigned tickets
- Update ticket status to **In Progress**, **On Hold**, **Resolved**, or **Unresolved**
- Add remarks documenting actions taken
- Reassign tickets only through authorized admin processes
- May cancel tickets when justified (with documented reason)
- Must not access tickets outside their assignment scope unless authorized

### 4.5 Area Operation Manager (AOM)

- Manage operations for **assigned branches only**
- Create tickets for employees within assigned branches
- Monitor branch ticket activity and employee-related concerns
- May cancel tickets for their assigned branches while in a cancellable status
- Must not access data for branches outside their assignment

### 4.6 Operation Manager (OM) / Head of Operations (HOM)

- Oversee operational assignments and branch coverage
- Create tickets on behalf of employees across branches
- Monitor tickets they created or that fall under operations oversight
- May cancel tickets they personally created while in a cancellable status
- Manage AOM branch and employee assignments as authorized

### 4.7 Administrator

- Full oversight of all tickets across branches
- Approve pending tickets and assign them to IT staff
- Decline invalid or duplicate tickets with a documented reason
- Reassign tickets when necessary
- Access cancelled ticket records and audit logs
- Enforce this policy and manage user access

---

## 5. Ticket Creation Standards

### 5.1 When to File a Ticket

Employees and authorized managers must file a TMS ticket when:

- Equipment or software is not functioning correctly
- Network or connectivity issues affect work
- A facility issue impacts safety, access, or operations
- An asset requires repair, replacement, or technical review

### 5.2 Required Information

Every ticket must include:

1. **Employee** — The person affected by the issue
2. **Branch** — The location where the issue occurs
3. **Category** — Network, Hardware, Software, Facility, or Other
4. **Priority** — Low, Medium, or High (see Section 6)
5. **Concern Details** — A clear, factual description of the problem

When applicable, link the ticket to the related **asset/inventory record**.

### 5.3 Prohibited Ticket Submissions

The following are not permitted:

- Duplicate tickets for the same unresolved issue
- Tickets with false, misleading, or incomplete information
- Personal, non-work-related requests
- Abusive, threatening, or inappropriate content
- Tickets filed to circumvent approval or access controls

Violations may result in ticket closure, account restrictions, and disciplinary action per company HR policy.

### 5.4 Ticket Numbering

Upon successful submission, TMS automatically assigns a ticket number in the format:

```
STM-YYYYMMDD-XXXX
```

Example: `STM-20260615-0042`

This number must be referenced in all follow-up communication.

---

## 6. Priority Guidelines

| Priority | When to Use | Target Response |
|----------|-------------|-----------------|
| **High** | Work stoppage, security incident, safety risk, or branch-wide outage | Acknowledge within 2 business hours; resolve or escalate same business day |
| **Medium** | Issue affects productivity but workarounds exist | Acknowledge within 1 business day; resolve within 3 business days |
| **Low** | Minor issue, enhancement request, or non-urgent maintenance | Acknowledge within 2 business days; resolve within 5 business days |

> **Note:** IT and Administration may adjust priority during review if the assigned level does not reflect actual business impact. Repeated misuse of High priority may be flagged for review.

---

## 7. Ticket Lifecycle and Status Definitions

### 7.1 Status Definitions

| Status | Meaning |
|--------|---------|
| **Pending** | Ticket submitted and awaiting admin approval/assignment |
| **In Progress** | Approved and assigned; IT is actively working on the issue |
| **On Hold** | Work paused pending external input, parts, vendor action, or user response |
| **Resolved** | IT has completed work; awaiting requester confirmation or rating |
| **Unresolved** | Issue could not be fully resolved; requires follow-up or escalation |
| **Reopened** | Previously resolved ticket returned for additional work |
| **Closed** | Ticket declined by admin or formally closed with no further action |
| **Cancelled** | Ticket withdrawn before completion |

### 7.2 Standard Workflow

```
[Created] → Pending
              ↓
         Admin Review
         ↙         ↘
    Approve &      Decline
    Assign            ↓
       ↓           Closed
  In Progress
    ↙  ↓  ↘
On Hold  Resolved  Unresolved
           ↓
    Employee Rating
           ↓
      (Closed / Archive)
```

### 7.3 Approval and Assignment

1. New tickets enter **Pending** status upon creation.
2. Administrators review pending tickets for validity, priority, and routing.
3. Upon approval, the ticket is assigned to an IT staff member and moves to **In Progress**.
4. Invalid tickets may be **Declined** with a mandatory reason; declined tickets are marked **Closed**.

### 7.4 IT Resolution Actions

Assigned IT staff may update tickets to:

- **In Progress** — Active work underway
- **On Hold** — Waiting on user, vendor, or parts (remarks required)
- **Resolved** — Work completed; requester is notified to rate support
- **Unresolved** — Issue remains; remarks must explain next steps

All status changes must include remarks when the action is not self-explanatory.

### 7.5 Post-Resolution Feedback

When a ticket is marked **Resolved**:

1. The requester receives a system notification
2. The requester may submit a **one-time satisfaction rating** for IT support
3. Ratings are used for service quality monitoring and must be submitted in good faith

---

## 8. Cancellation Policy

### 8.1 Cancellable Statuses

Tickets may be cancelled only while in one of the following statuses:

- Pending
- In Progress
- On Hold
- Reopened

Tickets that are **Resolved**, **Closed**, **Cancelled**, or **Unresolved** cannot be cancelled through the standard cancel action.

### 8.2 Who May Cancel

| Role | Cancellation Authority |
|------|------------------------|
| **Employee** | Own tickets only |
| **Head / HR** | Tickets within their department |
| **AOM** | Tickets within their assigned branches |
| **OM / HOM** | Tickets they created |
| **IT** | Any ticket (with documented reason) |
| **Administrator** | Any ticket |

### 8.3 Cancellation Requirements

- A **reason** must be provided for every cancellation
- Cancellation is recorded in ticket history and the system audit log
- Cancelled tickets remain in the system for record-keeping and cannot be reactivated; a new ticket must be filed if the issue recurs

---

## 9. Escalation Procedures

Escalate a ticket when:

- Target response times (Section 6) are exceeded
- A ticket remains **On Hold** for more than 3 business days without update
- A ticket is marked **Unresolved** and business impact continues
- A **High** priority ticket has not been acknowledged within the target window

### Escalation Path

1. **Employee / Requester** → Contact their Department Head or AOM
2. **Department Head / AOM** → Contact IT supervisor or Administrator
3. **Administrator** → Reassign, reprioritize, or coordinate cross-department resolution

All escalations should reference the **ticket number** and include the current status and business impact.

---

## 10. Access Control and Data Privacy

### 10.1 Role-Based Access

TMS enforces role-based access control (RBAC). Users may only view and act on data permitted by their role:

- **Employees** see their own tickets and assets
- **AOMs** see only their assigned branches
- **Heads** see their department scope
- **IT** sees assigned and department-routed tickets
- **Administrators** have full system visibility

Sharing login credentials or accessing another user's account is strictly prohibited.

### 10.2 Data Handling

- Ticket content may contain operational or personal information; handle it confidentially
- Do not disclose ticket details to unauthorized persons
- Screenshots or exports of ticket data for non-business purposes are not permitted
- All significant actions are logged in the audit trail (`tbllogs`) and ticket history

### 10.3 Notifications

TMS sends in-system notifications to relevant parties when tickets are created, updated, resolved, or cancelled. Users are responsible for checking notifications during working hours.

---

## 11. Audit, Compliance, and Record Retention

### 11.1 Audit Trail

All ticket actions — including creation, approval, assignment, status changes, cancellation, and decline — are logged with:

- Date and time (Asia/Manila timezone)
- User who performed the action
- Role at time of action
- Before/after status where applicable

### 11.2 Record Retention

Ticket records and history are retained for operational, compliance, and reporting purposes. Administrators may generate monthly and historical ticket reports for management review.

### 11.3 Policy Violations

Suspected misuse of TMS — including fraudulent tickets, unauthorized access attempts, or deliberate priority abuse — must be reported to HR and Administration. Violations may result in:

- Ticket closure without action
- Restriction or revocation of TMS access
- Disciplinary action up to and including termination

---

## 12. System Availability and User Obligations

### 12.1 User Responsibilities

All TMS users must:

- Keep login credentials secure
- Log out when leaving a shared or unattended workstation
- Use TMS as the primary channel for support requests (not informal chat or email alone)
- Update contact and branch information in the system when changes occur
- Cooperate with IT during diagnosis and resolution

### 12.2 System Maintenance

Scheduled maintenance may temporarily affect TMS availability. Critical outages affecting multiple branches should be communicated through official company channels in addition to any TMS downtime notices.

---

## 13. Reporting and Performance Monitoring

Management may use TMS data to monitor:

- Open, pending, and overdue ticket counts by branch
- Average resolution time by priority and category
- IT support ratings and feedback trends
- Cancellation and decline rates
- Monthly ticket volume

This data is used for operational planning and service improvement, not for punitive measures against good-faith reporters.

---

## 14. Policy Review and Updates

This policy will be reviewed **annually** or when significant TMS changes are deployed (new roles, workflows, or modules). Updates require approval from Operations and IT leadership.

| Version | Date | Changes |
|---------|------|---------|
| 1.0 | June 15, 2026 | Initial policy aligned with TMS roles, statuses, and workflows |

---

## 15. Acknowledgment

By using the Storage-Mart Ticket Management System, all users acknowledge that they have read, understood, and agree to comply with this policy.

For questions about this policy or TMS access, contact your **Department Head**, **AOM**, or **IT Administration**.

---

*Storage-Mart — Ticketing Management System*
