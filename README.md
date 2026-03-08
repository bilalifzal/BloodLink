<h1 align="center">🩸 BloodLink Pro - Enterprise Ecosystem</h1>

<div align="center">
  <strong>A Full-Stack Blood Bank Management & Emergency Response Architecture</strong><br>
  <i>Digitizing the critical medical supply chain to bridge the gap between donors and patients with zero latency.</i>
</div>

<br>

<div align="center">
  <a href="https://bloodlinkpro.kesug.com/"><b>🔴 Live Production Deployment</b></a>
</div>

---

## 📖 Project Overview

**BloodLink Pro** is a comprehensive, data-driven web application designed to solve real-world logistical nightmares in the emergency blood supply chain. Unlike traditional static directories, this platform operates as a centralized **Command and Control System**. It utilizes advanced backend logic, smart compatibility algorithms, and direct API integrations to connect hospitals, administrators, and voluntary donors in real-time.

---

## 🏗️ Core Modules & Exhaustive Feature List

### 🎮 1. Admin Command Center (The Brain)
* **Role-Based Access Control (RBAC):** Secure authentication system ensuring only authorized personnel can verify donors and approve emergency broadcasts.
* **Transactional Unit Assignment:** Admins can manually assign blood units to specific patients. This executes a secure SQL transaction that locks the unit, updates global inventory, and transitions the request status (`Pending` ➝ `In Progress` ➝ `Completed`), preventing duplicate allocations.
* **Inventory Orchestration:** Real-time tracking of 8 distinct blood groups. The system automatically flags inventory statuses as `Critical` (Red), `Low` (Yellow), or `Stable` (Green) based on SQL aggregate counts.

### 📲 2. Direct-Bridge Communication Suite
* **WhatsApp API Integration:** 💬 Automated generation of pre-filled message strings. When a unit is assigned, Admins can notify the recipient instantly via WhatsApp without saving contact numbers manually.
* **One-Tap Emergency VoIP:** 📞 Integrated `tel:` protocol bindings across the search interface to trigger immediate cellular calls to donors during critical ICU and trauma cases.

### 🧬 3. Automated Medical Eligibility Engine
* **The 90-Day Safety Protocol:** To adhere strictly to international WHO health standards, the backend enforces a 3-month recovery period for all donors.
* **Algorithmic Filtering:** The system dynamically calculates `(Current Date - Last Donation Date)`. If `< 90 days`, the donor is automatically flagged as **"Resting"** (Yellow Badge), and their contact buttons are disabled to protect their health.
* **Smart-Match Compatibility:** The search engine doesn't just look for exact matches. It understands cross-compatibility (e.g., matching an O- donor to an A- patient) and prioritizes exact matches first, followed by compatible matches.

### 🪪 4. Digital Identity & Gamification Ecosystem
* **Dynamic Hero ID Cards:** 🆔 Utilizes `html2canvas` and DOM Manipulation to dynamically render high-resolution, downloadable ID Cards with unique QR codes for rapid hospital verification.
* **Automated Recognition:** 🏆 The database tracks "Lives Impacted." Upon reaching donation milestones, the system auto-generates signed **Certificates of Appreciation** to incentivize and gamify community altruism.
* **Privacy Controls:** Donors retain full control over their data with a `hide_contact` toggle. If enabled, public users cannot see their phone number and must request access via an Administrator.

### 📊 5. Real-Time Analytics & Geo-Routing
* **AI-Driven Data Visualization:** Interactive JS-based charts track Supply vs. Demand curves, Regional Demographics, and Blood Group Saturation for predictive resource planning.
* **Geo-Spatial Campaign Engine:** Integrated Google Maps API to visualize upcoming blood drive locations and facilitate one-tap volunteer registration.
* **Live Emergency Feed:** A synchronized marquee ticker broadcasts `Urgent` requests (e.g., C-Sections, Accidents) to the homepage instantly, ensuring high visibility.

### 📱 6. Mobile-First Responsive Architecture
* **Fluid Grid System:** Built entirely on the **Bootstrap 5** framework, ensuring the UI is strictly agnostic to screen size. 
* **Adaptive Components:** Complex data tables in the Admin Panel intelligently transform into card-based layouts on mobile devices.
* its system database configuration has been updated now.
* **Accessibility:** Optimized for low-bandwidth 4G connections, ensuring critical performance during field emergencies.

---

## 💻 Technical Stack & Architecture

### Backend & Database
* **Core:** PHP 8.2 (Strict Object-Oriented Architecture)
* **Database:** MySQL (Relational Schema)
* **Security:** Prepared Statements (SQLi Protection), Bcrypt Password Hashing, CSRF Protection, and Session Hijacking defenses.
* **Data Integrity:** Foreign Key constraints to maintain relationships between `donors`, `blood_requests`, and `campaigns`.

### Frontend & APIs
* **UI/UX:** HTML5, CSS3, Bootstrap 5
* **JavaScript:** AJAX for asynchronous data loading, jQuery
* **Libraries:** `html2canvas` (Client-side rendering), Chart.js (Analytics)
* **Integrations:** WhatsApp Business API, Google Maps Platform
  

---

## ⚙️ Local Installation & Setup Instructions

To run this project locally on your machine, follow these steps:

1. **Clone the repository:**
   ```bash
   git clone [https://github.com/bilalifzal/BloodLink.git](https://github.com/bilalifzal/BloodLink.git)
   bloodlinkpro.kesug.com
