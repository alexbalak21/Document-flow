<?php

namespace App\Services;

use App\Models\Resume;
use App\Models\Experience;
use App\Models\Education;
use App\Models\Certification;
use App\Models\Skill;
use App\Models\Language;
use App\Models\SoftSkill;
use App\Models\Section;

class MarkdownService
{
    // ----------------------------------------------------------------
    // EXPORT
    // ----------------------------------------------------------------
    public function export(Resume $resume): string
    {
        $resume->load(['sections', 'experiences', 'educations', 'certifications', 'skills', 'languages', 'softSkills']);

        $lines = [];

        // META block
        $lines[] = '---';
        $lines[] = 'name: ' . $resume->name;
        $lines[] = 'language: ' . $resume->language;
        $lines[] = '---';
        $lines[] = '';

        // Markdown sections (HEADER, PROFILE, CONTACT, HOBBIES, …)
        foreach ($resume->sections as $section) {
            $lines[] = '# ' . strtoupper($section->section_type ?? $section->title);
            $lines[] = 'title: ' . $section->title;
            if ($section->icon) {
                $lines[] = 'icon: ' . $section->icon;
            }
            $lines[] = '';
            $lines[] = $section->markdown_content ?? '';
            $lines[] = '';
        }

        // SKILLS
        if ($resume->skills->count()) {
            $lines[] = '# SKILLS';
            foreach ($resume->skills as $skill) {
                $lines[] = '## Skill';
                $lines[] = 'name: ' . $skill->name;
                if ($skill->icon)        $lines[] = 'icon: ' . $skill->icon;
                $lines[] = 'level_type: ' . $skill->level_type;
                if (!is_null($skill->level_value)) $lines[] = 'level_value: ' . $skill->level_value;
                $lines[] = '';
            }
        }

        // LANGUAGES
        if ($resume->languages->count()) {
            $lines[] = '# LANGUAGES';
            foreach ($resume->languages as $lang) {
                $lines[] = '## Language';
                $lines[] = 'name: ' . $lang->name;
                $lines[] = 'level: ' . $lang->level;
                $lines[] = '';
            }
        }

        // SOFT SKILLS
        if ($resume->softSkills->count()) {
            $lines[] = '# SOFT_SKILLS';
            foreach ($resume->softSkills as $ss) {
                $lines[] = '## SoftSkill';
                $lines[] = 'name: ' . $ss->name;
                if ($ss->icon)        $lines[] = 'icon: ' . $ss->icon;
                if ($ss->description) $lines[] = 'description: ' . $ss->description;
                $lines[] = '';
            }
        }

        // EXPERIENCE
        if ($resume->experiences->count()) {
            $lines[] = '# EXPERIENCE';
            foreach ($resume->experiences as $exp) {
                $lines[] = '## Entry';
                $lines[] = 'position_title: ' . $exp->position_title;
                $lines[] = 'company: ' . $exp->company;
                if ($exp->location)  $lines[] = 'location: ' . $exp->location;
                $lines[] = 'start_date: ' . $exp->start_date;
                if ($exp->end_date)  $lines[] = 'end_date: ' . $exp->end_date;
                if ($exp->description) {
                    $lines[] = 'description: |';
                    foreach (explode("\n", $exp->description) as $dl) {
                        $lines[] = '  ' . $dl;
                    }
                }
                $lines[] = '';
            }
        }

        // EDUCATION
        if ($resume->educations->count()) {
            $lines[] = '# EDUCATION';
            foreach ($resume->educations as $edu) {
                $lines[] = '## Education';
                $lines[] = 'school: ' . $edu->school;
                $lines[] = 'diploma: ' . $edu->diploma;
                $lines[] = 'year: ' . $edu->year;
                $lines[] = '';
            }
        }

        // CERTIFICATIONS
        if ($resume->certifications->count()) {
            $lines[] = '# CERTIFICATIONS';
            foreach ($resume->certifications as $cert) {
                $lines[] = '## Certification';
                $lines[] = 'name: ' . $cert->name;
                $lines[] = 'organization: ' . $cert->organization;
                if ($cert->year) $lines[] = 'year: ' . $cert->year;
                $lines[] = '';
            }
        }

        return implode("\n", $lines);
    }

    // ----------------------------------------------------------------
    // IMPORT
    // ----------------------------------------------------------------
    public function import(string $markdown, int $userId): Resume
    {
        $lines = explode("\n", str_replace("\r\n", "\n", $markdown));

        // Parse META block (--- ... ---)
        $meta = [];
        $startIdx = 0;
        if (trim($lines[0]) === '---') {
            for ($i = 1; $i < count($lines); $i++) {
                if (trim($lines[$i]) === '---') { $startIdx = $i + 1; break; }
                if (preg_match('/^(\w+):\s*(.+)$/', $lines[$i], $m)) {
                    $meta[$m[1]] = trim($m[2]);
                }
            }
        }

        $resume = Resume::create([
            'user_id'  => $userId,
            'name'     => $meta['name'] ?? 'Imported CV',
            'language' => $meta['language'] ?? 'fr',
        ]);

        // Split remaining content into # SECTION blocks
        $remaining = implode("\n", array_slice($lines, $startIdx));
        $chunks    = preg_split('/^# ([A-Z_]+)$/m', $remaining, -1, PREG_SPLIT_DELIM_CAPTURE);

        for ($i = 1; $i < count($chunks); $i += 2) {
            $sectionKey  = trim($chunks[$i]);
            $sectionBody = trim($chunks[$i + 1]);

            match ($sectionKey) {
                'SKILLS'      => $this->importSkills($resume, $sectionBody),
                'LANGUAGES'   => $this->importLanguages($resume, $sectionBody),
                'SOFT_SKILLS' => $this->importSoftSkills($resume, $sectionBody),
                'EXPERIENCE'  => $this->importExperiences($resume, $sectionBody),
                'EDUCATION'   => $this->importEducations($resume, $sectionBody),
                'CERTIFICATIONS' => $this->importCertifications($resume, $sectionBody),
                default       => $this->importSection($resume, $sectionKey, $sectionBody),
            };
        }

        return $resume;
    }

    // ----------------------------------------------------------------
    // Import helpers
    // ----------------------------------------------------------------

    /** Parse "## BlockName\nkey: value\n..." blocks into array of assoc arrays */
    private function parseBlocks(string $body): array
    {
        $rawBlocks = preg_split('/^## .+$/m', $body, -1, PREG_SPLIT_NO_EMPTY);
        $blocks = [];
        foreach ($rawBlocks as $block) {
            $block = trim($block);
            if ($block === '') continue;
            $entry = [];
            $descLines = [];
            $inDesc = false;
            foreach (explode("\n", $block) as $line) {
                if ($inDesc) {
                    $descLines[] = ltrim($line, ' ');
                    continue;
                }
                if (preg_match('/^description:\s*\|$/', $line)) {
                    $inDesc = true;
                    continue;
                }
                if (preg_match('/^(\w+):\s*(.*)$/', $line, $m)) {
                    $entry[$m[1]] = trim($m[2]);
                }
            }
            if ($inDesc) $entry['description'] = implode("\n", $descLines);
            $blocks[] = $entry;
        }
        return $blocks;
    }

    private function importSkills(Resume $resume, string $body): void
    {
        foreach ($this->parseBlocks($body) as $b) {
            if (empty($b['name'])) continue;
            $resume->skills()->create([
                'name'        => $b['name'],
                'icon'        => $b['icon'] ?? null,
                'level_type'  => $b['level_type'] ?? 'intermediate',
                'level_value' => isset($b['level_value']) ? (int)$b['level_value'] : null,
            ]);
        }
    }

    private function importLanguages(Resume $resume, string $body): void
    {
        foreach ($this->parseBlocks($body) as $b) {
            if (empty($b['name'])) continue;
            $resume->languages()->create([
                'name'  => $b['name'],
                'level' => $b['level'] ?? 'intermediate',
            ]);
        }
    }

    private function importSoftSkills(Resume $resume, string $body): void
    {
        foreach ($this->parseBlocks($body) as $b) {
            if (empty($b['name'])) continue;
            $resume->softSkills()->create([
                'name'        => $b['name'],
                'icon'        => $b['icon'] ?? null,
                'description' => $b['description'] ?? null,
            ]);
        }
    }

    private function importExperiences(Resume $resume, string $body): void
    {
        foreach ($this->parseBlocks($body) as $b) {
            if (empty($b['position_title'])) continue;
            $resume->experiences()->create([
                'position_title' => $b['position_title'],
                'company'        => $b['company'] ?? '',
                'location'       => $b['location'] ?? null,
                'start_date'     => $b['start_date'] ?? '',
                'end_date'       => $b['end_date'] ?? null,
                'description'    => $b['description'] ?? null,
            ]);
        }
    }

    private function importEducations(Resume $resume, string $body): void
    {
        foreach ($this->parseBlocks($body) as $b) {
            if (empty($b['school'])) continue;
            $resume->educations()->create([
                'school'  => $b['school'],
                'diploma' => $b['diploma'] ?? '',
                'year'    => $b['year'] ?? '',
            ]);
        }
    }

    private function importCertifications(Resume $resume, string $body): void
    {
        foreach ($this->parseBlocks($body) as $b) {
            if (empty($b['name'])) continue;
            $resume->certifications()->create([
                'name'         => $b['name'],
                'organization' => $b['organization'] ?? '',
                'year'         => $b['year'] ?? null,
            ]);
        }
    }

    private function importSection(Resume $resume, string $key, string $body): void
    {
        // Extract optional "title:" and "icon:" meta lines
        $title   = ucfirst(strtolower($key));
        $icon    = null;
        $content = [];
        foreach (explode("\n", $body) as $line) {
            if (preg_match('/^title:\s*(.+)$/', $line, $m))      { $title = trim($m[1]); continue; }
            if (preg_match('/^icon:\s*(.+)$/', $line, $m))       { $icon  = trim($m[1]); continue; }
            $content[] = $line;
        }
        $resume->sections()->create([
            'title'            => $title,
            'icon'             => $icon,
            'markdown_content' => trim(implode("\n", $content)),
            'section_type'     => strtolower($key),
            'order_index'      => $resume->sections()->count(),
        ]);
    }
}
