$ErrorActionPreference = 'Stop'
$testRoot = Join-Path (Split-Path (Split-Path $PSScriptRoot -Parent) -Parent) ('tmp\start-local-' + [guid]::NewGuid())
New-Item -ItemType Directory -Path (Join-Path $testRoot 'scripts') -Force | Out-Null
Copy-Item -LiteralPath (Join-Path (Split-Path $PSScriptRoot -Parent) 'start-local.ps1') -Destination (Join-Path $testRoot 'scripts\start-local.ps1')
$testStarts = [System.Collections.Generic.List[object]]::new()
$testOccupied = @()
$testOriginalPort = $env:EXP_DB_PORT

# Replace OS boundaries; no real server or network state is changed by this test.
function Get-Command { param($Name, $ErrorAction) [pscustomobject]@{ Source = 'test-php.exe' } }
function Get-NetTCPConnection { param($State, $ErrorAction) $testOccupied }
function Start-Process {
    param($FilePath, $ArgumentList, $WorkingDirectory, $WindowStyle, $RedirectStandardOutput, $RedirectStandardError)
    if (!(Test-Path -LiteralPath (Split-Path $RedirectStandardOutput -Parent))) { throw 'Log directory missing' }
    $testStarts.Add([pscustomobject]@{ Arguments = $ArgumentList; Window = $WindowStyle })
}

try {
    $env:EXP_DB_PORT = '3310'
    . (Join-Path $testRoot 'scripts\start-local.ps1') | Out-Null
    if ($testStarts.Count -ne 4) { throw 'Expected four server launches' }
    if ($testStarts.Where({ $_.Window -ne 'Hidden' }).Count) { throw 'Servers must launch hidden' }
    if ($env:EXP_DB_PORT -ne '3310') { throw 'Explicit database port was overwritten' }
    Write-Output 'PASS: fresh checkout creates logs and preserves configured database port'

    $testStarts.Clear()
    $testOccupied = @([pscustomobject]@{ LocalPort = 8002 })
    $testRejected = $false
    try { . (Join-Path $testRoot 'scripts\start-local.ps1') | Out-Null }
    catch { if ($_.Exception.Message -notlike '*8002*') { throw }; $testRejected = $true }
    if (!$testRejected -or $testStarts.Count -ne 0) { throw 'Port conflict must stop all launches' }
    Write-Output 'PASS: occupied port prevents partial startup'

    $testOccupied = @()
    $env:EXP_DB_PORT = $null
    . (Join-Path $testRoot 'scripts\start-local.ps1') | Out-Null
    if ($env:EXP_DB_PORT -ne '3307') { throw 'Missing default database port' }
    Write-Output 'PASS: absent database port uses local demonstration default'
} finally {
    $env:EXP_DB_PORT = $testOriginalPort
}
