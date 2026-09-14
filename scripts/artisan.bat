@echo off
set PHPRC=C:\laragon\bin\php\php-8.4.12-nts-Win32-vs17-x64
set PHP=C:\laragon\bin\php\php-8.4.12-nts-Win32-vs17-x64\php.exe
set ARTISAN=C:\laragon\www\simpeg\artisan
echo Menjalankan: %PHP% %ARTISAN% %*
"%PHP%" "%ARTISAN%" %*
