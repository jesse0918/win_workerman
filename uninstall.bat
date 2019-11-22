REM: uninstall.bat for workerman-for-win
@echo off

set regDir=HKEY_LOCAL_MACHINE\SYSTEM\ControlSet001\Services\

reg delete %regDir%workman_websocket\Parameters
instsrv.exe workman_websocket remove
