$ErrorActionPreference = 'Stop'
$projectRoot = Split-Path $PSScriptRoot -Parent
$phpExe = Join-Path $projectRoot '.runtime\php\php.exe'
if (!(Test-Path -LiteralPath $phpExe)) { $phpExe = (Get-Command php -ErrorAction Stop).Source }
$apps = @(@('tugas-1-login',8001),@('tugas-2-keranjang',8002),@('tugas-toko-online',8003))
foreach ($app in $apps) {
    $dir = Join-Path $projectRoot $app[0]
    $port = $app[1]
    $log = Join-Path $projectRoot ('.runtime\' + $app[0])
    Start-Process -FilePath $phpExe -ArgumentList '-S',"127.0.0.1:$port",'-t','public' -WorkingDirectory $dir -WindowStyle Hidden -RedirectStandardOutput "$log-out.log" -RedirectStandardError "$log-error.log"
    Write-Output "$($app[0]): http://127.0.0.1:$port"
}
$env:EXP_DB_PORT = '3307'
Start-Process -FilePath $phpExe -ArgumentList '-S','127.0.0.1:8004','-t','.' -WorkingDirectory $projectRoot -WindowStyle Hidden -RedirectStandardOutput (Join-Path $projectRoot '.runtime\experiments-out.log') -RedirectStandardError (Join-Path $projectRoot '.runtime\experiments-error.log')
Write-Output 'Eksperimen: http://127.0.0.1:8004/eksperimen/'
