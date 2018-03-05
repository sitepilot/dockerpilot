@echo off
set dockerpilot=%cd%\bin
echo Adding %dockerpilot% to PATH...

if exist %dockerpilot% setx PATH "%dockerpilot%"
if exist %dockerpilot% set PATH=%PATH%;%dockerpilot%

cd ../
if not exist config.php (
    echo Copying configuration...
    copy %cd%\source\config-example.php config.php
)

echo Installing packages...
composer install --no-dev

echo Done!
pause
