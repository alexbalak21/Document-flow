# Full‑Stack Laravel CV Builder — Project Specification

This document describes the architecture, features, data model, workflow, and Markdown import/export system of the Laravel application that manages user accounts, multiple resumes, customizable CV templates, and structured CV content.

---

# 1. Project Overview

This application is a full‑stack Laravel platform where users can:

- Create an account  
- Create multiple resumes (CVs)  
- Customize each resume with:
  - Profile image (Base64 stored)
  - Language setting
  - Name/title
  - Multiple editable sections
- Edit sections using Markdown
- Manage structured entities:
  - Skills (with levels)
  - Languages (with levels)
  - Soft skills
  - Experience entries
  - Education entries
  - Certifications
- Choose icons for sections and skills
- Export CVs to PDF (HTML → PDF)
- Import/export full CVs as Markdown files
- Use multiple templates (future)
- Build custom templates (future)

The system behaves like a lightweight CMS dedicated to CV creation.

---

# 2. Core Concepts

## Users
Each user has:
- Email  
- Password  
- Profile settings (optional)  
- Multiple resumes  

## Resumes (CVs)
Each resume has:
- Name  
- Language  
- Profile image (Base64)  
- Template ID  
- Multiple sections  
- Structured entities:
  - Experience  
  - Education  
  - Certifications  
  - Skills  
  - Languages  
  - Soft skills  
- Metadata  

## Sections (Markdown-based)
Sections correspond to Markdown blocks:

- `# HEADER`  
- `# PROFILE`  
- `# CONTACT`  
- `# SKILLS`  
- `# EXPERIENCE`  
- `# EDUCATION`  
- `# CERTIFICATIONS`  
- `# LANGUAGES`  
- `# SOFT_SKILLS`  
- `# HOBBIES`

Each section contains:
- Title  
- Optional icon  
- Markdown content  
- Section type  

---

# 3. Structured Entities

## Skills
Fields:
- icon (optional)  
- name  
- level_type (beginner, intermediate, advanced, expert, percentage)  
- level_value (optional numeric)  

## Languages
Fields:
- name  
- level (basic, intermediate, advanced, fluent)  

## Soft Skills
Fields:
- name  
- icon (optional)  
- description (optional)  

## Experience
Fields:
- position_title  
- company  
- location (city, state, country)  
- start_date (Month‑Year)  
- end_date (Month‑Year or “Present”)  
- description (text)  

## Education
Fields:
- school  
- diploma  
- year  

## Certifications
Fields:
- name  
- organization  
- year (optional)  

---

# 4. Database Structure

## users
- id  
- name  
- email  
- password  
- timestamps  

## resumes
- id  
- user_id  
- name  
- language  
- profile_image_base64  
- template_id  
- timestamps  

## sections
- id  
- resume_id  
- title  
- icon  
- markdown_content  
- order_index  
- section_type  
- timestamps  

## skills
- id  
- resume_id  
- icon  
- name  
- level_type  
- level_value  
- timestamps  

## languages
- id  
- resume_id  
- name  
- level  
- timestamps  

## soft_skills
- id  
- resume_id  
- name  
- icon  
- description  
- timestamps  

## experiences
- id  
- resume_id  
- position_title  
- company  
- location  
- start_date  
- end_date  
- description  
- timestamps  

## educations
- id  
- resume_id  
- school  
- diploma  
- year  
- timestamps  

## certifications
- id  
- resume_id  
- name  
- organization  
- year  
- timestamps  

---

# 5. Application Pages

## 5.1 Dashboard
- List resumes  
- Create resume  
- Edit resume  
- Delete resume  

## 5.2 Resume Editor
Sections:
1. Profile Image  
2. Header  
3. Profile  
4. Contact  
5. Skills  
6. Experience  
7. Education  
8. Certifications  
9. Languages  
10. Soft Skills  
11. Hobbies  

## 5.3 Template Preview
- Render CV  
- Live preview  
- Export PDF  

---

# 6. Markdown Editing

Markdown is used for:
- Header  
- Profile  
- Contact  
- Hobbies  
- Soft Skills (optional)  
- Section titles  

Structured entities (Experience, Education, Certifications, Skills, Languages) use database models.

---

# 7. Template System

## Phase 1
- Single template  
- Dynamic section injection  
- Base64 profile image  

## Phase 2
- Multiple templates  
- Stored in `/resources/views/templates/`  

## Phase 3
- Template builder  
- Drag‑and‑drop layout  
- Custom colors  
- Custom fonts  

---

# 8. PDF Generation

### Option A: Puppeteer (recommended)
- Perfect CSS rendering  
- Supports gradients, flexbox, pseudo‑elements  

### Option B: Snappy (wkhtmltopdf)
- Faster  
- Less CSS support  

---

# 9. API Endpoints

## Auth
- register  
- login  
- logout  

## Resumes
- CRUD  

## Sections
- CRUD  

## Skills
- CRUD  

## Languages
- CRUD  

## Soft Skills
- CRUD  

## Experience
- CRUD  

## Education
- CRUD  

## Certifications
- CRUD  

## Markdown Import/Export
- export-md  
- import-md  

---

# 10. Markdown Import & Export System

## Experience
```
# EXPERIENCE
## Entry
position_title: Full‑Stack Developer
company: Novocib
location: Lyon, France
start_date: Jul 2025
end_date: Feb 2026
description: |
  - Improved SCO
  - Built internal tools
  - React + Tailwind development
```

## Soft Skills
```
# SOFT_SKILLS
## SoftSkill
name: Communication
icon: comments
description: Able to explain complex topics clearly.

## SoftSkill
name: Problem‑Solving
```

## Education
```
# EDUCATION
## Education
school: CMFP (AFPA)
diploma: Bac+2 — Développeur Web Full‑Stack
year: 2026

## Education
school: Armée de Terre, BA721
diploma: Licence Aéronautique
year: 2012
```

## Certifications
```
# CERTIFICATIONS
## Certification
name: IBM Java Developer
organization: IBM
year: 2025

## Certification
name: Meta React Developer
organization: Meta
year: 2024

## Certification
name: Microsoft Python Developer
organization: Microsoft
```

---

# 11. Future Features

- Template builder  
- Drag‑and‑drop ordering  
- AI CV assistant  
- LinkedIn import  
- DOCX export  
- Public CV link  

---

# 12. Summary

This Laravel application is a modular CV builder with:

- User accounts  
- Multiple resumes  
- Structured experience  
- Structured education  
- Structured certifications  
- Skills  
- Languages  
- Soft skills  
- Markdown sections  
- Base64 images  
- Template rendering  
- PDF export  
- Markdown import/export  

It is designed to evolve into a complete CV‑building platform with customizable templates and advanced editing tools.
