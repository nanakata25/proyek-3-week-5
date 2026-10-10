$ErrorActionPreference = 'Stop'
$projectRoot = Split-Path $PSScriptRoot -Parent
$phpExe = Join-Path $projectRoot '.runtime\php\php.exe'
if (!(Test-Path -LiteralPath $phpExe)) { $phpExe = (Get-Command php -ErrorAction Stop).Source }
$runtimeDir = Join-Path $projectRoot '.runtime'
$ports = @(8001, 8002, 8003, 8004)
$occupiedPorts = @(Get-NetTCPConnection -State Listen -ErrorAction Stop |
    Where-Object { $_.LocalPort -in $ports } |
    Select-Object -ExpandProperty LocalPort -Unique)
if ($occupiedPorts.Count -gt 0) {
    throw "Server belum dijalankan. Port berikut sudah digunakan: $($occupiedPorts -join ', '). Hentikan server sebelumnya atau jalankan aplikasi pada port lain."
}
New-Item -ItemType Directory -Path $runtimeDir -Force | Out-Null
$apps = @(@('tugas-1-login',8001),@('tugas-2-keranjang',8002),@('tugas-toko-online',8003))
foreach ($app in $apps) {
    $dir = Join-Path $projectRoot $app[0]
    $port = $app[1]
    $log = Join-Path $projectRoot ('.runtime\' + $app[0])
    Start-Process -FilePath $phpExe -ArgumentList '-S',"127.0.0.1:$port",'-t','public' -WorkingDirectory $dir -WindowStyle Hidden -RedirectStandardOutput "$log-out.log" -RedirectStandardError "$log-error.log"
    Write-Output "$($app[0]): http://127.0.0.1:$port"
}
if (!$env:EXP_DB_PORT) { $env:EXP_DB_PORT = '3307' }
Start-Process -FilePath $phpExe -ArgumentList '-S','127.0.0.1:8004','-t','.' -WorkingDirectory $projectRoot -WindowStyle Hidden -RedirectStandardOutput (Join-Path $projectRoot '.runtime\experiments-out.log') -RedirectStandardError (Join-Path $projectRoot '.runtime\experiments-error.log')
Write-Output 'Eksperimen: http://127.0.0.1:8004/eksperimen/'
