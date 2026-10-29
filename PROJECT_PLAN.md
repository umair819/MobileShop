# 📱 CellNex — Mobile Shop POS & Repair Management System
**Project Status:** 🟡 In Progress  
**Priority:** ⭐⭐⭐ Medium-High  
**Target Market:** Mobile phone shops, repair shops, used phone traders, accessories dealers in Pakistan  
**Tech Stack:** PHP + MySQL / SQLite (Desktop Offline-First) + HTML/CSS/JS  

---

## 📋 Project Overview

Pakistan mein har mobile market aur bazaar (Hafeez Center, Hall Road Lahore, Saddar Karachi, Star City Mall, Dubai Plaza Rawalpindi) mein shops ko ek all-in-one platform chahiye jo:
1. **Phones Buy & Sell (IMEI Tracked)** kare.
2. **Mobile Repairing & Service Job Cards** manage kare (jo pure market mein sab se high-margin business hai).
3. **Spare Parts Inventory** (screens, batteries, charging ports) maintain kare.
4. **Customer Khata (Udhaar)** aur Party Ledgers automate kare.
5. **Market Approval / Haath Par Dena** track kare.
6. **Automated WhatsApp Alerts** (receipts, repair ready, udhaar reminders) send kare.

---

## 🏆 KamboPro Benchmark & High-Value Features (To Adopt in CellNex)
> **Audited from KamboPro ERP (October 2026):** In features ko CellNex mein lazmi shamil karna hai taake CellNex market ke top competitor KamboPro ko trading mein beat kare aur repairs mein monopoly banaye.

### 1. 🔍 Dedicated IMEI Lifecycle Search & Legal Police Audit (`imei_search.php`)
- **Real-time 6 to 15 digit IMEI Search:** Counter par sirf aakhri 6 digit scan/type karne par poora record samne aaye.
- **Complete Birth-to-Sale Trace:**
  - Phone kab aur kis se khareeda (Supplier ya Walk-in Customer name, phone, CNIC photo).
  - Purchase Price + Refurbishment/Repair Cost = Landed Cost Basis.
  - Current Status: `Available`, `On Approval (Haath Par)`, `Sold`, `In Repair`, `Returned`, `Damaged`.
  - Sale details: Kis customer ko bika, CNIC, Phone, Sale Date, Invoice #, Net Profit.
- **1-Click Police / CPLC / FIA Verification Slip:** Chori ya inquiry ki soorat mein dukaandar ki legal bachat ke liye printable ownership proof report.

### 2. 🛍️ Dual-Mode Purchase Engine with Landed Refurbishment Cost
- **Mode A (Wholesale / Importer):** Box-packed lot purchase from distributors with trade credit terms.
- **Mode B (Walk-in Customer Used Phone Purchase):**
  - Walk-in seller details: Name, Mobile, CNIC Front & Back photo attachment, Purchase Affidavit.
  - **Landed Cost Formula:**  
    $$\text{Total Landed Cost} = \text{Purchase Price} + \text{Repair/Refurbishment Cost}$$  
    *(Maslan Rs. 100,000 ka phone khareeda aur Rs. 8,000 ka display change karwaya to cost basis Rs. 108,000 hogi taake sale par accurate profit niklay).*

### 3. 📱 Dual IMEI Tracking (SIM 1 & SIM 2)
- Pakistan mein 90% Android & dual-eSIM phones dual-IMEI hote hain.
- `imei_number_1` aur `imei_number_2` dono store karne ki capability.
- Settings mein toggle: *"Track Dual IMEI (Yes/No)"*.

### 4. 💰 Live Counter Profit Transparency in POS
- Sale screen par salesman/cashier ko item ke barabar purchase cost aur live profit margin dikhe.
- Dukaandar ko turant pata chalay ga ke customer ko kitna discount de sakta hai bina nuqsaan ke.

### 5. 🔄 Sale & Purchase Returns with Restocking & Deduction (Katoti)
- Customer used phone wapis kare to configurable restocking deduction (maslan 5% ya 10% katoti) apply ho.
- Wapis aane wala phone automatically inventory mein usi original IMEI ke sath dobara `Available` ho jaye.

### 6. 💵 Daily Shift Closing & Physical Cash Drawer Reconciliation (`daily_closing.php`)
- Counter chori aur staff mismanagement rokne ke liye:
  $$\text{Expected Drawer Cash} = \text{Opening Cash} + \text{Cash Sales} + \text{Khata Wasooli} - \text{Cash Purchases} - \text{Expenses}$$
- Cashier/Owner se "Physical Counted Cash" input mangna.
- System automatically **Variance / Difference (Short / Excess)** calculate aur log karega.
- Printable Day-End Closing Slip (X-Report).

### 7. 📒 Simplified Double-Entry Accounting & Multi-Ledgers
- **Cash & Bank Accounts:** Multi-channel tracking (Cash Drawer, Meezan Bank, HBL, Bank Alfalah, JazzCash, EasyPaisa).
- **Vouchers:** Cash Payment, Cash Receipt, Bank Transfer, Journal Voucher.
- **Ledgers:** Customer Ledger (Udhaar), Supplier Ledger (Dena), Party Ledger (jo customer bhi hai aur supplier bhi).

### 8. 📷 "Scan with Mobile" QR Camera Scanner
- Salesman ke phone par QR code scan karwa kar uske phone camera ko PC counter ke liye wireless barcode/IMEI scanner banana.

---

## 💎 CellNex's Killer Moats (Where CellNex Crushes KamboPro)

| Feature | CellNex | KamboPro | Why CellNex Wins |
| :--- | :---: | :---: | :--- |
| **🔧 Mobile Repair / Job Cards** | ✅ **Core Module** | ❌ **0% Support** | **Highest profit margin in mobile shops** |
| **💊 Spare Parts Compatibility** | ✅ **Core Module** | ❌ **0% Support** | Direct tracking of displays, batteries, flexes |
| **🤝 Market Approval (Haath Par Dena)** | ✅ **Built-in** | ❌ **0% Support** | Hall Road / Hafeez Center dealer-to-dealer trial |
| **🖼️ Visual Showroom Counter POS** | ✅ **`pos.php`** | ❌ Generic tables | Customer-facing visual specs & quick WhatsApp share |
| **⚡ 100% Offline-First Uptime** | ✅ **Desktop App** | ❌ Cloud Only | Load-shedding aur internet cut par counter kabhi nahi rukega |
| **🤖 WhatsApp Bot Automation** | ✅ **Automated** | ❌ No bot | Repair ready SMS/WA, digital receipt, udhaar reminder |
| **💵 One-Time Lifetime License** | ✅ **Shop Friendly** | ❌ Monthly SaaS | Pakistani shopkeepers hate recurring monthly fees |

---

## ✅ Core Features Checklist

### 1. 🔐 Login & Multi-Role Security
- [x] `login.php` exists
- [ ] Multi-user roles:
  - **Owner / ShopAdmin:** Full access, profits, settings, delete permissions.
  - **Counter Salesman:** POS, IMEI sales, approvals, no cost price visibility (unless permitted).
  - **Repair Technician:** Job cards, assigned repairs, parts request, repair status.

### 2. 📱 Phone Inventory / Stock (Enhanced)
- [x] `inventory.php` exists
- [ ] Phone catalog (Brand, Model, RAM, Storage, Color, PTA Status)
- [ ] **Dual IMEI tracking (`imei_1`, `imei_2`)**
- [ ] **Status Lifecycle:** `Available`, `On Approval`, `Sold`, `In Repair`, `Returned`, `Damaged`
- [ ] Phone condition: New Box-Pack, Open Box, Refurbished, Used (10/10, 9/10)
- [ ] Accessories inventory (Chargers, Cables, Handsfree, Protectors, Cases)
- [ ] Low stock alerts & reorder levels

### 3. 🛒 POS & Sales Engine
- [x] `pos.php` visual showroom exists
- [ ] Fast search by Model or IMEI (last 6 digits)
- [ ] Phone sale with automated IMEI status update to `Sold`
- [ ] Live margin indicator (Purchase cost vs Sale price)
- [ ] Multiple payment splits (Cash + JazzCash + Bank + Udhaar)
- [ ] Fast accessories barcode checkout

### 4. 🔧 Mobile Repair / Service Job Card System (THE BIG MOAT)
- [ ] **Job Card Creation:**
  - Customer name, mobile, alternate phone.
  - Device brand, model, color, IMEI / Serial.
  - Screen Lock (Pattern draw / PIN password / Passcode).
  - Physical condition upon receive (scratches, dent, broken back, camera lens ok/cracked).
  - Customer complaint & preliminary estimate.
- [ ] **Technician Assignment & Commission:** Assign job to in-house technician or market specialist.
- [ ] **Repair Status Tracking:** `Received` ➔ `Diagnosing` ➔ `Waiting for Parts` ➔ `In Progress` ➔ `Repaired / Ready` ➔ `Delivered` ➔ `Unrepairable / Returned`.
- [ ] **Spare Parts Auto-Deduct:** Repair mein lagne wala display ya battery inventory se auto-deduct ho.
- [ ] **Repair Job Card Print:** Thermal & A4 token for customer with terms (no claim after 7 days, water damage risk, etc.).
- [ ] **Automated WhatsApp Notification:** Customer ko alert: *"Aapka phone repair ho gaya hai, dukaan se collect kar lein"*.

### 5. 💊 Spare Parts Inventory
- [ ] Catalog: Displays/LCDs, Batteries, Charging Strips, Glass, Cameras, ICs, Housings.
- [ ] Compatibility matrix (e.g. iPhone 11 Display, Samsung A12 Battery).
- [ ] Technician usage audit & profit margin per repair.

### 6. 🤝 Market Approval / Check (Haath Par Dena)
- [x] `approval_history` table exists in `dashboard.php`
- [ ] 1-Click "Give on Approval" to dealer / customer with return deadline.
- [ ] Approval tracking screen: Kis shop ke paas kitnay phone check par pare hain.
- [ ] 1-Click "Convert to Sale" ya "Return to Stock".

### 7. 👥 Customer & Supplier Khata (Udhaar)
- [x] `khata.php` exists
- [ ] Customer profile with CNIC Front/Back photo storage
- [ ] Udhaar ledgers with partial payment collections
- [ ] Supplier purchase payables tracking
- [ ] WhatsApp payment reminder with shop bank account details

### 8. 🧾 Invoices & Legal Certificates
- [x] `invoice.php`, `print_invoice.php` exist
- [ ] Thermal 80mm & 58mm POS receipt
- [ ] A4 Full Tax/Legal Sale Invoice with IMEI, warranty, and seller verification
- [ ] **Police / CPLC Ownership Certificate** generated in 1-click from IMEI search

### 9. 📊 Reports & Daily Closing
- [x] `reports.php` exists
- [ ] **Daily Shift Closing (`daily_closing.php`):** System drawer vs physical cash count variance
- [ ] Brand-wise & Model-wise sales and profit report
- [ ] IMEI Sale Register (Legal audit requirement)
- [ ] Repair job profitability & technician performance report

### 10. 🤖 WhatsApp Bot Integration
- [ ] Repair job card confirmation
- [ ] Repair ready notification
- [ ] Digital sale invoice with IMEI & warranty terms
- [ ] Udhaar reminder with polite wording & account details
- [ ] Owner daily closing summary report

---

## 🛠️ Tech Stack & Deployment Architecture

| Component | Technology | Role |
| :--- | :--- | :--- |
| **Backend** | PHP 8.x | Lightweight, ultra-fast, universally compatible |
| **Database** | MySQL (Store Server) / SQLite (Single PC) | Zero-maintenance local storage |
| **Frontend** | HTML5, Bootstrap 5, FontAwesome, Vanilla JS | Clean, responsive, glassmorphic POS aesthetic |
| **Desktop Wrapper** | Electron / Native PHP Desktop | 100% offline desktop executable with silent thermal print |
| **Hardware** | USB Barcode/QR Scanner, 80mm Thermal Printer, Cash Drawer | Plug & Play POS hardware support |

---

## 🎯 Master Development Phases

| Phase | Milestone / Features | Source / Benchmark | Status |
| :---: | :--- | :--- | :---: |
| **Phase 1** | Inventory + POS + Invoice | CellNex Core | ✅ Completed |
| **Phase 2** | Customer Khata + Basic Reports | CellNex Core | ✅ Completed |
| **Phase 3** | **Dedicated IMEI Lifecycle Search (`imei_search.php`) & Dual IMEI** | **Adopted from KamboPro** | 🟡 Next Up |
| **Phase 4** | **Dual Purchase Engine (Used Phone Walk-in + CNIC Attachments + Refurb Cost)** | **Adopted from KamboPro** | 🟡 Next Up |
| **Phase 5** | **Daily Shift Closing & Physical Cash Drawer Variance (`daily_closing.php`)** | **Adopted from KamboPro** | 🟡 Next Up |
| **Phase 6** | **Mobile Repairing & Service Job Card System** | **CellNex Moat (KamboPro Killer)** | 🔴 Planned |
| **Phase 7** | **Spare Parts Inventory & Usage Auto-Deduction** | **CellNex Moat** | 🔴 Planned |
| **Phase 8** | **Market Approval (Haath Par Dena) Dealer Management** | **CellNex Unique** | 🔴 Planned |
| **Phase 9** | **WhatsApp Bot Engine (Receipts, Repair Alerts, Udhaar)** | **CellNex Moat** | 🔴 Planned |
| **Phase 10** | **Simplified Double-Entry Multi-Bank Ledger & Cashier Roles** | **Adopted from KamboPro** | 🔴 Planned |
| **Phase 11** | **MachineGuid PC Licensing & Electron Desktop Build** | CellNex Security | 🔴 Planned |

---

## 💰 Monetization & Market Pricing Strategy

| Package | Target Customer | Pricing (PKR) | Model |
| :--- | :--- | :--- | :--- |
| **Single Counter** | Small mobile shop / accessories shop | Rs. 8,000 - 12,000 | Lifetime License (1 PC) |
| **Pro (Sales + Repairs)** | Busy showroom with repair counter | Rs. 18,000 - 25,000 | Multi-User LAN (POS + Technician PC) |
| **Wholesale / Chain** | Multi-branch or wholesale trading shop | Rs. 35,000 - 50,000 | Multi-Terminal + Cloud Sync |
| **Annual Support / Updates** | All active shops | Rs. 3,000 - 5,000 / year | Annual AMC |

---

## 📌 Implementation Reminder for Next Work Session
Jab bhi CellNex par development shuru karein, sab se pehle **Phase 3 (IMEI Search & Dual IMEI)** aur **Phase 4 (Dual Purchases with Refurb Cost & CNIC Upload)** implement karein, uske foran baad **Phase 6 (Repair Job Cards)** shuru karein jo software ko market mein unbeatable bana dega.
