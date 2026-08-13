# Build ZIP archive with 7-Zip for Claude
7z a "document-flow.zip" `
"app\Http" `
"app\Models" `
"app\Services" `
"app\Providers" `
"database\migrations" `
"resources\views" `
"resources\css" `
"resources\js" `
"routes" `
"config" `
"vite.config.js" `
"package.json" `
"composer.json" `
"storage\app\templates" `
"update.md" `
"work_done.md" `
"tests" `
"phpunit.xml" `
".env"

Write-Host "Archive created: document-flow.zip"
