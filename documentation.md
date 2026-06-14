# PharmaCore Pharmacy Management System Documentation

## 1. Project Overview

PharmaCore is a complete Pharmacy Management System designed for single branch and multi branch pharmacy businesses. The system should manage pharmacy POS, medicine inventory, batch wise stock, expiry tracking, purchases, sales, supplier ledgers, customer ledgers, returns, expenses, reports, alerts, users, roles, permissions, and audit logs.

This document is created as a handoff for the programming agent and design implementation agent. The UI designs will be provided separately, but the development should follow this functional scope and business logic.

## 2. Main Goal

Build a professional pharmacy ERP system where pharmacy owners and staff can:

- Sell medicines through POS.
- Manage medicine inventory batch wise.
- Track expiry dates and low stock.
- Purchase medicines from suppliers.
- Return medicines to suppliers.
- Handle sale returns from customers.
- Manage customer and supplier ledgers.
- Manage multi branch pharmacy operations.
- Track POS shifts and cashier cash handling.
- Generate reports and analytics.
- Control staff access using roles and permissions.
- Keep complete audit logs of sensitive actions.

## 3. Product Name

System name: PharmaCore

## 4. Design Theme

Use the following UI color system across the whole application.

| Purpose | Color |
|---|---|
| Primary color | 0F766E |
| Secondary color | 2563EB |
| Background | F8FAFC |
| Card background | FFFFFF |
| Sidebar background | 0F172A |
| Text primary | 111827 |
| Text secondary | 6B7280 |
| Border | E5E7EB |
| Success | 16A34A |
| Warning | F59E0B |
| Danger | DC2626 |
| Info | 0284C7 |

General UI style:

- Modern admin dashboard layout.
- Clean cards with rounded corners.
- Left sidebar navigation.
- Top header with search, filters, notifications, and user profile.
- Professional medical ERP look.
- Clear tables with filters and action buttons.
- Status badges for payment, stock, approval, expiry, and user states.
- Modal and drawer based create/edit flows.
- Responsive layout where possible.

## 5. User Roles

The system should support role based access control.

### 5.1 Super Admin

Full access to all branches, settings, users, permissions, reports, audit logs, and system configuration.

### 5.2 Business Owner

Can view all branches, financial reports, sales, purchases, stock, ledgers, expenses, and high level analytics.

### 5.3 Branch Manager

Can manage assigned branch, approve shift closing, approve returns, approve stock adjustments, manage branch staff, and view branch reports.

### 5.4 Pharmacist

Can verify prescriptions, manage medicine details, check stock, approve prescription required medicines, and assist in POS operations.

### 5.5 Cashier

Can open POS shift, create sales, print invoices, process payments, and close shift. Limited access to reports and settings.

### 5.6 Inventory Manager

Can manage stock, batch numbers, expiry, stock adjustments, stock transfers, low stock, reorder, and inventory reports.

### 5.7 Purchase Manager

Can create purchases, add supplier invoices, manage supplier returns, supplier purchase history, and purchase reports.

### 5.8 Accountant

Can manage ledgers, supplier payments, customer receipts, expenses, reports, balances, and financial summaries.

### 5.9 Auditor

Can view audit logs, reports, stock movements, ledger changes, and user activity. Mostly read only access.

## 6. Core Pharmacy Rules

These rules are very important for implementation.

### 6.1 Batch Wise Stock

Every medicine stock entry must be stored batch wise.

Each batch should include:

- Medicine ID
- Batch number
- Expiry date
- Purchase price
- Sale price
- Quantity
- Branch ID
- Supplier ID
- Purchase invoice ID
- Barcode
- Status

### 6.2 Expiry Tracking

The system must track expiry dates for every batch.

Expiry status examples:

- Safe
- Near expiry
- Expiring in 30 days
- Expiring in 60 days
- Expiring in 90 days
- Expired
- Quarantined
- Returned to supplier
- Disposed

Expired medicines should not be allowed in POS sale.

### 6.3 FEFO Stock Sale

POS should follow FEFO logic, First Expiry First Out.

When selling a medicine, the system should select the batch with the nearest valid expiry date first.

### 6.4 Low Stock and Reorder

Each medicine should have reorder settings per branch.

Required fields:

- Minimum stock
- Reorder level
- Maximum stock
- Preferred supplier
- Average daily sale
- Suggested reorder quantity

Suggested reorder quantity can be calculated as:

Suggested Qty = Maximum Stock minus Current Stock plus Safety Stock

### 6.5 Multi Branch

Every important transaction should be branch linked.

Examples:

- Sales
- Purchases
- Stock
- Transfers
- Adjustments
- Expenses
- Ledgers
- Users
- Shifts
- Reports

### 6.6 POS Shift Logic

Cashiers should open a shift before selling.

Shift should track:

- Opening cash
- Cash sales
- Card sales
- Bank transfer sales
- Credit sales
- Refunds
- Shift expenses
- Expected cash
- Counted cash
- Cash shortage
- Cash excess
- Closing status
- Manager approval

### 6.7 Ledger Posting

Sales, purchases, returns, receipts, payments, and expenses should automatically affect ledgers.

Customer ledger increases on credit sale.
Customer ledger decreases on customer receipt or sale return.
Supplier ledger increases on credit purchase.
Supplier ledger decreases on supplier payment or purchase return.
Expenses should create expense ledger entries.

### 6.8 Audit Logs

Every sensitive action must be logged.

Examples:

- Login and failed login
- Sale creation
- Sale return
- Purchase creation
- Purchase return
- Stock adjustment
- Stock transfer
- Medicine price change
- User role change
- Permission change
- Ledger adjustment
- Report export
- Record delete
- Expense approval

## 7. Screen List

The project currently contains 36 screens.

| No | Screen Name | Main Purpose |
|---|---|---|
| 1 | Dashboard screen | High level business overview |
| 2 | POS screen | Medicine sale and checkout |
| 3 | Ledger screen | Customer, supplier, cash, and account ledgers |
| 4 | Purchase screen | Purchase invoice listing |
| 5 | Add Purchase screen | Add supplier purchase invoice |
| 6 | Sales screen | Sales listing and sales management |
| 7 | Supplier screen | Supplier management |
| 8 | Reports screen | Reports and analytics |
| 9 | Settings screen | System settings |
| 10 | Inventory / Stock Management screen | Branch wise stock and batch stock |
| 11 | Medicine / Product Management screen | Medicine catalog |
| 12 | Add Medicine screen | Add new medicine |
| 13 | Stock Transfer screen | Transfer stock between branches |
| 14 | Stock Adjustment screen | Adjust stock quantity |
| 15 | Customer Management screen | Customer records |
| 16 | Sale Return screen | Customer sale return |
| 17 | Purchase Return screen | Supplier purchase return |
| 18 | Shift Management screen | POS shift overview |
| 19 | POS Shift Opening screen | Open cashier shift |
| 20 | POS Shift Closing screen | Close cashier shift |
| 21 | Barcode Management screen | Barcode generation and printing |
| 22 | Alerts / Notifications screen | Alerts and system notifications |
| 23 | User Management screen | Staff user accounts |
| 24 | Roles & Permissions screen | Access control |
| 25 | Branch Management screen | Multi branch management |
| 26 | Expense Management screen | Expense recording and approval |
| 27 | Customer Ledger Detail screen | Single customer ledger |
| 28 | Supplier Ledger Detail screen | Single supplier ledger |
| 29 | Invoice Detail screen | Detailed sale invoice |
| 30 | Prescription Verification screen | Verify prescription required medicines |
| 31 | Expiry Management screen | Expired and near expiry stock |
| 32 | Low Stock / Reorder screen | Reorder planning |
| 33 | Audit Logs screen | System audit trail |
| 34 | Login screen | Secure login |
| 35 | Forgot Password screen | Password recovery |
| 36 | User Profile screen | User account and security settings |

## 8. Main Modules

## 8.1 Authentication Module

Screens:

- Login screen
- Forgot Password screen
- User Profile screen

Features:

- Email and password login.
- Optional OTP verification.
- Forgot password flow.
- Password reset.
- Branch selection after login if user has multiple branches.
- Login audit logging.
- Failed login tracking.
- Account blocked state.
- Active session management.
- Two factor authentication option.

## 8.2 Dashboard Module

Screen:

- Dashboard screen

Features:

- Total sales
- Total purchases
- Profit summary
- Stock value
- Low stock count
- Expiry alerts
- Branch comparison
- Recent sales
- Recent purchases
- Pending approvals
- Reports shortcut
- Alerts shortcut

## 8.3 POS Module

Screens:

- POS screen
- Invoice Detail screen
- Sale Return screen
- POS Shift Opening screen
- POS Shift Closing screen
- Shift Management screen

Features:

- Search medicine by name, generic name, barcode, or batch.
- Add medicine to cart.
- Select customer or walk in customer.
- Apply discount.
- Select payment method.
- Process cash sale, card sale, bank transfer, credit sale, or split payment.
- Print receipt.
- Deduct stock batch wise.
- Use FEFO batch selection.
- Hold sale if prescription verification is required.
- Connect sale with active POS shift.
- Handle sale returns.
- Track shift opening and closing cash.

## 8.4 Medicine Module

Screens:

- Medicine / Product Management screen
- Add Medicine screen
- Barcode Management screen

Features:

- Add medicine.
- Edit medicine.
- Manage medicine category.
- Manage generic name.
- Manage manufacturer.
- Manage dosage form.
- Manage strength.
- Manage pack size.
- Manage barcode.
- Manage prescription required flag.
- Manage controlled medicine flag.
- Manage purchase price and sale price.
- Manage tax and discount.
- Generate product barcode.
- Generate batch barcode.
- Print barcode labels.

## 8.5 Inventory Module

Screens:

- Inventory / Stock Management screen
- Stock Transfer screen
- Stock Adjustment screen
- Expiry Management screen
- Low Stock / Reorder screen

Features:

- Branch wise stock.
- Batch wise stock.
- Stock movement tracking.
- Stock transfer between branches.
- Stock adjustment with approval.
- Expiry monitoring.
- Low stock alerts.
- Reorder level management.
- Supplier based reorder suggestions.
- Stock value reports.
- Quarantine expired stock.
- Block expired batch from POS sale.

## 8.6 Purchase Module

Screens:

- Purchase screen
- Add Purchase screen
- Purchase Return screen
- Supplier Ledger Detail screen

Features:

- Create purchase invoice.
- Add supplier invoice number.
- Add batch number and expiry date.
- Add purchase quantity.
- Add bonus quantity.
- Add purchase price and sale price.
- Update inventory on purchase.
- Update supplier ledger.
- Return damaged or expired medicine to supplier.
- Supplier payment tracking.
- Supplier purchase history.

## 8.7 Sales Module

Screens:

- Sales screen
- POS screen
- Invoice Detail screen
- Sale Return screen
- Customer Ledger Detail screen

Features:

- Create sale invoice.
- Sale listing.
- Customer sale history.
- Sale return.
- Payment tracking.
- Customer ledger posting.
- Medicine stock deduction.
- Print and download invoice.
- Link sale with POS shift.
- Link sale with prescription verification if required.

## 8.8 Ledger Module

Screens:

- Ledger screen
- Customer Ledger Detail screen
- Supplier Ledger Detail screen
- Expense Management screen

Features:

- Customer ledger.
- Supplier ledger.
- Cash ledger.
- Bank ledger.
- Expense ledger.
- Debit and credit entries.
- Opening balance.
- Closing balance.
- Statement generation.
- Payment receipts.
- Supplier payments.
- Credit note and debit note.
- Manual adjustment with approval.

## 8.9 Expense Module

Screen:

- Expense Management screen

Features:

- Add branch expense.
- Add POS shift expense.
- Add utility expense.
- Add salary expense.
- Add rent expense.
- Add maintenance expense.
- Expense approval.
- Expense category.
- Attachment upload.
- Payment method.
- Ledger posting.
- Budget tracking.
- Recurring expenses.

## 8.10 User and Permission Module

Screens:

- User Management screen
- Roles & Permissions screen
- User Profile screen

Features:

- Create user.
- Edit user.
- Assign role.
- Assign branch access.
- Assign POS counter access.
- Manage permissions.
- Reset password.
- Block user.
- Track login history.
- Manage active sessions.
- Manage notification preferences.
- Audit role and permission changes.

## 8.11 Branch Module

Screen:

- Branch Management screen

Features:

- Add branch.
- Edit branch.
- Assign branch manager.
- Manage branch users.
- Manage POS counters.
- Branch wise stock.
- Branch wise sales.
- Branch wise purchases.
- Branch expenses.
- Branch transfers.
- Branch reports.

## 8.12 Alerts Module

Screen:

- Alerts / Notifications screen

Features:

- Low stock alerts.
- Out of stock alerts.
- Expiry alerts.
- Supplier payment due alerts.
- Customer payment due alerts.
- Shift cash shortage alerts.
- Controlled medicine alerts.
- Security alerts.
- Approval alerts.
- Alert rules.
- Notification channels.
- Resolve, dismiss, assign, and escalate alerts.

## 8.13 Prescription Module

Screen:

- Prescription Verification screen

Features:

- Upload prescription.
- Link prescription to POS invoice.
- Verify doctor details.
- Verify customer details.
- Verify prescribed medicines.
- Verify controlled medicines.
- Hold sale until verification.
- Manager approval for controlled medicine.
- Reject invalid prescription.
- Store prescription attachment.
- Audit verification actions.

## 8.14 Reports Module

Screen:

- Reports screen

Features:

- Sales report.
- Purchase report.
- Profit report.
- Stock report.
- Expiry report.
- Low stock report.
- Customer ledger report.
- Supplier ledger report.
- Expense report.
- Shift report.
- User activity report.
- Export PDF, Excel, CSV.
- Print reports.

## 8.15 Audit Module

Screen:

- Audit Logs screen

Features:

- Track all sensitive actions.
- Track old value and new value.
- Track user, role, branch, IP address, and device.
- Filter logs by module, user, action, risk, and date.
- View security events.
- Export audit logs.
- Review suspicious activity.

## 9. Suggested Database Entities

This section gives a starting structure. The programming agent can adjust based on final framework.

## 9.1 Users

Fields:

- id
- name
- email
- phone
- password_hash
- role_id
- status
- two_factor_enabled
- last_login_at
- created_at
- updated_at

## 9.2 Roles

Fields:

- id
- name
- description
- type
- status
- created_at
- updated_at

## 9.3 Permissions

Fields:

- id
- module
- action
- description
- created_at
- updated_at

## 9.4 Role Permissions

Fields:

- id
- role_id
- permission_id
- allowed
- created_at
- updated_at

## 9.5 Branches

Fields:

- id
- name
- code
- type
- phone
- email
- address
- city
- manager_id
- status
- created_at
- updated_at

## 9.6 User Branch Access

Fields:

- id
- user_id
- branch_id
- role_id
- access_level
- status
- created_at
- updated_at

## 9.7 POS Counters

Fields:

- id
- branch_id
- name
- code
- assigned_user_id
- status
- created_at
- updated_at

## 9.8 Medicines

Fields:

- id
- name
- generic_name
- category_id
- manufacturer_id
- dosage_form
- strength
- pack_size
- barcode
- prescription_required
- controlled_medicine
- status
- created_at
- updated_at

## 9.9 Medicine Categories

Fields:

- id
- name
- description
- status
- created_at
- updated_at

## 9.10 Suppliers

Fields:

- id
- name
- contact_person
- phone
- email
- address
- city
- payment_terms
- opening_balance
- status
- created_at
- updated_at

## 9.11 Customers

Fields:

- id
- name
- customer_type
- phone
- email
- address
- city
- credit_limit
- opening_balance
- status
- created_at
- updated_at

## 9.12 Purchases

Fields:

- id
- purchase_no
- supplier_id
- branch_id
- invoice_date
- supplier_invoice_no
- subtotal
- discount
- tax
- grand_total
- paid_amount
- due_amount
- payment_status
- status
- created_by
- created_at
- updated_at

## 9.13 Purchase Items

Fields:

- id
- purchase_id
- medicine_id
- batch_no
- expiry_date
- quantity
- bonus_quantity
- purchase_price
- sale_price
- discount
- tax
- line_total
- created_at
- updated_at

## 9.14 Medicine Batches

Fields:

- id
- medicine_id
- branch_id
- supplier_id
- purchase_id
- batch_no
- expiry_date
- purchase_price
- sale_price
- quantity
- available_quantity
- barcode
- status
- created_at
- updated_at

## 9.15 Sales

Fields:

- id
- sale_no
- branch_id
- pos_counter_id
- shift_id
- customer_id
- sale_date
- subtotal
- discount
- tax
- grand_total
- paid_amount
- due_amount
- payment_method
- payment_status
- invoice_status
- return_status
- prescription_status
- created_by
- created_at
- updated_at

## 9.16 Sale Items

Fields:

- id
- sale_id
- medicine_id
- batch_id
- batch_no
- expiry_date
- quantity
- unit_price
- discount
- tax
- line_total
- returned_quantity
- created_at
- updated_at

## 9.17 Sale Returns

Fields:

- id
- return_no
- sale_id
- customer_id
- branch_id
- return_date
- refund_method
- subtotal
- refund_amount
- status
- reason
- created_by
- approved_by
- created_at
- updated_at

## 9.18 Purchase Returns

Fields:

- id
- return_no
- purchase_id
- supplier_id
- branch_id
- return_date
- settlement_method
- subtotal
- return_amount
- status
- reason
- created_by
- approved_by
- created_at
- updated_at

## 9.19 Stock Movements

Fields:

- id
- medicine_id
- batch_id
- branch_id
- movement_type
- quantity_in
- quantity_out
- balance_after
- reference_type
- reference_id
- reason
- created_by
- created_at

Movement type examples:

- Purchase In
- Sale Out
- Sale Return In
- Purchase Return Out
- Stock Adjustment In
- Stock Adjustment Out
- Transfer Out
- Transfer In
- Expired Stock Out
- Quarantine

## 9.20 Stock Transfers

Fields:

- id
- transfer_no
- from_branch_id
- to_branch_id
- transfer_date
- status
- requested_by
- approved_by
- dispatched_by
- received_by
- notes
- created_at
- updated_at

## 9.21 Stock Transfer Items

Fields:

- id
- transfer_id
- medicine_id
- batch_id
- batch_no
- expiry_date
- quantity
- created_at
- updated_at

## 9.22 Stock Adjustments

Fields:

- id
- adjustment_no
- branch_id
- adjustment_date
- adjustment_type
- reason
- status
- created_by
- approved_by
- created_at
- updated_at

## 9.23 POS Shifts

Fields:

- id
- shift_no
- branch_id
- pos_counter_id
- cashier_id
- opening_cash
- opened_at
- closed_at
- cash_sales
- card_sales
- bank_sales
- credit_sales
- refunds
- expenses
- expected_cash
- counted_cash
- cash_difference
- status
- approved_by
- created_at
- updated_at

## 9.24 Expenses

Fields:

- id
- expense_no
- branch_id
- category_id
- expense_date
- title
- amount
- tax
- total_amount
- payment_method
- payment_account
- related_shift_id
- status
- approval_status
- created_by
- approved_by
- created_at
- updated_at

## 9.25 Ledger Entries

Fields:

- id
- ledger_type
- customer_id
- supplier_id
- account_id
- branch_id
- voucher_no
- voucher_type
- transaction_date
- description
- debit
- credit
- balance
- reference_type
- reference_id
- created_by
- created_at

Ledger type examples:

- Customer
- Supplier
- Cash
- Bank
- Expense

## 9.26 Prescriptions

Fields:

- id
- prescription_no
- customer_id
- sale_id
- branch_id
- doctor_name
- doctor_registration_no
- clinic_name
- prescription_date
- attachment_path
- verification_status
- verified_by
- approved_by
- rejection_reason
- notes
- created_at
- updated_at

## 9.27 Alerts

Fields:

- id
- alert_no
- branch_id
- module
- alert_type
- title
- message
- priority
- status
- assigned_to
- reference_type
- reference_id
- created_at
- resolved_at

## 9.28 Audit Logs

Fields:

- id
- user_id
- role_id
- branch_id
- module
- action
- reference_type
- reference_id
- old_value
- new_value
- ip_address
- device
- risk_level
- status
- created_at

## 10. Main Workflows

## 10.1 Purchase Workflow

1. User creates purchase invoice.
2. User selects supplier and branch.
3. User adds medicine items with batch number and expiry date.
4. System updates medicine batch stock.
5. System creates stock movement records.
6. System posts supplier ledger entry.
7. System logs audit action.
8. If paid amount is added, system posts supplier payment entry.

## 10.2 POS Sale Workflow

1. Cashier opens POS shift.
2. Cashier searches medicine by barcode, name, or generic name.
3. System checks branch stock.
4. System selects valid batch using FEFO.
5. System blocks expired batches.
6. If medicine requires prescription, sale is held for verification.
7. Cashier selects customer or walk in customer.
8. Cashier selects payment method.
9. System creates sale invoice.
10. System deducts batch stock.
11. System creates stock movement records.
12. System posts customer ledger if credit customer.
13. System updates shift totals.
14. System prints invoice.
15. System logs audit action.

## 10.3 Sale Return Workflow

1. User selects sale invoice.
2. System shows return eligible items.
3. User enters return quantity and reason.
4. Manager approval may be required.
5. System returns stock to correct batch if valid.
6. System creates sale return record.
7. System updates customer ledger or refund.
8. System creates stock movement.
9. System logs audit action.

## 10.4 Purchase Return Workflow

1. User selects purchase invoice.
2. User selects return items and batches.
3. User enters return reason.
4. Manager approval may be required.
5. System deducts stock from selected batch.
6. System creates purchase return record.
7. System updates supplier ledger.
8. System creates stock movement.
9. System logs audit action.

## 10.5 Stock Transfer Workflow

1. User selects source branch and destination branch.
2. User selects medicine batches.
3. User enters transfer quantity.
4. Manager approval may be required.
5. System deducts stock from source branch.
6. System adds pending receiving stock for destination branch.
7. Destination branch receives stock.
8. System creates stock movement records.
9. System logs audit action.

## 10.6 Stock Adjustment Workflow

1. User selects branch and medicine batch.
2. User selects adjustment type.
3. User enters quantity and reason.
4. Manager approval may be required.
5. System updates batch stock.
6. System creates stock movement.
7. System logs audit action.

## 10.7 Expiry Workflow

1. System checks batch expiry dates.
2. System marks near expiry batches.
3. System blocks expired batches from sale.
4. User can quarantine expired stock.
5. User can create supplier return if eligible.
6. User can create discount sale if still valid.
7. User can create stock adjustment for expired stock.
8. System logs audit action.

## 10.8 Low Stock Reorder Workflow

1. System checks current stock against reorder level.
2. System generates low stock alerts.
3. System calculates suggested reorder quantity.
4. User reviews supplier suggestions.
5. User creates purchase order or stock transfer.
6. System tracks pending reorder actions.
7. System logs audit action.

## 10.9 Prescription Verification Workflow

1. POS detects prescription required or controlled medicine.
2. Sale is held until verification.
3. Cashier uploads prescription.
4. Pharmacist checks doctor, customer, medicine, quantity, and attachment.
5. Controlled medicine may need manager approval.
6. Pharmacist approves or rejects prescription.
7. If approved, invoice can be completed.
8. If rejected, item is removed or sale is cancelled.
9. System logs audit action.

## 10.10 Expense Workflow

1. User creates expense.
2. User selects branch, category, payment method, and amount.
3. User attaches receipt if available.
4. Approval may be required.
5. System posts expense ledger after approval.
6. If linked with POS shift, expense affects expected cash.
7. System logs audit action.

## 11. Required APIs

The programming agent can implement REST APIs or GraphQL. Below is suggested REST structure.

## 11.1 Auth APIs

- POST /api/login
- POST /api/logout
- POST /api/forgot-password
- POST /api/reset-password
- POST /api/verify-otp
- GET /api/me
- PUT /api/profile
- PUT /api/profile/password

## 11.2 User APIs

- GET /api/users
- POST /api/users
- GET /api/users/{id}
- PUT /api/users/{id}
- DELETE /api/users/{id}
- POST /api/users/{id}/block
- POST /api/users/{id}/reset-password
- GET /api/users/{id}/activity

## 11.3 Role APIs

- GET /api/roles
- POST /api/roles
- GET /api/roles/{id}
- PUT /api/roles/{id}
- DELETE /api/roles/{id}
- GET /api/permissions
- PUT /api/roles/{id}/permissions

## 11.4 Branch APIs

- GET /api/branches
- POST /api/branches
- GET /api/branches/{id}
- PUT /api/branches/{id}
- DELETE /api/branches/{id}
- GET /api/branches/{id}/stock
- GET /api/branches/{id}/sales
- GET /api/branches/{id}/users

## 11.5 Medicine APIs

- GET /api/medicines
- POST /api/medicines
- GET /api/medicines/{id}
- PUT /api/medicines/{id}
- DELETE /api/medicines/{id}
- GET /api/medicines/{id}/batches
- GET /api/medicines/{id}/stock
- POST /api/medicines/import

## 11.6 Inventory APIs

- GET /api/inventory
- GET /api/inventory/batches
- GET /api/inventory/low-stock
- GET /api/inventory/expiry
- POST /api/inventory/adjustments
- POST /api/inventory/transfers
- POST /api/inventory/transfers/{id}/approve
- POST /api/inventory/transfers/{id}/receive

## 11.7 Purchase APIs

- GET /api/purchases
- POST /api/purchases
- GET /api/purchases/{id}
- PUT /api/purchases/{id}
- DELETE /api/purchases/{id}
- POST /api/purchases/{id}/payment
- POST /api/purchase-returns
- GET /api/purchase-returns

## 11.8 Sales APIs

- GET /api/sales
- POST /api/sales
- GET /api/sales/{id}
- POST /api/sales/{id}/payment
- POST /api/sale-returns
- GET /api/sale-returns
- GET /api/invoices/{id}

## 11.9 POS APIs

- GET /api/pos/search-medicine
- POST /api/pos/create-sale
- POST /api/pos/hold-sale
- POST /api/pos/complete-sale
- GET /api/pos/active-shift
- POST /api/pos/shifts/open
- POST /api/pos/shifts/close

## 11.10 Ledger APIs

- GET /api/ledgers/customers/{id}
- GET /api/ledgers/suppliers/{id}
- POST /api/ledgers/customer-receipt
- POST /api/ledgers/supplier-payment
- POST /api/ledgers/adjustment

## 11.11 Expense APIs

- GET /api/expenses
- POST /api/expenses
- GET /api/expenses/{id}
- PUT /api/expenses/{id}
- POST /api/expenses/{id}/approve
- POST /api/expenses/{id}/reject

## 11.12 Prescription APIs

- GET /api/prescriptions
- POST /api/prescriptions
- GET /api/prescriptions/{id}
- POST /api/prescriptions/{id}/verify
- POST /api/prescriptions/{id}/reject
- POST /api/prescriptions/{id}/manager-approval

## 11.13 Alert APIs

- GET /api/alerts
- POST /api/alerts/rules
- PUT /api/alerts/{id}/read
- PUT /api/alerts/{id}/resolve
- PUT /api/alerts/{id}/assign
- PUT /api/alerts/{id}/dismiss

## 11.14 Report APIs

- GET /api/reports/sales
- GET /api/reports/purchases
- GET /api/reports/profit
- GET /api/reports/stock
- GET /api/reports/expiry
- GET /api/reports/low-stock
- GET /api/reports/customer-ledger
- GET /api/reports/supplier-ledger
- GET /api/reports/expenses
- GET /api/reports/audit

## 11.15 Audit APIs

- GET /api/audit-logs
- GET /api/audit-logs/{id}
- POST /api/audit-logs/export
- POST /api/audit-logs/{id}/review

## 12. Important Validations

## 12.1 Purchase Validations

- Supplier is required.
- Branch is required.
- At least one item is required.
- Batch number is required for every item.
- Expiry date is required for every item.
- Quantity must be greater than zero.
- Purchase price must be greater than or equal to zero.
- Sale price must be greater than or equal to purchase price unless admin allows loss sale.

## 12.2 Sale Validations

- Active shift is required for cashier sale.
- Medicine must have available stock.
- Expired batch cannot be sold.
- Controlled medicine requires prescription verification.
- Credit sale requires customer.
- Credit sale should respect customer credit limit unless manager approval is given.
- Discount beyond role limit requires approval.

## 12.3 Stock Transfer Validations

- Source branch and destination branch cannot be same.
- Transfer quantity cannot exceed available stock.
- Expired batch cannot be transferred as sellable stock.
- Transfer must be received by destination branch.

## 12.4 Stock Adjustment Validations

- Adjustment reason is required.
- Negative stock should not be allowed unless admin permission exists.
- High value adjustment requires approval.
- Expired stock adjustment should be audit logged.

## 12.5 Expense Validations

- Expense category is required.
- Branch is required.
- Amount must be greater than zero.
- Receipt attachment may be required based on category.
- High amount expense requires approval.

## 12.6 User and Permission Validations

- Email must be unique.
- Role is required.
- Branch access is required for branch level users.
- Cashier must have POS counter access.
- Role changes must be audit logged.
- Super admin role should be protected.

## 13. Status Values

## 13.1 General Status

- Active
- Inactive
- Pending
- Approved
- Rejected
- Cancelled
- Completed
- Draft
- Blocked

## 13.2 Payment Status

- Paid
- Unpaid
- Partial Paid
- Overdue
- Refunded

## 13.3 Stock Status

- In Stock
- Low Stock
- Critical Low Stock
- Out of Stock
- Near Expiry
- Expired
- Quarantined
- Returned
- Adjusted

## 13.4 Shift Status

- Open
- Closed
- Pending Approval
- Approved
- Rejected

## 13.5 Verification Status

- Pending Verification
- Verified
- Rejected
- Needs Review
- Manager Approval Required

## 13.6 Alert Status

- Unread
- Read
- Pending
- In Progress
- Resolved
- Dismissed
- Escalated

## 14. Permissions Matrix

Each module should support permissions where applicable.

Common permission actions:

- View
- Create
- Edit
- Delete
- Approve
- Reject
- Export
- Print

Modules:

- Dashboard
- POS
- Sales
- Sale Returns
- Purchases
- Purchase Returns
- Inventory
- Medicines
- Stock Adjustments
- Stock Transfers
- Suppliers
- Customers
- Customer Ledger
- Supplier Ledger
- Expenses
- Reports
- Alerts
- Shift Management
- Barcode Management
- User Management
- Roles and Permissions
- Branch Management
- Settings
- Audit Logs

Sensitive permissions:

- Delete sale invoice
- Delete purchase invoice
- Edit completed sale
- Edit completed purchase
- Change medicine price
- Allow negative stock sale
- Approve controlled medicine sale
- Delete stock movement
- Approve stock adjustment
- Approve shift cash shortage
- Manage users
- Manage roles
- Change system settings
- View profit reports
- Export financial reports
- Access audit logs
- Delete audit logs
- Backup and restore system

## 15. Reports Required

## 15.1 Sales Reports

- Daily sales
- Monthly sales
- Branch wise sales
- Cashier wise sales
- Medicine wise sales
- Customer wise sales
- Payment method wise sales
- Sale return report

## 15.2 Purchase Reports

- Supplier wise purchases
- Branch wise purchases
- Medicine wise purchases
- Purchase return report
- Purchase price change report

## 15.3 Inventory Reports

- Current stock report
- Batch wise stock report
- Branch wise stock report
- Low stock report
- Out of stock report
- Expiry report
- Stock adjustment report
- Stock transfer report
- Stock movement report

## 15.4 Financial Reports

- Profit report
- Expense report
- Customer receivable report
- Supplier payable report
- Cash report
- Bank report
- Ledger report
- Shift cash report

## 15.5 User and Audit Reports

- User activity report
- Login history report
- Permission change report
- Audit log report
- Report export history

## 16. Notifications and Alerts

The system should generate alerts for:

- Low stock
- Out of stock
- Near expiry
- Expired stock
- Supplier payment due
- Customer payment due
- Cash shortage
- Cash excess
- Large discount
- Controlled medicine sale
- Prescription pending
- Purchase price change
- Stock adjustment approval
- Stock transfer approval
- Sale return approval
- Purchase return approval
- Failed login attempts
- Role or permission changes
- Backup failure
- Printer or barcode scanner issue

Notification channels:

- In app notification
- Email
- SMS
- WhatsApp
- Push notification

## 17. Non Functional Requirements

## 17.1 Security

- Passwords must be hashed.
- Role based access control must be enforced from backend.
- Sensitive APIs must check permissions.
- Audit logs should be protected.
- User sessions should expire after inactivity.
- 2FA should be supported if possible.
- Exporting financial reports should be logged.

## 17.2 Performance

- Tables must support pagination.
- Filters should be server side.
- Reports should support date range filters.
- Large exports should be queued if needed.
- Medicine search in POS should be fast.
- Barcode search should be optimized.

## 17.3 Reliability

- Stock updates must be transactional.
- Sale creation and stock deduction must happen in one transaction.
- Purchase creation and stock increase must happen in one transaction.
- Ledger posting must be consistent with sale, purchase, payment, return, and expense records.
- Failed operations should rollback.

## 17.4 Usability

- All important tables should have search and filters.
- Forms should have clear validation messages.
- Success and error messages should be user friendly.
- Critical actions should require confirmation.
- Modals should clearly show impact before saving.

## 17.5 Audit and Compliance

- Keep audit logs for every sensitive action.
- Store old value and new value where possible.
- Store user, role, branch, IP address, and device.
- Do not allow normal users to delete audit logs.
- Log every report export.

## 18. Development Notes for Programming Agent

## 18.1 Implementation Priority

Recommended development order:

1. Authentication and role based permissions.
2. Branch management.
3. Medicine and supplier management.
4. Purchase module with batch stock.
5. Inventory and stock movement.
6. POS sale with FEFO batch deduction.
7. Customer and supplier ledgers.
8. Sale return and purchase return.
9. POS shift opening and closing.
10. Expiry management.
11. Low stock and reorder.
12. Prescription verification.
13. Expense management.
14. Reports and analytics.
15. Alerts and notifications.
16. Audit logs.
17. User profile, settings, polish, and final QA.

## 18.2 Stock Transaction Safety

Use database transactions for:

- Purchase creation
- Sale creation
- Sale return
- Purchase return
- Stock transfer
- Stock adjustment
- Expiry adjustment
- Ledger posting

## 18.3 Money Handling

Store money values using decimal type, not floating point.

Recommended precision:

- decimal(12, 2)

## 18.4 Date Handling

Important dates:

- Purchase invoice date
- Sale date
- Expiry date
- Shift opened at
- Shift closed at
- Payment due date
- Ledger transaction date
- Created at
- Updated at

Use timezone aware datetime handling.

## 18.5 File Uploads

File uploads are needed for:

- Prescription attachments
- Expense receipts
- Supplier invoices
- Return proof
- Stock adjustment proof
- Reports export files

## 18.6 Barcode

Barcode should support:

- Product barcode
- Batch barcode
- Supplier barcode
- Custom barcode
- Label printing

Barcode scan should work in POS.

## 19. Acceptance Criteria

The project is ready when:

- Users can log in securely.
- Roles and permissions work from backend.
- Branch wise access works.
- Medicine can be added.
- Purchase invoice adds batch wise stock.
- POS can sell medicine and deduct correct batch.
- Expired stock cannot be sold.
- Low stock alerts are generated.
- Customer ledger updates on credit sale and receipt.
- Supplier ledger updates on purchase and payment.
- Sale return updates stock and ledger.
- Purchase return updates stock and ledger.
- POS shift opening and closing works.
- Expenses can be added and posted to ledger.
- Reports can be filtered and exported.
- Alerts are generated and can be resolved.
- Audit logs record sensitive actions.
- All 36 screens are connected with actual data.

## 20. Final Notes for Claude Design Agent

The design agent should use the provided screen prompts and designs as the visual source of truth.

Important UI instructions:

- Keep all screens consistent.
- Use the PharmaCore branding.
- Keep the admin sidebar consistent.
- Keep top header consistent.
- Use the color palette provided in this document.
- Use medical ERP visual style.
- Use badges for statuses.
- Use tables for operational data.
- Use modals and drawers for create/edit flows.
- Avoid landing page style design.
- Avoid marketing sections.
- Focus on real pharmacy workflow screens.

## 21. Final Notes for Programming Agent

The programming agent should treat this as a full ERP style system, not only a simple CRUD application.

Most important engineering areas:

- Correct stock calculations.
- Batch wise medicine tracking.
- Expiry logic.
- Ledger accuracy.
- Role based permissions.
- POS shift accuracy.
- Audit logging.
- Report filters.
- Transaction safety.
- Multi branch data separation.

Every module should be built in a scalable way because future features may include:

- Online orders.
- Doctor prescription integration.
- Supplier portal.
- Customer portal.
- Mobile POS.
- WhatsApp reminders.
- Advanced analytics.
- AI based reorder suggestions.
- Multi company support.
