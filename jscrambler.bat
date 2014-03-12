@echo off

rem -------------------------------------------------------------
rem  JScrambler command line script for Windows.
rem  This is the bootstrap script for running jscrambler.php on Windows.
rem -------------------------------------------------------------

@setlocal

set BIN_PATH=%~dp0

if "%PHP_COMMAND%" == "" set PHP_COMMAND=php.exe

"%PHP_COMMAND%" "%BIN_PATH%jscrambler.php" %*

@endlocal