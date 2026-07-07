**Table of Contents**

-   [1. Introduction](#1-introduction)
    -   [1.1 Purpose](#11-purpose)
    -   [1.2 Scope](#12-scope)
    -   [1.3 Definitions, Acronyms, and Abbreviations](#13-definitions-acronyms-and-abbreviations)
    -   [1.4 References](#14-references)
    -   [1.5 Overview](#15-overview)
-   [2. Overall Description](#2-overall-description)
    -   [2.1 Product Perspective](#21-product-perspective)
    -   [2.2 Product Functions](#22-product-functions)
    -   [2.3 User Characteristics](#23-user-characteristics)
    -   [2.4 Constraints](#24-constraints)
    -   [2.5 Assumptions and Dependencies](#25-assumptions-and-dependencies)
-   [3. Specific Requirements](#3-specific-requirements)
    -   [3.1 Functional Requirements](#31-functional-requirements)
        -   [3.1.1 User Management](#311-user-management)
        -   [3.1.2 Partner and Employee Management](#312-partner-and-employee-management)
        -   [3.1.3 Activity and Honor Management](#313-activity-and-honor-management)
        -   [3.1.4 Document Generation (SPK & BAST)](#314-document-generation-spk--bast)
        -   [3.1.5 PPK Management](#315-ppk-management)
        -   [3.1.6 Monitoring Pembayaran](#316-monitoring-pembayaran)
        -   [3.1.7 Microsite Management](#317-microsite-management)
        -   [3.1.8 Reporting and Analytics](#318-reporting-and-analytics)
    -   [3.2 Non-Functional Requirements](#32-non-functional-requirements)
        -   [3.2.1 Performance](#321-performance)
        -   [3.2.2 Security](#322-security)
        -   [3.2.3 Usability](#323-usability)
        -   [3.2.4 Reliability](#324-reliability)
-   [4. Conclusion](#4-conclusion)

# System Requirements Specification (SRS) for Sikendis

> **Business Rules Reference:** For detailed date calculation and document rules, see [docs/BUSINESS_RULES.md](docs/BUSINESS_RULES.md).

## 1. Introduction

### 1.1 Purpose

This document provides the System Requirements Specification (SRS) for Sikendis (Sistem Keuangan dan Kendali Administrasi Statistik), a web-based administrative system for BPS Kabupaten Mempawah. It defines functional and non-functional requirements for all stakeholders.

### 1.2 Scope

Sikendis manages the full administrative lifecycle of statistical survey activities (kegiatan manmit), from partner assignment and contract generation (SPK) through to work handover documentation (BAST) and payment tracking (Monitoring Pembayaran).

### 1.3 Definitions, Acronyms, and Abbreviations

-   **SRS**: System Requirements Specification
-   **Sikendis**: Sistem Keuangan dan Kendali Administrasi Statistik
-   **BPS**: Badan Pusat Statistik (Statistics Indonesia)
-   **SPK**: Surat Perjanjian Kerja — work contract between BPS and a partner
-   **BAST**: Berita Acara Serah Terima — work handover certificate signed upon activity completion
-   **PPK**: Pejabat Pembuat Komitmen — the authorized BPS official who signs contracts/BAST
-   **Mitra**: Partner/enumerator contracted to perform survey field work
-   **Honor**: Remuneration type and amount defined per activity and role
-   **Alokasi Honor**: Assignment of a specific mitra to a specific honor, generating an SPK and BAST
-   **Kegiatan Manmit**: A statistical survey activity with defined periods and roles
-   **tanggal_akhir_kegiatan**: The single source of truth for contract month and document dates (see BUSINESS_RULES.md §1)
-   **Monitoring Pembayaran**: Payment tracking module for submitted documents
-   **DomPDF**: PHP library for PDF document generation
-   **Filament**: PHP framework for building the admin panel

### 1.4 References

-   [Business Rules — Kontrak & Dokumen](docs/BUSINESS_RULES.md)
-   Laravel Official Documentation
-   Filament PHP Documentation

### 1.5 Overview

This SRS is organized into Overall Description (high-level) and Specific Requirements (detailed functional and non-functional). For implementation details and date calculation rules, refer to the Business Rules document.

---

## 2. Overall Description

### 2.1 Product Perspective

Sikendis is a self-contained, web-based system built on Laravel + Filament. It generates official PDF documents (SPK, BAST) using DomPDF, with all date and PPK logic derived dynamically from the database — eliminating hardcoded values.

### 2.2 Product Functions

-   **Partner & Employee Management**: Manage mitra profiles, eligibility, and assignment history
-   **Activity Management**: Define kegiatan manmit with schedules, honor structures, and mitra assignments
-   **Contract Generation (SPK)**: Auto-generate SPK PDFs with correct period, PPK, and partner details
-   **Work Handover (BAST)**: Auto-generate BAST PDFs with correct PPK based on actual document date
-   **PPK Management**: Dynamic PPK selection based on historical records, no hardcoding
-   **Monitoring Pembayaran**: Track document submission and payment status
-   **Microsite Management**: Lightweight event landing pages
-   **Reporting & Analytics**: Dashboards and activity reports

### 2.3 User Characteristics

-   **Administrators**: Full access — manage all data, generate documents, configure PPK history
-   **Operators**: Manage specific modules — input activity data, assign mitras, submit documents
-   **Viewers**: Read-only access to reports and status

### 2.4 Constraints

-   PHP 8.2+ required; MySQL/MariaDB for database
-   Document generation depends on DomPDF; complex layouts may require manual template adjustments
-   PPK transitions must be recorded in `riwayat_ppks` before documents for affected periods are generated

### 2.5 Assumptions and Dependencies

-   `honor.tanggal_akhir_kegiatan` is always set correctly by the PJK (activity coordinator) before allocations are created
-   `kegiatan_manmit.tgl_mulai_pelaksanaan` and `tgl_akhir_pelaksanaan` must cover the entire activity range (all phases: field work, data entry, processing) so that honor dates fall within this window
-   Holiday calendar is maintained in the application for workday calculations

---

## 3. Specific Requirements

### 3.1 Functional Requirements

#### 3.1.1 User Management

-   **FR1.1**: Administrators can create, view, update, and delete user accounts with role-based access control.
-   **FR1.2**: Each user requires a unique username and valid email address with strong password enforcement.

#### 3.1.2 Partner and Employee Management

-   **FR2.1**: The system manages mitra information including personal details, ID (ID Sobat), kecamatan, and desa.
-   **FR2.2**: The system enforces mitra eligibility rules, including schedule conflict detection across concurrent alokasi honors.
-   **FR2.3**: Administrators can view assignment and payment history per mitra.

#### 3.1.3 Activity and Honor Management

-   **FR3.1**: Administrators define kegiatan manmit with a name, type (SURVEI/SENSUS), frequency, and execution schedule.

-   **FR3.2 — Jadwal Pelaksanaan:** The execution schedule (`tgl_mulai_pelaksanaan` to `tgl_akhir_pelaksanaan`) **must cover the entire activity period** including all phases (field work, data entry, processing). All `honor.tanggal_akhir_kegiatan` values for this activity must fall within this window. The system warns if a honor's date falls outside this range.

-   **FR3.3**: Each kegiatan manmit has one or more honor records defining: role (jabatan), honor type, unit, price per unit, and `tanggal_akhir_kegiatan`.

-   **FR3.4 — Single Source of Truth:** `honor.tanggal_akhir_kegiatan` is the canonical field determining:
    - The contract month (1st to last day of that month)
    - The PPK who signs the documents
    - The SPK and BAST document dates
    - The payment deadline (+20 days)
    > `kegiatan_manmit.tgl_mulai/akhir_pelaksanaan` is **not** used for contract or BAST date calculations.

-   **FR3.5**: Administrators create alokasi honors (via import or manual entry) assigning mitras to honors with target quantities.

-   **FR3.6 — Multi-Month Activities:** A single kegiatan manmit may have honors with `tanggal_akhir_kegiatan` in different months. Each month is handled independently with its own SPK set and BAST set.

#### 3.1.4 Document Generation (SPK & BAST)

-   **FR4.1 — SPK Generation:**
    - Contract period: `startOfMonth(tanggal_akhir_kegiatan)` to `endOfMonth(tanggal_akhir_kegiatan)`
    - SPK `tanggal_nomor`: last working day before contract start (previous month)
    - PPK: determined by `getPpkByDate(tanggal_nomor_spk)` from `riwayat_ppks`
    - One nomor SPK is shared across all kegiatan a mitra has in the same month

-   **FR4.2 — BAST Generation:**
    - `tanggal_nomor` BAST: last working day on or before `honor.tanggal_akhir_kegiatan`
    - `tanggal_nomor` BAST must **not exceed** `tanggal_akhir_kegiatan`
    - PPK: determined by `getPpkByDate(alokasi.bast.tanggal_nomor)` — NOT from URL parameters
    - BAST filter by month uses `honor.tanggal_akhir_kegiatan`, not `kegiatanManmit.tgl_akhir_pelaksanaan`

-   **FR4.3 — Cascading Date Updates:** Changing `honor.tanggal_akhir_kegiatan` automatically propagates to all dependent `alokasi_honors` and `nomor_surats` via `HonorObserver`. The UI warns users about the number of affected allocations before saving.

-   **FR4.4**: Document templates (SPK, BAST) are pre-defined Blade views. Sensus contracts support custom pasal content via RichEditor.

#### 3.1.5 PPK Management

-   **FR5.1**: PPK assignments are stored in `riwayat_ppks` with NIP, name, `tgl_mulai`, and `tgl_selesai`.
-   **FR5.2**: The system resolves the active PPK for any given date by querying `riwayat_ppks` — no hardcoding allowed.
-   **FR5.3**: Administrators manage PPK history via the **Pengaturan → Riwayat PPK** UI. Adding a new row activates the new PPK from `tgl_mulai` onwards.

#### 3.1.6 Monitoring Pembayaran

-   **FR6.1**: The system tracks document submission status from creation through approval and payment.
-   **FR6.2**: Users can filter by activity, month, and mitra.
-   **FR6.3**: Automated notifications are sent for incomplete or pending documents.

#### 3.1.7 Microsite Management

-   **FR7.1**: Administrators create simple one-page event websites with pre-designed templates.

#### 3.1.8 Reporting and Analytics

-   **FR8.1**: Dashboard showing key metrics: active mitras, pending BASTs, total honor by period.
-   **FR8.2**: Activity reports filterable by kegiatan, bulan, and jabatan.

---

### 3.2 Non-Functional Requirements

#### 3.2.1 Performance

-   **NFR1.1**: All pages load within 3 seconds under normal conditions.
-   **NFR1.2**: The system handles at least 100 concurrent users without significant degradation.
-   **NFR1.3**: API response time under 500ms for standard requests.

#### 3.2.2 Security

-   **NFR2.1**: Sensitive data is encrypted in transit (HTTPS) and at rest.
-   **NFR2.2**: Protection against SQL injection, XSS, and CSRF via Laravel's built-in mechanisms.
-   **NFR2.3**: Role-based access control prevents unauthorized access to administrative functions.

#### 3.2.3 Usability

-   **NFR3.1**: The UI provides live warnings and context-aware helper text at all data entry points.
-   **NFR3.2**: Multi-month activities display per-month action buttons (Cetak Kontrak, Cetak BAST) for clarity.
-   **NFR3.3**: Date fields show hints about downstream effects (e.g., how many allocations will be updated).
-   **NFR3.4**: The system is responsive on desktop and tablet devices.

#### 3.2.4 Reliability

-   **NFR4.1**: System uptime of at least 99.9%.
-   **NFR4.2**: All cascading date updates execute within a single database transaction to prevent partial updates.
-   **NFR4.3**: A bulk-fix command (`honor:fix-tanggal`) is available to correct historical data inconsistencies.

---

## 4. Conclusion

This SRS reflects the current implementation of Sikendis. All date and document rules are governed by `honor.tanggal_akhir_kegiatan` as the single source of truth, with PPK resolved dynamically from `riwayat_ppks`. For detailed calculation rules and code references, see [docs/BUSINESS_RULES.md](docs/BUSINESS_RULES.md).

*Last updated: 2026-07-07*
