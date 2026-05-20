# Jenkins Configuration for HUB Application

## Prerequisites
- Jenkins 2.375+
- Docker and Docker CLI installed on Jenkins agent
- Git plugin
- Pipeline plugin

## Setup Instructions

### 1. Add Jenkins Credentials

1. Go to **Jenkins Dashboard** → **Manage Jenkins** → **Manage Credentials**
2. Click **Global** under **Stores scoped to Jenkins**
3. Click **Add Credentials** (top left)
4. Select **Secret text** from the dropdown
5. Paste the API Token: `8a8e5625aeba705a5205f55f5ac06717`
6. Set the ID as: `jenkins-api-token-8a8e5625aeba705a5205f55f5ac06717`
7. Set Description: `Jenkins API Token for HUB Application`
8. Click **Create**

### 2. Create a New Pipeline Job

1. Go to **Jenkins Dashboard** → **New Item**
2. Enter job name: `hub-app-pipeline`
3. Select **Pipeline**
4. Click **OK**

### 3. Configure Pipeline

In the Pipeline configuration:

- **Pipeline script from SCM**: Git
- **Repository URL**: `https://github.com/gibsongelera/hir.git`
- **Branch**: `*/main`
- **Script Path**: `Jenkinsfile`

### 4. Build Triggers

Add triggers to automatically run the pipeline:
- **Poll SCM**: `H/15 * * * *` (every 15 minutes)
- **GitHub hook trigger**: If you have GitHub webhook configured
- **Manual trigger**: Via build button

## Docker Setup

### Option 1: Using Docker Compose (Local Testing)

```bash
docker-compose up -d
```

This will:
- Build the PHP application Docker image
- Start Apache web server on port 80
- Start MySQL database
- Initialize database from SQL file

### Option 2: Using Docker CLI

```bash
# Build image
docker build -t hub-app:latest .

# Run container
docker run -d \
  --name hub-app-container \
  -p 80:80 \
  -e DATABASE_HOST=localhost \
  -e DATABASE_USER=root \
  -e DATABASE_PASSWORD=root \
  -e DATABASE_NAME=campus_relief_hub \
  hub-app:latest
```

## Remote Execution

The Jenkinsfile is configured for remote execution via Jenkins API:

### Using Jenkins API to Trigger Build

```bash
# Using API token authentication
curl -X POST http://localhost:9090/job/hub-app-pipeline/build \
  -H "Authorization: Bearer 8a8e5625aeba705a5205f55f5ac06717"
```

### Using Basic Auth with Jenkins Username

```bash
curl -X POST http://localhost:9090/job/hub-app-pipeline/buildWithParameters \
  -u username:8a8e5625aeba705a5205f55f5ac06717 \
  -F token=hub-app-trigger
```

## Environment Variables (Optional)

Create a `.env` file in the project root to override defaults:

```bash
DB_HOST=localhost
DB_USER=root
DB_PASSWORD=your_password
DB_NAME=campus_relief_hub
```

## Accessing the Application

After successful deployment:
- **Web Application**: http://localhost/hub1/
- **Admin Panel**: http://localhost/hub1/admin/
- **Login Page**: http://localhost/hub1/auth/login.php

## Troubleshooting

### Docker Build Fails
- Ensure PHP 8.2 image is available
- Check Docker daemon is running
- Verify all dependencies in Dockerfile are correct

### Pipeline Fails to Connect to Database
- Ensure MySQL container is running
- Check database credentials in environment variables
- Verify database initialization SQL file exists

### Port 80 Already in Use
- Stop the XAMPP Apache server: `apache_stop.bat`
- Or use a different port: Change `80:80` to `8080:80`

## File Structure

```
.
├── Dockerfile              # Docker image configuration
├── docker-compose.yml      # Docker Compose configuration
├── Jenkinsfile            # Jenkins CI/CD pipeline
├── .dockerignore           # Docker ignore file
├── JENKINS_SETUP.md        # This file
├── hub1/                   # Main application
├── config/                 # Database configuration
└── database/               # SQL initialization files
```

## Next Steps

1. Commit these files to GitHub
2. Set up Jenkins credentials with the API token
3. Create and configure the pipeline job
4. Run the first build manually to test
5. Configure build triggers for automated deployments
