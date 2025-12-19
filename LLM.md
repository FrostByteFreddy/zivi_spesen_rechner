Requirements Document: ZiviSpesen Self-Hosted (Drupal)

1. Project Overview & Architecture
   Goal: A self-hosted Drupal-based web app to automate "Zivildienst" expense calculations and management.

Environment: Standard Linux Server (e.g., Ubuntu/Debian).

Stack: Drupal 10/11 (PHP 8.3+), MySQL/PostgreSQL, and local file storage.

Constraint: No third-party API dependencies (e.g., no Vercel, no external PDF APIs, no S3). All processing and storage must happen on the local server.

2. Core Functional Requirements
   2.1 User Roles & Authentication
   Zivi (User): Custom profile fields for IBAN and Address. Can create "Monthly Expense" content.

Admin: Full access to review, approve, and export any Zivi's reports.

2.2 Expense Logic (Calculations)
The system must use these rates as base configuration:

Daily Allowance: Taschengeld (7.50 CHF), Morgenessen (4.00 CHF), Mittagessen (9.00 CHF), Nachtessen (7.00 CHF), OV/Sonstiges (6.80 CHF).

Logic: The app should calculate "Anrechenbare Tage" (Total days) from a Start/End date and multiply by the rates.

2.3 Expense Items & Receipts
Automatic Items: Generated based on the duration.

Manual Items: Zivis can add custom lines (e.g., "Einkauf gem. Quittung").

Receipts: File upload field for images/PDFs, stored in Drupal’s private:// file system on the Linux server for security.

3. Implementation Details for AntiGravity
   3.1 Content Modeling (Drupal Entities)
   Content Type: "Spesenabrechnung"

Fields: field_date_range (Start/End), field_zivi_ref (User reference), field_status (Draft/Submitted/Approved).

Paragraphs/Inline Entities: "Expense Line Item"

Fields: field_item_type (Breakfast, Lunch, etc.), field_rate, field_quantity, field_total_amount, field_receipt (File upload).

3.2 PDF Generation (Self-Hosted)
Module: Use the Entity Print module combined with the PDF Forge or dompdf library.

Requirement: PDF generation must be performed locally using PHP libraries. No external rendering services allowed.

3.3 Server Setup Requirements
Web Server: Apache 2.4 or Nginx.

Database: MySQL 8.0+ or MariaDB 10.6+.

PHP: Version 8.1 or 8.3 with extensions: PDO, XML, GD, OpenSSL, JSON, cURL, Mbstring, and zlib.
