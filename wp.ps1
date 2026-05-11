#!/usr/bin/env pwsh
# WP-CLI wrapper for the running kumiai_wp container.
#
# Usage:
#   .\wp.ps1 user list
#   .\wp.ps1 post list --post_type=post --format=table
#   .\wp.ps1 option get siteurl
#   .\wp.ps1 search-replace 'http://old' 'https://new' --dry-run
#
# PowerShell gotcha — quote any flag whose VALUE contains commas:
#   GOOD:  .\wp.ps1 user list "--fields=ID,user_login,user_email"
#   BAD:   .\wp.ps1 user list  --fields=ID,user_login,user_email
# (PS 5.1 tokenizes unquoted commas as argument separators.)

$ErrorActionPreference = 'Stop'

# Verify the main WP container is up — wp-cli reuses its volumes & network.
$running = docker ps --filter "name=^kumiai_wp$" --format "{{.Names}}"
if ($running -ne 'kumiai_wp') {
    Write-Host "Container 'kumiai_wp' is not running. Start it first with: docker compose up -d" -ForegroundColor Red
    exit 1
}

# Discover the network kumiai_wp is attached to (handles non-default project names).
$network = docker inspect kumiai_wp --format '{{range $k, $v := .NetworkSettings.Networks}}{{$k}}{{end}}'

# Inherit DB credentials from the running container so .env changes propagate automatically.
$envDump = docker inspect kumiai_wp --format '{{range .Config.Env}}{{println .}}{{end}}'
function Get-EnvValue($name) {
    ($envDump | Select-String "^${name}=" | ForEach-Object { ($_ -split '=', 2)[1] } | Select-Object -First 1)
}
$dbUser = Get-EnvValue 'WORDPRESS_DB_USER'
$dbPass = Get-EnvValue 'WORDPRESS_DB_PASSWORD'
$dbName = Get-EnvValue 'WORDPRESS_DB_NAME'

$dockerArgs = @(
    'run', '--rm',
    '--network', $network,
    '--volumes-from', 'kumiai_wp',
    '--user', '33:33',
    '-e', 'WORDPRESS_DB_HOST=db:3306',
    '-e', "WORDPRESS_DB_USER=$dbUser",
    '-e', "WORDPRESS_DB_PASSWORD=$dbPass",
    '-e', "WORDPRESS_DB_NAME=$dbName",
    'wordpress:cli'
) + $args

& docker @dockerArgs
exit $LASTEXITCODE
