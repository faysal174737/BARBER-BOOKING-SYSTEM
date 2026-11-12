$ErrorActionPreference = 'Stop' # Stop the launcher if a service-start operation fails.
$localDbPort = if ($env:BARBER_DB_PORT) { [int]$env:BARBER_DB_PORT } else { 3306 } # Use the configured database port, or the existing default port 3306.
$dbConnection = Test-NetConnection -ComputerName 127.0.0.1 -Port $localDbPort -InformationLevel Quiet -WarningAction SilentlyContinue # Check whether the local database port is already accepting connections.
if (-not $dbConnection) { # Start the database only when that port is not reachable.
    if (Get-Process mysqld -ErrorAction SilentlyContinue) { throw 'MySQL is already running on another port. Set BARBER_DB_PORT to its port before starting the project.' } # Avoid starting another database over a server that is already running elsewhere.
    Start-Process -FilePath 'C:\xampp\mysql\bin\mysqld.exe' -ArgumentList '--defaults-file=C:\xampp\mysql\bin\my.ini',"--port=$localDbPort",'--bind-address=127.0.0.1' -WindowStyle Hidden # Launch the existing local MariaDB service in a hidden window.
} # End the database-start branch.
if (-not (Get-Process httpd -ErrorAction SilentlyContinue)) { # Check whether the existing Apache process is already running.
    Start-Process -FilePath 'C:\xampp\apache\bin\httpd.exe' -WorkingDirectory 'C:\xampp\apache\bin' -WindowStyle Hidden # Launch the existing web server in a hidden window.
} # End the web-server-start branch.
if (-not (Get-Process ollama -ErrorAction SilentlyContinue)) { # Check whether the optional local model service is already running.
    $ollamaExecutable = Join-Path $env:LOCALAPPDATA 'Programs\Ollama\ollama.exe' # Locate the existing installed local-model executable.
    Start-Process -FilePath $ollamaExecutable -ArgumentList 'serve' -WindowStyle Hidden # Start the existing optional service in a hidden window.
} # End the optional-service-start branch.
Write-Output 'Open http://localhost/Barber_Booking_System/ after the services start.' # Print the existing local prototype address.
