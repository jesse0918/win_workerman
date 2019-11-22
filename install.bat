REM: install.bat for workerman-for-win
@echo off

set cxDir=%~dp0
set wraper=%cxDir%srvany.exe
set appDir=%cxDir%Applications\OA\
set regDir=HKEY_LOCAL_MACHINE\SYSTEM\ControlSet001\Services\
set phpDir=D:\WebServer\Php

set cxPHP=%phpDir%php.exe

REM ########install start_websocket ########
set cxSrv=workman_websocket
set cxApp=%appDir%start_websocket.php

set cxReg=%regDir%%cxSrv%\Parameters\
instsrv %cxSrv% %wraper%
reg add %cxReg% /v AppDirectory /t REG_SZ /d "%phpDir%" /f
reg add %cxReg% /v Application /t REG_SZ /d "%cxPHP%" /f 
reg add %cxReg% /v AppParameters /t REG_SZ /d "%cxApp%" /f
