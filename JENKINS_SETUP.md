# Jenkins Configuration for HUB Application

## Quick Start - Docker Setup

### Start Jenkins with Docker Compose

```bash
# Navigate to project directory
cd c:\xampp\htdocs\hub2

# Start all services (Jenkins, App, Database)
docker-compose up -d

# View Jenkins logs
docker-compose logs -f jenkins
```

Jenkins will be available at: **http://localhost:9090**

### Get Jenkins Initial Admin Password

```bash
# View the initial setup password
docker-compose exec jenkins cat /var/jenkins_home/secrets/initialAdminPassword
```

This password is needed for first-time setup in the web UI.

## Manual Setup (If not using Docker Compose)

### Prerequisites
- Docker and Docker Compose installed
- Port 9090 available on your machine

### 1. Build and Start Jenkins

```powershell
# PowerShell - Navigate to project directory
cd c:\xampp\htdocs\hub2

# Build custom Jenkins image with Docker support
docker build -f Dockerfile.jenkins -t hub-jenkins:latest .

# Run Jenkins container with Docker socket mounted
docker run -d `
  --name hub-app-jenkins `
  -p 9090:8080 `
  -p 50000:50000 `
  -v jenkins-data:/var/jenkins_home `
  -v /var/run/docker.sock:/var/run/docker.sock `
  -v /usr/bin/docker:/usr/bin/docker `
  --restart unless-stopped `
  hub-jenkins:latest

# Get initial admin password
docker logs hub-app-jenkins | Select-String "initialAdminPassword"
```

### 2. Initial Jenkins Setup (Web UI)

1. Open http://localhost:9090
2. Copy the initial admin password from logs
3. Complete the setup wizard:
   - Unlock Jenkins
   - Create Admin User
   - Install suggested plugins
   - Configure Instance

### 3. Add API Token Credential

1. Go to **Manage Jenkins** → **Manage Users**
2. Click on your admin user
3. Click **Configure** (or **API Token** tab)
4. Click **Add new Token**
5. Name it: `hub-app-token`
6. Copy the generated token
7. Add it to Jenkins credentials:
   - Go to **Manage Jenkins** → **Manage Credentials**
   - Click **Global** under "Stores scoped to Jenkins"
   - Click **Add Credentials**
   - Type: **Secret text**
   - Secret: `8a8e5625aeba705a5205f55f5ac06717` (or your actual token)
   - ID: `jenkins-api-token`
   - Save

### 4. Create Pipeline Job

1. Click **+ New Item**
2. Enter name: `hub-app-pipeline`
3. Select **Pipeline**
4. Click **OK**
5. Under "Pipeline":
   - Select: **Pipeline script from SCM**
   - SCM: **Git**
   - Repository URL: `https://github.com/gibsongelera/hir.git`
   - Branch: `*/main`
   - Script Path: `Jenkinsfile`
6. Save

### 5. Trigger Build

**Option A: Web UI**
- Go to `hub-app-pipeline` job
- Click **Build Now**

**Option B: Jenkins API (PowerShell)**

```powershell
$token = "your-api-token"
$username = "admin"  # your Jenkins username
$jenkinsUrl = "http://localhost:9090"
$jobName = "hub-app-pipeline"

# Create auth header
$base64 = [Convert]::ToBase64String([Text.Encoding]::ASCII.GetBytes("${username}:${token}"))
$headers = @{"Authorization" = "Basic $base64"}

# Get CSRF crumb
$crumbUrl = "$jenkinsUrl/crumbIssuer/api/json"
$crumb = (Invoke-WebRequest -Uri $crumbUrl -Headers $headers -UseBasicParsing).Content | ConvertFrom-Json

# Trigger build
$buildUrl = "$jenkinsUrl/job/$jobName/build"
$buildHeaders = $headers + @{$crumb.crumbRequestField = $crumb.crumb}

Invoke-WebRequest -Uri $buildUrl -Method POST -Headers $buildHeaders -UseBasicParsing
Write-Host "Build triggered!"
```

## Docker Networking

Jenkins can access other services via their container names:
- **Web App**: http://web (or http://web/hub1 for application)
- **Database**: db:3306 (hostname: `db`)

## Troubleshooting

### Jenkins won't start
```bash
# Check logs
docker-compose logs jenkins

# Restart
docker-compose restart jenkins

# Rebuild image if needed
docker-compose build --no-cache jenkins
```

### Jenkins can't access Docker
- Verify Docker socket is mounted: `/var/run/docker.sock:/var/run/docker.sock`
- Check jenkins user is in docker group in Dockerfile
- Restart Jenkins container

### Port 9090 already in use
Change in docker-compose.yml:
```yaml
ports:
  - "8080:8080"  # Use 8080 instead of 9090
```

### Can't trigger builds via API
1. Verify API token in credentials
2. Check user has build trigger permissions
3. Ensure CSRF crumb is included (see PowerShell script above)
4. Verify job exists and is configured correctly

## File Structure

```
├── Dockerfile              # PHP application container
├── Dockerfile.jenkins      # Jenkins container with Docker support
├── docker-compose.yml      # Multi-service orchestration
├── Jenkinsfile            # Pipeline definition
├── init-jenkins.sh        # Jenkins initialization script
├── JENKINS_SETUP.md       # This file
└── hub1/                  # Application code
```

## Environment Variables

Edit `docker-compose.yml` to customize:
- `JENKINS_OPTS`: Jenkins startup options
- `DATABASE_HOST`, `DATABASE_USER`, etc.: App database settings

## Next Steps

1. ✅ Start services: `docker-compose up -d`
2. ✅ Access Jenkins: http://localhost:9090
3. ✅ Get admin password: `docker-compose logs jenkins`
4. ✅ Complete setup wizard
5. ✅ Add API credentials
6. ✅ Create pipeline job
7. ✅ Test build trigger

## Support

For issues, check:
- Jenkins logs: `docker-compose logs jenkins`
- Docker container status: `docker ps`
- Network connectivity: `docker network inspect hub-network`
