# ============================================================================
# God File Detector Script
# ============================================================================
# Deskripsi: Mendeteksi file dengan lebih dari N baris kode (god files)
#            untuk target refactoring
# 
# Penggunaan:
#   .\detect-god-files.ps1                    # Default: >400 baris
#   .\detect-god-files.ps1 -Threshold 300    # Custom threshold
#   .\detect-god-files.ps1 -OutputCsv        # Export ke CSV
# ============================================================================

param(
    [int]$Threshold = 400,
    [string]$ProjectPath = (Split-Path -Parent $PSScriptRoot),
    [switch]$OutputCsv,
    [string]$CsvPath = "god-files-report.csv"
)

# Warna untuk output
$colors = @{
    Header  = "Cyan"
    Warning = "Yellow"
    Error   = "Red"
    Success = "Green"
    Info    = "White"
}

# Folder yang di-exclude
$excludeFolders = @(
    "vendor",
    "node_modules",
    ".git",
    "storage",
    ".idea",
    ".vscode",
    "bootstrap\cache"
)

# Ekstensi file yang dicek (blade.php sudah termasuk dalam *.php)
$includeExtensions = @(
    "*.php",
    "*.js",
    "*.ts",
    "*.vue",
    "*.jsx",
    "*.tsx",
    "*.css",
    "*.scss"
)

function Write-Header {
    param([string]$Text)
    Write-Host ""
    Write-Host ("=" * 70) -ForegroundColor $colors.Header
    Write-Host " $Text" -ForegroundColor $colors.Header
    Write-Host ("=" * 70) -ForegroundColor $colors.Header
    Write-Host ""
}

function Write-Summary {
    param(
        [int]$TotalFiles,
        [int]$GodFiles,
        [int]$TotalLines,
        [int]$Threshold
    )
    
    Write-Host ""
    Write-Host ("=" * 70) -ForegroundColor $colors.Header
    Write-Host " RINGKASAN" -ForegroundColor $colors.Header
    Write-Host ("=" * 70) -ForegroundColor $colors.Header
    Write-Host ""
    Write-Host "  Total file yang dipindai  : " -NoNewline -ForegroundColor $colors.Info
    Write-Host "$TotalFiles" -ForegroundColor $colors.Success
    Write-Host "  God files ditemukan       : " -NoNewline -ForegroundColor $colors.Info
    Write-Host "$GodFiles" -ForegroundColor $(if ($GodFiles -gt 0) { $colors.Warning } else { $colors.Success })
    Write-Host "  Threshold                 : " -NoNewline -ForegroundColor $colors.Info
    Write-Host ">$Threshold baris" -ForegroundColor $colors.Info
    Write-Host "  Total baris (god files)   : " -NoNewline -ForegroundColor $colors.Info
    Write-Host "$TotalLines" -ForegroundColor $colors.Warning
    Write-Host ""
}

function Get-LineCount {
    param([string]$FilePath)
    try {
        $content = Get-Content -Path $FilePath -ErrorAction Stop
        return ($content | Measure-Object -Line).Lines
    }
    catch {
        return -1
    }
}

function Get-CategoryName {
    param([string]$FilePath)
    
    $relativePath = $FilePath.Replace($ProjectPath, "").TrimStart("\", "/")
    
    if ($relativePath -match "^app\\Filament") { return "Filament Resource" }
    if ($relativePath -match "^app\\Http\\Controllers") { return "Controller" }
    if ($relativePath -match "^app\\Models") { return "Model" }
    if ($relativePath -match "^app\\Services") { return "Service" }
    if ($relativePath -match "^app\\Exports") { return "Export" }
    if ($relativePath -match "^app\\Imports") { return "Import" }
    if ($relativePath -match "^database\\seeders") { return "Seeder" }
    if ($relativePath -match "^database\\migrations") { return "Migration" }
    if ($relativePath -match "^resources\\views") { return "View" }
    if ($relativePath -match "\.blade\.php$") { return "Blade Template" }
    if ($relativePath -match "\.vue$") { return "Vue Component" }
    if ($relativePath -match "\.js$") { return "JavaScript" }
    if ($relativePath -match "\.ts$") { return "TypeScript" }
    
    return "Other"
}

# Main Script
Write-Header "GOD FILE DETECTOR - Project Sikendis"

Write-Host "  Project Path : $ProjectPath" -ForegroundColor $colors.Info
Write-Host "  Threshold    : >$Threshold baris" -ForegroundColor $colors.Info
Write-Host "  Excluding    : $($excludeFolders -join ', ')" -ForegroundColor $colors.Info
Write-Host ""
Write-Host "  Memindai file..." -ForegroundColor $colors.Info

# Build exclude pattern
$excludePattern = $excludeFolders | ForEach-Object { "*\$_\*" }

# Get all files
$allFiles = @()
foreach ($ext in $includeExtensions) {
    $files = Get-ChildItem -Path $ProjectPath -Recurse -Filter $ext -File -ErrorAction SilentlyContinue |
        Where-Object { 
            $filePath = $_.FullName
            $exclude = $false
            foreach ($pattern in $excludePattern) {
                if ($filePath -like $pattern) {
                    $exclude = $true
                    break
                }
            }
            -not $exclude
        }
    $allFiles += $files
}

# Remove duplicates by FullName
$allFiles = $allFiles | Sort-Object -Property FullName -Unique

$totalScanned = 0
$godFiles = @()

foreach ($file in $allFiles) {
    $totalScanned++
    $lineCount = Get-LineCount -FilePath $file.FullName
    
    if ($lineCount -gt $Threshold) {
        $relativePath = $file.FullName.Replace($ProjectPath, "").TrimStart("\", "/")
        $category = Get-CategoryName -FilePath $file.FullName
        
        $godFiles += [PSCustomObject]@{
            Path      = $relativePath
            Lines     = $lineCount
            Category  = $category
            FullPath  = $file.FullName
            Extension = $file.Extension
        }
    }
}

# Sort by line count descending
$godFiles = $godFiles | Sort-Object -Property Lines -Descending

# Display results
if ($godFiles.Count -gt 0) {
    Write-Host ""
    Write-Host " GOD FILES DITEMUKAN (>$Threshold baris):" -ForegroundColor $colors.Warning
    Write-Host ("-" * 70) -ForegroundColor $colors.Info
    Write-Host ""
    
    # Table header
    $format = "{0,-50} {1,8} {2,-15}"
    Write-Host ($format -f "FILE", "BARIS", "KATEGORI") -ForegroundColor $colors.Header
    Write-Host ($format -f ("-" * 50), ("-" * 8), ("-" * 15)) -ForegroundColor $colors.Info
    
    foreach ($file in $godFiles) {
        $displayPath = $file.Path
        if ($displayPath.Length -gt 48) {
            $displayPath = "..." + $displayPath.Substring($displayPath.Length - 45)
        }
        
        $severityColor = switch ($file.Lines) {
            { $_ -gt 1000 } { $colors.Error; break }
            { $_ -gt 600 }  { $colors.Warning; break }
            default         { $colors.Info }
        }
        
        Write-Host ($format -f $displayPath, $file.Lines, $file.Category) -ForegroundColor $severityColor
    }
    
    # Group by category
    Write-Host ""
    Write-Host " RINGKASAN PER KATEGORI:" -ForegroundColor $colors.Header
    Write-Host ("-" * 40) -ForegroundColor $colors.Info
    
    $godFiles | Group-Object -Property Category | Sort-Object -Property Count -Descending | ForEach-Object {
        $totalLines = ($_.Group | Measure-Object -Property Lines -Sum).Sum
        Write-Host "  $($_.Name): $($_.Count) file(s), $totalLines baris total" -ForegroundColor $colors.Info
    }
}
else {
    Write-Host ""
    Write-Host " Tidak ada god files ditemukan! Kode sudah bersih." -ForegroundColor $colors.Success
}

# Calculate totals
$totalGodFileLines = ($godFiles | Measure-Object -Property Lines -Sum).Sum
if ($null -eq $totalGodFileLines) { $totalGodFileLines = 0 }

Write-Summary -TotalFiles $totalScanned -GodFiles $godFiles.Count -TotalLines $totalGodFileLines -Threshold $Threshold

# Export to CSV if requested
if ($OutputCsv) {
    $csvFullPath = Join-Path $ProjectPath $CsvPath
    $godFiles | Select-Object Path, Lines, Category | Export-Csv -Path $csvFullPath -NoTypeInformation -Encoding UTF8
    Write-Host " CSV exported ke: $csvFullPath" -ForegroundColor $colors.Success
    Write-Host ""
}

# Return data for further processing if needed
return $godFiles
