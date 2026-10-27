[CmdletBinding()]
param()

$secret = "sikap_deploy_sec_2026_unri"
$url = "https://sikap.fkp.unri.ac.id/deploy-webhook"

try {
    $headers = @{
        "X-Deploy-Key" = $secret
        "Accept"       = "application/json"
    }
    $body = @{
        key = $secret
        mode = "fast"
        commits = 10
    }

    $resp = Invoke-RestMethod -Uri $url -Method POST -Body $body -Headers $headers -TimeoutSec 180

    if ($resp -and $resp.status -eq "success") {
        Write-Host ("[SERVER RESPONSE] " + $resp.message) -ForegroundColor Green
        if ($resp.logs -and $resp.logs.code_update) {
            Write-Host ("[DETAIL UPDATE]   " + $resp.logs.code_update) -ForegroundColor Cyan
        }
        if ($resp.logs -and $resp.logs.migrate) {
            Write-Host ("[MIGRATE]         " + $resp.logs.migrate) -ForegroundColor Gray
        }
        exit 0
    } else {
        $msg = if ($resp -and $resp.message) { $resp.message } else { "Unknown server response" }
        Write-Host ("[SERVER ERROR]    " + $msg) -ForegroundColor Red
        exit 1
    }
} catch {
    Write-Host ("[SERVER ERROR]    " + $_.Exception.Message) -ForegroundColor Red
    if ($_.Exception.Response) {
        try {
            $stream = $_.Exception.Response.GetResponseStream()
            $reader = New-Object System.IO.StreamReader($stream)
            $errBody = $reader.ReadToEnd()
            if ($errBody) {
                Write-Host ("[SERVER BODY]     " + $errBody) -ForegroundColor DarkRed
            }
        } catch {}
    }
    exit 1
}
