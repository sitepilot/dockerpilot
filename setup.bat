@echo off
set dockerpilot=%cd%\bin
echo Adding %dockerpilot% to PATH...

if exist %dockerpilot% setx PATH "%dockerpilot%"
if exist %dockerpilot% set PATH=%PATH%;%dockerpilot%

echo Done!
pause
