# 📱 MobileShop — Mobile Phone Shop Management System
**Project Status:** 🟡 In Progress  
**Priority:** ⭐⭐⭐ Medium-High  
**Target Market:** Mobile phone shops, repair shops, accessories dealers in Pakistan  
**Tech Stack:** PHP + MySQL + HTML/CSS/JS

---

## 📋 Project Overview

Pakistan mein every bazaar mein dozens of mobile shops hain. Inhe chahiye:
- Sale POS (phones, accessories, SIM)
- Repair/service job tracking
- Customer khata (udhaar)
- IMEI tracking for phone sales
- WhatsApp connectivity

---

## ✅ Features To Develop / Complete

### 1. 🔐 Login & Security
- [x] login.php exists
- [ ] Multi-user roles (Owner, Salesman, Technician)

### 2. 📱 Phone Inventory / Stock
- [x] inventory.php exists
- [ ] Phone catalog (Brand, Model, RAM, Storage, Color, Price)
- [ ] IMEI number tracking (very important!)
- [ ] Stock in from supplier
- [ ] Stock out on sale
- [ ] Low stock alerts
- [ ] Phone condition: New, Open Box, Refurbished, Used
- [ ] Accessories inventory (charger, case, screen guard)

### 3. 🛒 POS / Sales
- [x] pos.php exists
- [ ] Fast search by brand/model/IMEI
- [ ] Phone sale with IMEI recording
- [ ] Accessories sale
- [ ] Bundle deals (phone + case + screen guard)
- [ ] Multiple payment methods
- [ ] Udhaar (credit) sales

### 4. 🔧 Repair / Job Card Management
- [ ] Job card creation (customer, phone model, complaint, received date)
- [ ] Technician assignment
- [ ] Repair status: Received / In Progress / Repaired / Delivered
- [ ] Spare parts used tracking
- [ ] Repair charges
- [ ] Customer notification when ready
- [ ] Repair history per phone
- [ ] WhatsApp notification to customer

### 5. 👥 Customer Management
- [x] khata.php exists (Udhaar!)
- [ ] Customer profile (Name, CNIC, Phone)
- [ ] Purchase history
- [ ] Udhaar tracking
- [ ] Payment collection
- [ ] Send reminder via WhatsApp

### 6. 🧾 Invoice & Warranty
- [x] invoice.php, print_invoice.php exist
- [ ] Sale invoice with IMEI
- [ ] Warranty period tracking per sale
- [ ] Warranty claim management
- [ ] Repair job card print

### 7. 🛍️ Purchase Management
- [x] purchase.php exists
- [ ] Supplier list (importers, distributors)
- [ ] Purchase invoice entry
- [ ] Supplier payment tracking
- [ ] Import duty/tax notes

### 8. 💊 Spare Parts Inventory
- [ ] Spare parts catalog (screens, batteries, charging ports, etc.)
- [ ] Per-model compatibility
- [ ] Stock levels
- [ ] Usage in repairs auto-deduct

### 9. 📊 Reports
- [x] reports.php exists
- [ ] Daily sales report
- [ ] Brand-wise sales
- [ ] Best selling models
- [ ] Repair job report
- [ ] Profit margin report
- [ ] IMEI sale register (legal requirement)
- [ ] Udhaar report

### 10. 📱 WhatsApp Integration
- [ ] Repair ready notification to customer
- [ ] Sale receipt via WhatsApp
- [ ] Udhaar reminder
- [ ] Daily summary to owner

### 11. ⚙️ Settings
- [x] settings.php exists
- [ ] Shop name, logo, PTA registration
- [ ] Warranty terms
- [ ] Backup & restore

### 12. 🔒 Licensing
- [ ] Trial period
- [ ] License key system

---

## 🛠️ Tech Stack Details

| Layer | Technology |
|-------|-----------|
| Backend | PHP |
| Database | MySQL |
| Frontend | HTML + CSS + JS |

---

## 🎯 Development Phases

| Phase | Tasks | Status |
|-------|-------|--------|
| Phase 1 | Inventory + POS + Invoice | ✅ Done |
| Phase 2 | Customer khata + Reports | ✅ Done |
| Phase 3 | Repair/Job card system | 🔴 Not Started |
| Phase 4 | Spare parts inventory | 🔴 Not Started |
| Phase 5 | WhatsApp integration | 🔴 Not Started |
| Phase 6 | IMEI tracking compliance | 🔴 Not Started |
| Phase 7 | Licensing + Build | 🔴 Not Started |

---

## 💰 Monetization Plan

| Plan | Price | Features |
|------|-------|----------|
| Trial | Free (15 days) | All features |
| Single Shop | Rs. 5,000 - 10,000 | 1 PC license |
| Chain | Rs. 18,000 - 25,000 | Multi-branch |
| Annual Support | Rs. 2,000 - 3,000/year | |

---

## 📝 Notes & Ideas

- **Repair job card** = most requested feature by mobile shop owners
- **IMEI tracking** = legal in Pakistan (PTA regulations) — sell this as compliance feature
- **PTA Device registration** module add karo (very relevant after PTA DIRBS)
- Spare parts inventory = second business opportunity (sell to repair technicians)
- Target: Hafeez Center Lahore, Hall Road, mobile shops Facebook groups

---

## 🔐 Final Phase: Security & Licensing System (Launch Se Pehle Lazim)

> **Is phase ko complete kiye baghair software sell nahi karna!**

### Kya Implement Karna Hai:

#### Step 1 — PC ID Generation (PHP)
- [ ] Python helper script se Windows `MachineGuid` read karo → SHA256
  ```
  Registry: HKEY_LOCAL_MACHINE\SOFTWARE\Microsoft\Cryptography → MachineGuid
  ```
- [ ] Fallback: MAC + hostname → SHA256

#### Step 2 — Trial System (Server-Side)
- [ ] 15-day trial server pe register
- [ ] File delete se reset nahi hoga
- [ ] "Trial — X days remaining" dikhao

#### Step 3 — License Activation
- [ ] `XXXX-XXXX-XXXX-XXXX` format key
- [ ] `POST /api/activate` → `{key, pc_id}`
- [ ] Encrypted local file mein save

#### Step 4 — Startup Validation
- [ ] PC ID verify every startup
- [ ] Har 3 din online check
- [ ] 7-din offline grace

#### Step 5 — Deactivation + 3-Day Lock
- [ ] Settings → "Deactivate License"
- [ ] 3-din lock after deactivation

#### Step 6 — Code Protection
- [ ] PHP IonCube obfuscation
- [ ] License module alag protected file

#### Step 7 — Admin License Panel
- [ ] Key generate, assign, revoke

### Phase Table:
| Phase | Tasks | Status |
|-------|-------|--------|
| Phase 8 | PC ID + Trial (server-side) | 🔴 Not Started |
| Phase 8 | License activation | 🔴 Not Started |
| Phase 8 | Startup validation | 🔴 Not Started |
| Phase 8 | Deactivation + lock | 🔴 Not Started |
| Phase 8 | Admin panel | 🔴 Not Started |
| Phase 8 | IonCube + final build | 🔴 Not Started |
