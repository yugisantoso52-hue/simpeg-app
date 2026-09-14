@echo off
echo === Membuka Firewall Port 80 untuk Laragon Apache ===
netsh advfirewall firewall add rule name="Laragon-Apache-HTTP-Port80" protocol=TCP dir=in localport=80 action=allow profile=any
echo.
echo === Rule berhasil! Laragon bisa diakses via http://172.30.22.156/simpeg ===
pause
